<?php
/**
 * File for adding CSS and Javascript files to pages.
 *
 * @package WordPress Boilerplate
 */

use Idleberg\ViteManifest\Manifest;

/**
 * Class for adding CSS and Javascript files to pages.
 */
class Assets {

	/**
	 * Manifest object used to retrieve distribution ready assets.
	 *
	 * @var Manifest
	 */
	protected $manifest;

	/**
	 * Enqueue assets.
	 */
	public function __construct() {
		$this->manifest = self::get_manifest();
		if ( $this->is_in_development_mode() ) {
			$this->add_hot_reload_assets( 'wp_head', 'src/main.js' );
			$this->add_hot_reload_assets( 'admin_head', 'src/admin.js' );
		} else {
			$this->enqueue_styles( 'wp_enqueue_scripts', 'src/main.js' );
			$this->enqueue_styles( 'admin_enqueue_scripts', 'src/admin.js' );
			$this->add_editor_styles( 'src/admin.js' );
		}
	}

	/**
	 * Registers a script for enqueuing.
	 *
	 * @param string $handle
	 * @param string $path
	 * @param array $dependencies
	 * @param bool|array $in_footer
	 *
	 * @return void
	 */
	public static function register_vite_script( string $handle, string $path, array $dependencies, $in_footer = array() )
	{
		$manifest   = self::get_manifest();
		$entrypoint = $manifest->getEntrypoint( $path );

		wp_register_script(
			$handle,
			$entrypoint['url'],
			$dependencies,
			$entrypoint['hash'],
			$in_footer
		);
	}

	/**
	 * Checks if asset compilation process is in development mode.
	 *
	 * @return bool true if asset compilation process is in development mode, else false.
	 */
	protected function is_in_development_mode() {
		return file_exists( get_template_directory() . '/hot-reload.json' );
	}

	/**
	 * Enqueues assets for Vite hot reloading.
	 *
	 * @param string $action the WordPress action.
	 * @param string $entrypoint The asset entrypoint.
	 */
	protected function add_hot_reload_assets( string $action, string $entrypoint ) {
		add_action(
			$action,
			function () use ( $entrypoint ) {
				$info = json_decode( file_get_contents( get_template_directory() . '/hot-reload.json' ), true );

				// phpcs:ignore
				echo '<script type="module" src="//' . esc_html( $info['hostname'] ) . ':' . esc_html( $info['port'] ) . '/@vite/client"></script>';

				// phpcs:ignore
				echo '<script type="module" src="//' . esc_html( $info['hostname'] ) . ':' . esc_html( $info['port'] ) . '/' . esc_html( $entrypoint ) . '"></script>';
			}
		);
	}

	/**
	 * Instantiate manifest object.
	 * 
	 * @return Manifest
	 */
	protected static function get_manifest() {
		return new Manifest(
			get_template_directory() . '/dist/.vite/manifest.json',
			get_template_directory_uri() . '/dist/assets'
		);
	}

	/**
	 * Enqueue styles from manifest for a specified WordPress action.
	 *
	 * @param string $action the WordPress action.
	 * @param string $entrypoint The asset entrypoint.
	 */
	protected function enqueue_styles( string $action, string $entrypoint ) {
		add_action(
			$action,
			function () use ( $entrypoint ) {
				$styles = $this->manifest->getStyles( $entrypoint );
				$asset = $this->manifest->getManifest()[ $entrypoint ];
				foreach ( $styles as $style ) {
					if ( empty( $style['url'] ) ) {
						continue;
					}
					wp_enqueue_style( $asset['name'], $style['url'], array(), $style['hash'] );
				}
			}
		);
	}

	/**
	 * Adds styles to the FSE editor from manifest.
	 *
	 * @param string $entrypoint The asset entrypoint.
	 */
	protected function add_editor_styles( string $entrypoint ) {
		$styles = $this->manifest->getStyles( $entrypoint );
		foreach ( $styles as $style ) {
			if ( empty( $style['url'] ) ) {
				continue;
			}
			add_editor_style( $style['url'] );
		}
	}
}
