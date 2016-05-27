<?php
namespace EventEspresso\AttendeeMover\services\commands;

use EventEspresso\core\exceptions\EntityNotFoundException;
use EventEspresso\core\exceptions\InvalidEntityException;
use EventEspresso\core\services\commands\CommandHandlerInterface;
use EventEspresso\core\services\commands\CommandInterface;
use EventEspresso\core\services\registration\Cancel;
use EventEspresso\core\services\registration\Copy;
use EventEspresso\core\services\registration\Create;

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
 * @since         $VID:$
 */
class MoveAttendeeCommandHandler implements CommandHandlerInterface
{

	/**
	 * @param CommandInterface $command
	 * @throws \EE_Error
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
		$new_ticket = $command->ticket();
		// have we already processed this registration change ? if so, then bail...
		$this->checkIfRegistrationChangeAlreadyProcessed( $old_registration, $new_ticket );
		$old_ticket = $old_registration->ticket();
		// get transaction for original registration
		$transaction = $this->getTransaction( $old_registration );
		// apply any applicable promotions that were initially used during registration to new line items
		// do_action(
		// 	'AHEE__\AttendeeMover\form\Complete__process__new_ticket_line_item_added',
		// 	$new_ticket_line_item,
		// 	$new_ticket,
		// 	$old_ticket,
		// 	$old_registration
		// );
		// create new registration and it's associated line items
		$new_registration = Create::registrationAndLineItemForTransaction(
			$transaction,
			$old_ticket,
			$old_registration->count()
		);
		// move/copy over additional data from old registration, like reg form question answers, and reg payments
		Copy::registrationDetails( $new_registration, $old_registration );
		Copy::registrationPayments( $new_registration, $old_registration );
		// then cancel original line item for ticket
		Cancel::registrationTicketAndLineItem( $old_registration );
		// reset transaction status back to incomplete
		$transaction->set_status( \EEM_Transaction::incomplete_status_code );
		// update transaction and all line item totals and subtotals
		$transaction->total_line_item()->recalculate_total_including_taxes();
		/** @type \EE_Registration_Processor $registration_processor */
		$registration_processor = \EE_Registry::instance()->load_class( 'Registration_Processor' );
		$registration_processor->update_registration_status_and_trigger_notifications( $new_registration );
		// tag the old registration as moved
		$old_registration->add_extra_meta(
			'registration-moved',
			array( 'TKT_ID' => $new_ticket->ID(), 'NEW_REG_ID' => $new_registration->ID() )
		);
		return $new_registration;
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
		$reg_moved = $registration->get_extra_meta( 'registration-moved', true, array() );
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
}
// End of file MoveAttendeeCommandHandler.php
// Location: wp-content/plugins/eea-attendee-mover/services/commands/MoveAttendeeCommandHandler.php