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

require_once(dirname(__DIR__, 3) . '/config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/local/setcheck/lib.php');

use local_setcheck\services\TemplateService;

// Get context from the contextid parameter.
$contextid = optional_param('contextid', 0, PARAM_INT);
list($context, $course, $cm) = get_context_info_array($contextid);

// Set up the page context and title.
$PAGE->set_url('/local/setcheck/pages/manage_template.php');
$PAGE->add_body_class('manage-template-page limitedwidth');
$PAGE->requires->css('/local/setcheck/styles/styles.css');

// Check login and permissions.
require_login($course, false, $cm);
require_capability('moodle/filter:manage', $context);
$PAGE->set_context($context);

$PAGE->set_title(get_string('manage_activity_templates', 'local_setcheck'));
$PAGE->set_heading(get_string('manage_activity_templates', 'local_setcheck'));

$PAGE->set_pagelayout('admin');
$PAGE->activityheader->disable();
echo $OUTPUT->header();

// Fetch templates based on the current context.
$templates = [];
if ($context->contextlevel == CONTEXT_COURSECAT) {
    $categoryid = $context->instanceid;
    $templates = TemplateService::get_templates_for_category($categoryid);
}
if ($context->contextlevel == CONTEXT_COURSE) {
    $categoryid = $context->instanceid;
    $courseid = $course->id;
    $templatescat = TemplateService::get_templates_for_category_from_module($course, $categoryid);
    $templatescourse = TemplateService::get_templates_for_course($courseid);

    $templates = $templatescat + $templatescourse;
}

// Fetch full template details from the database.
$fulltemplates = [];
foreach (array_keys($templates) as $templateid) {
    $fulltemplate = $DB->get_record('local_setcheck_templates', ['id' => $templateid], '*', MUST_EXIST);
    $fulltemplates[$templateid] = $fulltemplate;
}

$PAGE->set_title(get_string('manage_activity_templates', 'local_setcheck'));
$PAGE->set_heading(get_string('manage_activity_templates', 'local_setcheck'));

// Display the list of templates in a table.
if (!empty($templates)) {
    $table = new html_table();
    $table->head = [
        get_string('templatename', 'local_setcheck'),
        get_string('context', 'local_setcheck'),
        get_string('actions', 'local_setcheck'),
    ];

    foreach ($fulltemplates as $template) {
        if ($template->categoryid) {
            $category = \core_course_category::get($template->categoryid);
            $contextname = $category->get_formatted_name();
        } else if ($template->courseid) { // Check for course ID.
            $course = get_course($template->courseid);
            $contextname = $course->fullname;
        } else {
            // Handle the case where both categoryid and courseid are null.
            $contextname = get_string('unknown_context', 'local_setcheck');
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

if ($context->contextlevel == CONTEXT_COURSECAT) {
    $url = new moodle_url("/local/setcheck/pages/create_template.php", [
        'contextid' => $contextid,
    ]);
    // Add the button with GET request method.
    echo $OUTPUT->single_button($url, get_string('create_template', 'local_setcheck'), 'get');
}

if ($context->contextlevel == CONTEXT_COURSE) {
    $url = new moodle_url("/local/setcheck/pages/create_template.php", [
        'contextid' => $contextid,
    ]);
    // Add the button with GET request method.
    echo $OUTPUT->single_button($url, get_string('create_template', 'local_setcheck'), 'get');
}

// Display the page footer.
echo $OUTPUT->footer();
