<?php
namespace EventEspresso\AttendeeMover\form;

use EE_Admin_Page;
use EE_Error;
use EE_Form_Section_HTML;
use EE_Form_Section_Proper;
use EED_Attendee_Mover;
use EEH_HTML;
use EventEspresso\core\libraries\form_sections\form_handlers\FormHandler;
use EventEspresso\core\libraries\form_sections\form_handlers\SequentialStepForm;
use InvalidArgumentException;
use EventEspresso\core\exceptions\InvalidDataTypeException;

if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}



/**
 * Class VerifyChanges
 * the third form in the sequential form steps for the Attendee Mover admin page
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         1.0.0
 */
class VerifyChanges extends Step {

	/**
	 * SelectTicket constructor
	 *
	 * @param \EE_Registry $registry
	 * @throws \EventEspresso\core\exceptions\InvalidDataTypeException
	 * @throws \InvalidArgumentException
	 * @throws \DomainException
	 */
	public function __construct( \EE_Registry $registry ) {
		parent::__construct(
			3,
			__( 'Verify Changes', 'event_espresso' ),
			__( '"Verify Changes" Attendee Mover Step', 'event_espresso' ),
			'verify_changes',
			'',
			FormHandler::ADD_FORM_TAGS_AND_SUBMIT,
			$registry
		);
	}



	/**
	 * creates and returns the actual form
	 *
	 * @return EE_Form_Section_Proper
	 * @throws \EventEspresso\core\exceptions\EntityNotFoundException
	 * @throws \LogicException
	 * @throws \EE_Error
	 */
	public function generate() {
		$registration = $this->getRegistration( $this->REG_ID );
		$old_event = $registration->event_obj();
		$old_ticket = $registration->ticket();
		$new_event = $this->getEvent( $this->EVT_ID );
		$new_ticket = $this->getTicket( $this->TKT_ID );
		$price_change = $new_ticket->price() - $old_ticket->price();
		$price_class = $price_change < 0 ? ' ee-txn-refund' : '';
		$th1 = __( 'Attendee Name', 'event_espresso' );
		$th2 = __( 'Old Event', 'event_espresso' );
		$th3 = __( 'Old Ticket', 'event_espresso' );
		$th4 = __( 'New Event', 'event_espresso' );
		$th5 = __( 'New Ticket', 'event_espresso' );
		$th6 = __( 'Price Change', 'event_espresso' );
		$this->setForm(
			new \EE_Form_Section_Proper(
				array(
					'name'            => $this->slug(),
					'layout_strategy' => new \EE_Div_Per_Section_Layout(),
					'subsections'     => array(
						'changes' => new EE_Form_Section_HTML(
							\EEH_HTML::table(
								\EEH_HTML::thead(
									\EEH_HTML::tr(
										\EEH_HTML::th( $th1 ) .
										\EEH_HTML::th( $th2 ) .
										\EEH_HTML::th( $th3 ) .
										\EEH_HTML::th( $th4 ) .
										\EEH_HTML::th( $th5 ) .
										\EEH_HTML::th( $th6 )
									)
								) .
								\EEH_HTML::tbody(
									\EEH_HTML::tr(
										\EEH_HTML::td(
											$registration->attendee()->name(),
											'',
											'am-attendee-name-td',
											'',
											'data-th="' . $th1 . '"'
										) .
										\EEH_HTML::td(
											$old_event->name(),
											'',
											'am-old-event-name-td',
											'',
											'data-th="' . $th2 . '"'
										) .
										\EEH_HTML::td(
											$old_ticket->name_and_info(),
											'',
											'am-old-ticket-name-td',
											'',
											'data-th="' . $th3 . '"'
										) .
										\EEH_HTML::td(
											$new_event->name(),
											'',
											'am-new-event-name-td',
											'',
											'data-th="' . $th4 . '"'
										) .
										\EEH_HTML::td(
											$new_ticket->name_and_info(),
											'',
											'am-new-ticket-name-td',
											'',
											'data-th="' . $th5 . '"'
										) .
										\EEH_HTML::td(
											\EEH_Template::format_currency( $price_change ),
											'',
											'am-price-change-td jst-rght' . $price_class,
											'',
											'data-th="' . $th6 . '"'
										)
									)
								),
								'eea-attendee-mover-info-table-' . $this->slug(),
								'eea-attendee-mover-info-table ee-responsive-table'
							)
						),
						new \EE_Form_Section_Proper(
							array(
								'layout_strategy' => new \EE_Admin_Two_Column_Layout(),
								'subsections'     => array(
									'trigger_notifications' => new \EE_Yes_No_Input(
										array(
											'html_label_text' => __( 'Trigger Notifications?', 'event_espresso' ),
											'html_help_text'  => __(
												'If "Yes" is selected, then notifications regarding these changes will be sent to the registration\'s contact.',
												'event_espresso'
											),
										)
									),
									'1' => new EE_Form_Section_HTML( \EEH_HTML::br() ),
								),
							)
						),
						$this->slug() . '-submit-btn' => $this->generateSubmitButton(),
						$this->slug() . '-cancel-btn' => $this->generateCancelButton(),
						'2' => new EE_Form_Section_HTML( \EEH_HTML::br(2) ),
						'EVT_ID' => new \EE_Fixed_Hidden_Input(
							array( 'default'  => $this->getEventId() )
						),
						'TKT_ID' => new \EE_Fixed_Hidden_Input(
							array( 'default'  => $this->getTicketId() )
						),
					)
				)
			)
		);

		return $this->form();
	}



	/**
	 * handles processing the form submission
	 * returns true or false depending on whether the form was processed successfully or not
	 *
	 * @param array $form_data
	 * @return bool
	 * @throws \LogicException
	 * @throws \EventEspresso\core\exceptions\InvalidFormSubmissionException
	 * @throws \EE_Error
	 * @throws \EventEspresso\core\exceptions\EntityNotFoundException
	 * @throws \InvalidArgumentException
	 * @throws InvalidDataTypeException
	 */
	public function process( $form_data = array() ) {
		$valid_data = (array) parent::process( $form_data );
		if ( empty( $valid_data ) ) {
			return false;
		}
		// check that it was the submit button that was clicked and not the cancel button
		if (
			! (
				isset( $valid_data['verify_changes-submit-btn'] )
		         && $valid_data['verify_changes-submit-btn'] === $this->submitBtnText()
			)
		) {
			EE_Error::add_attention(
				__( 'Registration changes have been cancelled.', 'event_espresso' )
			);
			EE_Error::get_notices( false, true );
			wp_safe_redirect(
				EE_Admin_Page::add_query_args_and_nonce(
					array(
						'action'  => 'view_registration',
						'_REG_ID' => $this->REG_ID,
					),
					REG_ADMIN_URL
				)
			);
			exit();
		}
		if (
			isset( $valid_data['trigger_notifications'] )
			&& $valid_data['trigger_notifications'] === true
		) {
			// send out notifications
			add_filter( 'FHEE__EED_Messages___maybe_registration__deliver_notifications', '__return_true', 10 );
		} else {
			add_filter( 'FHEE__EED_Messages___maybe_registration__deliver_notifications', '__return_false', 15 );
		}
		$this->setRedirectTo( SequentialStepForm::REDIRECT_TO_NEXT_STEP );
		return true;
	}



}
// End of file VerifyChanges.php
// Location: /VerifyChanges.php