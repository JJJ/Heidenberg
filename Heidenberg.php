<?php
/**
 * Plugin Name: Heidenberg
 * Plugin URI:  https://jjj.blog/heidenberg
 * Description: Using spirits to communicate over long distances.
 * Author:      John James Jacoby
 * Author URI:  https://jjj.blog
 * Version:     1.0.0
 * Text Domain: heidenberg
 * Domain Path: /Heidenberg/includes/languages
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * The main plugin requirements checker
 *
 * @since 1.0.0
 */
final class Heidenberg_Requirements_Check {

	/**
	 * Plugin file
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $file = '';

	/**
	 * Plugin basename
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $base = '';

	/**
	 * Plugin main class name
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $main_class = 'Heidenberg\\Plugin';

	/**
	 * Requirements array
	 *
	 * @var array
	 * @since 1.0.0
	 */
	private $requirements = array(

		// PHP
		'php' => array(
			'minimum' => '5.6.0',
			'name'    => 'PHP',
			'exists'  => true,
			'current' => false,
			'checked' => false,
			'met'     => false
		),

		// WordPress
		'wp' => array(
			'minimum' => '5.2.0',
			'name'    => 'WordPress',
			'exists'  => true,
			'current' => false,
			'checked' => false,
			'met'     => false
		)
	);

	/** Strings ***************************************************************/

	/**
	 * Plugin specific URL for an external requirements page.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	private function unmet_requirements_url() {
		return 'https://jjj.blog/heidenberg';
	}

	/**
	 * Plugin specific text to quickly explain what's wrong.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	private function unmet_requirements_text() {
		esc_html_e( 'This plugin is not fully active.', 'heidenberg' );
	}

	/**
	 * Plugin specific text to describe a single unmet requirement.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	private function unmet_requirements_description_text() {
		return esc_html__( 'Requires %s (%s), but (%s) is installed.', 'heidenberg' );
	}

	/**
	 * Plugin specific text to describe a single missing requirement.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	private function unmet_requirements_missing_text() {
		return esc_html__( 'Requires %s (%s), but it appears to be missing.', 'heidenberg' );
	}

	/**
	 * Plugin specific text used to link to an external requirements page.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	private function unmet_requirements_link() {
		return esc_html__( 'Requirements', 'heidenberg' );
	}

	/**
	 * Plugin specific aria label text to describe the requirements link.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	private function unmet_requirements_label() {
		return esc_html__( 'Heidenberg Requirements', 'heidenberg' );
	}

	/**
	 * Plugin specific text used in CSS to identify attribute IDs and classes.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	private function unmet_requirements_name() {
		return 'heidenberg';
	}

	/**
	 * Plugin specific text-domain loader.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'heidenberg' );
	}

	/** Load ******************************************************************/

	/**
	 * Setup plugin requirements
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Setup file & base
		$this->file = __FILE__;
		$this->base = plugin_basename( $this->file );

		// Always load translations
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		// Load or quit
		$this->met()
			? $this->load()
			: $this->quit();
	}

	/**
	 * Quit without loading
	 *
	 * @since 1.0.0
	 */
	private function quit() {
		add_action( 'admin_head',                        array( $this, 'admin_head'        ) );
		add_filter( "plugin_action_links_{$this->base}", array( $this, 'plugin_row_links'  ) );
		add_action( "after_plugin_row_{$this->base}",    array( $this, 'plugin_row_notice' ) );
	}

	/** Specific Methods ******************************************************/

	/**
	 * Load normally
	 *
	 * @since 1.0.0
	 */
	private function load() {

		// Maybe include the bundled bootstrapper
		if ( ! class_exists( $this->main_class ) ) {
			require_once dirname( $this->file ) . '/Heidenberg/Heidenberg.php';
		}

		// Maybe hook-in the bootstrapper
		if ( ! class_exists( $this->main_class ) ) {
			return;
		}

		// Bootstrap to plugins_loaded
		add_action( 'plugins_loaded', array( $this, 'bootstrap' ) );

		// Register the activation hook
		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Register the deactivation hook
		register_deactivation_hook( $this->file, array( $this, 'uninstall' ) );
	}

	/**
	 * Install, usually on an activation hook.
	 *
	 * @since 1.0.0
	 */
	public function install() {

		// Bootstrap to include all of the necessary files
		$this->bootstrap();

		// Installer
		$this->main_class::install();
	}

	/**
	 * Uninstall, usually on a deactivation hook.
	 *
	 * @since 1.0.0
	 */
	public function uninstall() {

		// Bootstrap to include all of the necessary files
		$this->bootstrap();

		// Uninstaller
		$this->main_class::uninstall();
	}

	/**
	 * Bootstrap everything.
	 *
	 * @since 1.0.0
	 */
	public function bootstrap() {
		$this->main_class::instance( $this->file );
	}

	/** Multisite *************************************************************/

	/**
	 * Is plugin activation network wide?
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	private function is_network_wide() {
		return ! empty( $_GET['networkwide'] )
			? (bool) $_GET['networkwide']
			: false;
	}

	/** Agnostic Methods ******************************************************/

