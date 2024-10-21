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

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/assign/mod_form.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/accesslib.php');
require_once($CFG->dirroot . '/course/lib.php'); // Include course lib for course creation.
require_once($CFG->dirroot . '/mod/assign/lib.php'); // Include assignment lib for assign creation.
require_once($CFG->dirroot . '/course/modlib.php'); // Include modlib to get add_moduleinfo().
require_once($CFG->dirroot . '/mod/assign/mod_form.php'); // Include the assignment form.
require_once($CFG->libdir . '/formslib.php'); // Include Moodle form library.

// First, check if course and assignment exist in plugin config.
$courseid = get_config('local_setcheck', 'courseid');
$assignmentid = get_config('local_setcheck', 'assignmentid');
$cm = null;  // Define $cm outside of the if-else block.

if (!$courseid || !$assignmentid) {
    // Step 1: Create a hidden course if it doesn't exist.
    $courseconfig = new stdClass();
    $courseconfig->fullname = 'Template Course for Setcheck';
    $courseconfig->shortname = 'setcheck_template_course_' . time(); // Unique shortname.
    $courseconfig->category = 1; // Default category.
    $courseconfig->visible = 0; // Hidden course.

    $course = create_course($courseconfig);
    $courseid = $course->id; // Store course ID.

    // Save course ID to the plugin config.
    set_config('courseid', $courseid, 'local_setcheck');

    // Get the module ID for 'assign' from the mdl_modules table.
    $module = $DB->get_record('modules', ['name' => 'assign'], '*', MUST_EXIST);
    $moduleid = $module->id; // Get the 'assign' module ID.

    // Prepare the moduleinfo object.
    $moduleinfo = new stdClass();
    $moduleinfo->modulename = 'assign'; // The name of the module. MUST BE SET CORRECTLY.
    $moduleinfo->module = $moduleid; // Assign the module ID for 'assign'.
    $moduleinfo->course = $courseid;
    $moduleinfo->section = 0; // Section 0 or the appropriate section ID.
    $moduleinfo->visible = 0; // Hidden course module.

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

    // Fetch the course object.
    $course = $DB->get_record('course', ['id' => $courseid]);

    // Create the course module and assignment instance using add_moduleinfo.
    $moduleinfo = add_moduleinfo($moduleinfo, $course);

    // Store the assignment ID and course module ID.
    $assignmentid = $moduleinfo->instance;
    $cmid = $moduleinfo->coursemodule;

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

$context = context_module::instance($cmid);

/**
 * Form for creating assignment templates in local_setcheck plugin.
 * This form extends the default assignment settings form (mod_assign_mod_form).
 *
 * @package    local_setcheck
 * @copyright  2024 David Kelly
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_setcheck_assign_template_form extends moodleform {
    /**
     * @var object Course module object.
     */
    protected $cm;

    /**
     * @var int Course ID.
     */
    protected $courseid;

    /**
     * Constructor to accept course module and course ID.
     *
     * @param moodle_url $actionurl URL for the form action.
     * @param object $cm Course module object.
     * @param int $courseid Course ID.
     */
    public function __construct($actionurl, $cm, $courseid) {
        $this->cm = $cm;
        $this->courseid = $courseid;

        // Call the parent constructor.
        parent::__construct($actionurl);
    }

    /**
     * Defines the form structure.
     *
     * @return void
     */
    public function definition() {
        global $DB;

        $mform = $this->_form;
        $mform->updateAttributes(['id' => 'create_template_form']);

        // Dynamically load the existing assignment form via reflection.
        $templatehtmlids = []; // Array to store HTML IDs.

        // Add a collapsible section for Template Settings.
        $mform->addElement('header', 'templatesettings', get_string('template_settings', 'local_setcheck'));
        $mform->setExpanded('templatesettings', true);

        // Add the template name field.
        $templatenameelement = $mform->addElement('text', 'template_name', get_string('template_name', 'local_setcheck'));
        $mform->setType('template_name', PARAM_TEXT);
        $mform->addRule('template_name', get_string('required', 'local_setcheck'), 'required', null, 'server');
        $mform->setDefault('template_name', '');

        // Add the template description field.
        $templatedescriptionelement = $mform->addElement(
            'editor', 'template_description',
            get_string('template_description', 'local_setcheck')
        );
        $mform->setType('template_description', PARAM_RAW);
        $mform->setDefault('template_description', '');

        // Save the HTML ID of the 'template_name' field.
        $templatehtmlids['template_name'] = $templatenameelement->getAttribute('id');
        $templatehtmlids['template_description'] = $templatedescriptionelement->getAttribute('id');

        // Add assignment form fields dynamically and store their HTML IDs.
        $this->add_assign_form_fields($mform, $templatehtmlids);
        $this->remove_unwanted_elements($mform);

        // Add a button array to the form (Save and Cancel buttons).
        $buttonarray = [];
        $buttonarray[] = $mform->createElement(
            'button',
            'save_template_button',
            get_string('save_template',
            'local_setcheck')
        );
        $buttonarray[] = $mform->createElement(
            'cancel',
            'cancel_template_button',
            get_string('cancel')
        );

        // Add the group of buttons to the form.
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
        $mform->setType('template_html_ids', PARAM_RAW);

        $mform->closeHeaderBefore('buttonar');

    }

    /**
     * Removes unwanted elements from the form.
     *
     * @param MoodleQuickForm $mform The form object.
     * @return void
     */
    private function remove_unwanted_elements($mform) {
        // List of elements to remove.
        $elementstoremove = [
            'general',
            'name',         // Assignment name.
            'intro',        // Introduction/Description.
            'introattachments', // Attachments.
            'submissionattachments', // Attachments.
            'pageheader',   // Page header (if applicable).
            'introeditor',  // Intro editor if present.
            'showdescription',
            'activityeditor',
            'competenciessection',
            'tagshdr',
            'tags',
            'buttonar',
            'competenciessection',
            'competencies',
            'competency_rule',
            'override_grade',
            'restrictgroupbutton',
            'availabilityconditionsheader',
            'availabilityconditionsjson',
            '_qf__mod_assign_mod_form',
            'modstandardelshdr',
            'visible',
            'cmidnumber',
            'lang',
            'groupmode',
            'groupingid',
            'restrictgroupbutton',
            'availabilityconditionsheader',
            'availabilityconditionsjson',
            'course',
            'coursemodule',
            'section',
            'module',
            'modulename',
            'instance',
            'add',
            'update',
            'return',
            'sr',
            'beforemod',
            'showonly',
            'coursecontentnotification',
        ];

        // Iterate over each element name and remove it if it exists.
        foreach ($elementstoremove as $elementname) {
            if ($mform->elementExists($elementname)) {
                $mform->removeElement($elementname);
            }
        }
    }

    /**
     * Adds assignment form fields dynamically using reflection.
     *
     * @param MoodleQuickForm $mform The form object.
     * @param array $templatehtmlids Array to store HTML IDs of form elements.
     * @return void
     */
    private function add_assign_form_fields($mform, &$templatehtmlids) {
        global $DB;

        // Fetch data needed for the constructor of mod_assign_mod_form.
        $course = $DB->get_record('course', ['id' => $this->courseid], '*', MUST_EXIST);
        $cm = get_coursemodule_from_id('assign', $this->cm->id, $this->courseid);

        $cm->idnumber = ''; // Set to empty string.
        $cm->completiongradeitemnumber = -1; // Set to -1.
        $cm->availability = '{}'; // Set to an empty JSON object.
        $cm->lang = ''; // Set to an empty string.
        $cm->section = 0; // Set to visible.

        // Fake data for $current if needed.
        $current = new stdClass();
        $current->instance = 0; // Fake or retrieve current instance data if needed.
        $current->course = $course->id; // Add the course ID.
        $current->coursemodule = ''; // Add the course module ID.
        $current->visible = isset($cm->visible) ? $cm->visible : 1;

        // Instantiate the original assignment form.
        $assignform = new mod_assign_mod_form($current, $cm->section, $cm, $course);

        // Use reflection to access the protected property _form (where the elements are stored).
        $reflection = new ReflectionClass($assignform);
        $formproperty = $reflection->getProperty('_form');
        $formproperty->setAccessible(true);
        $originalform = $formproperty->getValue($assignform);

        // Adding a type definition for the 'assignsubmission_comments_enabled' element.
        $mform->setType('assignsubmission_comments_enabled', PARAM_INT);

        // Adding a type definition for the 'assignsubmission_onlinetext_wordlimit' element.
        $mform->setType('assignsubmission_onlinetext_wordlimit', PARAM_INT);

        foreach ($originalform->_elements as $element) {
            $elementname = $element->getName();
            $mform->addElement($element);
            // echo $elementname . "<br>";

            $mform->setType($elementname, PARAM_RAW);
        }
    }
}

