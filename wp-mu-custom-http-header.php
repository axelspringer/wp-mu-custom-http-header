<?php

// @codingStandardsIgnoreFile

add_filter( 'wp_headers', function ( $headers, WP $wp ) {
    if ( is_string( getenv( 'WP_LAYER' ) ) && strtolower( getenv( 'WP_LAYER' ) ) === 'backend' ) {
        return $headers;
    }

    function getPageType( array $vars = array() ) {

        if ( count( $vars ) === 0 ) {
            return 'home';
        }

        $name        = isset( $vars['name'] ) ? $vars['name'] : null;
        $hasName     = ! is_null( $name );
        $category    = isset( $vars['category_name'] ) ? isset( $vars['category_name'] ) : null;
        $hasCategory = ! is_null( $category );

        if ( $hasName && $hasCategory ) {
            return 'article';
        }


        if ( ! $hasName && $hasCategory ) {
            return 'channel';
        }

        if ( isset( $vars['json'] ) ) {
            return 'json';
        }

        if ( isset( $vars['s'] ) ) {
            return 'search';
        }

        return '';
    }

    $cacheControlConf = [
        'article' => [ 'maxAge' => 10 * 60 ],
        'channel' => [ 'maxAge' => 3 * 60 ],
        'home'    => [ 'maxAge' => 120 ],
        'json'    => [ 'maxAge' => 120 ],
        'search'  => [ 'maxAge' => 15 * 60 ],
    ];


    $pageType = getPageType( $wp->query_vars );

    if ( $pageType === '' ) {
        return $headers;
    }

    if ( ! array_key_exists( $pageType, $cacheControlConf ) ) {
        return $headers;
    }

    $config          = $cacheControlConf[ $pageType ];
    $staleRevalidate = 3600 * 24;
    $staleError      = 3600 * 24 * 3;

    //http://www.sobstel.org/blog/http-cache-stale-while-revalidate-stale-if-error/

    $headers['Cache-Control'] = "max-age={$config['maxAge']}, stale-while-revalidate=$staleRevalidate, stale-if-error=$staleError";

    return $headers;
}, 1, 2 );