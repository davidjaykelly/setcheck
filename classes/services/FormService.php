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
 * UPDATE
 *
 * @package    local_setcheck
 * @copyright  2024 David Kelly
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_setcheck\services;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/assign/mod_form.php');
require_once($CFG->libdir . '/formslib.php');

/**
 * Helper class to manage the dynamic loading of assignment form fields.
 */
class FormService {

    /**
     * Add assignment form fields dynamically using reflection.
     *
     * @param \MoodleQuickForm $mform The form object.
     * @param int $courseid The course ID.
     * @param int $cmid The course module ID.
     * @param array $templatehtmlids Array to store HTML IDs of form elements.
     * @return void
     */
    public static function add_assign_form_fields($mform, $courseid, $cm) {
        global $DB;

        // Fetch data needed for the constructor of mod_assign_mod_form.
        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        $cm = get_coursemodule_from_id('assign', $cm->id, $courseid);

        $cm->idnumber = ''; // Set to empty string.
        $cm->completiongradeitemnumber = -1; // Set to -1.
        $cm->availability = '{}'; // Set to an empty JSON object.
        $cm->lang = ''; // Set to an empty string.
        $cm->section = 0; // Set to visible.

        // Fake data for $current if needed.
        $current = new \stdClass();
        $current->instance = 0; // Fake or retrieve current instance data if needed.
        $current->course = $course->id; // Add the course ID.
        $current->coursemodule = ''; // Add the course module ID.
        $current->visible = isset($cm->visible) ? $cm->visible : 1;

        // Instantiate the original assignment form.
        $assignform = new \mod_assign_mod_form($current, $cm->section, $cm, $course);

        // Use reflection to access the protected property _form (where the elements are stored).
        $reflection = new \ReflectionClass($assignform);
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

    /**
     * Remove unwanted elements from a form.
     *
     * @param \MoodleQuickForm $mform The form object.
     * @param array $removedelemenentsarray Array of elements to be removed.
     * @return void
     */
    public static function remove_unwanted_elements($mform) {
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
     * Adds template fields to the form.
     *
     * @param \MoodleQuickForm $mform The form object.
     * @return void
     */
    public static function add_template_fields($mform) {
        // Add the template name field.
        $mform->addElement('text', 'template_name', get_string('template_name', 'local_setcheck'));
        $mform->setType('template_name', PARAM_TEXT);
        $mform->addRule('template_name', get_string('required', 'local_setcheck'), 'required', null, 'server');
        $mform->setDefault('template_name', '');

        // Add the template description field.
        $mform->addElement(
            'editor', 'template_description',
            get_string('template_description', 'local_setcheck')
        );
        $mform->setType('template_description', PARAM_RAW);
        $mform->setDefault('template_description', '');

    }

    /**
     * Adds button array to the form.
     *
     * @param \MoodleQuickForm $mform The form object.
     * @return void
     */
    public static function add_button_array($mform, $pagecontextid) {
        $contextid = $pagecontextid;
        $redirecturl = new \moodle_url('/local/setcheck/pages/manage_templates.php',
                [
                    'contextid' => $contextid,
                ]
            );

        // Add a button array to the form (Save and Cancel buttons).
        $buttonarray = [];
        $buttonarray[] = $mform->createElement('button', 'save_template_button', get_string('save_template', 'local_setcheck'));
        $buttonarray[] = $mform->createElement(
            'button',
            'cancel_template_button',
            get_string('cancel'),
            ['type' => 'button', 'onclick' => 'window.location.href="'.$redirecturl->out(false).'"; return false;']
        );
        // Add the group of buttons to the form.
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
        $mform->setType('template_html_ids', PARAM_RAW);

        $mform->closeHeaderBefore('buttonar');
    }
}
