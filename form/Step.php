<?php
namespace EventEspresso\AttendeeMover\form;

use EE_Event;
use EE_Registration;
use EE_Ticket;
use EEM_Event;
use EEM_Registration;
use EEM_Ticket;
use EventEspresso\core\exceptions\EntityNotFoundException;
use EventEspresso\core\libraries\form_sections\form_handlers\SequentialStepForm;

if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}



/**
 * Class Step
 * abstract parent class for individual forms in the Attendee Mover sequential form
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         1.0.0
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
	 * @var \EE_Ticket $current_ticket
	 */
	protected $current_ticket;



	/**
	 * SequentialStepForm constructor
	 *
	 * @param int          $order
	 * @param string       $form_name
	 * @param string       $admin_name
	 * @param string       $slug
	 * @param string       $form_action
	 * @param string       $form_config
	 * @param \EE_Registry $registry
	 */
	public function __construct(
		$order,
		$form_name,
		$admin_name,
		$slug,
		$form_action = '',
		$form_config = 'add_form_tags_and_submit',
		\EE_Registry $registry
	) {
		parent::__construct( $order, $form_name, $admin_name, $slug, $form_action, $form_config, $registry );
		$this->REG_ID = $this->getRegId();
		$this->EVT_ID = $this->getEventId();
		$this->TKT_ID = $this->getTicketId();
		$this->addRedirectArgs(
			array(
				'_REG_ID' => $this->REG_ID,
				'EVT_ID'  => $this->EVT_ID,
				'TKT_ID'  => $this->TKT_ID,
			)
		);
		$this->addFormActionArgs(
			array(
				'_REG_ID' => $this->REG_ID,
				'EVT_ID'  => $this->EVT_ID,
				'TKT_ID'  => $this->TKT_ID,
			)
		);
	}



	/**
	 * @return int
	 */
	protected function getRegId() {
		$request = $this->registry->load_core( 'Request' );
		return absint( $request->get( '_REG_ID', 0 ) );
	}



	/**
	 * @param int $REG_ID
	 * @return \EE_Registration
	 * @throws \EventEspresso\core\exceptions\EntityNotFoundException
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
		$request = $this->registry->load_core( 'Request' );
		return absint( $request->get( 'EVT_ID', 0 ) );
	}



	/**
	 * @param int $EVT_ID
	 * @return \EE_Event
	 * @throws \EventEspresso\core\exceptions\EntityNotFoundException
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
		$request = $this->registry->load_core( 'Request' );
		return absint( $request->get( 'TKT_ID', 0 ) );
	}



	/**
	 * @param int $TKT_ID
	 * @return \EE_Ticket
	 * @throws \EventEspresso\core\exceptions\EntityNotFoundException
	 */
	protected function getTicket( $TKT_ID = 0 ) {
		$ticket = EEM_Ticket::instance()->get_one_by_ID( $TKT_ID );
		if ( ! $ticket instanceof EE_Ticket ) {
			throw new EntityNotFoundException( 'Ticket ID', $TKT_ID );
		}
		return $ticket;
	}



	/**
	 * @return \EE_Ticket
	 */
	protected function getCurrentTicket() {
		if ( ! $this->current_ticket instanceof EE_Ticket ) {
			$registration = $this->getRegistration( $this->REG_ID );
			$this->current_ticket = $registration->ticket();
		}
		return $this->current_ticket;
	}



	/**
	 * registrantInformation
	 *
	 * @return string
	 */
	public function registrantInformation() {
		$registration = $this->getRegistration( $this->REG_ID );
		$ticket = $this->getCurrentTicket();
		$event = $registration->event();
		return \EEH_HTML::div(
			\EEH_HTML::h4( esc_html__( 'Current Registration Information', 'event_espresso' ) )
			.
			\EEH_HTML::span(
				esc_html__( 'Attendee Name: ', 'event_espresso' ),
				'',
				'',
				'display:inline-block; width:120px; white-space: nowrap;'
			)
			.
			\EEH_HTML::span( $registration->attendee()->full_name(), '', '', 'margin-left: 1em; white-space: nowrap;' )
			.
			\EEH_HTML::span(
				sprintf( esc_html__( ' ( ID: %1$d ) ', 'event_espresso' ), $registration->ID() ),
				'',
				'',
				'color:#999999; font-size:.8em; margin-left: 1em; white-space: nowrap;'
			)
			.
			\EEH_HTML::br()
			.
			\EEH_HTML::span(
				esc_html__( 'Current Event: ', 'event_espresso' ),
				'',
				'',
				'display:inline-block; width:120px; white-space: nowrap;'
			)
			.
			\EEH_HTML::span( $event->name(), '', '', 'margin-left: 1em; white-space: nowrap;' )
			.
			\EEH_HTML::span(
				sprintf( esc_html__( ' ( ID: %1$d ) ', 'event_espresso' ), $event->ID() ),
				'',
				'',
				'color:#999999; font-size:.8em; margin-left: 1em; white-space: nowrap;'
			)
			.
			\EEH_HTML::br()
			.
			\EEH_HTML::span(
				esc_html__( 'Current Ticket: ', 'event_espresso' ),
				'',
				'',
				'display:inline-block; width:120px; white-space: nowrap;'
			)
			.
			\EEH_HTML::span(
				$ticket->name() . ' : ' . $ticket->pretty_price(),
				'',
				'',
				'margin-left: 1em; white-space: nowrap;'
			)
			.
			\EEH_HTML::span(
				sprintf( esc_html__( ' ( ID: %1$d ) ', 'event_espresso' ), $ticket->ID() ),
				'',
				'',
				'color:#999999; font-size:.8em; margin-left: 1em; white-space: nowrap;'
			),
			'',
			'',
			'background:#fafafa; font-size:.85em; margin:1em 0 3em; padding:.25em 2em 2em;'
		);
	}



}
// End of file Step.php
// Location: /Step.php