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
	 * Example usage:
	 * ```php
	 * $links = [
	 *     'docs' => [
	 *         'label' => 'Documentation',
	 *         'url'   => 'https://example.com/docs'
	 *     ],
	 *     'settings' => [
	 *         'action' => true,
	 *         'label'  => 'Settings',
	 *         'url'    => admin_url('admin.php?page=my-settings'),
	 *         'capability' => 'manage_options'
	 *     ]
	 * ];
	 *
	 * register_plugin_links(__FILE__, $links);
	 * ```
	 *
	 * @param string        $file           Plugin file path
	 * @param array         $external_links Array of external links
	 * @param array         $utm_args       Optional UTM parameters
	 * @param callable|null $error_callback Optional error callback
	 *
	 * @return PluginLinks|null PluginLinks instance or null on failure
	 */
	function register_plugin_links(
		string $file,
		array $external_links = [],
		array $utm_args = [],
		?callable $error_callback = null
	): ?PluginLinks {
		try {
			return PluginLinks::instance( $file )->register( $external_links, $utm_args );
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
	 * Example usage:
	 * ```php
	 * register_edd_plugin_links(
	 *     __FILE__,
	 *     'extensions',
	 *     'my_extension',
	 *     [
	 *         'pro' => [
	 *             'label' => 'Upgrade to Pro',
	 *             'url'   => 'https://example.com/pro'
	 *         ]
	 *     ]
	 * );
	 * ```
	 *
	 * @param string        $file             Plugin file path
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

		return register_plugin_links( $file, $external_links, $utm_args, $error_callback );
	}
endif;

/**
 * Usage Examples:
 *
 * 1. Basic registration:
 * ```php
 * $links = [
 *     'docs' => [
 *         'label' => 'Documentation',
 *         'url'   => 'https://example.com/docs'
 *     ],
 *     'settings' => [
 *         'action'     => true,
 *         'label'      => 'Settings',
 *         'capability' => 'manage_options',
 *         'url'        => admin_url('admin.php?page=my-settings')
 *     ]
 * ];
 *
 * register_plugin_links(__FILE__, $links);
 * ```
 *
 * 2. With UTM tracking:
 * ```php
 * register_plugin_links(
 *     __FILE__,
 *     $links,
 *     [
 *         'utm_source'   => 'plugin-page',
 *         'utm_campaign' => 'documentation'
 *     ]
 * );
 * ```
 *
 * 3. EDD integration:
 * ```php
 * register_edd_plugin_links(
 *     __FILE__,
 *     'extensions',
 *     'my_extension',
 *     [
 *         'pro' => [
 *             'label' => 'Upgrade to Pro',
 *             'url'   => 'https://example.com/pro'
 *         ]
 *     ]
 * );
 * ```
 */