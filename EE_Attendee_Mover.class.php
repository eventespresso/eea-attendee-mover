<?php if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit();
}
// define the plugin directory path and URL
define( 'EE_ATTENDEE_MOVER_BASENAME', plugin_basename( EE_ATTENDEE_MOVER_PLUGIN_FILE ) );
define( 'EE_ATTENDEE_MOVER_PATH', plugin_dir_path( __FILE__ ) );
define( 'EE_ATTENDEE_MOVER_URL', plugin_dir_url( __FILE__ ) );
define( 'EE_ATTENDEE_MOVER_ADMIN', EE_ATTENDEE_MOVER_PATH . 'admin' . DS . 'attendee_mover' . DS );



/**
 * Class  EE_Attendee_Mover
 *
 * @package     Event Espresso
 * @subpackage  eea-attendee-mover
 * @author      Brent Christensen
 */
Class  EE_Attendee_Mover extends EE_Addon {

	public static function register_addon() {
		// register addon via Plugin API
		EE_Register_Addon::register(
			'Attendee_Mover',
			array(
				'version'               => EE_ATTENDEE_MOVER_VERSION,
				'plugin_slug'           => 'attendee_mover',
				'min_core_version'      => EE_ATTENDEE_MOVER_CORE_VERSION_REQUIRED,
				'main_file_path'        => EE_ATTENDEE_MOVER_PLUGIN_FILE,
				// 'admin_path'            => EE_ATTENDEE_MOVER_ADMIN,
				'admin_callback'        => '',
				'config_class'          => 'EE_Attendee_Mover_Config',
				'config_name'           => 'EE_Attendee_Mover',
				'autoloader_paths'      => array(
					'EE_Attendee_Mover_Config'       => EE_ATTENDEE_MOVER_PATH . 'EE_Attendee_Mover_Config.php',
				),
				'module_paths'          => array( EE_ATTENDEE_MOVER_PATH . 'EED_Attendee_Mover.module.php' ),

				'pue_options'           => array(
					'pue_plugin_slug' => 'eea-attendee-mover',
					'plugin_basename' => EE_ATTENDEE_MOVER_BASENAME,
					'checkPeriod'     => '24',
					'use_wp_update'   => false,
				),

			)
		);
	}



}
// End of file EE_Attendee_Mover.class.php
// Location: wp-content/plugins/eea-attendee-mover/EE_Attendee_Mover.class.php
