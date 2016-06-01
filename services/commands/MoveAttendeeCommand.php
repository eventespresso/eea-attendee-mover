<?php
namespace EventEspresso\AttendeeMover\services\commands;

use EventEspresso\core\services\commands\CommandInterface;

if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}



/**
 * Class MoveAttendeeCommand
 * A DTO (Data Transfer Object) for passing a registration and ticket to the MoveAttendeeCommandHandler
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         4.9.0
 */
class MoveAttendeeCommand implements CommandInterface{

	/**
	 * @var \EE_Registration $registration
	 */
	private $registration;

	/**
	 * @var \EE_Ticket $ticket
	 */
	private $ticket;



	/**
	 * MoveAttendeeCommand constructor.
	 *
	 * @param \EE_Registration $old_registration
	 * @param \EE_Ticket       $new_ticket
	 */
	public function __construct( \EE_Registration $old_registration, \EE_Ticket $new_ticket ) {
		$this->registration = $old_registration;
		$this->ticket = $new_ticket;
	}



	/**
	 * @return \EE_Registration
	 */
	public function registration() {
		return $this->registration;
	}



	/**
	 * @return \EE_Ticket
	 */
	public function ticket() {
		return $this->ticket;
	}


}
// End of file MoveAttendeeCommand.php
// Location: wp-content/plugins/eea-attendee-mover/services/commands/MoveAttendeeCommand.php