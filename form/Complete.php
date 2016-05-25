<?php
namespace AttendeeMover\form;

use AttendeeMover\services\MoveAttendee;

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
	 * @throws \InvalidArgumentException
	 * @throws \DomainException
	 * @throws \EventEspresso\core\exceptions\InvalidDataTypeException
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
	 * @throws \EventEspresso\core\exceptions\EntityNotFoundException
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
	 * @throws \EventEspresso\core\exceptions\InvalidFormSubmissionException
	 * @throws \EventEspresso\core\exceptions\EntityNotFoundException
	 */
	public function display() {}



	/**
	 * handles processing the form submission
	 * returns true or false depending on whether the form was processed successfully or not
	 *
	 * @param array $form_data
	 * @return bool
	 * @throws \EventEspresso\core\exceptions\InvalidDataTypeException
	 * @throws \OutOfRangeException
	 * @throws \RuntimeException
	 * @throws \EventEspresso\core\exceptions\UnexpectedEntityException
	 * @throws \LogicException
	 * @throws \EventEspresso\core\exceptions\InvalidFormSubmissionException
	 * @throws \EE_Error
	 * @throws \EventEspresso\core\exceptions\EntityNotFoundException
	 * @throws \InvalidArgumentException
	 */
	public function process( $form_data = array() )
	{
		$old_registration = $this->getRegistration( $this->REG_ID );
		$new_ticket = $this->getTicket( $this->TKT_ID );
		$new_registration = MoveAttendee::process(
			$old_registration,
			$new_ticket
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





}
// End of file Complete.php
// Location: /Complete.php