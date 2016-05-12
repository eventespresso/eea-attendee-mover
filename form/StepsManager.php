<?php
namespace AttendeeMover\steps\form;

use EventEspresso\Core\Exceptions\BaseException;
use EventEspresso\Core\Exceptions\InvalidClassException;
use EventEspresso\Core\Exceptions\InvalidDataTypeException;
use EventEspresso\Core\Exceptions\InvalidEntityException;
use EventEspresso\Core\Exceptions\InvalidIdentifierException;
use EventEspresso\Core\Exceptions\InvalidInterfaceException;
use EventEspresso\core\libraries\form_sections\SequentialStepFormManager;
use EventEspresso\core\services\collections\Collection;
use EventEspresso\core\services\collections\CollectionDetails;
use EventEspresso\core\services\collections\CollectionLoader;
use InvalidArgumentException;

if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}



/**
 * Class StepsManager
 * Manages the sequential form steps for the Attendee Mover admin page
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         4.9.0
 */
class StepsManager extends SequentialStepFormManager {



	/**
	 * StepsManager constructor
	 *
	 * @param string      $base_url
	 * @param string      $default_form_step
	 * @param string      $progress_step_style
	 * @param \EE_Request $request
	 * @throws InvalidDataTypeException
	 * @throws InvalidArgumentException
	 */
	public function __construct( $base_url, $default_form_step, $progress_step_style, \EE_Request $request = null) {
		parent::__construct( $base_url, $default_form_step, $progress_step_style, $request );
	}



	/**
	 * @throws BaseException
	 * @throws InvalidClassException
	 * @throws InvalidDataTypeException
	 * @throws InvalidEntityException
	 * @throws InvalidIdentifierException
	 * @throws InvalidInterfaceException
	 * @throws InvalidArgumentException
	 */
	public function buildForm() {
		$this->buildCurrentStepFormForDisplay();
	}



	/**
	 * @param array $form_data
	 * @throws BaseException
	 * @throws InvalidClassException
	 * @throws InvalidDataTypeException
	 * @throws InvalidEntityException
	 * @throws InvalidIdentifierException
	 * @throws InvalidInterfaceException
	 * @throws InvalidArgumentException
	 */
	public function processForm( $form_data = array() ) {
		$this->buildCurrentStepFormForProcessing();
		$this->processCurrentStepForm( $form_data );
	}



	/**
	 * @return Collection|null
	 * @throws InvalidEntityException
	 * @throws InvalidIdentifierException
	 * @throws InvalidInterfaceException
	 * @throws \EventEspresso\Core\Exceptions\InvalidFilePathException
	 * @throws InvalidDataTypeException
	 * @throws InvalidClassException
	 */
	protected function getFormStepsCollection() {
		static $form_steps = null;
		if ( ! $form_steps instanceof Collection ) {
			$loader = new CollectionLoader(
				new CollectionDetails(
					'attendee_mover_form_steps',
					'\EventEspresso\core\libraries\form_sections\SequentialStepFormInterface',
					array(
						'\AttendeeMover\steps\form\SelectEvent',
						'\AttendeeMover\steps\form\SelectTicket',
						'\AttendeeMover\steps\form\VerifyChanges',
					),
					array(),
					'',
					CollectionDetails::ID_CALLBACK_METHOD,
					'slug'
				)
			);
			$form_steps = $loader->getCollection();
		}
		return $form_steps;
	}





}
// End of file StepsManager.php
// Location: /StepsManager.php