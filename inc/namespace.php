<?php
/**
 * Main Traduttore Registry library.
 *
 * @since 1.0.0
 *
 * @package Required\Traduttore_Registry
 */

namespace Required\Traduttore_Registry;

use \DateTime;

/**
 * Adds a new project to load translations for.
 *
 * @since 1.0.0
 *
 * @param string $type    Project type. Either plugin or theme.
 * @param string $slug    Project slug.
 * @param string $api_url Full GlotPress API URL for the project.
 */
function add_project( $type, $slug, $api_url ) {
	/**
	 * Short-circuits translations API requests for private projects.
	 */
	add_filter(
		'translations_api',
		function ( $result, $requested_type, $args ) use ( $type, $slug, $api_url ) {
			if ( $type === $requested_type && $slug === $args['slug'] ) {
				return get_translations( $type, $args['slug'], $api_url );
			}

			return $result;
		},
		10,
		3
	);

	/**
	 * Filters the translations transients to include the private plugin or theme.
	 *
	 * @see wp_get_translation_updates()
	 */
	add_filter(
		'site_transient_update_' . $type . 's',
		function ( $value ) use ( $type, $slug, $api_url ) {
			if ( ! $value ) {
				$value = (object) [];
			}

			if ( ! isset( $value->translations ) ) {
				$value->translations = [];
			}

			$installed_translations = wp_get_installed_translations( $type . 's' );
			$translations           = get_translations( $type, $slug, $api_url );

			foreach ( (array) $translations['translations'] as $translation ) {
				if ( isset( $installed_translations[ $slug ][ $translation['language'] ] ) && $translation['updated'] ) {
					$local  = new DateTime( $installed_translations[ $slug ][ $translation['language'] ]['PO-Revision-Date'] );
					$remote = new DateTime( $translation['updated'] );

					if ( $local >= $remote ) {
						continue;
					}
				}

				$translation['type'] = $type;
				$translation['slug'] = $slug;

				$value->translations[] = $translation;
			}

			return $value;
		}
	);
}

/**
 * Gets the translations for a given project.
 *
 * @since 1.0.0
 *
 * @param string $type Project type. Either plugin or theme.
 * @param string $slug Project slug.
 * @param string $url  Full GlotPress API URL for the project.
 *
 * @return array Translation data.
 */
function get_translations( $type, $slug, $url ) {
	$transient = $type . '_translations_' . $slug;

	$results = get_site_transient( $transient );

	if ( false === $results ) {
		$res = json_decode( wp_remote_retrieve_body( wp_remote_get( $url, [ 'timeout' => 3 ] ) ), true );

		if ( $res ) {
			set_site_transient( $transient, $res, HOUR_IN_SECONDS * 12 );

			return $res;
		}
	}

	return $results;
}
