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

const TRANSIENT_KEY_PLUGIN = 'traduttore-registry-plugins';
const TRANSIENT_KEY_THEME  = 'traduttore-registry-themes';

/**
 * Adds a new project to load translations for.
 *
 * @since 1.0.0
 *
 * @param string $type    Project type. Either plugin or theme.
 * @param string $slug    Project directory slug.
 * @param string $api_url Full GlotPress API URL for the project.
 */
function add_project( $type, $slug, $api_url ) {
	if ( ! has_action( 'init', __NAMESPACE__ . '\register_clean_translations_cache' ) ) {
		add_action( 'init', __NAMESPACE__ . '\register_clean_translations_cache', 9999 );
	}

	/**
	 * Short-circuits translations API requests for private projects.
	 */
	add_filter(
		'translations_api',
		function ( $result, $requested_type, $args ) use ( $type, $slug, $api_url ) {
			if ( $type . 's' === $requested_type && $slug === $args['slug'] ) {
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
				$value = new \stdClass();
			}

			if ( ! isset( $value->translations ) ) {
				$value->translations = [];
			}

			$translations = get_translations( $type, $slug, $api_url );

			if ( ! isset( $translations['translations'] ) ) {
				return $value;
			}

			$installed_translations = wp_get_installed_translations( $type . 's' );

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
 * Registers actions for clearing translation caches.
 *
 * @since 2.0.0
 */
function register_clean_translations_cache() {
	$clear_plugin_translations = function() {
		clean_translations_cache( 'plugin' );
	};
	$clear_theme_translations  = function() {
		clean_translations_cache( 'theme' );
	};

	add_action( 'set_site_transient_update_plugins', $clear_plugin_translations );
	add_action( 'delete_site_transient_update_plugins', $clear_plugin_translations );

	add_action( 'set_site_transient_update_themes', $clear_theme_translations );
	add_action( 'delete_site_transient_update_themes', $clear_theme_translations );
}

/**
 * Clears existing translation cache for a given type.
 *
 * @since 2.0.0
 *
 * @param string $type Project type. Either plugin or theme.
 */
function clean_translations_cache( $type ) {
	$transient_key = constant( __NAMESPACE__ . '\TRANSIENT_KEY_' . strtoupper( $type ) );
	$translations  = get_site_transient( $transient_key );

	if ( ! is_object( $translations ) ) {
		return;
	}

	// Don't delete the cache if there are multiple requests.
	$timeout          = 15;
	$time_not_changed = isset( $translations->_last_checked ) && ( time() - $translations->_last_checked ) > $timeout;

	if ( ! $time_not_changed ) {
		return;
	}

	delete_site_transient( $transient_key );
}

/**
 * Gets the translations for a given project.
 *
 * @since 1.0.0
 *
 * @param string $type Project type. Either plugin or theme.
 * @param string $slug Project directory slug.
 * @param string $url  Full GlotPress API URL for the project.
 *
 * @return array Translation data.
 */
function get_translations( $type, $slug, $url ) {
	$transient_key = constant( __NAMESPACE__ . '\TRANSIENT_KEY_' . strtoupper( $type ) );
	$translations  = get_site_transient( $transient_key );

	if ( ! is_object( $translations ) ) {
		$translations = new \stdClass();
	}

	if ( isset( $translations->{$slug} ) && is_array( $translations->{$slug} ) ) {
		return $translations->{$slug};
	}

	$result = json_decode( wp_remote_retrieve_body( wp_remote_get( $url, [ 'timeout' => 2 ] ) ), true );
	if ( is_array( $result ) ) {
		$translations->{$slug}       = $result;
		$translations->_last_checked = time();

		set_site_transient( $transient_key, $translations );
		return $result;
	}

	// Nothing found.
	return [];
}
