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
 * Manage Templates page.
 *
 * @package    local_setcheck
 * @copyright  2024 David Kelly
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$PAGE->set_url('/local/setcheck/manage_templates.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('manage_templates', 'local_setcheck'));
$PAGE->set_heading(get_string('manage_templates', 'local_setcheck'));

require_login();
require_capability('moodle/site:config', context_system::instance());

echo $OUTPUT->header();

// Display existing templates.
$templates = $DB->get_records('local_setcheck_templates');

echo html_writer::start_tag('table', ['class' => 'generaltable']);
echo html_writer::start_tag('tr');
echo html_writer::tag('th', get_string('name', 'local_setcheck'));
echo html_writer::tag('th', get_string('description', 'local_setcheck'));
echo html_writer::tag('th', get_string('actions', 'local_setcheck'));
echo html_writer::end_tag('tr');

foreach ($templates as $template) {
    echo html_writer::start_tag('tr');
    echo html_writer::tag('td', $template->name);
    echo html_writer::tag('td', $template->description);
    echo html_writer::tag('td',
        html_writer::link(new moodle_url('/local/setcheck/view_template.php', ['id' => $template->id]),
            get_string('view', 'local_setcheck')) . ' | ' .
        html_writer::link(new moodle_url('/local/setcheck/edit_template.php', ['id' => $template->id]),
            get_string('edit')) . ' | ' .
        html_writer::link(new moodle_url('/local/setcheck/delete_template.php', ['id' => $template->id]),
            get_string('delete'))
    );
    echo html_writer::end_tag('tr');
}

echo html_writer::end_tag('table');

echo html_writer::link(new moodle_url('/local/setcheck/create_template.php'),
    get_string('create_template', 'local_setcheck'),
    ['class' => 'btn btn-primary']);

echo $OUTPUT->footer();
