<?php
/**
 * Plugin Name: Contact Form post collector
 * Plugin URI: https://github.com/websoldier/contact-form-post-collector
 * Description: Add site reviews and questions support with contact form 7.
 * Version: 1.0
 * Author: NikolayS93
 * Author URI: https://vk.com/nikolays_93
 * Author EMAIL: NikolayS93@ya.ru
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cf7save
 * Domain Path: /languages/
 *
 * @package cf7save
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'You shall not pass' );
}

if ( ! defined( 'CF7_SAVE' ) ) {
	define( 'CF7_SAVE', '_cf7_save' );
}

require_once ABSPATH . 'wp-admin/includes/plugin.php';

if ( ! function_exists( 'cf_save_post_when_sent' ) ) :
	/**
	 * Save post.
	 *
	 * @param  WPCF7_ContactForm $contact_form wpcf7 form instance.
	 * @return WPCF7_ContactForm $contact_form wpcf7 form instance.
	 */
	function cf_save_post_when_sent( $contact_form ) {
		// Do not pay attention without save mark.
		if ( ! isset( $_REQUEST[ CF7_SAVE ] ) ) {
			return $contact_form;
		}

		/**
		 * Current submission.
		 *
		 * @var WPCF7_Submission $submission
		 */
		$submission = \WPCF7_Submission::get_instance();

		/**
		 * Sanitized request form data.
		 *
		 * @var array $posted_data
		 */
		$posted_data = $submission->get_posted_data();

		/**
		 * Registred post types.
		 *
		 * @var array $post_types
		 */
		$post_types = get_post_types();

		/**
		 * Current requested form field type value.
		 *
		 * @var string $post_type
		 */
		$post_type = sanitize_text_field( wp_unslash( $_REQUEST[ CF7_SAVE ] ) );

		/**
		 * Check uncorrect post type.
		 *
		 * @link https://codex.wordpress.org/Debugging_in_WordPress
		 */
		if ( ! in_array( $post_type, $post_types, true ) ) {
			$wp_debug_display = defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY;
			if ( $wp_debug_display || ( ! $wp_debug_display && WP_DEBUG ) ) {
				esc_html_e( 'Post type not registred.', 'cf7save' );
				die();
			}

			return $contact_form;
		}

		$builtin_fields = array( 'post_title', 'post_content', 'post_excerpt' );

		$_post = array(
			'post_title'  => 'Отзыв от ' . wp_date( get_option( 'date_format' ) ),
			'post_type'   => $post_type,
			'post_date'   => current_time( 'mysql' ),
			'post_status' => 'pending',
			'meta_input'  => array(
				'user_id' => get_current_user_id(),
			),
		);

		array_walk(
			$posted_data,
			function( $value, $key ) use ( &$_post ) {

				if ( in_array( $key, $builtin_fields, true ) ) {
					$_post[ $key ] = $value;
				} elseif ( '_' !== substr( $key, 0, 1 ) ) {
					$_post['meta_input'][ $key ] = $value;
				}

			}
		);

		require_once ABSPATH . 'wp-blog-header.php';
		wp_insert_post( $_post );
		return $contact_form;
	}
endif;

if ( ! function_exists( 'cf_save_load' ) ) :
	/**
	 * Load this plugin.
	 */
	function cf_save_load() {
		load_plugin_textdomain( 'cf7save', false, basename( __DIR__ ) . '/languages/' );
		add_action( 'wpcf7_mail_sent', 'cf_save_post_when_sent', 50, 1 );
	}
endif;

add_action( 'plugins_loaded', 'cf_save_load', 10 );