	/**
	 * Plugin agnostic method to output the additional plugin row
	 *
	 * @since 1.0.0
	 */
	public function plugin_row_notice() {
		?><tr class="active <?php echo esc_attr( $this->unmet_requirements_name() ); ?>-row">
		<th class="check-column">
			<span class="dashicons dashicons-warning"></span>
		</th>
		<td class="column-primary">
			<?php $this->unmet_requirements_text(); ?>
		</td>
		<td class="column-description">
			<?php $this->unmet_requirements_description(); ?>
		</td>
		</tr><?php
	}

	/**
	 * Plugin agnostic method used to output all unmet requirement information
	 *
	 * @since 1.0.0
	 */
	private function unmet_requirements_description() {
		foreach ( $this->requirements as $properties ) {
			if ( empty( $properties['met'] ) ) {
				$this->unmet_requirement_description( $properties );
			}
		}
	}

	/**
	 * Plugin agnostic method to output specific unmet requirement information
	 *
	 * @since 1.0.0
	 * @param array $requirement
	 */
	private function unmet_requirement_description( $requirement = array() ) {

		// Requirement exists, but is out of date
		if ( ! empty( $requirement['exists'] ) ) {
			$text = sprintf(
				$this->unmet_requirements_description_text(),
				'<strong>' . esc_html( $requirement['name']    ) . '</strong>',
				'<strong>' . esc_html( $requirement['minimum'] ) . '</strong>',
				'<strong>' . esc_html( $requirement['current'] ) . '</strong>'
			);

			// Requirement could not be found
		} else {
			$text = sprintf(
				$this->unmet_requirements_missing_text(),
				'<strong>' . esc_html( $requirement['name']    ) . '</strong>',
				'<strong>' . esc_html( $requirement['minimum'] ) . '</strong>'
			);
		}

		// Output the description (unescaped, contains HTML)
		echo wpautop( $text );
	}

	/**
	 * Plugin agnostic method to output unmet requirements styling
	 *
	 * @since 1.0.0
	 */
	public function admin_head() {

		// Get the requirements row name
		$name = $this->unmet_requirements_name(); ?>

		<style id="<?php echo esc_attr( $name ); ?>">
			.plugins tr[data-plugin="<?php echo esc_html( $this->base ); ?>"] th,
			.plugins tr[data-plugin="<?php echo esc_html( $this->base ); ?>"] td,
			.plugins .<?php echo esc_html( $name ); ?>-row th,
			.plugins .<?php echo esc_html( $name ); ?>-row td {
				background: #fff5f5;
			}
			.plugins tr[data-plugin="<?php echo esc_html( $this->base ); ?>"] th {
				box-shadow: none;
			}
			.plugins .<?php echo esc_html( $name ); ?>-row th span {
				margin-left: 6px;
				color: #dc3232;
			}
			.plugins tr[data-plugin="<?php echo esc_html( $this->base ); ?>"] th,
			.plugins .<?php echo esc_html( $name ); ?>-row th.check-column {
				border-left: 4px solid #dc3232 !important;
			}
			.plugins .<?php echo esc_html( $name ); ?>-row .column-description p {
				margin: 0;
				padding: 0;
			}
			.plugins .<?php echo esc_html( $name ); ?>-row .column-description p:not(:last-of-type) {
				margin-bottom: 8px;
			}
		</style>
		<?php
	}

	/**
	 * Plugin agnostic method to add the "Requirements" link to row actions
	 *
	 * @since 1.0.0
	 * @param array $links
	 * @return array
	 */
	public function plugin_row_links( $links = array() ) {

		// Add the Requirements link
		$links['requirements'] =
			'<a href="' . esc_url( $this->unmet_requirements_url() ) . '" aria-label="' . esc_attr( $this->unmet_requirements_label() ) . '">'
			. esc_html( $this->unmet_requirements_link() )
			. '</a>';

		// Return links with Requirements link
		return $links;
	}

	/** Checkers **************************************************************/

	/**
	 * Plugin specific requirements checker
	 *
	 * @since 1.0.0
	 */
	private function check() {

		// Loop through requirements
		foreach ( $this->requirements as $dependency => $properties ) {

			// Which dependency are we checking?
			switch ( $dependency ) {

				// PHP
				case 'php' :
					$version = phpversion();
					break;

				// WP
				case 'wp' :
					$version = get_bloginfo( 'version' );
					break;

				/**
				 * Insert your own requirements checks here as needed for any
				 * plugin or theme code that this plugin will depend on, then
				 * delete this paragraph.
				 */

				// Unknown
				default :
					$version = false;
					break;
			}

			// Merge to original array
			if ( ! empty( $version ) ) {
				$this->requirements[ $dependency ] = array_merge( $this->requirements[ $dependency ], array(
					'current' => $version,
					'checked' => true,
					'met'     => version_compare( $version, $properties['minimum'], '>=' )
				) );
			}
		}
	}

	/**
	 * Have all requirements been met?
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	public function met() {

		// Run the check
		$this->check();

		// Default to true (any false below wins)
		$retval  = true;
		$to_meet = wp_list_pluck( $this->requirements, 'met' );

		// Look for unmet dependencies, and exit if so
		foreach ( $to_meet as $met ) {
			if ( empty( $met ) ) {
				$retval = false;
				continue;
			}
		}

		// Return
		return $retval;
	}
}

// Invoke the checker
new Heidenberg_Requirements_Check();
