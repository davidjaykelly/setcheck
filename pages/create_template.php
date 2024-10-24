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
$context = context::instance_by_id($pagecontextid);

require_login();

// Set up the page context and title.
$PAGE->set_url('/local/setcheck/pages/create_template.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('create_template', 'local_setcheck'));
$PAGE->set_heading(get_string('create_template', 'local_setcheck'));
$PAGE->add_body_class('create-template-page limitedwidth');
$PAGE->requires->css('/local/setcheck/styles/styles.css');
$PAGE->requires->js_call_amd('local_setcheck/create_template', 'init');

// Check user's capability to create templates.
require_capability('moodle/site:config', $context);

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
    // Get course ID from the config.
    $courseid = get_config('local_setcheck', 'courseid');
    $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

    // Fetch the module ID for the 'assign' module.
    $module = $DB->get_record('modules', ['name' => 'assign'], '*', MUST_EXIST);
    $moduleid = $module->id; // Get the 'assign' module ID.

    // Proceed to create the assignment inside the hidden course.
    $moduleinfo = new stdClass();
    $moduleinfo->modulename = 'assign';
    $moduleinfo->module = $moduleid;
    $moduleinfo->course = $courseid;
    $moduleinfo->section = 0;
    $moduleinfo->visible = 0;
    // Assignment specific settings.
    $moduleinfo->name = 'Template Assignment for Setcheck';
    $moduleinfo->intro = 'This is the introductory text for the template assignment.'; // Required field.
    $moduleinfo->introformat = 1; // HTML format for the intro.
    $moduleinfo->duedate = time() + (7 * 24 * 60 * 60); // Set due date 1 week from now.
    $moduleinfo->cutoffdate = time() + (14 * 24 * 60 * 60); // Set cutoff date 2 weeks from now.
    $moduleinfo->allowsubmissionsfromdate = time(); // Allow submissions from now.
    $moduleinfo->grade = 100; // Set the maximum grade.
    // Ensure these required fields are not NULL.
    $moduleinfo->submissiondrafts = 0; // Disable submission drafts, set to 1 to enable.
    $moduleinfo->requiresubmissionstatement = 0; // No submission statement required.
    $moduleinfo->sendnotifications = 0; // No notifications.
    $moduleinfo->sendlatenotifications = 0; // No late notifications.
    $moduleinfo->sendstudentnotifications = 1; // Enable student notifications.
    // Make sure other values are not null.
    $moduleinfo->gradingduedate = time() + (21 * 24 * 60 * 60); // Set grading due date 3 weeks from now.
    $moduleinfo->teamsubmission = 0; // No team submissions.
    $moduleinfo->requireallteammemberssubmit = 0; // No need for all team members to submit.
    $moduleinfo->blindmarking = 0; // Disable blind marking.
    $moduleinfo->attemptreopenmethod = 'none'; // No reopen attempts.
    $moduleinfo->markingworkflow = 0; // Disable marking workflow.
    $moduleinfo->markingallocation = 0; // Disable marking allocation.

    $course = $DB->get_record('course', ['id' => $courseid]);  // Fetch the course object.

    // Create & save the assignment.
    $moduleinfo = add_moduleinfo($moduleinfo, $course);
    $cmid = $moduleinfo->coursemodule;

    // Store the assignment ID and course module ID.
    $assignmentid = $moduleinfo->instance;
    // Save IDs to the plugin config.
    set_config('assignmentid', $assignmentid, 'local_setcheck');

    // Fetch course module information for the newly created assignment.
    $cm = get_coursemodule_from_id('assign', $cmid, $courseid, true, MUST_EXIST);
    $cm->course = $courseid;
    $cm->coursemodule = $cmid;
    $cm->visible = 0;  // Set visibility, defaulting to visible if necessary.

} else {
    // Validate that the course module and assignment are still valid.
    $cm = $DB->get_record('course_modules', ['course' => $courseid, 'instance' => $assignmentid]);

    if ($cm) {
        $cm->coursemodule = $cm->id;  // Set the coursemodule property.
        $cm->course = $courseid;       // Set the course property if it's not already set.
        $cm->visible = $cm->visible ?? 0;  // Set visibility if not already set.
        $cm->idnumber = '';
        $cm->completiongradeitemnumber = -1; // No specific grade item for completion.
        $cm->availability = json_encode([]); // No availability conditions.
        $cm->deletioninprogress = 0; // Module is not in the process of being deleted.
        $cm->lang = ''; // Default or unspecified language.

        $course = $DB->get_record('course', ['id' => $courseid]);  // Fetch the course object.
    } else {
        // If the course module is broken or missing, reset the config.
        set_config('courseid', null, 'local_setcheck');
        set_config('assignmentid', null, 'local_setcheck');
        redirect(new moodle_url('/local/setcheck/create_template.php'), 'Course module or assignment was invalid. Please refresh.');
    }

    $cmid = $cm->id; // Use the existing course module ID.
}
// Turn off debugging messages temporarily.
$previousdebug = $CFG->debug;
$CFG->debug = DEBUG_NONE;
$PAGE->set_cm($cm, $course);
$CFG->debug = $previousdebug;

$actionurl = new moodle_url('/local/setcheck/pages/create_template.php');

// Create the form with the required arguments.
$mform = new \local_setcheck\form\assign_template_form($actionurl, $cm, courseid: $courseid);

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
    echo $OUTPUT->header();
    $mform->display(); // Display the form.
    echo $OUTPUT->footer();
}
