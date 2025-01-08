# WordPress Plugin Links Registration Library

A comprehensive PHP library for managing WordPress plugin links, providing a robust solution for registering both action links and row meta links. This library offers features like capability checks, UTM tracking, conditional display, and seamless integration with Easy Digital Downloads.

## Features

- 🚀 Simple registration of plugin action links and row meta
- 🔒 Role-based capability checks for link visibility
- 📊 Built-in UTM parameter management
- 🎯 Conditional link display based on custom logic
- 🔄 Easy Digital Downloads (EDD) integration
- 🌐 External link management with new tab controls
- ✨ Clean, chainable API
- 🛠️ Comprehensive error handling
- 🔍 Debug logging support

## Requirements

- PHP 7.4 or higher
- WordPress 5.0 or higher

## Installation

You can install the package via composer:

```bash
composer require arraypress/wp-register-plugin-links
```

## Basic Usage

Here's a simple example of registering plugin links:

```php
// Define your links
$links = [
	'documentation' => [
		'label' => 'Documentation',
		'url'   => 'https://example.com/docs'
	],
	'settings'      => [
		'action'     => true,
		'label'      => 'Settings',
		'url'        => admin_url( 'admin.php?page=my-settings' ),
		'capability' => 'manage_options'
	]
];

// Register links
register_plugin_links( __FILE__, 'my-plugin', $links );
```

## Configuration Options

Each link can be configured with these options:

| Option | Type | Description | Default |
|--------|------|-------------|---------|
| `action` | bool | Whether to show as action link | `false` |
| `label` | string | Link text to display | Required |
| `url` | string | Link URL | Required |
| `utm` | bool | Whether to add UTM parameters | `true` |
| `new_tab` | bool | Open in new tab | `true` |
| `capability` | string | Required user capability | `''` |
| `conditions` | callable | Function returning bool for conditional display | `null` |

## Advanced Usage

### Conditional Display

Control link visibility based on custom conditions:

```php
$links = [
	'upgrade' => [
		'label'      => 'Upgrade to Pro',
		'url'        => 'https://example.com/pro',
		'capability' => 'manage_options',
		'conditions' => function () {
			return ! is_pro_version_active();
		}
	]
];
```

### UTM Tracking

Add UTM parameters to external links:

```php
$links = [
	'docs' => [
		'label' => 'Documentation',
		'url'   => 'https://example.com/docs',
		'utm'   => true
	]
];

$utm_args = [
	'utm_source'   => 'plugin-page',
	'utm_medium'   => 'plugin-row',
	'utm_campaign' => 'documentation'
];

register_plugin_links( __FILE__, 'my-plugin', $links, $utm_args );
```

### EDD Integration

Special integration for Easy Digital Downloads plugins:

```php
register_edd_plugin_links(
	__FILE__,          // Plugin file
	'my-plugin',       // Prefix
	'extensions',      // Settings tab
	'my_extension',    // Settings section
	[
		'pro' => [
			'label' => 'Upgrade to Pro',
			'url'   => 'https://example.com/pro'
		]
	]
);
```

### Error Handling

Handle registration errors with a custom callback:

```php
register_plugin_links(
	__FILE__,
	'my-plugin',
	$links,
	$utm_args,
	function ( $exception ) {
		error_log( 'Plugin links registration failed: ' . $exception->getMessage() );
	}
);
```

### Multiple Plugins Support

The library supports managing links for multiple plugins:

```php
// First plugin
register_plugin_links( __FILE__, 'plugin-one', $links_one );

// Second plugin
register_plugin_links( __FILE__, 'plugin-two', $links_two );
```

## Full Example

Here's a comprehensive example showing various features:

```php
class MyPlugin {
	public function init() {
		$this->register_plugin_links();
	}

	private function register_plugin_links() {
		$links = [
			'settings' => [
				'action'     => true,
				'label'      => 'Settings',
				'url'        => admin_url( 'admin.php?page=my-settings' ),
				'capability' => 'manage_options',
				'new_tab'    => false,
				'utm'        => false
			],
			'docs'     => [
				'label'      => 'Documentation',
				'url'        => 'https://example.com/docs',
				'capability' => 'read',
				'conditions' => [ $this, 'should_show_docs' ]
			],
			'pro'      => [
				'label'      => 'Upgrade to Pro',
				'url'        => 'https://example.com/pro',
				'capability' => 'manage_options',
				'conditions' => [ $this, 'is_free_version' ]
			]
		];

		$utm_args = [
			'utm_source'   => 'plugin-page',
			'utm_medium'   => 'plugin-row',
			'utm_campaign' => 'wordpress-admin'
		];

		register_plugin_links(
			__FILE__,
			'my-plugin',
			$links,
			$utm_args,
			[ $this, 'handle_registration_error' ]
		);
	}

	public function should_show_docs(): bool {
		return current_user_can( 'manage_options' ) || is_debug_mode_active();
	}

	public function is_free_version(): bool {
		return ! defined( 'MY_PLUGIN_PRO' );
	}

	public function handle_registration_error( Exception $e ): void {
		error_log( 'Plugin links registration failed: ' . $e->getMessage() );
	}
}
```

## Debug Mode

When `WP_DEBUG` is enabled, the library will log:
- Link registration attempts
- Capability checks
- Conditional evaluations
- Error messages

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request. For major changes, please open an issue first to discuss what you would like to change.

## License

This project is licensed under the GPL2+ License - see the LICENSE file for details.

## Support

For support, please use the [issue tracker](https://github.com/arraypress/wp-register-plugin-links/issues).