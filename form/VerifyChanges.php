<?php
namespace AttendeeMover\steps\form;

use EE_Form_Section_Proper;
use EventEspresso\Core\Exceptions\InvalidDataTypeException;
use EventEspresso\core\libraries\form_sections\SequentialStepForm;
use InvalidArgumentException;

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
class VerifyChanges extends SequentialStepForm {

	/**
	 * SelectTicket constructor
	 *
	 * @throws \EventEspresso\Core\Exceptions\InvalidDataTypeException
	 * @throws \InvalidArgumentException
	 */
	public function __construct() {
		parent::__construct(
			3,
			__( 'Verify Changes', 'event_espresso' ),
			__( '"Verify Changes" Attendee Mover Step', 'event_espresso' ),
			'verify_changes'
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
	 * @return int
	 */
	protected function getTicketId() {
		$request = \EE_Registry::instance()->load_core( 'Request' );
		return absint( $request->get( 'TKT_ID', 0 ) );
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
	 * @param array $form_data
	 * @return bool
	 * @throws \InvalidArgumentException
	 * @throws InvalidDataTypeException
	 */
	public function process( $form_data = array() ) {
		\EEH_Debug_Tools::printr( __FUNCTION__, __CLASS__, __FILE__, __LINE__, 2 );
		$success = false;
		// process form and toggle $success to true if no errors occur and everything goes ok
		if ( $success ) {
			$this->addRedirectArgs(
				array(
					'EVT_ID' => $this->getEventId(),
					'TKT_ID' => $this->getTicketId(),
					// todo set new
				)
			);
			return true;
		}
		return false;
	}



}
// End of file VerifyChanges.php
// Location: /VerifyChanges.php