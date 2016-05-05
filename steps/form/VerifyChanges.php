<?php
namespace AttendeeMover\steps\form;

use EE_Form_Section_Proper;
use EventEspresso\core\libraries\form_sections\SequentialStepFormInterface;

if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}



/**
 * Class VerifyChanges
 * Description
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         $VID:$
 */
class VerifyChanges implements SequentialStepFormInterface {



	/**
	 * @return int
	 */
	public function order() {
		return 3;
	}



	/**
	 * a public name for the form that can be displayed on the frontend of a site
	 *
	 * @return string
	 */
	public function formName() {
		return __( 'Verify Changes', 'event_espresso' );
	}



	/**
	 * a public name for the form that can be displayed, but only in the admin
	 *
	 * @return string
	 */
	public function adminName() {
		return __( '"Verify Changes" Attendee Mover Step', 'event_espresso' );
	}



	/**
	 * a URL friendly string that can be used for identifying the form
	 *
	 * @return string
	 */
	public function slug() {
		return 'verify_changes';
	}



	/**
	 * called after the form is instantiated
	 * and used for performing any logic that needs to occur early
	 * before any of the other methods are called.
	 * returns true if everything is ok to proceed,
	 * and false if no further form logic should be implemented
	 *
	 * @return boolean
	 */
	public function initialize() {
	}



	/**
	 * used for localizing any string or variables for use in JS
	 *
	 * @return void
	 */
	public function localizeVariables() {
	}



	/**
	 * used for setting up css and js
	 *
	 * @return void
	 */
	public function enqueueStylesAndScripts() {
	}



	/**
	 * creates and returns the actual form
	 *
	 * @return EE_Form_Section_Proper
	 */
	public function generate() {
		\EEH_Debug_Tools::printr( __FUNCTION__, __CLASS__, __FILE__, __LINE__, 2 );
	}



	/**
	 * takes the generated form and displays it along with ony other non-form HTML that may be required
	 * returns a string of HTML that can be directly echoed in a template
	 *
	 * @return string
	 */
	public function display() {
		\EEH_Debug_Tools::printr( __FUNCTION__, __CLASS__, __FILE__, __LINE__, 2 );
	}



	/**
	 * handles processing the form submission
	 * returns true or false depending on whether the form was processed successfully or not
	 *
	 * @return boolean
	 */
	public function process() {
		\EEH_Debug_Tools::printr( __FUNCTION__, __CLASS__, __FILE__, __LINE__, 2 );
	}



}
// End of file VerifyChanges.php
// Location: /VerifyChanges.php