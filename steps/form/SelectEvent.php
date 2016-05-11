<?php
namespace AttendeeMover\steps\form;

use EE_Form_Section_Proper;
use EventEspresso\core\libraries\form_sections\SequentialStepForm;

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
class SelectEvent extends SequentialStepForm {

	/**
	 * SelectEvent constructor.
	 *
	 * @throws \EventEspresso\Core\Exceptions\InvalidDataTypeException
	 * @throws \InvalidArgumentException
	 */
	public function __construct() {
		parent::__construct(
			1,
			__( 'Select Event', 'event_espresso' ),
			__( '"Select Event" Attendee Mover Step', 'event_espresso' ),
			'select_event'
		);
	}



	/**
	 * creates and returns the actual form
	 *
	 * @return EE_Form_Section_Proper
	 * @throws \LogicException
	 */
	public function generate() {
		\EEH_Debug_Tools::printr( __FUNCTION__, __CLASS__, __FILE__, __LINE__, 2 );
		$this->setForm(
			new \EE_Form_Section_Proper(
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
			)
		);
		return $this->form();
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