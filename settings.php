<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * Setting file
 *
 * @package    local_setcheck
 * @copyright  2024 David Kelly
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $ADMIN->add('localplugins', new admin_category('local_setcheck', get_string('pluginname', 'local_setcheck')));

    $ADMIN->add('local_setcheck', new admin_externalpage('local_setcheck_manage_templates',
        get_string('manage_templates', 'local_setcheck'),
        new moodle_url('/local/setcheck/pages/manage_templates.php')
    ));

    $ADMIN->add('local_setcheck', new admin_externalpage('local_setcheck_create_template',
        get_string('create_template', 'local_setcheck'),
        new moodle_url('/local/setcheck/pages/create_template.php')
    ));

    $settings = new admin_settingpage('local_setcheck_general', get_string('generalsettings', 'local_setcheck'));
    $ADMIN->add('local_setcheck', $settings);

    $settings->add(new admin_setting_heading('local_setcheck_templates',
        get_string('templates', 'local_setcheck'),
        get_string('templates_desc', 'local_setcheck')));
}
