<?php

namespace EventEspresso\AttendeeMover\form;

use DomainException;
use EE_Error;
use EE_Form_Section_Proper;
use EE_Registration;
use EE_Registry;
use EventEspresso\core\exceptions\EntityNotFoundException;
use EventEspresso\core\exceptions\InvalidDataTypeException;
use EventEspresso\core\exceptions\InvalidEntityException;
use EventEspresso\core\exceptions\InvalidFormSubmissionException;
use EventEspresso\core\exceptions\UnexpectedEntityException;
use EventEspresso\core\libraries\form_sections\form_handlers\FormHandler;
use EventEspresso\core\libraries\form_sections\form_handlers\SequentialStepForm;
use InvalidArgumentException;
use LogicException;
use OutOfRangeException;
use ReflectionException;
use RuntimeException;

defined('EVENT_ESPRESSO_VERSION') || exit('No direct script access allowed');



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
     * @param EE_Registry $registry
     * @throws InvalidDataTypeException
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function __construct(EE_Registry $registry)
    {
        $this->setDisplayable();
        parent::__construct(
            4,
            esc_html__('Complete', 'event_espresso'),
            esc_html__('"Complete" Attendee Mover Step', 'event_espresso'),
            'complete',
            '',
            FormHandler::ADD_FORM_TAGS_AND_SUBMIT,
            $registry
        );
    }



    /**
     * creates and returns the actual form
     *
     * @throws EntityNotFoundException
     * @throws LogicException
     * @throws EE_Error
     */
    public function generate()
    {
        $this->setForm(
            new EE_Form_Section_Proper(
                array(
                    'name'        => $this->slug(),
                    'subsections' => array(),
                )
            )
        );
        return $this->form();
    }



    /**
     * normally displays the form, but we are going to skip right to processing our changes
     *
     * @return string
     * @throws EE_Error
     * @throws LogicException
     * @throws InvalidArgumentException
     * @throws InvalidFormSubmissionException
     * @throws EntityNotFoundException
     */
    public function display()
    {
        return '';
    }



    /**
     * handles processing the form submission
     * returns true or false depending on whether the form was processed successfully or not
     *
     * @param array $form_data
     * @return bool
     * @throws InvalidEntityException
     * @throws ReflectionException
     * @throws InvalidDataTypeException
     * @throws OutOfRangeException
     * @throws RuntimeException
     * @throws UnexpectedEntityException
     * @throws LogicException
     * @throws InvalidFormSubmissionException
     * @throws EE_Error
     * @throws EntityNotFoundException
     * @throws InvalidArgumentException
     */
    public function process($form_data = array())
    {
        // in case an exception is thrown, we need to go back to the previous step,
        // because this step has no displayable content
        $this->setRedirectTo(SequentialStepForm::REDIRECT_TO_PREV_STEP);
        $old_registration = $this->getRegistration($this->REG_ID);
        $new_ticket = $this->getTicket($this->TKT_ID);
        /** @var \EventEspresso\AttendeeMover\services\commands\MoveAttendeeCommand $MoveAttendeeCommand */
        $new_registration = $this->registry->BUS->execute(
            $this->registry->create(
                'EventEspresso\AttendeeMover\services\commands\MoveAttendeeCommand',
                array($old_registration, $new_ticket)
            )
        );
        if (! $new_registration instanceof EE_Registration) {
            throw new InvalidEntityException(get_class($new_registration), 'EE_Registration');
        }
        // setup redirect to new registration details admin page
        $this->setRedirectUrl(REG_ADMIN_URL);
        $this->addRedirectArgs(
            array(
                'action'  => 'view_registration',
                '_REG_ID' => $new_registration->ID(),
            )
        );
        // and update the redirectTo constant as well
        $this->setRedirectTo(SequentialStepForm::REDIRECT_TO_OTHER);
        EE_Error::add_success(
            sprintf(
                esc_html__(
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
