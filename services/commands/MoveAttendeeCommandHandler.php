<?php
namespace EventEspresso\AttendeeMover\services\commands;

use EventEspresso\core\exceptions\EntityNotFoundException;
use EventEspresso\core\exceptions\InvalidEntityException;
use EventEspresso\core\exceptions\InsufficientPermissionsException;
use EventEspresso\core\services\capabilities\RegistrationsCapChecker;
use EventEspresso\core\services\commands\CommandBusInterface;
use EventEspresso\core\services\commands\CommandHandler;
use EventEspresso\core\services\commands\CommandInterface;

if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}



/**
 * Class MoveAttendee
 * receives a MoveAttendeeCommand object via the handle() method,
 * which must contains valid registration and ticket objects,
 * and returns a new registration
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         1.0.0
 */
class MoveAttendeeCommandHandler extends CommandHandler
{

	/**
	 * @var RegistrationsCapChecker $cap_checker
	 */
	private $cap_checker;



	/**
	 * MoveAttendeeCommandHandler constructor.
	 *
	 * @param RegistrationsCapChecker $cap_checker
	 * @param \EE_Registry            $registry
	 * @param CommandBusInterface     $command_bus
	 */
	public function __construct(
		RegistrationsCapChecker $cap_checker,
		\EE_Registry $registry,
		CommandBusInterface $command_bus
	)
	{
		$this->cap_checker = $cap_checker;
		parent::__construct( $registry, $command_bus );
	}



	/**
	 * @param CommandInterface $command
	 * @throws \RuntimeException
	 * @throws \EventEspresso\core\exceptions\EntityNotFoundException
	 * @throws \EventEspresso\core\exceptions\UnexpectedEntityException
	 * @throws \OutOfRangeException
	 * @return mixed
	 */
	public function handle( CommandInterface $command )
	{
		/** @var MoveAttendeeCommand $command */
		if ( ! $command instanceof MoveAttendeeCommand ) {
			throw new InvalidEntityException( get_class( $command ), 'MoveAttendeeCommand' );
		}
		$old_registration = $command->registration();
		// You can DO IT !!! ... or err... maybe you can't !
		$this->checkCapabilities( $old_registration );
		$new_ticket = $command->ticket();
		// have we already processed this registration change ? if so, then bail...
		$this->checkIfRegistrationChangeAlreadyProcessed( $old_registration, $new_ticket );
		// get transaction for original registration
		$transaction = $this->getTransaction( $old_registration );
		// create new line item for new ticket
		$ticket_line_item = $this->executeSubCommand(
			'EventEspresso\core\services\ticket\CreateTicketLineItemCommand',
			array( $transaction, $new_ticket, 1 )
		);
		// then generate a new registration from that
		$new_registration = $this->executeSubCommand(
			'EventEspresso\core\services\registration\CreateRegistrationCommand',
			array(
				$transaction,
				$ticket_line_item,
				$old_registration->count(),
				$old_registration->group_size(),
			)
		);
		// move/copy over additional data from old registration, like reg form question answers
		$this->executeSubCommand(
			'EventEspresso\core\services\registration\CopyRegistrationDetailsCommand',
			array( $new_registration, $old_registration )
		);
		// and registration payments
		$this->executeSubCommand(
			'EventEspresso\core\services\registration\CopyRegistrationPaymentsCommand',
			array( $new_registration, $old_registration )
		);
		// then cancel original line item for ticket
		$this->executeSubCommand(
			'EventEspresso\core\services\registration\CancelRegistrationAndTicketLineItemCommand',
			array( $old_registration )
		);
		// perform final status updates and trigger notifications
		$this->executeSubCommand(
			'EventEspresso\core\services\registration\UpdateRegistrationAndTransactionAfterChangeCommand',
			array( $new_registration )
		);
		// tag registrations for identification purposes
		$this->addExtraMeta( $old_registration, $new_registration, $new_ticket );
		return $new_registration;
	}



	/**
	 * @param \EE_Registration $old_registration
	 * @throws InsufficientPermissionsException
	 */
	protected function checkCapabilities( \EE_Registration $old_registration )
	{
		$this->cap_checker->editRegistrations(
			$old_registration,
			__( 'Edit Registration Ticket Selection', 'event_espresso' )
		);
	}



	/**
	 * @param \EE_Registration $registration
	 * @param \EE_Ticket       $new_ticket
	 * @return bool
	 * @throws \RuntimeException
	 * @throws \EE_Error
	 */
	protected function checkIfRegistrationChangeAlreadyProcessed(
		\EE_Registration $registration,
		\EE_Ticket $new_ticket
	) {
		$reg_moved = $registration->get_extra_meta( 'registration-moved-to', true, array() );
		if ( isset( $reg_moved['TKT_ID'] ) && $reg_moved['TKT_ID'] === $new_ticket->ID() ) {
			$reg_details_url = add_query_arg(
				array(
					'action'  => 'view_registration',
					'_REG_ID' => $registration->ID(),
				),
				REG_ADMIN_URL
			);
			throw new \RuntimeException(
				sprintf(
					__(
						'This exact registration change has already been processed. Please select a different event and/or ticket to change this registration. %3$sThe original cancelled registration can be viewed on the %1$sregistration details admin page%2$s.',
						'event_espresso'
					),
					'<a href="' . $reg_details_url . '">',
					'</a>',
					'<br />'
				)
			);
		}
	}



	/**
	 * @param \EE_Registration $registration
	 * @return \EE_Transaction
	 * @throws \EventEspresso\core\exceptions\EntityNotFoundException
	 */
	protected function getTransaction( \EE_Registration $registration )
	{
		$transaction = $registration->transaction();
		if ( ! $transaction instanceof \EE_Transaction ) {
			throw new EntityNotFoundException( 'Transaction ID', $registration->transaction_ID() );
		}
		return $transaction;
	}



	/**
	 * @param \EE_Registration $old_registration
	 * @param \EE_Registration $new_registration
	 * @param \EE_Ticket       $new_ticket
	 */
	protected function addExtraMeta(
		\EE_Registration $old_registration,
		\EE_Registration $new_registration,
		\EE_Ticket $new_ticket
	) {
		// tag the old registration as moved
		$old_registration->add_extra_meta(
			'registration-moved-to',
			array( 'TKT_ID' => $new_ticket->ID(), 'NEW_REG_ID' => $new_registration->ID() )
		);
		// and the new registration as well
		$new_registration->add_extra_meta(
			'registration-moved-from',
			array( 'TKT_ID' => $new_ticket->ID(), 'OLD_REG_ID' => $old_registration->ID() )
		);
	}

}
// End of file MoveAttendeeCommandHandler.php
// Location: wp-content/plugins/eea-attendee-mover/services/commands/MoveAttendeeCommandHandler.php