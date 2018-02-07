<?php

namespace Required\Traduttore_Registry;

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
    add_filter( 'translations_api', function ( $result, $requested_type, $args ) use ( $type, $slug, $api_url ) {
		if ( $type === $requested_type && $slug === $args['slug'] ) {
			return get_translations( $type, $args['slug'], $api_url );
		}

		return $result;
	}, 10, 3 );

    /**
     * Filters the translations transients to include the private plugin or theme.
     */
    add_filter( 'site_transient_update_' . $type . 's', function ( $value ) use ( $type, $slug, $api_url ) {
        if ( ! isset( $value->translations ) ) {
            $value->translations = [];
        }

        $translations = get_translations( $type, $slug, $api_url );

        foreach ( (array) $translations['translations'] as $translation ) {
            $translation['type'] = $type;
            $translation['slug'] = $slug;

            $value->translations[] = $translation;
        }

        return $value;
    } );
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
		$res = json_decode( wp_remote_retrieve_body( wp_remote_get( $url, [ 'timeout'   => 3 ] ) ), true );

		if ( $res ) {
			set_site_transient( $transient, $res, HOUR_IN_SECONDS * 12 );

			return $res;
		}
	}

	return $results;
}
