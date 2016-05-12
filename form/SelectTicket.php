<?php
namespace AttendeeMover\form;

use EE_Form_Section_Proper;
use EventEspresso\Core\Exceptions\InvalidDataTypeException;
use EventEspresso\core\libraries\form_sections\SequentialStepForm;
use InvalidArgumentException;

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
	 * @throws InvalidDataTypeException
	 * @throws InvalidArgumentException
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
	 * @return int
	 */
	protected function getEventId() {
		$request = \EE_Registry::instance()->load_core( 'Request' );
		return absint( $request->get( 'EVT_ID', 0 ) );
	}



	/**
	 * creates and returns the actual form
	 *
	 * @return EE_Form_Section_Proper
	 */
	public function generate() {
		\EEH_Debug_Tools::printr( __FUNCTION__, __CLASS__, __FILE__, __LINE__, 2 );
		$EVT_ID = $this->getEventId();
		\EEH_Debug_Tools::printr( $EVT_ID, '$EVT_ID', __FILE__, __LINE__ );
	}



	/**
	 * handles processing the form submission
	 * returns true or false depending on whether the form was processed successfully or not
	 *
	 * @param array $form_data
	 * @return bool
	 * @throws \InvalidArgumentException
	 * @throws InvalidDataTypeException
	 */
	public function process( $form_data = array() ) {
		\EEH_Debug_Tools::printr( __FUNCTION__, __CLASS__, __FILE__, __LINE__, 2 );
		$TKT_ID = 0;
		// process form and set $TKT_ID
		if ( $TKT_ID ) {
			$this->addRedirectArgs(
				array( 'EVT_ID' => $this->getEventId(),  'TKT_ID' => $TKT_ID )
			);
			return true;
		}
		return false;
	}



}
// End of file SelectTicket.php
// Location: /SelectTicket.php