<?php

namespace EventEspresso\AttendeeMover\form;

use EventEspresso\core\exceptions\InvalidClassException;
use EventEspresso\core\exceptions\InvalidDataTypeException;
use EventEspresso\core\exceptions\InvalidEntityException;
use EventEspresso\core\exceptions\InvalidFilePathException;
use EventEspresso\core\exceptions\InvalidIdentifierException;
use EventEspresso\core\exceptions\InvalidInterfaceException;
use EventEspresso\core\libraries\form_sections\form_handlers\FormHandler;
use EventEspresso\core\libraries\form_sections\form_handlers\SequentialStepFormManager;
use EventEspresso\core\services\collections\Collection;
use EventEspresso\core\services\collections\CollectionDetails;
use EventEspresso\core\services\collections\CollectionLoader;
use EventEspresso\core\services\request\RequestInterface;
use InvalidArgumentException;

/**
 * Class StepsManager
 * Manages the sequential form steps for the Attendee Mover admin page
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         4.9.0
 */
class StepsManager extends SequentialStepFormManager
{


    /**
     * StepsManager constructor
     *
     * @param string      $base_url
     * @param string      $default_form_step
     * @param string      $form_action
     * @param string      $form_config
     * @param string      $progress_step_style
     * @param RequestInterface $request
     * @throws InvalidDataTypeException
     * @throws InvalidArgumentException
     */
    public function __construct(
        $base_url,
        $default_form_step,
        $form_action = '',
        $form_config = FormHandler::ADD_FORM_TAGS_AND_SUBMIT,
        $progress_step_style = 'number_bubbles',
        RequestInterface $request = null
    ) {
        parent::__construct(
            $base_url,
            $default_form_step,
            $form_action,
            $form_config,
            $progress_step_style,
            $request
        );
    }


    /**
     * @return Step[]|Collection|null
     * @throws InvalidEntityException
     * @throws InvalidIdentifierException
     * @throws InvalidInterfaceException
     * @throws InvalidFilePathException
     * @throws InvalidDataTypeException
     * @throws InvalidClassException
     */
    protected function getFormStepsCollection()
    {
        static $form_steps = null;
        if (! $form_steps instanceof Collection) {
            $loader = new CollectionLoader(
                new CollectionDetails(
                    // collection name
                    'attendee_mover_form_steps',
                    // collection interface
                    'EventEspresso\AttendeeMover\form\Step',
                    // FQCNs for classes to add
                    apply_filters(
                        'FHEE__EventEspresso\AttendeeMover\form\StepsManager__getFormStepsCollection__form_step_classes',
                        array(
                            'EventEspresso\AttendeeMover\form\SelectEvent',
                            'EventEspresso\AttendeeMover\form\SelectTicket',
                            'EventEspresso\AttendeeMover\form\VerifyChanges',
                            'EventEspresso\AttendeeMover\form\Complete',
                        )
                    ),
                    // filepaths to classes to add
                    array(),
                    // filemask to use if parsing folder for files to add
                    '',
                    // what to use as identifier for collection entities
                    CollectionDetails::ID_CALLBACK_METHOD,
                    // we'll use the slug() method on our collection objects for setting the identifier
                    'slug'
                )
            );
            $form_steps = $loader->getCollection();
        }
        return $form_steps;
    }
}
