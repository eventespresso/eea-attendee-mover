<?php
namespace EventEspresso\AttendeeMover\form;

use DomainException;
use EE_Admin_Page;
use EE_Error;
use EE_Form_Section_HTML;
use EE_Form_Section_Proper;
use EE_Registry;
use EventEspresso\core\exceptions\EntityNotFoundException;
use EventEspresso\core\exceptions\InvalidFormSubmissionException;
use EventEspresso\core\libraries\form_sections\form_handlers\FormHandler;
use EventEspresso\core\libraries\form_sections\form_handlers\SequentialStepForm;
use EventEspresso\core\exceptions\InvalidDataTypeException;
use InvalidArgumentException;
use LogicException;
use ReflectionException;

/**
 * Class VerifyChanges
 * the third form in the sequential form steps for the Attendee Mover admin page
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         1.0.0
 */
class VerifyChanges extends Step
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
        parent::__construct(
            3,
            esc_html__('Verify Changes', 'event_espresso'),
            esc_html__('"Verify Changes" Attendee Mover Step', 'event_espresso'),
            'verify_changes',
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
     * @throws ReflectionException
     * @throws EntityNotFoundException
     * @throws LogicException
     */
    public function generate()
    {
        $registration = $this->getRegistration($this->REG_ID);
        try {
            $old_event = $registration->event();
            $old_event_name = $old_event->name();
        } catch (\Exception $e) {
            $old_event_name = esc_html__('Unknown Event', 'event_espresso');
            EE_Error::add_error($e->getMessage(), __FILE__, __FUNCTION__, __LINE__);
        }
        $old_ticket = $registration->ticket();
        $old_ticket_name_and_info = $old_ticket->name_and_info();
        $new_event = $this->getEvent($this->EVT_ID);
        $new_event_name = $new_event instanceof \EE_Event
            ? $new_event->name()
            : esc_html__('Unknown Event', 'event_espresso');
        $new_ticket = $this->getTicket($this->TKT_ID);
        $price_change = $new_ticket->price() - $old_ticket->price();
        $price_class = $price_change < 0
            ? ' ee-txn-refund'
            : '';
        $th2 = esc_html__('Current Event', 'event_espresso');
        $th3 = esc_html__('Current Ticket', 'event_espresso');
        $th4 = esc_html__('New Event', 'event_espresso');
        $th5 = esc_html__('New Ticket', 'event_espresso');
        $th6 = esc_html__('Price Change', 'event_espresso');
        $this->setForm(
            new \EE_Form_Section_Proper(
                array(
                    'name'            => $this->slug(),
                    'layout_strategy' => new \EE_Div_Per_Section_Layout(),
                    'subsections'     => array(
                        'changes'                     => new EE_Form_Section_HTML(
                            \EEH_HTML::table(
                                \EEH_HTML::thead(
                                    \EEH_HTML::tr(
                                        // \EEH_HTML::th( $th1 ) .
                                        \EEH_HTML::th($th2) .
                                        \EEH_HTML::th($th3) .
                                        \EEH_HTML::th($th4) .
                                        \EEH_HTML::th($th5) .
                                        \EEH_HTML::th($th6)
                                    )
                                ) .
                                \EEH_HTML::tbody(
                                    \EEH_HTML::tr(
                                        \EEH_HTML::td(
                                            $old_event_name,
                                            '',
                                            'am-old-event-name-td',
                                            '',
                                            'data-th="' . $th2 . '"'
                                        ) .
                                        \EEH_HTML::td(
                                            $old_ticket_name_and_info,
                                            '',
                                            'am-old-ticket-name-td',
                                            '',
                                            'data-th="' . $th3 . '"'
                                        ) .
                                        \EEH_HTML::td(
                                            $new_event_name,
                                            '',
                                            'am-new-event-name-td',
                                            '',
                                            'data-th="' . $th4 . '"'
                                        ) .
                                        \EEH_HTML::td(
                                            $new_ticket->name_and_info(),
                                            '',
                                            'am-new-ticket-name-td',
                                            '',
                                            'data-th="' . $th5 . '"'
                                        ) .
                                        \EEH_HTML::td(
                                            \EEH_Template::format_currency($price_change),
                                            '',
                                            'am-price-change-td jst-rght' . $price_class,
                                            '',
                                            'data-th="' . $th6 . '"'
                                        )
                                    )
                                ),
                                'eea-attendee-mover-info-table-' . $this->slug(),
                                'eea-attendee-mover-info-table ee-responsive-table'
                            )
                        ),
                        'notifications'               => new \EE_Form_Section_Proper(
                            array(
                                'layout_strategy' => new \EE_Admin_Two_Column_Layout(),
                                'subsections'     => array(
                                    'trigger_send' => new \EE_Yes_No_Input(
                                        array(
                                            'html_label_text' => esc_html__('Trigger Notifications?', 'event_espresso'),
                                            'html_help_text'  => esc_html__(
                                                'If "Yes" is selected, then notifications regarding these changes will be sent to the registration\'s contact (for example: Registration Approved (Event Admin context) + Registration Approved (Primary Registrant context), etc).',
                                                'event_espresso'
                                            ),
                                        )
                                    ),
                                    '1'            => new EE_Form_Section_HTML(\EEH_HTML::br()),
                                ),
                            )
                        ),
                        $this->slug() . '-submit-btn' => $this->generateSubmitButton(),
                        $this->slug() . '-cancel-btn' => $this->generateCancelButton(),
                        '2'                           => new EE_Form_Section_HTML(\EEH_HTML::br(2)),
                        'EVT_ID'                      => new \EE_Fixed_Hidden_Input(
                            array('default' => $this->getEventId())
                        ),
                        'TKT_ID'                      => new \EE_Fixed_Hidden_Input(
                            array('default' => $this->getTicketId())
                        ),
                    ),
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
     * @throws LogicException
     * @throws InvalidFormSubmissionException
     * @throws EE_Error
     * @throws EntityNotFoundException
     * @throws InvalidArgumentException
     * @throws InvalidDataTypeException
     */
    public function process($form_data = array())
    {
        $valid_data = (array) parent::process($form_data);
        if (empty($valid_data)) {
            return false;
        }
        // check that it was the submit button that was clicked and not the cancel button
        if (! (
            isset($valid_data['verify_changes-submit-btn'])
            && $valid_data['verify_changes-submit-btn'] === $this->submitBtnText()
        )) {
            EE_Error::add_attention(
                esc_html__('Registration changes have been cancelled.', 'event_espresso')
            );
            EE_Error::get_notices(false, true);
            wp_safe_redirect(
                EE_Admin_Page::add_query_args_and_nonce(
                    array(
                        'action'  => 'view_registration',
                        '_REG_ID' => $this->REG_ID,
                    ),
                    REG_ADMIN_URL
                )
            );
            exit();
        }
        if (isset($valid_data['notifications'], $valid_data['notifications']['trigger_send'])
            && $valid_data['notifications']['trigger_send'] === true
        ) {
            // send out notifications
            $this->setNotify();
            add_filter('FHEE__EED_Messages___maybe_registration__deliver_notifications', '__return_true', 10);
        } else {
            add_filter('FHEE__EED_Messages___maybe_registration__deliver_notifications', '__return_false', 15);
        }

        if (isset($valid_data['promotions'], $valid_data['promotions']['copy_promotions'])
            && $valid_data['promotions']['copy_promotions'] === true
        ) {
            // Copy promotions
            $this->setCopyPromos();
        }
        $this->addRedirectArgs(
            array(
                'ee-notify' => $this->notify,
                'ee-copy-promos' => $this->copyPromos
            )
        );
        $this->setRedirectTo(SequentialStepForm::REDIRECT_TO_NEXT_STEP);
        return true;
    }
}
