<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
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
		 // EE_Config::register_route( 'attendee_mover', 'EED_Attendee_Mover', 'run' );
	 }

	 /**
	  * 	set_hooks_admin - for hooking into EE Admin Core, other modules, etc
	  *
	  *  @access 	public
	  *  @return 	void
	  */
	 public static function set_hooks_admin() {
		 // ajax hooks
		 add_action( 'wp_ajax_get_attendee_mover', array( 'EED_Attendee_Mover', 'get_attendee_mover' ));
		 add_action( 'wp_ajax_nopriv_get_attendee_mover', array( 'EED_Attendee_Mover', 'get_attendee_mover' ));
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
	 *		@ override magic methods
	 *		@ return void
	 */
	public function __set($a,$b) { return FALSE; }
	public function __get($a) { return FALSE; }
	public function __isset($a) { return FALSE; }
	public function __unset($a) { return FALSE; }
	public function __clone() { return FALSE; }
	public function __wakeup() { return FALSE; }
	public function __destruct() { return FALSE; }

 }
// End of file EED_Attendee_Mover.module.php
// Location: /wp-content/plugins/eea-attendee-mover/EED_Attendee_Mover.module.php
