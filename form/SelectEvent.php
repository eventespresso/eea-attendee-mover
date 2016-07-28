<?php
namespace EventEspresso\AttendeeMover\form;

use EE_Form_Section_Proper;
use EE_Error;
use EventEspresso\core\libraries\form_sections\form_handlers\FormHandler;
use EventEspresso\core\libraries\form_sections\form_handlers\SequentialStepForm;
use LogicException;
use InvalidArgumentException;
use EventEspresso\core\exceptions\InvalidDataTypeException;
use EventEspresso\core\exceptions\InvalidFormSubmissionException;

if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}



/**
 * Class SelectEvent
 * the first form in the sequential form steps for the Attendee Mover admin page
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         1.0.0
 */
class SelectEvent extends Step {

	/**
	 * SelectEvent constructor
	 *
	 * @param \EE_Registry $registry
	 * @throws InvalidDataTypeException
	 * @throws InvalidArgumentException
	 * @throws \DomainException
	 */
	public function __construct( \EE_Registry $registry ) {
		parent::__construct(
			1,
			__( 'Select Event', 'event_espresso' ),
			__( '"Select Event" Attendee Mover Step', 'event_espresso' ),
			'select_event',
			'',
			FormHandler::ADD_FORM_TAGS_AND_SUBMIT,
			$registry
		);
	}



	/**
	 * creates and returns the actual form
	 *
	 * @return EE_Form_Section_Proper
	 * @throws EE_Error
	 * @throws LogicException
	 */
	public function generate() {
		$this->setForm(
			new \EE_Form_Section_Proper(
				array(
					'name'          => $this->slug(),
					'subsections'   => array(
						'EVT_ID' => new \EE_Select_Ajax_Model_Rest_Input(
							array(
								'html_name'          => 'ee-select2-' . $this->slug(),
								'html_id'            => 'ee-select2-' . $this->slug(),
								'html_class'         => 'ee-select2',
								'html_label_text'    => __( 'Select New Event', 'event_espresso' ),
								'model_name'         => 'Event',
								'display_field_name' => 'EVT_name',
								'query_params'       => array(
									0 => apply_filters(
                                        'FHEE__AttendeeMover_form_SelectEvent__generate__where_parameters',
                                        array(
                                            'Datetime.DTT_EVT_end' => array(
                                                '>',
                                                \EEM_Datetime::instance()->current_time_for_query('DTT_EVT_end')
                                            )
                                        )
                                    ),
									'caps'  => \EEM_Base::caps_read_admin
								),
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
	 * @throws InvalidFormSubmissionException
	 * @throws EE_Error
	 * @throws LogicException
	 * @throws InvalidArgumentException
	 * @throws InvalidDataTypeException
	 */
	public function process( $form_data = array() ) {
		// process form
		$valid_data = (array) parent::process( $form_data );
		if ( empty( $valid_data ) ) {
			return false;
		}
		// set $EVT_ID from valid form data
		$EVT_ID = isset( $valid_data['EVT_ID' ] ) ? absint( $valid_data['EVT_ID' ] ) : 0;
		if ( $EVT_ID ) {
			$this->addRedirectArgs(  array( 'EVT_ID' => $EVT_ID ) );
			$this->setRedirectTo( SequentialStepForm::REDIRECT_TO_NEXT_STEP );
			return true;
		}
		return false;
	}



}
// End of file SelectEvent.php
// Location: /SelectEvent.php