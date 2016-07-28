<?php
use EventEspresso\core\exceptions\ExceptionStackTraceDisplay;
use EventEspresso\core\libraries\form_sections\form_handlers\FormHandler;

if ( ! defined( 'EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
/**
 * Class  EED_Attendee_Mover
 *
 * @package			Event Espresso
 * @subpackage		eea-attendee-mover
 * @author 			Brent Christensen
 *
 * ------------------------------------------------------------------------
 */
class EED_Attendee_Mover extends EED_Module {


	/**
	 * @var \Registrations_Admin_Page $admin_page
	 */
	protected static $admin_page;


	/**
	 * @return EED_Attendee_Mover
	 */
	public static function instance() {
		return parent::get_instance( __CLASS__ );
	}



	 /**
	  * 	set_hooks - for hooking into EE Core, other modules, etc
	  *
	  *  @access 	public
	  *  @return 	void
	  */
	 public static function set_hooks() {
		 EED_Attendee_Mover::register_namespace_and_dependencies();
	 }

	 /**
	  * 	set_hooks_admin - for hooking into EE Admin Core, other modules, etc
	  *
	  *  @access 	public
	  *  @return 	void
	  */
	 public static function set_hooks_admin() {
		 EED_Attendee_Mover::register_namespace_and_dependencies();
		 add_action(
			 'FHEE__EE_Admin_Page___load_page_dependencies__after_load__espresso_registrations__edit_attendee_selections',
			 array( 'EED_Attendee_Mover', 'edit_attendee_selections_init' )
		 );
		 add_filter(
			 'FHEE__EE_Registrations_List_Table__column_actions__actions',
		      array( 'EED_Attendee_Mover', 'edit_attendee_selections_button_reg_list' ),
			 10, 2
		 );
		 add_action(
			 'AHEE__reg_admin_details_main_meta_box_reg_details__top',
		      array( 'EED_Attendee_Mover', 'edit_attendee_selections_button' ),
			 10, 1
		 );
		 add_action(
			 'AHEE__reg_status_change_buttons__after_header',
		      array( 'EED_Attendee_Mover', 'registration_moved_notice' ),
			 10, 1
		 );
		 add_filter(
			 'FHEE__Extend_Registrations_Admin_Page__page_setup__page_routes',
			 array( 'EED_Attendee_Mover', 'attendee_mover_page_routes' ),
			 10, 2
		 );
		 add_filter(
			 'FHEE__Extend_Registrations_Admin_Page__page_setup__page_config',
			 array( 'EED_Attendee_Mover', 'attendee_mover_page_config' ),
			 10, 2
		 );
		 add_filter(
			 'FHEE__EE_Admin_Page___display_legend__items',
			 array( 'EED_Attendee_Mover', 'reg_admin_list_legend' ),
			 10, 1
		 );
	 }



	/**
	 * register_namespace_and_dependencies
	 */
	public static function register_namespace_and_dependencies() {
		EE_Psr4AutoloaderInit::psr4_loader()->addNamespace( 'EventEspresso\AttendeeMover', __DIR__ );
		$attendee_mover_dependencies = array(
			'EventEspresso\AttendeeMover\form\SelectEvent' => array(
				'EE_Registry' => EE_Dependency_Map::load_from_cache,
			),
			'EventEspresso\AttendeeMover\form\SelectTicket' => array(
				'EE_Registry' => EE_Dependency_Map::load_from_cache,
			),
			'EventEspresso\AttendeeMover\form\VerifyChanges' => array(
				'EE_Registry' => EE_Dependency_Map::load_from_cache,
			),
			'EventEspresso\AttendeeMover\form\Complete' => array(
				'EE_Registry' => EE_Dependency_Map::load_from_cache,
			),
			'EventEspresso\AttendeeMover\services\commands\MoveAttendeeCommandHandler' => array(
				'EventEspresso\core\domain\services\ticket\CreateTicketLineItemService' => EE_Dependency_Map::load_from_cache,
				'EventEspresso\core\domain\services\registration\CreateRegistrationService' => EE_Dependency_Map::load_from_cache,
				'EventEspresso\core\domain\services\registration\CopyRegistrationService' => EE_Dependency_Map::load_from_cache,
				'EventEspresso\core\domain\services\registration\CancelRegistrationService' => EE_Dependency_Map::load_from_cache,
				'EventEspresso\core\domain\services\registration\UpdateRegistrationService' => EE_Dependency_Map::load_from_cache,
			)
		);
		foreach ( $attendee_mover_dependencies as $class => $dependencies ) {
			if ( ! EE_Dependency_Map::register_dependencies( $class, $dependencies ) ) {
				EE_Error::add_error(
					sprintf(
						__( 'Could not register dependencies for "%1$s"', 'event_espresso' ),
						$class
					),
					__FILE__,
					__FUNCTION__,
					__LINE__
				);
			}
		}
	}






	 /**
	  *    run - initial module setup
	  *
	  * @access    public
	  * @param  WP $WP
	  * @return    void
	  */
	 public function run( $WP ) {
	 }



	/**
	 * callback for FHEE__Extend_Registrations_Admin_Page__page_setup__page_routes

	 *
*@param array $page_routes
	 * @param \Registrations_Admin_Page $admin_page
	 * @return mixed
	 */
	public static function attendee_mover_page_routes( array $page_routes, \Registrations_Admin_Page $admin_page ) {
		EED_Attendee_Mover::$admin_page = $admin_page;
		$req_data = $admin_page->get_request_data();
		$REG_ID = ! empty( $req_data['_REG_ID'] ) && ! is_array( $req_data['_REG_ID'] )
			? $req_data['_REG_ID']
			: 0;
		$EVT_ID = ! empty( $req_data['EVT_ID'] ) && ! is_array( $req_data['EVT_ID'] )
			? $req_data['EVT_ID']
			: 0;
		$TKT_ID = ! empty( $req_data['TKT_ID'] ) && ! is_array( $req_data['TKT_ID'] )
			? $req_data['TKT_ID']
			: 0;
		$page_routes['edit_attendee_selections'] = array(
			'func'       => array( 'EED_Attendee_Mover', 'edit_attendee_selections' ),
			'capability' => 'ee_edit_registration',
			'obj_id'     => $REG_ID,
			'_REG_ID'     => $REG_ID,
			'EVT_ID'     => $EVT_ID,
			'TKT_ID'     => $TKT_ID,
		);
		$page_routes['process_attendee_selections'] = array(
			'func'       => array( 'EED_Attendee_Mover', 'process_attendee_selections' ),
			'args'       => array( $admin_page ),
			'capability' => 'ee_edit_registration',
			'_REG_ID'     => $REG_ID,
			'EVT_ID'     => $EVT_ID,
			'TKT_ID'     => $TKT_ID,
			'noheader'   => true
		);
		return $page_routes;
	}



	/**
	 * callback for FHEE__Extend_Registrations_Admin_Page__page_setup__page_config
	 *
	 * @param array $page_config current page config.
	 * @param \Registrations_Admin_Page $admin_page
	 * @since  1.0.0
	 * @return array
	 */
	public static function attendee_mover_page_config( $page_config, \Registrations_Admin_Page $admin_page ) {
		EED_Attendee_Mover::$admin_page = $admin_page;
		$req_data = $admin_page->get_request_data();
		$page_config['edit_attendee_selections'] = array(
			'nav'           => array(
				'label'      => __( 'Change Event/Ticket Selection', 'event_espresso' ),
				'order'      => 15,
				'persistent' => false,
				'url'        => isset( $req_data['_REG_ID'] )
					? EE_Admin_Page::add_query_args_and_nonce(
						array(
							'action'  => 'edit_attendee_selections',
							'_REG_ID' => $req_data['_REG_ID']
						),
						$admin_page->get_current_page_view_url()
					)
					: $admin_page->admin_base_url()
			),
			'metaboxes'     => array_merge(
				$admin_page->default_espresso_metaboxes(),
				array(
					function () {
						add_meta_box(
							'edit-attendee-selection-mbox',
							__( 'Change Event/Ticket Selection', 'event_espresso' ),
							array( 'EED_Attendee_Mover', 'edit_attendee_selections_meta_box' ),
							EED_Attendee_Mover::$admin_page->wp_page_slug(),
							'normal',
							'high'
						);
					}
				)
			),
			'require_nonce' => true
		);
		return $page_config;
	}



	/**
	 * @param array            $actions
	 * @param \EE_Registration $registration
	 * @return array
	 * @throws \InvalidArgumentException
	 * @throws \EE_Error
	 */
	public static function edit_attendee_selections_button_reg_list( $actions = array(), \EE_Registration $registration ) {
		if (
			! in_array(
				$registration->status_ID(),
				array(
					EEM_Registration::status_id_cancelled,
					EEM_Registration::status_id_declined,
				)
			)
		) {
            $actions['edit_attendee_selections'] = '<li>' .
                                                   EED_Attendee_Mover::edit_attendee_selections_button(
                                                       $registration->ID(),
                                                       false,
                                                       false
                                                   ) .
                                                   '</li>';
        }
		return $actions;
	}



	/**
	 * @param int    $REG_ID
	 * @param bool   $button
	 * @param bool   $echo
	 * @return string|void
	 * @throws \InvalidArgumentException
	 */
	public static function edit_attendee_selections_button( $REG_ID = 0, $button = true, $echo = true ) {
		if (
			$REG_ID === 0
			|| ! EE_Registry::instance()->CAP->current_user_can(
				'ee_edit_registration',
				'espresso_registrations_change_event_or_ticket'
			)
		) {
			return '';
		}
		$url = EED_Attendee_Mover::get_edit_attendee_selections_url( $REG_ID );
		if ( $button ) {
			$link_text = $link_label = __( ' Change Event/Ticket Selection' );
			$link_class = 'button secondary-button right';
		} else {
			$link_text = '';
			$link_label = __( ' Change Event/Ticket Selection' );
			$link_class = 'right';
		}
		$html = EEH_Template::get_button_or_link(
			$url,
			$link_text,
			$link_class,
			'dashicons dashicons-controls-repeat',
			$link_label
		);
		if ( $echo ) {
			echo $html;
			return '';
		}
		return $html;
	}



	/**
	 * @return array
	 */
    public static function reg_admin_list_legend(array $items)
    {
        // insert attendee_mover icon before the "approved_status" icon
        $items = EEH_Array::insert_into_array(
            $items,
            array(
                'attendee_mover' => array(
                    'class' => 'dashicons dashicons-controls-repeat',
                    'desc'  => __('Change Event/Ticket Selection', 'event_espresso'),
                ),
            ),
            "approved_status"
        );
        return $items;
	}



	/**
	 * @param int  $REG_ID
	 * @param bool $process
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public static function get_edit_attendee_selections_url( $REG_ID = 0, $process = false ) {
		$REG_ID = absint( $REG_ID );
		if ( ! $REG_ID > 0 ) {
			throw new InvalidArgumentException(
				__( 'The Registration ID must be a positive integer.', 'event_espresso' )
			);
		}
		return EE_Admin_Page::add_query_args_and_nonce(
			array(
				'action'  => $process ? 'process_attendee_selections' : 'edit_attendee_selections',
				'_REG_ID' => $REG_ID,
			),
			REG_ADMIN_URL
		);
	}



	/**
	 * @param $REG_ID
	 */
	public static function registration_moved_notice( $REG_ID ) {
		/** @var EE_Registration $registration */
		$registration = EEM_Registration::instance()->get_one_by_ID( $REG_ID );
		$reg_moved_meta = array(
			'registration-moved-to' => array(
				'meta_key' => 'NEW_REG_ID',
				'message' => __(
					'%1$sThis registration was cancelled and moved to a %2$snew registration%3$s.%4$s',
					'event_espresso'
				)
			),
			'registration-moved-from' => array(
				'meta_key' => 'OLD_REG_ID',
				'message' => __(
					'%1$sThis registration was moved from a %2$sprevious registration%3$s which has been cancelled.%4$s',
					'event_espresso'
				)
			),
		);
		foreach ( $reg_moved_meta as $to_or_from => $reg_meta ) {
			$reg_moved = $registration->get_extra_meta( $to_or_from, true, array() );
			if ( isset( $reg_meta['meta_key'], $reg_moved[ $reg_meta['meta_key'] ], $reg_meta['message'] ) ) {
				$reg_details_url = add_query_arg(
					array(
						'action'  => 'view_registration',
						'_REG_ID' => $reg_moved[ $reg_meta['meta_key'] ],
					),
					REG_ADMIN_URL
				);
				echo sprintf(
					$reg_meta['message'],
					'<p class="important-notice">',
					'<a href="' . $reg_details_url . '">',
					'</a>',
					'</p>'
				);
			}
		}
	}



	/**
	 * @param bool $process
	 * @return \EventEspresso\AttendeeMover\form\StepsManager
	 * @throws \InvalidArgumentException
	 * @throws \EventEspresso\core\exceptions\InvalidDataTypeException
	 */
	public function get_form_steps_manager( $process = true ) {
		static $form_steps_manager = null;
		if ( ! $form_steps_manager instanceof \EventEspresso\AttendeeMover\form\StepsManager ) {
			$request = EE_Registry::instance()->load_core( 'Request' );
			$REG_ID = absint( $request->get( '_REG_ID', 0 ) );
			$form_steps_manager = new \EventEspresso\AttendeeMover\form\StepsManager(
				// base redirect URL
				EED_Attendee_Mover::get_edit_attendee_selections_url( $REG_ID, $process ),
				// default step slug
				'select_event',
				// form action
				'',
				// form config
				FormHandler::ADD_FORM_TAGS_AND_SUBMIT,
				// progress steps theme/style
				'number_bubbles',
				// EE_Request
				$request
			);
		}
		return $form_steps_manager;
	}



	public static function edit_attendee_selections_init() {
		EED_Attendee_Mover::instance()->_edit_attendee_selections_init();
	}



	/**
	 * _edit_attendee_selections_init
 * callback for action that hooks into the registration admin page prior to wp_enqueue_scripts
	 *
	 * @access    protected
	 * @return    void
	 * @throws \EventEspresso\core\exceptions\InvalidInterfaceException
	 * @throws \EventEspresso\core\exceptions\InvalidIdentifierException
	 * @throws \EventEspresso\core\exceptions\InvalidEntityException
	 * @throws \EventEspresso\core\exceptions\InvalidClassException
	 * @throws \InvalidArgumentException
	 * @throws \EventEspresso\core\exceptions\InvalidDataTypeException
	 */
	protected function _edit_attendee_selections_init() {
		try {
			$form_steps_manager = $this->get_form_steps_manager();
			$form_steps_manager->buildForm();
		} catch ( Exception $e ) {
			new ExceptionStackTraceDisplay( $e );
		}
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 1 );
	}



	/**
	 * callback that displays the page template
	 */
	public static function edit_attendee_selections() {
		// the details template wrapper
		EED_Attendee_Mover::$admin_page->display_admin_page_with_sidebar();
	}



	/**
	 * callback that adds the main "edit_attendee_selections" meta_box
	 * calls non static method below
	 *
	 * @throws \EventEspresso\core\exceptions\InvalidDataTypeException
	 * @throws \InvalidArgumentException
	 */
	public static function edit_attendee_selections_meta_box() {
		EED_Attendee_Mover::instance()->_edit_attendee_selections_meta_box();
	}



	/**
	 * _edit_attendee_selections_meta_box
	 *
	 * @throws \EventEspresso\core\exceptions\InvalidDataTypeException
	 * @throws \InvalidArgumentException
	 */
	public function _edit_attendee_selections_meta_box() {
		try {
			$form_steps_manager = $this->get_form_steps_manager();
			echo $form_steps_manager->displayProgressSteps();
			echo \EEH_HTML::h1( $form_steps_manager->getCurrentStep()->formName() );
			echo $form_steps_manager->displayCurrentStepForm();
		} catch ( Exception $e ) {
			new ExceptionStackTraceDisplay( $e );
		}
	}



	/**
	 * process_attendee_selections
	 * callback route for when the attendee mover step forms are being processed
	 *
	 * @access    public
	 * @param \Registrations_Admin_Page $admin_page
	 * @throws \EventEspresso\core\exceptions\InvalidClassException
	 * @throws \EventEspresso\core\exceptions\InvalidInterfaceException
	 * @throws \EventEspresso\core\exceptions\InvalidDataTypeException
	 * @throws \EventEspresso\core\exceptions\InvalidEntityException
	 * @throws \EventEspresso\core\exceptions\InvalidIdentifierException
	 * @throws \InvalidArgumentException
	 */
	public static function process_attendee_selections( \Registrations_Admin_Page $admin_page ) {
		EED_Attendee_Mover::instance()->_process_attendee_selections( $admin_page );
	}



	/**
	 * _process_attendee_selections
	 * callback route for when the attendee mover step forms are being processed
	 *
	 * @access protected
	 * @param  \Registrations_Admin_Page $admin_page
	 * @throws \EventEspresso\core\exceptions\InvalidClassException
	 * @throws \EventEspresso\core\exceptions\InvalidInterfaceException
	 * @throws \EventEspresso\core\exceptions\InvalidDataTypeException
	 * @throws \EventEspresso\core\exceptions\InvalidEntityException
	 * @throws \EventEspresso\core\exceptions\InvalidIdentifierException
	 * @throws \InvalidArgumentException
	 */
	protected function _process_attendee_selections( \Registrations_Admin_Page $admin_page ) {
		try {
			$form_steps_manager = $this->get_form_steps_manager( false );
			$form_steps_manager->processForm( $admin_page->get_request_data() );
		} catch ( Exception $e ) {
			new ExceptionStackTraceDisplay( $e );
		}
	}






	/**
	 * 	enqueue_scripts - Load the scripts and css
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public function enqueue_scripts() {
		// EE attendee_mover style
		wp_register_style(
			'espresso_attendee_mover',
			EE_ATTENDEE_MOVER_URL . 'css/espresso_attendee_mover.css'
		);
		wp_enqueue_style( 'espresso_attendee_mover' );
	}



	/**
	 * @param $a
	 * @param $b
	 * @return bool
	 */
	public function __set( $a, $b ) {
		return false;
	}



	/**
	 * @param $a
	 * @return bool
	 */
	public function __get( $a ) {
		return false;
	}



	/**
	 * @param $a
	 * @return bool
	 */
	public function __isset( $a ) {
		return false;
	}



	/**
	 * @param $a
	 * @return bool
	 */
	public function __unset( $a ) {
		return false;
	}



	/**
	 *
	 */
	public function __clone() {
	}



	/**
	 *
	 */
	public function __wakeup() {
	}



	/**
	 *
	 */
	public function __destruct() {
	}

}
// End of file EED_Attendee_Mover.module.php
// Location: /wp-content/plugins/eea-attendee-mover/EED_Attendee_Mover.module.php
