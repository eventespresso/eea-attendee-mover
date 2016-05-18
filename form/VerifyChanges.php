<?php
namespace AttendeeMover\form;

use EE_Form_Section_HTML;
use EE_Form_Section_Proper;
use EEH_HTML;
use InvalidArgumentException;
use EventEspresso\Core\Exceptions\InvalidDataTypeException;

if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}



/**
 * Class VerifyChanges
 * Description
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         4.9.0
 */
class VerifyChanges extends Step {

	/**
	 * SelectTicket constructor
	 *
	 * @throws \EventEspresso\Core\Exceptions\InvalidDataTypeException
	 * @throws \InvalidArgumentException
	 * @throws \DomainException
	 */
	public function __construct() {
		parent::__construct(
			3,
			__( 'Verify Changes', 'event_espresso' ),
			__( '"Verify Changes" Attendee Mover Step', 'event_espresso' ),
			'verify_changes'
		);

		$this->REG_ID = $this->getRegId();
		$this->EVT_ID = $this->getEventId();
		$this->TKT_ID = $this->getTicketId();
		$this->addRedirectArgs(
			array(
				'EVT_ID' => $this->EVT_ID,
				'TKT_ID' => $this->TKT_ID,
			)
		);
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
	 * @return EE_Form_Section_Proper
	 * @throws \EventEspresso\Core\Exceptions\EntityNotFoundException
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
					'name'        => $this->formName(),
					'subsections' => array(
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
											'id1',
											'class',
											'style',
											'data-th="' . $th1 . '"'
										) .
										\EEH_HTML::td(
											$old_event->name(),
											'id2',
											'class',
											'style',
											'data-th="' . $th2 . '"'
										) .
										\EEH_HTML::td(
											$old_ticket->name_and_info(),
											'id3',
											'class',
											'style',
											'data-th="' . $th3 . '"'
										) .
										\EEH_HTML::td(
											$new_event->name(),
											'id4',
											'class',
											'style',
											'data-th="' . $th4 . '"'
										) .
										\EEH_HTML::td(
											$new_ticket->name_and_info(),
											'id5',
											'class',
											'style',
											'data-th="' . $th5 . '"'
										) .
										\EEH_HTML::td(
											\EEH_Template::format_currency( $price_change ),
											'id6',
											'jst-rght' . $price_class,
											'style',
											'data-th="' . $th6 . '"'
										)
									)
								),
								'eea-attendee-mover-info-table-' . $this->slug(),
								'eea-attendee-mover-info-table ee-responsive-table'
							)
						),
						$this->slug() . '-submit-btn' => $this->generateSubmitButton(),
						$this->slug() . '-cancel-btn' => $this->generateCancelButton(),
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
	 * @throws \EventEspresso\Core\Exceptions\InvalidFormSubmissionException
	 * @throws \EE_Error
	 * @throws \EventEspresso\Core\Exceptions\EntityNotFoundException
	 * @throws \InvalidArgumentException
	 * @throws InvalidDataTypeException
	 */
	public function process( $form_data = array() ) {
		$valid_data = (array) parent::process( $form_data );
		if ( empty( $valid_data ) ) {
			return false;
		}
		if (
			! (
				isset( $valid_data['verify_changes-submit-btn'] )
		         && $valid_data['verify_changes-submit-btn'] === __( 'Submit', 'event_espresso' )
			)
		) {
			// todo return to registration screen
			return false;
		}
		return true;
	}



}
// End of file VerifyChanges.php
// Location: /VerifyChanges.php