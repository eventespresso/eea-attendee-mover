<?php

namespace EventEspresso\AttendeeMover\form;

use DomainException;
use EE_Error;
use EE_Event;
use EE_Registration;
use EE_Registry;
use EE_Ticket;
use EEH_HTML;
use EEM_Event;
use EEM_Registration;
use EEM_Ticket;
use EventEspresso\core\exceptions\EntityNotFoundException;
use EventEspresso\core\exceptions\InvalidDataTypeException;
use EventEspresso\core\libraries\form_sections\form_handlers\SequentialStepForm;
use Exception;
use InvalidArgumentException;
use ReflectionException;

/**
 * Class Step
 * abstract parent class for individual forms in the Attendee Mover sequential form
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         1.0.0
 */
abstract class Step extends SequentialStepForm
{

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
     * @var EE_Ticket $current_ticket
     */
    protected $current_ticket;

    /**
     * @var bool $notify
     */
    protected $notify;


    /**
     * SequentialStepForm constructor
     *
     * @param int         $order
     * @param string      $form_name
     * @param string      $admin_name
     * @param string      $slug
     * @param string      $form_action
     * @param string      $form_config
     * @param EE_Registry $registry
     * @throws DomainException
     * @throws InvalidDataTypeException
     * @throws InvalidArgumentException
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function __construct(
        $order,
        $form_name,
        $admin_name,
        $slug,
        $form_action = '',
        $form_config = 'add_form_tags_and_submit',
        EE_Registry $registry
    ) {
        parent::__construct(
            $order,
            $form_name,
            $admin_name,
            $slug,
            $form_action,
            $form_config,
            $registry
        );
        $this->REG_ID = $this->getRegId();
        $this->EVT_ID = $this->getEventId();
        $this->TKT_ID = $this->getTicketId();
        $this->notify = $this->getNotify();
        $this->addRedirectArgs(
            array(
                '_REG_ID'   => $this->REG_ID,
                'EVT_ID'    => $this->EVT_ID,
                'TKT_ID'    => $this->TKT_ID,
                'ee-notify' => $this->notify,
            )
        );
        $this->addFormActionArgs(
            array(
                '_REG_ID'   => $this->REG_ID,
                'EVT_ID'    => $this->EVT_ID,
                'TKT_ID'    => $this->TKT_ID,
                'ee-notify' => $this->notify,
            )
        );
    }


    /**
     * @return int
     * @throws EE_Error
     * @throws ReflectionException
     */
    protected function getRegId()
    {
        $request = $this->registry->load_core('Request');
        return absint($request->get('_REG_ID', 0));
    }


    /**
     * @param int $REG_ID
     * @return EE_Registration
     * @throws EntityNotFoundException
     */
    protected function getRegistration($REG_ID = 0)
    {
        $registration = EEM_Registration::instance()->get_one_by_ID($REG_ID);
        if (! $registration instanceof EE_Registration) {
            throw new EntityNotFoundException('Registration ID', $REG_ID);
        }
        return $registration;
    }


    /**
     * @return int
     * @throws ReflectionException
     * @throws EE_Error
     */
    protected function getEventId()
    {
        $request = $this->registry->load_core('Request');
        return absint($request->get('EVT_ID', 0));
    }


    /**
     * @param int $EVT_ID
     * @return EE_Event
     * @throws EE_Error
     * @throws EntityNotFoundException
     */
    protected function getEvent($EVT_ID = 0)
    {
        $event = EEM_Event::instance()->get_one_by_ID($EVT_ID);
        if (! $event instanceof EE_Event) {
            throw new EntityNotFoundException('Event ID', $EVT_ID);
        }
        return $event;
    }


    /**
     * @return int
     * @throws ReflectionException
     * @throws EE_Error
     */
    protected function getTicketId()
    {
        $request = $this->registry->load_core('Request');
        return absint($request->get('TKT_ID', 0));
    }


    /**
     * @param int $TKT_ID
     * @return EE_Ticket
     * @throws EntityNotFoundException
     */
    protected function getTicket($TKT_ID = 0)
    {
        $ticket = EEM_Ticket::instance()->get_one_by_ID($TKT_ID);
        if (! $ticket instanceof EE_Ticket) {
            throw new EntityNotFoundException('Ticket ID', $TKT_ID);
        }
        return $ticket;
    }


