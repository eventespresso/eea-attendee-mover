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
				'admin_path'            => EE_ATTENDEE_MOVER_ADMIN,
				'admin_callback'        => '',
				'config_class'          => 'EE_Attendee_Mover_Config',
				'config_name'           => 'EE_Attendee_Mover',
				// 'autoloader_paths'      => array(
				// 	'EE_Attendee_Mover_Config'       => EE_ATTENDEE_MOVER_PATH . 'EE_Attendee_Mover_Config.php',
				// 	'Attendee_Mover_Admin_Page'      => EE_ATTENDEE_MOVER_ADMIN . 'Attendee_Mover_Admin_Page.core.php',
				// 	'Attendee_Mover_Admin_Page_Init' => EE_ATTENDEE_MOVER_ADMIN . 'Attendee_Mover_Admin_Page_Init.core.php',
				// ),
				// 'dms_paths'             => array(
				// 	EE_ATTENDEE_MOVER_PATH . 'core' . DS . 'data_migration_scripts' . DS,
				// ),
				'module_paths'          => array( EE_ATTENDEE_MOVER_PATH . 'EED_Attendee_Mover.module.php' ),
				// 'shortcode_paths'       => array( EE_ATTENDEE_MOVER_PATH . 'EES_Attendee_Mover.shortcode.php' ),
				// 'widget_paths'          => array( EE_ATTENDEE_MOVER_PATH . 'EEW_Attendee_Mover.widget.php' ),
				// if plugin update engine is being used for auto-updates. not needed if PUE is not being used.
				'pue_options'           => array(
					'pue_plugin_slug' => 'eea-attendee-mover',
					'plugin_basename' => EE_ATTENDEE_MOVER_BASENAME,
					'checkPeriod'     => '24',
					'use_wp_update'   => false,
				),
				// 'capabilities'          => array(
				// 	'administrator' => array(
				// 		'edit_thing',
				// 		'edit_things',
				// 		'edit_others_things',
				// 		'edit_private_things',
				// 	),
				// ),
				// 'capability_maps'       => array(
				// 	'EE_Meta_Capability_Map_Edit' => array(
				// 		'edit_thing',
				// 		array( 'Attendee_Mover_Thing', 'edit_things', 'edit_others_things', 'edit_private_things' ),
				// 	),
				// ),
				// 'class_paths'           => EE_ATTENDEE_MOVER_PATH . 'core' . DS . 'db_classes',
				// 'model_paths'           => EE_ATTENDEE_MOVER_PATH . 'core' . DS . 'db_models',
				// 'class_extension_paths' => EE_ATTENDEE_MOVER_PATH . 'core' . DS . 'db_class_extensions',
				// 'model_extension_paths' => EE_ATTENDEE_MOVER_PATH . 'core' . DS . 'db_model_extensions',
				// 'custom_post_types'     => array(),
				// 'custom_taxonomies'     => array(),
				// 'default_terms'         => array(),
			)
		);
	}



}
// End of file EE_Attendee_Mover.class.php
// Location: wp-content/plugins/eea-attendee-mover/EE_Attendee_Mover.class.php
