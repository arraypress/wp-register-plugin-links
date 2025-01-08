<?php
/**
 * Plugin Links Registration Manager
 *
 * @package     ArrayPress\WP\Register
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\WP\Register;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use InvalidArgumentException;

/**
 * Class PluginLinks
 *
 * @since 1.0.0
 */
class PluginLinks {

	/**
	 * Instance of this class.
	 *
	 * @var self[] Array of instances
	 */
	private static array $instances = [];

	/**
	 * Collection of registered plugins
	 *
	 * @var array
	 */
	private array $registered_plugins = [];

	/**
	 * Debug mode status
	 *
	 * @var bool
	 */
	private bool $debug = false;

	/**
	 * Get instance of this class.
	 *
	 * @param string $prefix Unique identifier for the plugin instance
	 *
	 * @return self Instance of this class.
	 */
	public static function instance( string $prefix = 'default' ): self {
		if ( ! isset( self::$instances[ $prefix ] ) ) {
			self::$instances[ $prefix ] = new self();
		}

		return self::$instances[ $prefix ];
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->debug = defined( 'WP_DEBUG' ) && WP_DEBUG;
		add_filter( 'plugin_action_links', [ $this, 'filter_plugin_action_links' ], 10, 4 );
		add_filter( 'plugin_row_meta', [ $this, 'filter_plugin_row_meta' ], 10, 4 );
	}

	/**
	 * Register links for a plugin
	 *
	 * @param string $file           Plugin file path
	 * @param array  $external_links Array of external links
	 * @param array  $utm_args       Optional UTM parameters
	 *
	 * @return self Instance for method chaining
	 */
	public function register( string $file, array $external_links = [], array $utm_args = [] ): self {
		if ( empty( $file ) ) {
			throw new InvalidArgumentException( 'Plugin file path must be provided.' );
		}

		$basename = plugin_basename( $file );

		$this->registered_plugins[ $basename ] = [
			'file'           => $file,
			'external_links' => $this->prepare_links( $external_links ),
			'utm_args'       => $this->prepare_utm_args( $utm_args )
		];

		$this->log( sprintf( 'Registered plugin links for: %s', $basename ) );

		return $this;
	}

	/**
	 * Prepare links with defaults
	 *
	 * @param array $links Raw links array
	 *
	 * @return array Processed links
	 */
	protected function prepare_links( array $links ): array {
		$processed = [];

		foreach ( $links as $key => $link ) {
			$processed[ sanitize_key( $key ) ] = wp_parse_args( $link, [
				'action'     => false,
				'label'      => '',
				'url'        => '',
				'utm'        => true,
				'new_tab'    => true,
				'capability' => '',
				'conditions' => null
			] );
		}

		return $processed;
	}

	/**
	 * Prepare UTM parameters
	 *
	 * @param array $args UTM arguments
	 *
	 * @return array Processed UTM args
	 */
	protected function prepare_utm_args( array $args ): array {
		return wp_parse_args( $args, [
			'utm_source'   => 'plugins-page',
			'utm_medium'   => 'plugin-row',
			'utm_campaign' => 'admin'
		] );
	}

	/**
	 * Filter action links
	 *
	 * @param array  $links       Current links
	 * @param string $plugin_file Plugin path
	 *
	 * @return array Modified links
	 */
	public function filter_plugin_action_links( array $links, string $plugin_file ): array {
		if ( isset( $this->registered_plugins[ $plugin_file ] ) ) {
			$config = $this->registered_plugins[ $plugin_file ];
			$links  = $this->process_links( $links, $config, 'action' );
		}

		return $links;
	}

	/**
	 * Filter row meta
	 *
	 * @param array  $links       Current links
	 * @param string $plugin_file Plugin path
	 *
	 * @return array Modified links
	 */
	public function filter_plugin_row_meta( array $links, string $plugin_file ): array {
		if ( isset( $this->registered_plugins[ $plugin_file ] ) ) {
			$config = $this->registered_plugins[ $plugin_file ];
			$links  = $this->process_links( $links, $config, 'row_meta' );
		}

		return $links;
	}

	/**
	 * Process links based on configuration
	 *
	 * @param array  $existing_links Current links
	 * @param array  $config         Plugin configuration
	 * @param string $position       Link position type
	 *
	 * @return array Processed links
	 */
	protected function process_links( array $existing_links, array $config, string $position ): array {
		foreach ( $config['external_links'] as $key => $link ) {
			// Check position
			if ( ( $position === 'action' && ! $link['action'] ) ||
			     ( $position === 'row_meta' && $link['action'] ) ) {
				continue;
			}

			// Check capability
			if ( ! empty( $link['capability'] ) && ! current_user_can( $link['capability'] ) ) {
				continue;
			}

			// Check conditions
			if ( is_callable( $link['conditions'] ) && ! call_user_func( $link['conditions'] ) ) {
				continue;
			}

			// Generate link
			if ( ! empty( $link['url'] ) && ! empty( $link['label'] ) ) {
				$url = $link['utm'] ? $this->add_utm_params( $link['url'], $config['utm_args'] ) : $link['url'];

				$attrs = array_filter( [
					'href'   => esc_url( $url ),
					'target' => $link['new_tab'] ? '_blank' : null,
					'rel'    => $link['new_tab'] ? 'noopener noreferrer' : null
				] );

				$attr_string = '';
				foreach ( $attrs as $attr => $value ) {
					$attr_string .= sprintf( ' %s="%s"', $attr, esc_attr( $value ) );
				}

				$existing_links[ $key ] = sprintf(
					'<a%s>%s</a>',
					$attr_string,
					wp_kses_post( $link['label'] )
				);
			}
		}

		return $existing_links;
	}

	/**
	 * Add UTM parameters to URL
	 *
	 * @param string $url  Base URL
	 * @param array  $args UTM parameters
	 *
	 * @return string URL with UTM parameters
	 */
	protected function add_utm_params( string $url, array $args ): string {
		return add_query_arg( $args, $url );
	}

	/**
	 * Log debug message
	 *
	 * @param string $message Message to log
	 * @param array  $context Optional context
	 */
	protected function log( string $message, array $context = [] ): void {
		if ( $this->debug ) {
			error_log( sprintf(
				'[Plugin Links] %s %s',
				$message,
				! empty( $context ) ? json_encode( $context ) : ''
			) );
		}
	}

}