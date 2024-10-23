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
 * Manage templates page for the Setcheck plugin.
 *
 * This file provides an interface for managing templates,
 * including viewing, editing, and deleting templates in a context-aware manner.
 *
 * @package    local_setcheck
 * @copyright  2024 David Kelly
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/local/setcheck/lib.php');

use local_setcheck\services\TemplateService; // Import the TemplateService class.

// Parameters for determining context visibility (category or course).
$categoryid = optional_param('categoryid', null, PARAM_INT);
$coursetemplateid = optional_param('courseid', null, PARAM_INT);
$contextid = optional_param('pagecontextid', 0, PARAM_INT); // Default to 0 if not provided.
$contextlevel = optional_param('contextlevel', 'course', PARAM_ALPHA); // Default to 'course' if not provided.

$context = context::instance_by_id($contextid, IGNORE_MISSING);

if (!$context) {
    // If no context is provided, default to system context.
    $context = context_system::instance();
}

// Set up the page context and title.
$PAGE->set_url('/local/setcheck/pages/manage_template.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('manage_activity_templates', 'local_setcheck'));
$PAGE->set_heading(get_string('manage_activity_templates', 'local_setcheck'));
$PAGE->add_body_class('manage-template-page limitedwidth');
$PAGE->requires->css('/local/setcheck/styles/styles.css');
// $PAGE->requires->js_call_amd('local_setcheck/create_template', 'init');

require_login();
require_capability('moodle/site:config', $context); // Ensure user has admin rights.

// Fetch templates based on the current context.
$templates = [];
if ($context->contextlevel == CONTEXT_COURSECAT) {
    $category = \core_course_category::get($context->instanceid); // Get the category object.

}
// Display the page header.
echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('managetemplatesheading', 'local_setcheck'));

// Display the list of templates in a table.
if (!empty($templates)) {
    $table = new html_table();
    $table->head = [
        get_string('templatename', 'local_setcheck'),
        get_string('context', 'local_setcheck'),
        get_string('actions', 'local_setcheck'),
    ];

    foreach ($templates as $template) {
        if ($template->categoryid) {
            $category = \core_course_category::get($template->categoryid);
            $contextname = $category->get_formatted_name();
        } else {
            $course = get_course($template->courseid);
            $contextname = $course->fullname;
        }

        $actions = html_writer::link(
            new moodle_url('/local/setcheck/edit_template.php', ['id' => $template->id]),
            get_string('edit', 'local_setcheck')
        );
        $actions .= ' | ' . html_writer::link(
            new moodle_url('/local/setcheck/delete_template.php', ['id' => $template->id]),
            get_string('delete', 'local_setcheck'),
            ['onclick' => "return confirm('".get_string('confirmdelete', 'local_setcheck')."');"]
        );

        $table->data[] = [
            s($template->name),
            $contextname,
            $actions,
        ];
    }

    echo html_writer::table($table);
} else {
    echo html_writer::tag('p', get_string('notemplates', 'local_setcheck'));
}

// Add a button for creating a new template.
echo $OUTPUT->single_button(new moodle_url('/local/setcheck/create_template.php', ['contextid' => $context->id]), get_string('createtemplate', 'local_setcheck'));

// Display the page footer.
echo $OUTPUT->footer();
