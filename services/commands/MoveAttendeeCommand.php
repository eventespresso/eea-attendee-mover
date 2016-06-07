<?php
namespace EventEspresso\AttendeeMover\services\commands;

use EventEspresso\core\services\commands\CommandBusInterface;
use EventEspresso\core\services\commands\SelfExecutingCommand;

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
class MoveAttendeeCommand extends SelfExecutingCommand
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
	 * @var \EE_Registration $new_registration
	 */
	protected $new_registration;



	/**
	 * MoveAttendeeCommand constructor.
	 *
	 * @param \EE_Registration    $old_registration
	 * @param \EE_Ticket          $new_ticket
	 * @param \EE_Registry        $registry
	 * @param CommandBusInterface $command_bus
	 */
	public function __construct(
		\EE_Registration $old_registration,
		\EE_Ticket $new_ticket,
		\EE_Registry $registry,
		CommandBusInterface $command_bus
	) {
		$this->registration = $old_registration;
		$this->ticket = $new_ticket;
		parent::__construct( $registry, $command_bus, 'new_registration' );
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



	/**
	 * @return \EE_Registration
	 */
	public function newRegistration()
	{
		return $this->new_registration;
	}



}
// End of file MoveAttendeeCommand.php
// Location: wp-content/plugins/eea-attendee-mover/services/commands/MoveAttendeeCommand.php