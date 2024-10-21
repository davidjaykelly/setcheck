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
 * Page for viewing template details.
 *
 * @package    local_setcheck
 * @copyright  2024 David Kelly
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

// Get the template ID from the URL.
$templateid = required_param('id', PARAM_INT);

// Set up the page.
$PAGE->set_url('/local/setcheck/view_template.php', ['id' => $templateid]);
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('view_template', 'local_setcheck'));
$PAGE->set_heading(get_string('view_template', 'local_setcheck'));

require_login();
require_capability('moodle/site:config', context_system::instance());

echo $OUTPUT->header();

// Fetch the template from the database.
$template = $DB->get_record('local_setcheck_templates', ['id' => $templateid], '*', MUST_EXIST);

// Display template information.
echo html_writer::tag('h3', get_string('template_name', 'local_setcheck') . ': ' . format_string($template->name));
echo html_writer::tag('p', get_string('template_description', 'local_setcheck') . ': ' . format_string($template->description));

// Decode the JSON settings and display them.
$settings = json_decode($template->settings, true);
if ($settings && is_array($settings)) {
    echo html_writer::start_tag('table', ['class' => 'generaltable']);
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', get_string('setting_name', 'local_setcheck'));
    echo html_writer::tag('th', get_string('value', 'local_setcheck'));
    echo html_writer::tag('th', get_string('html_id', 'local_setcheck'));
    echo html_writer::end_tag('tr');

    foreach ($settings as $data) {
        echo html_writer::start_tag('tr');

        // Handle each setting correctly by referring to the fields you are expecting.
        echo html_writer::tag('td', format_string($data['setting_name'] ?? 'Unknown Setting'));
        echo html_writer::tag('td', format_string($data['value'] ?? 'N/A'));
        echo html_writer::tag('td', format_string($data['html_id'] ?? 'N/A'));

        echo html_writer::end_tag('tr');
    }

    echo html_writer::end_tag('table');
} else {
    echo html_writer::tag('p', get_string('no_settings_found', 'local_setcheck'));
}

echo html_writer::link(new moodle_url('/local/setcheck/manage_templates.php'),
    get_string('back_to_templates', 'local_setcheck'),
    ['class' => 'btn btn-secondary']);

// Display the page footer.
echo $OUTPUT->footer();
