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

// Get context from the pagecontextid parameter.
$pagecontextid = optional_param('contextid', 0, PARAM_INT); // Default to 0 if not provided.
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

// Check if the dummy course and assignment already exist.
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
    $moduleinfo->modulename = 'assign'; // The name of the module.
    $moduleinfo->module = $moduleid; // Assign the module ID for 'assign'.
    $moduleinfo->course = $courseid;
    $moduleinfo->section = 0; // Section 0 or the appropriate section ID.
    $moduleinfo->visible = 0; // Hidden course module.

    // Assignment specific settings.
    $moduleinfo->name = 'Template Assignment for Setcheck';
    $moduleinfo->intro = 'This is the introductory text for the template assignment.';
    $moduleinfo->duedate = time() + (7 * 24 * 60 * 60); // Set due date 1 week from now.

    // Create the course module and assignment instance.
    $moduleinfo = add_moduleinfo($moduleinfo, $course);

    // Store the assignment ID and course module ID.
    $assignmentid = $moduleinfo->instance;
    $cmid = $moduleinfo->coursemodule;

    // Save IDs to the plugin config.
    set_config('assignmentid', $assignmentid, 'local_setcheck');

    // Fetch course module information for the newly created assignment.
    $cm = get_coursemodule_from_id('assign', $cmid, $courseid, true, MUST_EXIST);
} else {
    // Validate that the course module and assignment are still valid.
    $cm = $DB->get_record('course_modules', ['course' => $courseid, 'instance' => $assignmentid]);

    if (!$cm) {
        // If the course module is broken or missing, reset the config and redirect.
        set_config('courseid', null, 'local_setcheck');
        set_config('assignmentid', null, 'local_setcheck');
        redirect(new moodle_url('/local/setcheck/pages/create_template.php'),
            'Course module or assignment was invalid. Please refresh.'
        );
    }
}

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

            $mform->setType($elementname, PARAM_RAW);
        }
    }
}

if (!$cm) {
    // If the course module is broken or missing, reset the config.
    set_config('courseid', null, 'local_setcheck');
    set_config('assignmentid', null, 'local_setcheck');
    redirect(new moodle_url('/local/setcheck/pages/create_template.php'),
    'Course module or assignment was invalid. Please refresh.'
    );
}

// Fetch the course object again if needed.
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
// Set up the page for form display.
$PAGE->set_cm($cm, $course); // Ensure the course matches the course module.
// Set the action URL for the form.
$actionurl = new moodle_url('/local/setcheck/pages/create_template.php');
// Create the form with the required arguments.
$mform = new local_setcheck_assign_template_form($actionurl, $cm, $courseid);

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
