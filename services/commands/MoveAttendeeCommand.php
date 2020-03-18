<?php

namespace EventEspresso\AttendeeMover\services\commands;

use EE_Error;
use EE_Registration;
use EE_Ticket;
use EventEspresso\core\domain\services\capabilities\CapCheck;
use EventEspresso\core\exceptions\InvalidDataTypeException;
use EventEspresso\core\services\commands\Command;
use EventEspresso\core\services\commands\CommandRequiresCapCheckInterface;

/**
 * Class MoveAttendeeCommand
 * Primarily a DTO (Data Transfer Object)
 * for passing a registration and ticket to the MoveAttendeeCommandHandler,
 * but also capable of self executing and passing itself to the CommandBus
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         1.0.0
 */
class MoveAttendeeCommand extends Command implements CommandRequiresCapCheckInterface
{

    /**
     * @var EE_Registration $registration
     */
    private $registration;

    /**
     * @var EE_Ticket $ticket
     */
    private $ticket;

    /**
     * @var bool $trigger_notifications
     */
    protected $trigger_notifications;


    /**
     * MoveAttendeeCommand constructor.
     *
     * @param EE_Registration $old_registration
     * @param EE_Ticket       $new_ticket
     * @param bool            $trigger_notifications
     */
    public function __construct(
        EE_Registration $old_registration,
        EE_Ticket $new_ticket,
        $trigger_notifications,
        $copy_promotions
    ) {
        $this->registration = $old_registration;
        $this->ticket = $new_ticket;
        $this->trigger_notifications = filter_var($trigger_notifications, FILTER_VALIDATE_BOOLEAN);
        $this->copy_promotions = filter_var($copy_promotions, FILTER_VALIDATE_BOOLEAN);
    }


    /**
     * @return CapCheck
     * @throws EE_Error
     * @throws InvalidDataTypeException
     */
    public function getCapCheck()
    {
        return new CapCheck(
            'ee_edit_registration',
            __('Edit Registration Ticket Selection', 'event_espresso'),
            $this->registration->ID()
        );
    }


    /**
     * @return EE_Registration
     */
    public function registration()
    {
        return $this->registration;
    }


    /**
     * @return EE_Ticket
     */
    public function ticket()
    {
        return $this->ticket;
    }


    /**
     * @return bool
     */
    public function triggerNotifications()
    {
        return $this->trigger_notifications;
    }


    /**
     * @return bool
     */
    public function copyPromotions()
    {
        return $this->copy_promotions;
    }
}
