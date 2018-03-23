<?php use EventEspresso\core\exceptions\InvalidDataTypeException;
use EventEspresso\core\exceptions\InvalidInterfaceException;

if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
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

    /**
     * @throws DomainException
     * @throws EE_Error
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws InvalidDataTypeException
     * @throws InvalidInterfaceException
     */
    public static function register_addon() {
		// register addon via Plugin API
		EE_Register_Addon::register(
			'Attendee_Mover',
			array(
				'version'               => EE_ATTENDEE_MOVER_VERSION,
				'plugin_slug'           => 'attendee_mover',
				'min_core_version'      => EE_ATTENDEE_MOVER_CORE_VERSION_REQUIRED,
				'min_wp_version'        => EE_ATTENDEE_MOVER_WP_VERSION_REQUIRED,
				'main_file_path'        => EE_ATTENDEE_MOVER_PLUGIN_FILE,
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


    /**
     * @return void;
     */
    public function after_registration()
    {
        EE_Psr4AutoloaderInit::psr4_loader()->addNamespace('EventEspresso\AttendeeMover', __DIR__);
        $attendee_mover_dependencies = array(
            'EventEspresso\AttendeeMover\form\StepsManager'                            => array(
                null,
                null,
                null,
                null,
                null,
                'EE_Request' => EE_Dependency_Map::load_from_cache,
            ),
            'EventEspresso\AttendeeMover\form\SelectEvent'                             => array(
                'EE_Registry' => EE_Dependency_Map::load_from_cache,
            ),
            'EventEspresso\AttendeeMover\form\SelectTicket'                            => array(
                'EE_Registry' => EE_Dependency_Map::load_from_cache,
            ),
            'EventEspresso\AttendeeMover\form\VerifyChanges'                           => array(
                'EE_Registry' => EE_Dependency_Map::load_from_cache,
            ),
            'EventEspresso\AttendeeMover\form\Complete'                                => array(
                'EE_Registry' => EE_Dependency_Map::load_from_cache,
            ),
            'EventEspresso\AttendeeMover\services\commands\MoveAttendeeCommandHandler' => array(
                'EventEspresso\core\domain\services\ticket\CreateTicketLineItemService'     => EE_Dependency_Map::load_from_cache,
                'EventEspresso\core\domain\services\registration\CreateRegistrationService' => EE_Dependency_Map::load_from_cache,
                'EventEspresso\core\domain\services\registration\CopyRegistrationService'   => EE_Dependency_Map::load_from_cache,
                'EventEspresso\core\domain\services\registration\CancelRegistrationService' => EE_Dependency_Map::load_from_cache,
                'EventEspresso\core\domain\services\registration\UpdateRegistrationService' => EE_Dependency_Map::load_from_cache,
            ),
        );
        foreach ($attendee_mover_dependencies as $class => $dependencies) {
            if (! EE_Dependency_Map::register_dependencies($class, $dependencies)) {
                EE_Error::add_error(
                    sprintf(
                        esc_html__('Could not register dependencies for "%1$s"', 'event_espresso'),
                        $class
                    ),
                    __FILE__,
                    __FUNCTION__,
                    __LINE__
                );
            }
        }
    }
}
// End of file EE_Attendee_Mover.class.php
// Location: wp-content/plugins/eea-attendee-mover/EE_Attendee_Mover.class.php
