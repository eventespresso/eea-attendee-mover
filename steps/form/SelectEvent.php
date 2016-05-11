<?php
namespace AttendeeMover\steps\form;

use EE_Form_Section_Proper;
use EventEspresso\core\libraries\form_sections\SequentialStepFormInterface;

if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}



/**
 * Class SelectEvent
 * Description
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         4.9.0
 */
class SelectEvent implements SequentialStepFormInterface {
	
	/**
	 *
	 * @var EE_Form_Section_Proper
	 */
	protected $form;



	/**
	 * @return int
	 */
	public function order() {
		return 1;
	}



	/**
	 * a public name for the form that can be displayed on the frontend of a site
	 *
	 * @return string
	 */
	public function formName() {
		return __( 'Select Event', 'event_espresso' );
	}



	/**
	 * a public name for the form that can be displayed, but only in the admin
	 *
	 * @return string
	 */
	public function adminName() {
		return __( '"Select Event" Attendee Mover Step', 'event_espresso' );
	}



	/**
	 * a URL friendly string that can be used for identifying the form
	 *
	 * @return string
	 */
	public function slug() {
		return 'select_event';
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
		$form = $this->generate();
		if( $form instanceof \EE_Form_Section_Proper ) {
			return true; 
		} else {
			return false;
		}
	}



	/**
	 * used for localizing any string or variables for use in JS
	 *
	 * @return void
	 */
	public function localizeVariables() {
		//form variables are localized when calling EE_Form_Section_Base::enqueue_js, which is done during SelectEvent::enqueueStylesAndScripts()
	}



	/**
	 * used for setting up css and js
	 *
	 * @return void
	 */
	public function enqueueStylesAndScripts() {
		$form = $this->generate();
		$form->enqueue_js();
	}



	/**
	 * creates and returns the actual form
	 *
	 * @return EE_Form_Section_Proper
	 */
	public function generate() {
		if( ! $this->form instanceof \EE_Form_Section_Proper ) {
			$this->form = new \EE_Form_Section_Proper(
				array(
					'event' => new \EE_Select_Ajax_Model_Rest_Input(
						array(
							'model_name' => 'Event',
							'display_field_name' => 'EVT_name',
							'query_params' => array(
								'caps' => \EEM_Base::caps_read_admin
							) 
						)
					)
				)
			);
		}
		if( ! $this->form instanceof \EE_Form_Section_Proper ) {
			throw new \EE_Error( __( 'SelectEvent attendee mover step form cannot be generated', 'event_espresso' ));
		}
		return $this->form;
	}



	/**
	 * takes the generated form and displays it along with ony other non-form HTML that may be required
	 * returns a string of HTML that can be directly echoed in a template
	 *
	 * @return string
	 */
	public function display() {
		return $this->generate()->get_html();
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
// End of file SelectEvent.php
// Location: /SelectEvent.php