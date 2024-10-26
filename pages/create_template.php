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
 * Page for creating templates extending mod_assign_mod_form.
 *
 * @package    local_setcheck
 * @copyright  2024 David Kelly
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__DIR__, 3) . '/config.php');
require_once($CFG->dirroot . '/mod/assign/mod_form.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/accesslib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/mod/assign/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->dirroot . '/mod/assign/mod_form.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/setcheck/classes/form/assign_template_form.php');


// Get context from the pagecontextid parameter.
$pagecontextid = optional_param('contextid', 0, PARAM_INT);
list($context, $course2, $cm2) = get_context_info_array($pagecontextid);

$PAGE->set_url('/local/setcheck/pages/create_template.php');
$PAGE->add_body_class('create-template-page limitedwidth');
$PAGE->requires->css('/local/setcheck/styles/styles.css');
$PAGE->requires->js_call_amd('local_setcheck/create_template', 'init');

require_login();
require_capability('moodle/site:config', $context);
$PAGE->set_context($context);
$PAGE->set_title(get_string('create_template', 'local_setcheck'));
$PAGE->set_heading(get_string('create_template', 'local_setcheck'));

// Check if the context level is category or course.
if ($context->contextlevel == CONTEXT_COURSECAT) {
    $categoryid = $context->instanceid;
    $contextlevel = 'category';
} else if ($context->contextlevel == CONTEXT_COURSE) {
    $courseid = $context->instanceid;
    $contextlevel = 'course';
} else {
    throw new moodle_exception('invalidcontextlevel', 'local_setcheck');
}

// Fetch the course module and other data based on configuration.
$courseid = get_config('local_setcheck', 'courseid');
$assignmentid = get_config('local_setcheck', 'assignmentid');
$cm = null;

if (!$assignmentid) {
    // Create a dummy assignment inside the hidden course.
    \local_setcheck\services\AssignmentService::create_assignment($courseid);
} else {
    // Validate that the course module and assignment are still valid.
    $cm = $DB->get_record('course_modules', ['course' => $courseid, 'instance' => $assignmentid]);
    // Set the course module properties.
    $course = \local_setcheck\services\AssignmentService::set_course_module_properties($cm, $courseid);
}

// Turn off debugging messages temporarily.
$previousdebug = $CFG->debug;
$CFG->debug = DEBUG_NONE;
$PAGE->set_cm($cm, $course);
$CFG->debug = $previousdebug;

$actionurl = new moodle_url('/local/setcheck/pages/create_template.php');

// Create the form with the required arguments.
$mform = new \local_setcheck\form\assign_template_form($actionurl, $cm, courseid: $courseid, pagecontextid: $pagecontextid);

// Turn off debugging messages temporarily.
$previousdebug = $CFG->debug;
$CFG->debug = DEBUG_NONE;
$PAGE->set_context($context);
$CFG->debug = $previousdebug;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;

    if (!empty($data['template_name'])) {
        $contextid = $data['contextid'];
        $context = context::instance_by_id($contextid);

        // Save the template details.
        $template = new stdClass();
        $template->name = $data['template_name'];
        $template->description = ''; // If you have a field for template description.

        // Assign category or course ID depending on context level.
        if ($context->contextlevel == CONTEXT_COURSECAT) {
            $categoryid = $context->instanceid;
            $template->categoryid = $categoryid;
            $template->courseid = null;
        } else if ($context->contextlevel == CONTEXT_COURSE) {
            $coursetemplateid = $context->instanceid;
            $template->courseid = $coursetemplateid;
            $template->categoryid = null;
        }

        $template->contextid = $contextid; // Use the captured context ID.
        $template->contextlevel = $contextlevel; // Use the captured context level.
        $template->creatorid = $USER->id;

        // Store assignment settings as an array with Setting Name, Value, and HTML ID.
        $settings = [];

        foreach ($data as $key => $value) {
            if ($key !== 'template_name') {
                // Use the original key as the setting name.
                $settingname = $key;

                // Construct the HTML ID by appending 'id_' to the key.
                $htmlid = 'id_' . $key;

                // Store the setting details.
                $settings[] = [
                    'setting_name' => $settingname,
                    'value' => $value,
                    'html_id' => $htmlid,
                ];
            }
        }

        // Save settings as JSON.
        $template->settings = json_encode($settings);
        $template->timecreated = $template->timemodified = time();

        // Insert the template into the 'local_setcheck_templates' table.
        $DB->insert_record('local_setcheck_templates', $template);

        // Generate the appropriate redirect URL based on context level.
        if ($context->contextlevel == CONTEXT_COURSECAT) {
            $redirecturl = new moodle_url('/local/setcheck/pages/manage_templates.php',
                [
                    'contextid' => $contextid,
                ]
            );
        } else if ($context->contextlevel == CONTEXT_COURSE) {
            $redirecturl = new moodle_url('/local/setcheck/pages/manage_templates.php',
                [
                    'contextid' => $contextid,
                ]
            );
        }

        // Return a JSON response with the redirect URL or redirect.
        echo json_encode(['redirect' => $redirecturl->out(false)]);
        exit;
    } else {
        echo json_encode(['error' => 'Template name is required.']);
        exit;
    }

} else {

    // If it's a GET request (when the page is loaded via a URL), just show the form without processing.
    $PAGE->set_pagelayout('admin');
    $PAGE->activityheader->disable();

    echo $OUTPUT->header();
    $mform->display(); // Display the form.
    echo $OUTPUT->footer();
}