if (!$cm) {
    // If the course module is broken or missing, reset the config.
    set_config('courseid', null, 'local_setcheck');
    set_config('assignmentid', null, 'local_setcheck');
    redirect(new moodle_url('/local/setcheck/create_template.php'), 'Course module or assignment was invalid. Please refresh.');
}

$cmid = $cm->id; // Use the existing course module ID.
$course = $DB->get_record('course', ['id' => $courseid]);

// Ensure the $cm is valid before using it.
if (!empty($cm)) {
    $PAGE->set_cm($cm, $course); // Ensure the course matches the course module.
} else {
    echo "<pre>Invalid course module: CMID is empty or null.</pre>";
    exit;
}

// Set up the page context and title.
$PAGE->set_url('/local/setcheck/create_template.php');
$PAGE->set_cm($cm, $course); // Ensure the course matches the course module.
$PAGE->set_context($context);
$PAGE->set_title(get_string('create_template', 'local_setcheck'));
$PAGE->set_heading(get_string('create_template', 'local_setcheck'));
$PAGE->add_body_class('create-template-page limitedwidth');
$PAGE->requires->css('/local/setcheck/styles.css');
$PAGE->requires->js_call_amd('local_setcheck/track_form_changes', 'init');

require_login();
require_capability('moodle/site:config', $context); // Ensure user has admin rights.

// Set the action URL for the form.
$actionurl = new moodle_url('/local/setcheck/create_template.php');

// Create the form with the required arguments.
$mform = new local_setcheck_assign_template_form($actionurl, $cm, $courseid);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;

    if (!empty($data['template_name'])) {
        // Save the template details.
        $template = new stdClass();
        $template->name = $data['template_name'];
        $template->description = ''; // If you have a field for template description.

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

        // Use Moodle's moodle_url to generate the redirect URL.
        $redirecturl = new moodle_url('/local/setcheck/manage_templates.php');

        // Return a JSON response with the redirect URL.
        echo json_encode(['redirect' => $redirecturl->out(false)]);
        exit;
    } else {
        echo json_encode(['error' => 'Template name is required.']);
        exit;
    }
}


echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
