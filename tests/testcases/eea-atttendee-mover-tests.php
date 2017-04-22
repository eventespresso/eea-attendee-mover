<?php

/**
 * Test class for espresso_addon_skeleton.php
 *
 * @since         1.0.0
 * @package       EventEspresso/AttendeeMover
 * @subpackage    tests
 */
class eea_attendee_mover_tests extends EE_UnitTestCase
{

    /**
     * Tests the loading of the main file
     */
    function test_loading_new_addon()
    {
        $this->assertEquals(10, has_action('AHEE__EE_System__load_espresso_addons', 'load_espresso_attendee_mover'));
        $this->assertTrue(class_exists('EE_Attendee_Mover'));
    }
}
