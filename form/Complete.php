<?php
namespace AttendeeMover\form;

use EventEspresso\Core\Exceptions\EntityNotFoundException;
use EventEspresso\Core\Exceptions\UnexpectedEntityException;

if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}



/**
 * Class Complete
 * final form in the sequential form steps for the Attendee Mover admin page
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         1.0.0
 */
class Complete extends Step
{


	/**
	 * SelectTicket constructor
	 *
	 * @throws \EventEspresso\Core\Exceptions\InvalidDataTypeException
	 * @throws \InvalidArgumentException
	 * @throws \DomainException
	 */
	public function __construct()
	{
		$this->setDisplayable();
		parent::__construct(
			4,
			__( 'Complete', 'event_espresso' ),
			__( '"Complete" Attendee Mover Step', 'event_espresso' ),
			'complete'
		);
		$this->REG_ID = $this->getRegId();
		$this->EVT_ID = $this->getEventId();
		$this->TKT_ID = $this->getTicketId();
		$this->addFormActionArgs(
			array(
				'EVT_ID' => $this->EVT_ID,
				'TKT_ID' => $this->TKT_ID,
			)
		);
	}



	/**
	 * creates and returns the actual form
	 *
	 * @throws \EventEspresso\Core\Exceptions\EntityNotFoundException
	 * @throws \LogicException
	 * @throws \EE_Error
	 */
	public function generate()
	{
		$this->setForm(
			new \EE_Form_Section_Proper(
				array(
					'name'        => $this->formName(),
					'subsections' => array()
				)
			)
		);
		return $this->form();
	}



	/**
	 * normally displays the form, but we are going to skip right to processing our changes
	 *
	 * @return string
	 * @throws \EE_Error
	 * @throws \LogicException
	 * @throws \InvalidArgumentException
	 * @throws \EventEspresso\Core\Exceptions\InvalidFormSubmissionException
	 * @throws \EventEspresso\Core\Exceptions\EntityNotFoundException
	 */
	public function display() {}



	/**
	 * handles processing the form submission
	 * returns true or false depending on whether the form was processed successfully or not
	 *
	 * @param array $form_data
	 * @return bool
	 * @throws \RuntimeException
	 * @throws \EventEspresso\Core\Exceptions\InvalidDataTypeException
	 * @throws \EventEspresso\core\exceptions\UnexpectedEntityException
	 * @throws \LogicException
	 * @throws \EventEspresso\Core\Exceptions\InvalidFormSubmissionException
	 * @throws \EE_Error
	 * @throws \EventEspresso\Core\Exceptions\EntityNotFoundException
	 * @throws \InvalidArgumentException
	 */
	public function process( $form_data = array() )
	{
		$old_registration = $this->getRegistration( $this->REG_ID );
		// have we already processed this registration change ? if so, then bail...
		$this->checkIfRegistrationChangeAlreadyProcessed( $old_registration );
		$old_ticket = $old_registration->ticket();
		// get transaction for original registration
		$transaction = $this->getTransaction( $old_registration );
		// create new registration and it's associated line items
		$new_registration = $this->createNewRegistrationAndLineItem( $transaction, $old_registration, $old_ticket );
		// move/copy over additional data from old registration, like reg form question answers, and reg payments
		$this->copyRegistrationDetails( $new_registration, $old_registration );
		$this->applyPaymentsToNewRegistration( $new_registration, $old_registration );
		// then cancel original line item for ticket
		$this->cancelOldRegistrationTicketAndLineItem( $transaction, $old_registration, $old_ticket);
		// reset transaction status back to incomplete
		$transaction->set_status( \EEM_Transaction::incomplete_status_code );
		// update transaction and all line item totals and subtotals
		$transaction->total_line_item()->recalculate_total_including_taxes();
		$this->updateNewRegistrationStatusAndTriggerNotifications( $new_registration );
		// tag the old registration as moved
		$old_registration->add_extra_meta(
			'registration-moved',
			array( 'TKT_ID' => $this->TKT_ID, 'NEW_REG_ID' => $new_registration->ID() )
		);
		// setup redirect to new registration details admin page
		$this->setRedirectUrl( REG_ADMIN_URL );
		$this->addRedirectArgs(
			array(
				'action' => 'view_registration',
				'_REG_ID' => $new_registration->ID()
			)
		);
		\EE_Error::add_success(
			sprintf(
				__(
					'Registration ID:%1$s has been successfully cancelled, and Registration ID:%2$s has been created to replace it.',
					'event_espresso'
				),
				$old_registration->ID(),
				$new_registration->ID()
			)
		);
		return true;
	}



