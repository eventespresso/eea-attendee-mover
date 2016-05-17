<?php
namespace AttendeeMover\form;

use EE_Event;
use EE_Registration;
use EE_Ticket;
use EEM_Event;
use EEM_Registration;
use EEM_Ticket;
use EventEspresso\Core\Exceptions\EntityNotFoundException;
use EventEspresso\core\libraries\form_sections\SequentialStepForm;

if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}



/**
 * Class Step
 * Description
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         4.9.0
 */
abstract class Step extends SequentialStepForm {

	/**
	 * @var int $REG_ID
	 */
	protected $REG_ID = 0;

	/**
	 * @var int $EVT_ID
	 */
	protected $EVT_ID = 0;

	/**
	 * @var int $TKT_ID
	 */
	protected $TKT_ID = 0;



	/**
	 * @return int
	 */
	protected function getRegId() {
		$request = \EE_Registry::instance()->load_core( 'Request' );
		return absint( $request->get( '_REG_ID', 0 ) );
	}



	/**
	 * @param int $REG_ID
	 * @return \EE_Registration
	 * @throws \EventEspresso\Core\Exceptions\EntityNotFoundException
	 */
	protected function getRegistration( $REG_ID = 0 ) {
		$registration = EEM_Registration::instance()->get_one_by_ID( $REG_ID );
		if ( ! $registration instanceof EE_Registration ) {
			throw new EntityNotFoundException( 'Registration ID', $REG_ID );
		}
		return $registration;
	}



	/**
	 * @return int
	 */
	protected function getEventId() {
		$request = \EE_Registry::instance()->load_core( 'Request' );
		return absint( $request->get( 'EVT_ID', 0 ) );
	}



	/**
	 * @param int $EVT_ID
	 * @return \EE_Event
	 * @throws \EventEspresso\Core\Exceptions\EntityNotFoundException
	 */
	protected function getEvent( $EVT_ID = 0 ) {
		$event = EEM_Event::instance()->get_one_by_ID( $EVT_ID );
		if ( ! $event instanceof EE_Event ) {
			throw new EntityNotFoundException( 'Event ID', $EVT_ID );
		}
		return $event;
	}



	/**
	 * @return int
	 */
	protected function getTicketId() {
		$request = \EE_Registry::instance()->load_core( 'Request' );
		return absint( $request->get( 'TKT_ID', 0 ) );
	}



	/**
	 * @param int $TKT_ID
	 * @return \EE_Ticket
	 * @throws \EventEspresso\Core\Exceptions\EntityNotFoundException
	 */
	protected function getTicket( $TKT_ID = 0 ) {
		$ticket = EEM_Ticket::instance()->get_one_by_ID( $TKT_ID );
		if ( ! $ticket instanceof EE_Ticket ) {
			throw new EntityNotFoundException( 'Ticket ID', $TKT_ID );
		}
		return $ticket;
	}



}
// End of file Step.php
// Location: /Step.php