<?php
namespace EventEspresso\AttendeeMover\form;

use DomainException;
use EE_Form_Section_Proper;
use EE_Error;
use EE_Registry;
use EE_Select_Ajax_Model_Rest_Input;
use EEM_Base;
use EEM_Datetime;
use EventEspresso\core\libraries\form_sections\form_handlers\FormHandler;
use EventEspresso\core\libraries\form_sections\form_handlers\SequentialStepForm;
use LogicException;
use InvalidArgumentException;
use EventEspresso\core\exceptions\InvalidDataTypeException;
use EventEspresso\core\exceptions\InvalidFormSubmissionException;
use ReflectionException;

/**
 * Class SelectEvent
 * the first form in the sequential form steps for the Attendee Mover admin page
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         1.0.0
 */
class SelectEvent extends Step
{

    /**
     * SelectEvent constructor
     *
     * @param EE_Registry $registry
     * @throws DomainException
     * @throws EE_Error
     * @throws InvalidArgumentException
     * @throws InvalidDataTypeException
     * @throws ReflectionException
     */
    public function __construct(EE_Registry $registry)
    {
        parent::__construct(
            1,
            esc_html__('Select Event', 'event_espresso'),
            esc_html__('"Select Event" Attendee Mover Step', 'event_espresso'),
            'select_event',
            '',
            FormHandler::ADD_FORM_TAGS_AND_SUBMIT,
            $registry
        );
        add_filter(
            'FHEE__EventEspresso_core_libraries_form_sections_form_handlers_SequentialStepFormManager__displayProgressSteps__before_steps',
            array($this, 'registrantInformation')
        );
    }


    /**
     * creates and returns the actual form
     *
     * @return EE_Form_Section_Proper
     * @throws EE_Error
     * @throws LogicException
     */
    public function generate()
    {
        $this->setForm(
            new EE_Form_Section_Proper(
                array(
                    'name'        => $this->slug(),
                    'subsections' => array(
                        'EVT_ID' => new EE_Select_Ajax_Model_Rest_Input(
                            array(
                                'html_name'          => 'ee-select2-' . $this->slug(),
                                'html_id'            => 'ee-select2-' . $this->slug(),
                                'html_class'         => 'ee-select2',
                                'html_label_text'    => esc_html__('Select New Event', 'event_espresso'),
                                'model_name'         => 'Event',
                                'display_field_name' => 'EVT_name',
                                'query_params'       => array(
                                    0       => apply_filters(
                                        'FHEE__AttendeeMover_form_SelectEvent__generate__where_parameters',
                                        array(
                                            'Datetime.DTT_EVT_end' => array(
                                                '>',
                                                EEM_Datetime::instance()->current_time_for_query('DTT_EVT_end'),
                                            ),
                                        )
                                    ),
                                    'limit' => 10,
                                    'caps'  => EEM_Base::caps_read_admin,
                                    'order_by' => array('Datetime.DTT_EVT_start' => 'ASC')
                                ),
                                'required'           => true,
                                'select2_args'       => array(
                                    'ajax'        => array(
                                        'data_interface' => 'EE_Attendee_Mover_Event_Select2',
                                    ),
                                    'placeholder' => esc_html__(
                                        'type event name to search, or click to view all',
                                        'event_espresso'
                                    ),
                                    'allowClear'  => true,
                                ),
                            )
                        ),
                    ),
                )
            )
        );
        return $this->form();
    }


    /**
     * used for setting up css and js
     *
     * @return void
     * @throws LogicException
     * @throws EE_Error
     */
    public function enqueueStylesAndScripts()
    {
        wp_register_script(
            'ee-moment-core',
            EE_THIRD_PARTY_URL . 'moment/moment-with-locales.min.js',
            array(),
            EVENT_ESPRESSO_VERSION,
            true
        );
        wp_register_script(
            'ee-moment',
            EE_THIRD_PARTY_URL . 'moment/moment-timezone-with-data.min.js',
            array('ee-moment-core'),
            EVENT_ESPRESSO_VERSION,
            true
        );
        wp_enqueue_script(
            'eea-attendee-mover-select-event',
            EE_ATTENDEE_MOVER_URL . 'scripts/attendee-mover-event-selector.js',
            array('form_section_select2_init', 'ee-moment'),
            EE_ATTENDEE_MOVER_VERSION,
            true
        );
        EE_Registry::$i18n_js_strings['attendee_mover_sold_out_datetime'] = esc_html__('sold out', 'event_espresso');
        $this->form()->enqueue_js();
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
    public function process($form_data = array())
    {
        // process form
        $valid_data = (array) parent::process($form_data);
        if (empty($valid_data)) {
            return false;
        }
        // set $EVT_ID from valid form data
        $EVT_ID = isset($valid_data['EVT_ID']) ? absint($valid_data['EVT_ID']) : 0;
        if ($EVT_ID) {
            $this->addRedirectArgs(array('EVT_ID' => $EVT_ID));
            $this->setRedirectTo(SequentialStepForm::REDIRECT_TO_NEXT_STEP);
            return true;
        }
        return false;
    }
}
