<?php

/*
 * Plugin Name: Reviews for WPCF7
 * Plugin URI: https://github.com/nikolays93/reviews-and-questions
 * Description: Add site reviews and questions support with contact form 7.
 * Version: 1.0
 * Author: NikolayS93
 * Author URI: https://vk.com/nikolays_93
 * Author EMAIL: NikolayS93@ya.ru
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wpcf7-reviews
 * Domain Path: /languages/
 */

namespace NikolayS93\Reviews;

if ( !defined( 'ABSPATH' ) ) exit('You shall not pass');

if( ! defined( 'NikolayS93\Reviews\REVIEW_MARK' ) ) {
    define( 'NikolayS93\Reviews\REVIEW_MARK', '_review' );
}

require_once ABSPATH . "wp-admin/includes/plugin.php";

if (version_compare(PHP_VERSION, '5.3') < 0) {
    throw new \Exception('Plugin requires PHP 5.3 or above');
}

add_action( 'plugins_loaded', function() {
    load_plugin_textdomain( 'wpcf7-reviews', false, basename(__DIR__) . '/languages/' );

    add_action('wpcf7_mail_sent', function ( $contact_form ) {
        $submission = \WPCF7_Submission::get_instance();
        $posted_data = $submission->get_posted_data();

        $post_types = get_post_types();

        if( isset( $_REQUEST[ REVIEW_MARK ] ) ) {
            $post_type = sanitize_text_field( $_REQUEST[ REVIEW_MARK ] );
        }
        elseif( isset( $posted_data[ REVIEW_MARK ] ) ) {
            $post_type = $posted_data[ REVIEW_MARK ];
        }

        if( ! empty( $post_type ) && ! in_array( $post_type, $post_types, true ) ) {
            return $contact_form;
        }

        $current_date = date('Y-m-d H:i:s');
        $_post = array(
            'post_title' => 'Отзыв',
            'post_type' => $posted_data[ REVIEW_MARK ],
            'post_date' => date('Y-m-d H:i:s'),
            'post_status' => 'pending',
            'meta_input' => array(),
        );

        if( isset( $posted_data[ 'post_title' ] ) ) {
            $_post[ 'post_title' ] = $posted_data['post_title'];
            unset( $posted_data[ 'post_title' ] );
        }

        if( isset( $posted_data[ 'post_content' ] ) ) {
            $_post[ 'post_content' ] = $posted_data['post_content'];
            unset( $posted_data[ 'post_content' ] );
        }

        if( isset( $posted_data[ 'post_excerpt' ] ) ) {
            $_post[ 'post_excerpt' ] = $posted_data['post_excerpt'];
            unset( $posted_data[ 'post_excerpt' ] );
        }

        if ( is_user_logged_in() ) {
            $_post[ 'meta_input' ][ 'user_id' ] = wp_get_current_user()->ID;
        }

        array_walk( $posted_data, function( &$value, $key ) use ( &$_post ) {

            if( '_' !== substr($key, 0, 1) ) {
                $_post[ 'meta_input' ][ $key ] = $value;
            }

        } );

        $_post['post_title'] .= ' от ' . $current_date;

        require_once( ABSPATH . 'wp-blog-header.php' );
        wp_insert_post( $_post );
    }, 50, 1 );

}, 10 );
