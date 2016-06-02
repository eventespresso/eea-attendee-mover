<?php
namespace EventEspresso\AttendeeMover\services\commands;

use EventEspresso\core\services\commands\AbstractCommand;
use EventEspresso\core\services\commands\CommandBusInterface;

if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}



/**
 * Class MoveAttendeeCommand
 * Primarily a DTO (Data Transfer Object)
 * for passing a registration and ticket to the MoveAttendeeCommandHandler,
 * but also capable of self executing and passing itself to the CommandBus
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         4.9.0
 */
class MoveAttendeeCommand extends AbstractCommand
{

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
	 * @param \EE_Registration    $old_registration
	 * @param \EE_Ticket          $new_ticket
	 * @param CommandBusInterface $command_bus
	 */
	public function __construct(
		\EE_Registration $old_registration,
		\EE_Ticket $new_ticket,
		CommandBusInterface $command_bus
	) {
		$this->registration = $old_registration;
		$this->ticket = $new_ticket;
		parent::__construct( $command_bus );
	}



	/**
	 * @return \EE_Registration
	 */
	public function registration()
	{
		return $this->registration;
	}



	/**
	 * @return \EE_Ticket
	 */
	public function ticket()
	{
		return $this->ticket;
	}



}
// End of file MoveAttendeeCommand.php
// Location: wp-content/plugins/eea-attendee-mover/services/commands/MoveAttendeeCommand.php