<?php
namespace AttendeeMover\steps\form;

use EE_Form_Section_Proper;
use EventEspresso\core\libraries\form_sections\SequentialStepForm;

if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}



/**
 * Class SelectTicket
 * Description
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         4.9.0
 */
class SelectTicket extends SequentialStepForm {

	/**
	 * SelectTicket constructor
	 *
	 * @throws \EventEspresso\Core\Exceptions\InvalidDataTypeException
	 * @throws \InvalidArgumentException
	 */
	public function __construct() {
		parent::__construct(
			2,
			__( 'Select Ticket', 'event_espresso' ),
			__( '"Select Ticket" Attendee Mover Step', 'event_espresso' ),
			'select_ticket'
		);
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
	 * handles processing the form submission
	 * returns true or false depending on whether the form was processed successfully or not
	 *
	 * @return boolean
	 */
	public function process() {
		\EEH_Debug_Tools::printr( __FUNCTION__, __CLASS__, __FILE__, __LINE__, 2 );
	}



}
// End of file SelectTicket.php
// Location: /SelectTicket.php