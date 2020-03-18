<?php

namespace EventEspresso\AttendeeMover\services\commands;

use EE_Error;
use EE_Registration;
use EE_Ticket;
use EventEspresso\core\domain\services\registration\CancelRegistrationService;
use EventEspresso\core\domain\services\registration\CopyRegistrationService;
use EventEspresso\core\domain\services\registration\CreateRegistrationService;
use EventEspresso\core\domain\services\registration\UpdateRegistrationService;
use EventEspresso\core\domain\services\ticket\CreateTicketLineItemService;
use EventEspresso\core\exceptions\EntityNotFoundException;
use EventEspresso\core\exceptions\InvalidEntityException;
use EventEspresso\core\exceptions\UnexpectedEntityException;
use EventEspresso\core\services\commands\CommandHandler;
use EventEspresso\core\services\commands\CommandInterface;
use OutOfRangeException;
use RuntimeException;

/**
 * Class MoveAttendee
 * receives a MoveAttendeeCommand object via the handle() method,
 * which must contains valid registration and ticket objects,
 * and returns a new registration
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         1.0.0
 */
class MoveAttendeeCommandHandler extends CommandHandler
{


    /**
     * @var CreateTicketLineItemService $create_ticket_line_item_service
     */
    private $create_ticket_line_item_service;

    /**
     * @var CreateRegistrationService $create_registration_service
     */
    private $create_registration_service;

    /**
     * @var CopyRegistrationService $copy_registration_service
     */
    private $copy_registration_service;

    /**
     * @var CancelRegistrationService $cancel_registration_service
     */
    private $cancel_registration_service;


    /**
     * @var UpdateRegistrationService $update_registration_service
     */
    private $update_registration_service;


    /**
     * Command constructor
     *
     * @param CreateTicketLineItemService $create_ticket_line_item_service
     * @param CreateRegistrationService   $create_registration_service
     * @param CopyRegistrationService     $copy_registration_service
     * @param CancelRegistrationService   $cancel_registration_service
     * @param UpdateRegistrationService   $update_registration_service
     */
    public function __construct(
        CreateTicketLineItemService $create_ticket_line_item_service,
        CreateRegistrationService $create_registration_service,
        CopyRegistrationService $copy_registration_service,
        CancelRegistrationService $cancel_registration_service,
        UpdateRegistrationService $update_registration_service
    ) {
        $this->create_ticket_line_item_service = $create_ticket_line_item_service;
        $this->create_registration_service = $create_registration_service;
        $this->copy_registration_service = $copy_registration_service;
        $this->cancel_registration_service = $cancel_registration_service;
        $this->update_registration_service = $update_registration_service;
    }


    /**
     * @param CommandInterface $command
     * @return mixed
     * @throws RuntimeException
     * @throws EntityNotFoundException
     * @throws UnexpectedEntityException
     * @throws OutOfRangeException
     * @throws InvalidEntityException
     * @throws EE_Error
     */
    public function handle(CommandInterface $command)
    {
        /** @var MoveAttendeeCommand $command */
        if (! $command instanceof MoveAttendeeCommand) {
            throw new InvalidEntityException(get_class($command), 'MoveAttendeeCommand');
        }
        $old_registration = $command->registration();
        $new_ticket = $command->ticket();
        // have we already processed this registration change ? if so, then bail...
        $this->checkIfRegistrationChangeAlreadyProcessed($old_registration, $new_ticket);
        // get transaction for original registration
        $transaction = $old_registration->transaction();
        // create new line item for new ticket
        $ticket_line_item = $this->create_ticket_line_item_service->create($transaction, $new_ticket);
        // then generate a new registration from that
        $new_registration = $this->create_registration_service->create(
            $new_ticket->get_related_event(),
            $transaction,
            $new_ticket,
            $ticket_line_item,
            $old_registration->count(),
            $old_registration->group_size()
        );
        // move/copy over registration payments
        $this->copy_registration_service->copyPaymentDetails($new_registration, $old_registration);
        // and additional data from old registration, like reg form question answers
        $this->copy_registration_service->copyRegistrationDetails($new_registration, $old_registration);
        // copy promotion line items if requested
        if ($command->copyPromotions()) {
            // move/copy over promotion line items
            $this->copy_registration_service->copyPromotionLineItems($new_registration, $old_registration);
        }
        // now cancel original registration and it's ticket line item
        $this->cancel_registration_service->cancelRegistrationAndTicketLineItem($old_registration, false);
        // manually increment ticket sold count for new ticket if registration is approved
        if ($new_registration->is_approved()) {
            $new_ticket->increase_sold();
            $new_ticket->save();
        }
        // bamboozle EED_Messages into sending notifications by tweaking the request vars
        $_REQUEST['txn_reg_status_change']['send_notifications'] = (int) $command->triggerNotifications();
        // perform final status updates and trigger notifications
        $this->update_registration_service->updateRegistrationAndTransaction($command->registration());
        // tag registrations for identification purposes
        $this->addExtraMeta($old_registration, $new_registration, $new_ticket);
        return $new_registration;
    }


    /**
     * @param EE_Registration $registration
     * @param EE_Ticket       $new_ticket
     * @return void
     * @throws RuntimeException
     * @throws EE_Error
     */
    protected function checkIfRegistrationChangeAlreadyProcessed(
        EE_Registration $registration,
        EE_Ticket $new_ticket
    ) {
        $reg_moved = $registration->get_extra_meta('registration-moved-to', true, array());
        if (isset($reg_moved['TKT_ID']) && $reg_moved['TKT_ID'] === $new_ticket->ID()) {
            $reg_details_url = add_query_arg(
                array(
                    'action'  => 'view_registration',
                    '_REG_ID' => $registration->ID(),
                ),
                REG_ADMIN_URL
            );
            throw new RuntimeException(
                sprintf(
                    __(
                        'This exact registration change has already been processed. Please select a different event and/or ticket to change this registration. %3$sThe original cancelled registration can be viewed on the %1$sregistration details admin page%2$s.',
                        'event_espresso'
                    ),
                    '<a href="' . $reg_details_url . '">',
                    '</a>',
                    '<br />'
                )
            );
        }
    }


    /**
     * @param EE_Registration $old_registration
     * @param EE_Registration $new_registration
     * @param EE_Ticket       $new_ticket
     * @throws EE_Error
     */
    protected function addExtraMeta(
        EE_Registration $old_registration,
        EE_Registration $new_registration,
        EE_Ticket $new_ticket
    ) {
        // tag the old registration as moved
        $old_registration->add_extra_meta(
            'registration-moved-to',
            array('TKT_ID' => $new_ticket->ID(), 'NEW_REG_ID' => $new_registration->ID())
        );
        // and the new registration as well
        $new_registration->add_extra_meta(
            'registration-moved-from',
            array('TKT_ID' => $new_ticket->ID(), 'OLD_REG_ID' => $old_registration->ID())
        );
    }
}
