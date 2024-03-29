<?php
/*
  Plugin Name: Event Espresso - Attendee Mover (EE4.9.13+)
  Plugin URI: http://www.eventespresso.com
  Description: The Attendee Mover add-on for Event Espresso will let you move attendees between events.
  Version: 1.0.8.rc.000
  Author: Event Espresso
  Author URI: http://www.eventespresso.com
  Copyright 2016 Event Espresso (email : support@eventespresso.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA02110-1301USA
 *
 * ------------------------------------------------------------------------
 *
 * Event Espresso
 *
 * Event Registration and Management Plugin for WordPress
 *
 * @ package	Event Espresso
 * @ author		Event Espresso
 * @ copyright	(c) 2008-2016 Event Espresso  All Rights Reserved.
 * @ license	http://eventespresso.com/support/terms-conditions/   * see Plugin Licensing *
 * @ link		http://www.eventespresso.com
 * @ version	EE4
 *
 * ------------------------------------------------------------------------
 */
// define versions and this file
define('EE_ATTENDEE_MOVER_CORE_VERSION_REQUIRED', '4.10.14.p');
define('EE_ATTENDEE_MOVER_WP_VERSION_REQUIRED', '4.4.0');
define('EE_ATTENDEE_MOVER_VERSION', '1.0.8.rc.000');
define('EE_ATTENDEE_MOVER_PLUGIN_FILE', __FILE__);


/**
 *    captures plugin activation errors for debugging
 */
function espresso_attendee_mover_plugin_activation_errors()
{

    if (WP_DEBUG) {
        $activation_errors = ob_get_contents();
        file_put_contents(
            EVENT_ESPRESSO_UPLOAD_DIR . 'logs' . DS . 'espresso_attendee_mover_plugin_activation_errors.html',
            $activation_errors
        );
    }
}

add_action('activated_plugin', 'espresso_attendee_mover_plugin_activation_errors');


/**
 *    registers addon with EE core
 */
function load_espresso_attendee_mover()
{
    if (class_exists('EE_Addon')) {
        /** @var EventEspresso\core\domain\Domain $core_domain */
        $core_domain = EventEspresso\core\services\loaders\LoaderFactory::getLoader()->getShared(
            EventEspresso\core\domain\Domain::class
        );
        if ($core_domain instanceof EventEspresso\core\domain\Domain && $core_domain->isCaffeinated()) {
            // attendee_mover version
            require_once(plugin_dir_path(__FILE__) . 'EE_Attendee_Mover.class.php');
            EE_Attendee_Mover::register_addon();
        } else {
            add_action('admin_notices', 'espresso_attendee_mover_activation_error');
        }
    } else {
        add_action('admin_notices', 'espresso_attendee_mover_activation_error');
    }
}

add_action('AHEE__EE_System__load_espresso_addons', 'load_espresso_attendee_mover');


/**
 *    verifies that addon was activated
 */
function espresso_attendee_mover_activation_check()
{
    if (! did_action('AHEE__EE_System__load_espresso_addons')) {
        add_action('admin_notices', 'espresso_attendee_mover_activation_error');
    }
}

add_action('init', 'espresso_attendee_mover_activation_check', 1);


/**
 *    displays activation error admin notice
 */
function espresso_attendee_mover_activation_error()
{
    unset($_GET['activate'], $_REQUEST['activate']);
    if (! function_exists('deactivate_plugins')) {
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    deactivate_plugins(plugin_basename(EE_ATTENDEE_MOVER_PLUGIN_FILE));
    ?>
    <div class="error">
        <p><?php
            printf(
                __(
                    'Event Espresso Attendee Mover could not be activated. Please ensure that Event Espresso version %1$s or higher is running',
                    'event_espresso'
                ),
                EE_ATTENDEE_MOVER_CORE_VERSION_REQUIRED
            );
            ?></p>
    </div>
    <?php
}
