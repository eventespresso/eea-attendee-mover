<?php use EventEspresso\core\libraries\form_sections\SequentialStepFormInterface;

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
	 * @var \EventEspresso\core\services\collections\Collection $form_steps
	 */
	protected $form_steps;

	/**
	 * @var \EventEspresso\core\services\progress_steps\ProgressStepManager $progress_step_manager
	 */
	protected $progress_step_manager;


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
		 EE_Psr4AutoloaderInit::psr4_loader()->addNamespace( 'AttendeeMover', __DIR__ );
		 // EE_Config::register_route( 'attendee_mover', 'EED_Attendee_Mover', 'run' );
	 }

	 /**
	  * 	set_hooks_admin - for hooking into EE Admin Core, other modules, etc
	  *
	  *  @access 	public
	  *  @return 	void
	  */
	 public static function set_hooks_admin() {
		 EE_Psr4AutoloaderInit::psr4_loader()->addNamespace( 'AttendeeMover', __DIR__ );
		 add_action(
			 'FHEE__EE_Admin_Page___load_page_dependencies__after_load__espresso_registrations__edit_ticket_selection',
			 array( 'EED_Attendee_Mover', 'edit_ticket_selections_init' )
		 );
		 add_filter(
			 'FHEE__EE_Registrations_List_Table__column_actions__actions',
		      array( 'EED_Attendee_Mover', 'edit_ticket_selection_button_reg_list' ),
			 10, 2
		 );
		 add_action(
			 'AHEE__reg_admin_details_main_meta_box_reg_details__top',
		      array( 'EED_Attendee_Mover', 'edit_ticket_selection_button' ),
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
		 // ajax hooks
		 add_action( 'wp_ajax_get_attendee_mover', array( 'EED_Attendee_Mover', 'get_attendee_mover' ) );
		 add_action( 'wp_ajax_nopriv_get_attendee_mover', array( 'EED_Attendee_Mover', 'get_attendee_mover' ) );
	 }



	 public static function get_attendee_mover(){
		 echo json_encode( array( 'response' => 'ok', 'details' => 'you have made an ajax request!') );
		 die;
	 }



	/**
	 *    config
	 *
	 * @return EE_Attendee_Mover_Config
	 */
	public function config(){
		// config settings are setup up individually for EED_Modules via
		// the EE_Configurable class that all modules inherit from,
		// so $this->config();  can be used anywhere to retrieve it's config,
		// and $this->_update_config( $EE_Config_Base_object ); can be used
		// to supply an updated instance of it's config object to piggy back
		// off of the config setup for the base EE_Attendee_Mover class,
		// just use the following (note: updates would have to occur from within that class)
		return EE_Registry::instance()->addons->EE_Attendee_Mover->config();
	}






	 /**
	  *    run - initial module setup
	  *
	  * @access    public
	  * @param  WP $WP
	  * @return    void
	  */
	 public function run( $WP ) {
		 // add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ));
	 }



	/**
	 * callback for FHEE__Extend_Registrations_Admin_Page__page_setup__page_routes

	 *
*@param array $page_routes
	 * @param \Registrations_Admin_Page $admin_page
	 * @return mixed
	 */
	public static function attendee_mover_page_routes( array $page_routes, \Registrations_Admin_Page $admin_page ) {
		if ( $admin_page instanceof \Registrations_Admin_Page ) {
			EED_Attendee_Mover::$admin_page = $admin_page;
		}
		$req_data = $admin_page->get_request_data();
		$reg_id = ! empty( $req_data['_REG_ID'] ) && ! is_array( $req_data['_REG_ID'] )
			? $req_data['_REG_ID']
			: 0;
		$page_routes['edit_ticket_selection'] = array(
			'func'       => array( 'EED_Attendee_Mover', '_edit_ticket_selection' ),
			'capability' => 'ee_edit_registration',
			'obj_id'     => $reg_id,
			'_REG_ID'    => $reg_id,
		);
		// $page_routes['update_wp_user_settings'] = array(
		// 	'func'       => array( 'EED_WP_Users_Admin', 'update_wp_user_settings' ),
		// 	'args'       => array( $admin_page ),
		// 	'capability' => 'manage_options',
		// 	'noheader'   => true
		// );
		return $page_routes;
	}



	/**
	 * callback for FHEE__Extend_Registrations_Admin_Page__page_setup__page_config

	 *
*@param array         $page_config current page config.
	 * @param \Registrations_Admin_Page $admin_page
	 * @since  1.0.0
	 * @return array
	 */
	public static function attendee_mover_page_config( $page_config, \Registrations_Admin_Page $admin_page ) {
		if ( $admin_page instanceof \Registrations_Admin_Page ) {
			EED_Attendee_Mover::$admin_page = $admin_page;
		}
		$req_data = $admin_page->get_request_data();
		$page_config['edit_ticket_selection'] = array(
			'nav'           => array(
				'label'      => __( 'Change Event/Ticket Selection', 'event_espresso' ),
				'order'      => 15,
				'persistent' => false,
				'url'        => isset( $req_data['_REG_ID'] )
					? EE_Admin_Page::add_query_args_and_nonce(
						array(
							'action'  => 'edit_ticket_selection',
							'_REG_ID' => $req_data['_REG_ID']
						),
						$admin_page->get_current_page_view_url()
					)
					: $admin_page->admin_base_url()
			),
			'metaboxes'     => array_merge(
				$admin_page->default_espresso_metaboxes(),
				array( array( 'EED_Attendee_Mover', 'add_edit_ticket_selection_meta_box' ) )
			),
			'require_nonce' => true
		);
		return $page_config;
	}



	/**
	 * @param array            $actions
	 * @param \EE_Registration $registration
	 * @return array
	 * @throws \EE_Error
	 */
	public static function edit_ticket_selection_button_reg_list( $actions = array(), \EE_Registration $registration ) {
		$actions['edit_ticket_selection'] = EED_Attendee_Mover::edit_ticket_selection_button(
			$registration->ID(),
			false,
			'migrate',
			false
		);
		return $actions;
	}



	/**
	 * @param int    $REG_ID
	 * @param bool   $button
	 * @param string $dashicon
	 * @param bool   $echo
	 * @return string|void
	 */
	public static function edit_ticket_selection_button( $REG_ID = 0, $button = true, $dashicon = 'tickets-alt', $echo = true ) {
		if (
			$REG_ID === 0
			|| ! EE_Registry::instance()->CAP->current_user_can(
				'ee_edit_registration',
				'espresso_registrations_change_event_or_ticket'
			)
		) {
			return '';
		}
		$url = EE_Admin_Page::add_query_args_and_nonce(
			array(
				'action'  => 'edit_ticket_selection',
				'_REG_ID' => $REG_ID,
			),
			REG_ADMIN_URL
		);
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
			'dashicons dashicons-' . $dashicon,
			$link_label
		);
		if ( $echo ) {
			echo $html;
			return '';
		}
		return $html;
	}



	public static function _edit_ticket_selection() {
		// the details template wrapper
		EED_Attendee_Mover::$admin_page->display_admin_page_with_sidebar();
	}



	public static function add_edit_ticket_selection_meta_box() {
		add_meta_box(
			'edit-ticket-selection-mbox',
			__( 'Change Event/Ticket Selection', 'event_espresso' ),
			array( 'EED_Attendee_Mover', 'edit_ticket_selection_meta_box' ),
			EED_Attendee_Mover::$admin_page->wp_page_slug(),
			'normal',
			'high'
		);
	}



	public static function edit_ticket_selections_init() {
		EED_Attendee_Mover::instance()->_edit_ticket_selections_init();
	}



	public function _edit_ticket_selections_init() {
		try {
			$progress_steps_collection = EED_Attendee_Mover::get_progress_steps_collection();
			$this->form_steps = EED_Attendee_Mover::get_form_steps_collection();
			/** @var SequentialStepFormInterface $form_step */
			foreach ( $this->form_steps as $form_step ) {
				$progress_steps_collection->add(
					new \EventEspresso\core\services\progress_steps\ProgressStep(
						$form_step->order(),
						$form_step->slug(),
						$form_step->slug(),
						$form_step->formName()
					),
					$form_step->slug()
				);
			}
			$this->progress_step_manager = new \EventEspresso\core\services\progress_steps\ProgressStepManager(
				'number_bubbles',
				'select_event',
				$progress_steps_collection
			);
			$this->progress_step_manager->setCurrentStep();
			$this->progress_step_manager->enqueueStylesAndScripts();
		} catch ( Exception $e ) {
			// EE_Error::add_error( $e->getMessage(), __FILE__, __FUNCTION__, __LINE__ );
		}
	}



	/**
	 * @return \EventEspresso\core\services\collections\Collection|null
	 * @throws \EventEspresso\Core\Exceptions\InvalidIdentifierException
	 * @throws \EventEspresso\Core\Exceptions\InvalidInterfaceException
	 * @throws \EventEspresso\Core\Exceptions\InvalidFilePathException
	 * @throws \EventEspresso\Core\Exceptions\InvalidDataTypeException
	 * @throws \EventEspresso\Core\Exceptions\InvalidClassException
	 */
	public static function get_form_steps_collection() {
		static $form_steps = null;
		if ( ! $form_steps instanceof \EventEspresso\core\services\collections\Collection ) {
			$loader = new \EventEspresso\core\services\collections\CollectionLoader(
				new \EventEspresso\core\services\collections\CollectionDetails(
					'attendee_mover_form_steps',
					'\EventEspresso\core\libraries\form_sections\SequentialStepFormInterface',
					array(
						'\AttendeeMover\steps\form\SelectEvent',
						'\AttendeeMover\steps\form\SelectTicket',
						'\AttendeeMover\steps\form\VerifyChanges',
					),
					array(),
					'',
					\EventEspresso\core\services\collections\CollectionDetails::ID_CALLBACK_METHOD,
					'slug'
				)
			);
			$form_steps = $loader->getCollection();
		}
		return $form_steps;
	}



	/**
	 * @return \EventEspresso\core\services\collections\Collection|null
	 * @throws \EventEspresso\Core\Exceptions\InvalidInterfaceException
	 */
	public static function get_progress_steps_collection() {
		static $collection = null;
		if ( ! $collection instanceof \EventEspresso\core\services\progress_steps\ProgressStepCollection ) {
			$collection = new \EventEspresso\core\services\progress_steps\ProgressStepCollection();
		}
		return $collection;
	}



	public static function edit_ticket_selection_meta_box() {
		EED_Attendee_Mover::instance()->_edit_ticket_selection_meta_box();
	}



	public function _edit_ticket_selection_meta_box() {
		$request = \EE_Registry::instance()->load_core( 'Request' );
		if ( ! $this->form_steps->setCurrent( $request->get( 'ee-step', 'select_event' ) ) ) {
			throw new \EventEspresso\Core\Exceptions\BaseException( 'Form Step could not be set' );
		}
		$this->progress_step_manager->displaySteps();
		/** @var SequentialStepFormInterface $form_step */
		$form_step = $this->form_steps->current();
		echo \EEH_HTML::h1( $form_step->formName() );
		$form_step->generate();
		$form_step->display();
	}



	/**
	  * displayAttendeeMoverForm
	  *
	  * @access    public
	  * @return    void
	  */
	 public function displayAttendeeMoverForm() {
		 // $CollectionLoaderManager = new \EventEspresso\core\services\collections\CollectionLoader(
			//  new \EventEspresso\core\services\progress_steps\ProgressStepCollection(),
			//  new \EspressoAttendeeMover\core\ProgressStepCollectionDetails()
		 // );
		 // $ProgressStepManager = new \EventEspresso\core\services\progress_steps\ProgressStepManager(
			//  $CollectionLoaderManager->getCollection()
		 // );
		 // $ProgressStepManager->currentStep();
	 }






	/**
	 * 	enqueue_scripts - Load the scripts and css
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public function enqueue_scripts() {
		//Check to see if the attendee_mover css file exists in the '/uploads/espresso/' directory
		if ( is_readable( EVENT_ESPRESSO_UPLOAD_DIR . "css/attendee_mover.css")) {
			//This is the url to the css file if available
			wp_register_style( 'espresso_attendee_mover', EVENT_ESPRESSO_UPLOAD_URL . 'css/espresso_attendee_mover.css' );
		} else {
			// EE attendee_mover style
			wp_register_style( 'espresso_attendee_mover', EE_ATTENDEE_MOVER_URL . 'css/espresso_attendee_mover.css' );
		}
		// attendee_mover script
		wp_register_script(
			'espresso_attendee_mover',
			EE_ATTENDEE_MOVER_URL . 'scripts/espresso_attendee_mover.js',
			array( 'jquery' ),
			EE_ATTENDEE_MOVER_VERSION,
			TRUE
		);

		wp_enqueue_style( 'espresso_attendee_mover' );
		wp_enqueue_script( 'espresso_attendee_mover' );
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