	/**
	 * @param \EE_Registration $registration
	 * @return boolean
	 * @throws \RuntimeException
	 * @throws \EE_Error
	 */
	protected function checkIfRegistrationChangeAlreadyProcessed( \EE_Registration $registration )
	{
		$reg_moved = $registration->get_extra_meta( 'registration-moved', true, array() );
		if ( isset( $reg_moved['TKT_ID'] ) && $reg_moved['TKT_ID'] === $this->TKT_ID ) {
			$reg_details_url = add_query_arg(
				array(
					'action'  => 'view_registration',
					'_REG_ID' => $this->REG_ID,
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
	 * @throws \EventEspresso\Core\Exceptions\EntityNotFoundException
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
	 * @param \EE_Transaction  $transaction
	 * @param \EE_Registration $old_registration
	 * @param \EE_Ticket       $old_ticket
	 * @return \EE_Registration
	 * @throws \EventEspresso\Core\Exceptions\UnexpectedEntityException
	 * @throws \EventEspresso\Core\Exceptions\EntityNotFoundException
	 * @throws \EE_Error
	 */
	protected function createNewRegistrationAndLineItem(
		\EE_Transaction $transaction,
		\EE_Registration $old_registration,
		\EE_Ticket $old_ticket
	) {
		$new_ticket = $this->getTicket( $this->TKT_ID );
		// create new line item for ticket
		$new_ticket_line_item = \EEH_Line_Item::add_ticket_purchase(
			$transaction->total_line_item(),
			$new_ticket,
			1
		);
		// apply any applicable promotions that were initially used during registration to new line items
		do_action(
			'AHEE__\AttendeeMover\form\Complete__process__new_ticket_line_item_added',
			$new_ticket_line_item,
			$new_ticket,
			$old_ticket,
			$old_registration
		);
		// create new registration from new ticket line item
		return $this->addRegistrationToTransaction(
			$transaction,
			$old_registration,
			$new_ticket_line_item
		);
	}

	/**
	 * @param \EE_Transaction  $transaction
	 * @param \EE_Registration $old_registration
	 * @param \EE_Line_Item    $ticket_line_item
	 * @return \EE_Registration
	 * @throws \EventEspresso\core\exceptions\UnexpectedEntityException
	 * @throws \EE_Error
	 */
	protected function addRegistrationToTransaction(
		\EE_Transaction $transaction,
		\EE_Registration $old_registration,
		\EE_Line_Item $ticket_line_item
	) {
		/** @type \EE_Registration_Processor $registration_processor */
		$registration_processor = \EE_Registry::instance()->load_class( 'Registration_Processor' );
		$registration = $registration_processor->generate_ONE_registration_from_line_item(
			$ticket_line_item,
			$transaction,
		// reuse attendee number from previous registration
			$old_registration->count()
		);
		if ( ! $registration instanceof \EE_Registration ) {
			throw new UnexpectedEntityException( $registration, 'EE_Registration' );
		}
		$registration->set_reg_date( time() );
		$registration->save();
		return $registration;
	}



	/**
	 * @param \EE_Registration $target_registration
	 * @param \EE_Registration $registration_to_copy
	 * @throws \EventEspresso\Core\Exceptions\EntityNotFoundException
	 * @throws \EE_Error
	 * @throws \EventEspresso\core\exceptions\UnexpectedEntityException
	 */
	protected function copyRegistrationDetails(
		\EE_Registration $target_registration,
		\EE_Registration $registration_to_copy
	) {
		// copy attendee
		$target_registration->set_attendee_id( $registration_to_copy->attendee_ID() );
		// $target_registration->set_status( $registration_to_copy->status_ID() );
		$target_registration->save();
		// get answers to previous reg questions
		$answers = $this->reindexAnswersByQuestionId( $registration_to_copy->answers() );
		// get questions to new event reg form
		$new_event = $this->getRegistrationEvent( $target_registration );
		$question_groups = $new_event->question_groups(
			array(
				array(
					'Event.EVT_ID'                     => $new_event->ID(),
					'Event_Question_Group.EQG_primary' => $registration_to_copy->is_primary_registrant()
				),
				'order_by' => array( 'QSG_order' => 'ASC' )
			)
		);
		foreach ( $question_groups as $question_group ) {
			if ( $question_group instanceof \EE_Question_Group ) {
				foreach ( $question_group->questions() as $question ) {
					if ( $question instanceof \EE_Question ) {
						$this->generateNewAnswer(
							$question,
							$target_registration,
							$answers
						);
					}
				}
			}
		}
	}



	/**
	 * @param \EE_Registration $registration
	 * @return \EE_Event
	 * @throws \EventEspresso\Core\Exceptions\EntityNotFoundException
	 */
	protected function getRegistrationEvent( \EE_Registration $registration )
	{
		$event = $registration->event();
		if ( ! $event instanceof \EE_Event ) {
			throw new EntityNotFoundException( 'Event ID', $registration->event_ID() );
		}
		return $event;
	}



	/**
	 * @param \EE_Answer[] $answers
	 * @return array
	 * @throws \EE_Error
	 */
	protected function reindexAnswersByQuestionId( array $answers )
	{
		$reindexed_answers = array();
		foreach ( $answers as $answer ) {
			if ( $answer instanceof \EE_Answer ) {
				$reindexed_answers[ $answer->question_ID() ] = $answer->value();
			}
		}
		return $reindexed_answers;
	}



	/**
	 * @param \EE_Question     $question
	 * @param \EE_Registration $registration
	 * @param                  $previous_answers
	 * @return \EE_Answer
	 * @throws \EventEspresso\core\exceptions\UnexpectedEntityException
	 * @throws \EE_Error
	 */
	protected function generateNewAnswer( \EE_Question $question, \EE_Registration $registration, $previous_answers )
	{
		$old_answer_value = isset( $previous_answers[ $question->ID() ] )
			? $previous_answers[ $question->ID() ]
			: '';

		$new_answer = \EE_Answer::new_instance(
			array(
				'QST_ID'    => $question->ID(),
				'REG_ID'    => $registration->ID(),
				'ANS_value' => $old_answer_value,
			)
		);
		if ( ! $new_answer instanceof \EE_Answer ) {
			throw new UnexpectedEntityException( $new_answer, 'EE_Answer' );
		}
		$new_answer->save();
		return $new_answer;
	}



	/**
	 * @param \EE_Registration $target_registration
	 * @param \EE_Registration $registration_to_copy
	 * @throws \EE_Error
	 * @throws \EventEspresso\core\exceptions\UnexpectedEntityException
	 */
	protected function applyPaymentsToNewRegistration(
		\EE_Registration $target_registration,
		\EE_Registration $registration_to_copy
	) {
		$previous_payments = $registration_to_copy->registration_payments();
		foreach ( $previous_payments as $previous_payment ) {
			if (
				$previous_payment instanceof \EE_Registration_Payment
				&& $previous_payment->payment() instanceof \EE_Payment
				&& $previous_payment->payment()->is_approved()
			) {
				$new_registration_payment = \EE_Registration_Payment::new_instance(
					array(
						'REG_ID'     => $target_registration->ID(),
						'PAY_ID'     => $previous_payment->ID(),
						'RPY_amount' => $previous_payment->amount(),
					)
				);
				if ( ! $new_registration_payment instanceof \EE_Registration_Payment ) {
					throw new UnexpectedEntityException( $new_registration_payment, 'EE_Registration_Payment' );
				}
				$new_registration_payment->save();
				$target_registration->set_paid( $previous_payment->amount() );
				$target_registration->save();
				// if new reg payment is good, then set old reg payment amount to zero
				$previous_payment->set_amount( 0 );
				$previous_payment->save();
				$registration_to_copy->set_paid( 0 );
				$registration_to_copy->save();
			}
		}
	}



	/**
	 * @param \EE_Transaction $transaction
	 * @param \EE_Registration $registration
	 * @param \EE_Ticket      $ticket
	 * @throws \EE_Error
	 * @throws \EventEspresso\Core\Exceptions\EntityNotFoundException
	 */
	protected function cancelOldRegistrationTicketAndLineItem(
		\EE_Transaction $transaction,
		\EE_Registration $registration,
		\EE_Ticket $ticket
	) {
		// get line item for original ticket
		$ticket_line_item = $this->getTicketLineItem( $transaction, $ticket );
		// get event line item for ticket and decrement quantity
		\EEH_Line_Item::decrement_quantity(
			\EEH_Line_Item::get_event_line_item_for_ticket(
				$transaction->total_line_item(),
				$ticket
			)
		);
		// then cancel original line item for ticket
		\EEH_Line_Item::cancel_line_item( $ticket_line_item );
		// cancel original registration
		$registration->set_status( \EEM_Registration::status_id_cancelled );
		$registration->save();
	}



	/**
	 * @param \EE_Transaction $transaction
	 * @param \EE_Ticket      $ticket
	 * @return \EE_Line_Item
	 * @throws \EventEspresso\Core\Exceptions\EntityNotFoundException
	 * @throws \EE_Error
	 */
	protected function getTicketLineItem( \EE_Transaction $transaction, \EE_Ticket $ticket )
	{
		$line_item = null;
		$ticket_line_items = \EEH_Line_Item::get_line_items_by_object_type_and_IDs(
			$transaction->total_line_item(),
			'Ticket',
			array( $ticket->ID() )
		);
		foreach ( $ticket_line_items as $ticket_line_item ) {
			if (
				$ticket_line_item instanceof \EE_Line_Item
				&& $ticket_line_item->OBJ_type() === 'Ticket'
				&& $ticket_line_item->OBJ_ID() === $ticket->ID()
			) {
				$line_item = $ticket_line_item;
				break;
			}
		}
		if ( ! ( $line_item instanceof \EE_Line_Item && $line_item->OBJ_type() === 'Ticket' ) ) {
			throw new EntityNotFoundException( 'Line Item Ticket ID', $ticket->ID() );
		}
		return $line_item;
	}



	/**
	 * @param \EE_Registration $new_registration
	 * @throws \EE_Error
	 */
	protected function updateNewRegistrationStatusAndTriggerNotifications( \EE_Registration $new_registration )
	{
		/** @type \EE_Registration_Processor $registration_processor */
		$registration_processor = \EE_Registry::instance()->load_class( 'Registration_Processor' );
		$registration_processor->toggle_incomplete_registration_status_to_default( $new_registration, false );
		$registration_processor->toggle_registration_status_for_default_approved_events( $new_registration, false );
		$registration_processor->toggle_registration_status_if_no_monies_owing( $new_registration, false );
		$new_registration->save();
		// trigger notifications
		$registration_processor->trigger_registration_update_notifications( $new_registration );
	}



}
// End of file Complete.php
// Location: /Complete.php