    /**
     * @return EE_Ticket
     * @throws EE_Error
     * @throws EntityNotFoundException
     */
    protected function getCurrentTicket()
    {
        if (! $this->current_ticket instanceof EE_Ticket) {
            $registration = $this->getRegistration($this->REG_ID);
            $this->current_ticket = $registration->ticket();
        }
        return $this->current_ticket;
    }


    /**
     * @return bool
     */
    public function notify()
    {
        return $this->notify;
    }


    /**
     * @return bool
     * @throws ReflectionException
     * @throws EE_Error
     */
    public function getNotify()
    {
        $request = $this->registry->load_core('Request');
        $this->setNotify($request->get('ee-notify', false));
        return $this->notify;
    }


    /**
     * @param bool $notify
     */
    public function setNotify($notify = true)
    {
        $this->notify = filter_var($notify, FILTER_VALIDATE_BOOLEAN);
    }


    /**
     * registrantInformation
     *
     * @return string
     */
    public function registrantInformation()
    {

        $attendee_name = esc_html__('Unknown', 'event_espresso');
        $EVT_ID = 0;
        $event_name = esc_html__('Unknown Event', 'event_espresso');
        $TKT_ID = 0;
        $ticket_name_and_price = esc_html__('Unknown Ticket', 'event_espresso');
        try {
            $registration = $this->getRegistration($this->REG_ID);
            $attendee_name = $registration->attendee()->full_name();
            $ticket = $this->getCurrentTicket();
            $TKT_ID = $ticket->ID();
            $ticket_name_and_price = $ticket->name() . ' : ' . $ticket->pretty_price();
            $event = $registration->event();
            if ($event instanceof EE_Event) {
                $event_name = $event->name();
                $EVT_ID = $event->ID();
            }
        } catch (Exception $e) {
            EE_Error::add_error($e->getMessage(), __FILE__, __FUNCTION__, __LINE__);
        }
        return EEH_HTML::div(
            EEH_HTML::h4(esc_html__('Current Registration Information', 'event_espresso'))
            .
            EEH_HTML::span(
                esc_html__('Attendee Name: ', 'event_espresso'),
                '',
                'eea-attendee-mover-current-reg-info-label-spn'
            )
            .
            EEH_HTML::span($attendee_name, '', 'eea-attendee-mover-current-reg-info-spn')
            .
            EEH_HTML::span(
                sprintf(esc_html__(' ( ID: %1$d ) ', 'event_espresso'), $this->REG_ID),
                '',
                'eea-attendee-mover-current-reg-info-spn eea-attendee-mover-current-reg-info-id-spn'
            )
            .
            EEH_HTML::br()
            .
            EEH_HTML::span(
                esc_html__('Current Event: ', 'event_espresso'),
                '',
                'eea-attendee-mover-current-reg-info-label-spn'
            )
            .
            EEH_HTML::span($event_name, '', 'eea-attendee-mover-current-reg-info-spn')
            .
            EEH_HTML::span(
                sprintf(esc_html__(' ( ID: %1$d ) ', 'event_espresso'), $EVT_ID),
                '',
                'eea-attendee-mover-current-reg-info-spn eea-attendee-mover-current-reg-info-id-spn'
            )
            .
            EEH_HTML::br()
            .
            EEH_HTML::span(
                esc_html__('Current Ticket: ', 'event_espresso'),
                '',
                'eea-attendee-mover-current-reg-info-label-spn'
            )
            .
            EEH_HTML::span(
                $ticket_name_and_price,
                '',
                'eea-attendee-mover-current-reg-info-spn'
            )
            .
            EEH_HTML::span(
                sprintf(esc_html__(' ( ID: %1$d ) ', 'event_espresso'), $TKT_ID),
                '',
                'eea-attendee-mover-current-reg-info-spn eea-attendee-mover-current-reg-info-id-spn'
            ),
            '',
            'eea-attendee-mover-current-reg-info-div'
        );
    }
}
