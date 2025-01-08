<?php
/**
 * Plugin Links Registration Helper Functions
 *
 * @package     ArrayPress\WP\Register
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 */

declare( strict_types=1 );

use ArrayPress\WP\Register\PluginLinks;

if ( ! function_exists( 'register_plugin_links' ) ):
	/**
	 * Register plugin links
	 *
	 * @param string        $file           Plugin file path
	 * @param string        $prefix         Unique prefix for this plugin
	 * @param array         $external_links Array of external links
	 * @param array         $utm_args       Optional UTM parameters
	 * @param callable|null $error_callback Optional error callback
	 *
	 * @return PluginLinks|null PluginLinks instance or null on failure
	 */
	function register_plugin_links(
		string $file,
		string $prefix,
		array $external_links = [],
		array $utm_args = [],
		?callable $error_callback = null
	): ?PluginLinks {
		try {
			return PluginLinks::instance( $prefix )->register( $file, $external_links, $utm_args );
		} catch ( Exception $e ) {
			if ( is_callable( $error_callback ) ) {
				call_user_func( $error_callback, $e );
			}

			return null;
		}
	}
endif;

if ( ! function_exists( 'register_edd_plugin_links' ) ):
	/**
	 * Register EDD-specific plugin links
	 *
	 * @param string        $file             Plugin file path
	 * @param string        $prefix           Unique prefix for this plugin
	 * @param string        $settings_tab     EDD settings tab
	 * @param string        $settings_section EDD settings section
	 * @param array         $external_links   Additional external links
	 * @param array         $utm_args         Optional UTM parameters
	 * @param callable|null $error_callback   Optional error callback
	 *
	 * @return PluginLinks|null PluginLinks instance or null on failure
	 */
	function register_edd_plugin_links(
		string $file,
		string $prefix,
		string $settings_tab = '',
		string $settings_section = '',
		array $external_links = [],
		array $utm_args = [],
		?callable $error_callback = null
	): ?PluginLinks {
		$default_links = [
			'support'    => [
				'label'      => __( 'Support', 'arraypress' ),
				'url'        => 'https://arraypress.com/support',
				'capability' => 'manage_options'
			],
			'extensions' => [
				'label'      => __( 'Extensions', 'arraypress' ),
				'url'        => 'https://arraypress.com/plugins',
				'capability' => 'manage_options'
			]
		];

		// Merge with default links
		$external_links = wp_parse_args( $external_links, $default_links );

		// Add settings link if EDD is active and paths provided
		if ( function_exists( 'edd_get_admin_url' ) && $settings_tab && $settings_section ) {
			$external_links['settings'] = [
				'action'     => true,
				'label'      => __( 'Settings', 'arraypress' ),
				'url'        => edd_get_admin_url( [
					'page'    => 'edd-settings',
					'tab'     => $settings_tab,
					'section' => $settings_section,
				] ),
				'utm'        => false,
				'new_tab'    => false,
				'capability' => 'manage_options',
				'conditions' => function () {
					return function_exists( 'edd_get_admin_url' );
				}
			];
		}

		return register_plugin_links( $file, $prefix, $external_links, $utm_args, $error_callback );
	}
endif;