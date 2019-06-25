<?php
/**
 * Main Plugin Class.
 *
 * @since 1.0.0
 */
namespace Heidenberg;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Main Plugin Class.
 *
 * @since 1.0.0
 */
final class Plugin {

	/**
	 * Plugin instance.
	 *
	 * @since 1.0.0
	 * @var object|Plugin
	 */
	private static $instance = null;

	/**
	 * Loader file.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $file = '';

	/**
	 * Current version.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $version = '1.0.0';

	/**
	 * Prefix.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $prefix = 'hb';

	/**
	 * Main instance.
	 *
	 * Insures that only one instance exists in memory at any one time.
	 * Also prevents needing to define globals all over the place.
	 *
	 * @static
	 * @staticvar array $instance
	 * @return object|Plugin
	 */
	public static function instance( $file = '' ) {

		// Return if already instantiated
		if ( self::is_instantiated() ) {
			return self::$instance;
		}

		// Setup the singleton
		self::setup_instance( $file );

		// Bootstrap
		self::$instance->setup_constants();
		self::$instance->setup_files();
		self::$instance->setup_application();

		// Return the instance
		return self::$instance;
	}

	/**
	 * Main installer, fired as a WordPress installation hook.
	 *
	 * As a general rule, try to avoid using this if you can.
	 *
	 * @since 1.0.0
	 */
	public static function install() {

	}

	/**
	 * Main uninstaller, fired as a WordPress uninstall hook.
	 *
	 * As a general rule, try to avoid using this if you can.
	 *
	 * @since 1.0.0
	 */
	public static function uninstall() {

	}

	/**
	 * Throw error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __NAMESPACE__, '2.0' );
	}

	/**
	 * Disable un-serializing of the class.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __NAMESPACE__, '2.0' );
	}

	/**
	 * Public magic isset method allows checking any key from any scope.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __isset( $key = '' ) {
		return (bool) isset( $this->{$key} );
	}

	/**
	 * Public magic get method allows getting any value from any scope.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get( $key = '' ) {
		return $this->__isset( $this->{$key} )
			? $this->{$key}
			: null;
	}

	/**
	 * Return whether the main loading class has been instantiated or not.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean True if instantiated. False if not.
	 */
	private static function is_instantiated() {

		// Return true if instance is correct class
		if ( ! empty( self::$instance ) && ( self::$instance instanceof Plugin ) ) {
			return true;
		}

		// Return false if not instantiated correctly
		return false;
	}

	/**
	 * Setup the singleton instance
	 *
	 * @since 1.0.0
	 * @param string $file
	 */
	private static function setup_instance( $file = '' ) {
		self::$instance       = new Plugin;
		self::$instance->file = $file;
	}

	/**
	 * Setup plugin constants.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function setup_constants() {

		// Uppercase
		$prefix = strtoupper( $this->prefix );

		// Plugin Version.
		if ( ! defined( "{$prefix}_PLUGIN_VERSION" ) ) {
			define( "{$prefix}_PLUGIN_VERSION", $this->version );
		}

		// Plugin Root File.
		if ( ! defined( "{$prefix}_PLUGIN_FILE" ) ) {
			define( "{$prefix}_PLUGIN_FILE", $this->file );
		}

		// Prepare file & directory
		$file = "{$prefix}_PLUGIN_FILE";
		$dir  = basename( __DIR__ );

		// Plugin Base Name.
		if ( ! defined( "{$prefix}_PLUGIN_BASE" ) ) {
			define( "{$prefix}_PLUGIN_BASE", trailingslashit( plugin_basename( $file ) . $dir ) );
		}

		// Plugin Folder Path.
		if ( ! defined( "{$prefix}_PLUGIN_DIR" ) ) {
			define( "{$prefix}_PLUGIN_DIR", trailingslashit( plugin_dir_path( $file ) . $dir ) );
		}

		// Plugin Folder URL.
		if ( ! defined( "{$prefix}_PLUGIN_URL" ) ) {
			define( "{$prefix}_PLUGIN_URL", trailingslashit( plugin_dir_url( $file ) . $dir ) );
		}
	}

	/**
	 * Setup files.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function setup_files() {

		// Admin specific
		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			$this->include_admin();

		// Front-end specific
		} else {
			$this->include_frontend();
		}

		// Files
		$this->include_common();

		// Drop-ins
		$this->include_dropins();
	}

	/**
	 * Setup the rest of the application
	 *
	 * @since 1.0.0
	 */
	private function setup_application() {
		// Instantiate any classes or setup any dependency injections here
	}

	/** Includes **************************************************************/

	/**
	 * Automatically include administration specific files.
	 *
	 * @since 1.0.0
	 */
	private function include_admin() {
		$this->slurp( 'admin' );
	}

	/**
	 * Automatically include front-end specific files.
	 *
	 * @since 1.0.0
	 */
	private function include_frontend() {
		$this->slurp( 'front-end' );
	}

	/**
	 * Automatically include files that are shared between all contexts.
	 *
	 * @since 1.0.0
	 */
	private function include_common() {
		$this->slurp( 'common' );
	}

	/**
	 * Automatically include any files in the /includes/drop-ins/ directory.
	 *
	 * @since 1.0.0
	 */
	private function include_dropins() {
		$this->slurp( 'drop-ins' );
	}

	/**
	 * Automatically include any files in a given directory.
	 *
	 * @since 1.0.0
	 *
	 * @param string $dir The name of the directory to include files from.
	 */
	private function slurp( $dir = '' ) {

		// Files & directory
		$files = array();
		$dir   = trailingslashit( __DIR__ ) . 'includes/' . $dir;

		// Bail if standard directory does not exist
		if ( ! is_dir( $dir ) ) {
			return;
		}

		// Try to open the directory
		$dh = opendir( $dir );

		// Bail if directory exists but cannot be opened
		if ( empty( $dh ) ) {
			return;
		}

		// Look for files in the directory
		while ( ( $plugin = readdir( $dh ) ) !== false ) {
			$ext = substr( $plugin, -4 );

			if ( $ext === '.php' ) {
				$name = substr( $plugin, 0, strlen( $plugin ) -4 );
				$files[ $name ] = trailingslashit( $dir ) . $plugin;
			}
		}

		// Close the directory
		closedir( $dh );

		// Skip empty index files
		unset( $files['index'] );

		// Bail if no files
		if ( empty( $files ) ) {
			return;
		}

		// Sort files alphabetically
		ksort( $files );

		// Include each file
		foreach ( $files as $file ) {
			require_once $file;
		}
	}
}

/**
 * Returns the plugin instance.
 *
 * The main function responsible for returning the one true plugin instance.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $hb = heidenberg(); ?>
 *
 * @since 1.0.0
 * @return object|Plugin
 */
function heidenberg() {
	return Plugin::instance();
}
