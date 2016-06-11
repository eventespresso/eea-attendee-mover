<?php
namespace EventEspresso\AttendeeMover\form;

use EE_Datetime;
use EE_Event;
use EE_Form_Section_Proper;
use EE_Ticket;
use EventEspresso\core\libraries\form_sections\form_handlers\FormHandler;
use EventEspresso\core\libraries\form_sections\form_handlers\SequentialStepForm;
use InvalidArgumentException;
use EventEspresso\core\exceptions\InvalidDataTypeException;

if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}



/**
 * Class SelectTicket
 * the second form in the sequential form steps for the Attendee Mover admin page
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         1.0.0
 */
class SelectTicket extends Step {

	/**
	 * SelectTicket constructor
	 *
	 * @param \EE_Registry $registry
	 * @throws InvalidDataTypeException
	 * @throws InvalidArgumentException
	 * @throws \DomainException
	 */
	public function __construct( \EE_Registry $registry ) {
		parent::__construct(
			2,
			__( 'Select Ticket', 'event_espresso' ),
			__( '"Select Ticket" Attendee Mover Step', 'event_espresso' ),
			'select_ticket',
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
	 * @throws \InvalidArgumentException
	 * @throws \EventEspresso\core\exceptions\InvalidDataTypeException
	 * @throws \LogicException
	 * @throws \EE_Error
	 */
	public function generate() {
		$event = $this->getEvent( $this->EVT_ID );
		$tickets_by_datetime = array();
		if ( $event instanceof EE_Event ) {
			$tickets = $event->tickets();
			foreach ( $tickets as $ticket ) {
				if ( $ticket instanceof EE_Ticket ) {
					foreach ( $ticket->datetimes() as $datetime ) {
						if ( $datetime instanceof EE_Datetime ) {
							if ( ! isset( $tickets_by_datetime[ $datetime->name() ] ) ) {
								$tickets_by_datetime[ $datetime->name() ] = array();
							}
							$tickets_by_datetime[ $datetime->name() ][ $ticket->ID() ]
								= $ticket->name() . ' : ' . $ticket->pretty_price();
						}
					}
				}
			}
		}
		$this->setForm(
			new \EE_Form_Section_Proper(
				array(
					'name'        => $this->slug(),
					'subsections' => array(
						'TKT_ID' => new \EE_Select_Input(
							$tickets_by_datetime,
							array(
								'html_name'          => 'ee-' . $this->slug(),
								'html_id'            => 'ee-' . $this->slug(),
								'html_class'         => 'ee-' . $this->slug(),
								'html_label_text'    => __( 'Select New Ticket', 'event_espresso' ),
								'required'           => true,
							)
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
	 * @throws \InvalidArgumentException
	 * @throws InvalidDataTypeException
	 */
	public function process( $form_data = array() ) {
		$valid_data = (array) parent::process( $form_data );
		if ( empty( $valid_data ) ) {
			return false;
		}
		// set $EVT_ID from valid form data
		$TKT_ID = isset( $valid_data['TKT_ID' ] ) ? absint( $valid_data['TKT_ID' ] ) : 0;
		// process form and set $TKT_ID
		if ( $TKT_ID ) {
			$registration = $this->getRegistration( $this->REG_ID );
			if ( $registration instanceof \EE_Registration ) {
				$existing_ticket_ID = $registration->ticket_ID();
				if ( $TKT_ID === $existing_ticket_ID ) {
					throw new \RuntimeException(
						__(
							'Registrations can not be moved if you select the exact same ticket that the registration already has! Please select a different ticket.',
							'event_espresso'
						)
					);
				}
			}
			$this->addRedirectArgs( array( 'TKT_ID' => $TKT_ID ) );
			$this->setRedirectTo( SequentialStepForm::REDIRECT_TO_NEXT_STEP );
			return true;
		}
		return false;
	}



}
// End of file SelectTicket.php
// Location: /SelectTicket.php