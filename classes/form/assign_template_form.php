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
 * Custom assignment form for local_setcheck plugin.
 *
 * @package    local_setcheck
 * @copyright  2024 David Kelly
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_setcheck\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/assign/mod_form.php');
use moodle_url;

/**
 * Extended form for creating assignment templates in local_setcheck plugin.
 */
class extended_assign_form extends \mod_assign_mod_form {
    /**
     * @var object Course module object.
     */
    protected $cm;

    /**
     * @var int Course ID.
     */
    protected $courseid;

    /**
     * @var object Context ID.
     */
    protected $pagecontextid;

    /**
     * Constructor to accept course module, course ID, and page context.
     *
     * @param \moodle_url $actionurl URL for the form action.
     * @param object $cm Course module object.
     * @param int $courseid Course ID.
     * @param int $pagecontextid Context ID for the page.
     */
    public function __construct($actionurl, $cm, $courseid, $pagecontextid) {
        global $DB;

        $this->cm = $cm;
        $this->courseid = $courseid;
        $this->pagecontextid = $pagecontextid;
        $cm->modname = 'assign'; // Set the module name.

        // Fetch required data for moodleform_mod constructor.
        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        $current = new \stdClass(); // Default to an empty stdClass for `current`.
        $section = 0; // Default section value.
        // Populate $current with necessary properties to suppress warnings.
        $current = new \stdClass();
        $current->coursemodule = $cm->id ?? null; // Ensure coursemodule is set.
        $current->course = $course->id; // Set course ID.
        $current->id = $cm->instance ?? null; // Instance ID if available.
        $current->modname = 'assign'; // Module name expected in moodleform_mod.
        $current->instance = $cm->instance ?? null; // Instance ID if available.

        // Set the module name manually to bypass Moodle's validation.
        $this->_modname = 'assign';

        parent::__construct($current, $section, $cm, $course);
    }

    /**
     * Define the form structure and include template-specific fields.
     */
    public function definition() {
        parent::definition();

        $mform = $this->_form;

        \local_setcheck\services\FormService::add_button_array($mform, $this->pagecontextid);
    }

    /**
     * Called to modify the form after the data is set.
     */
    public function definition_after_data() {
        // Call the parent method to handle standard functionality.
        parent::definition_after_data();

        $mform = $this->_form;

        // Now add the template fields under the 'template_settings' header.
        \local_setcheck\services\FormService::add_template_fields($mform);

        // Remove specific elements as needed.
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
            'tagshdr',
            'tags',
            'competenciessection',
            'competencies',
            'competency_rule',
            // 'override_grade',
            // '_qf__mod_assign_mod_form',
            // 'modstandardelshdr',
            // 'visible',
            // 'cmidnumber',
            // 'lang',
            // 'groupmode',
            // 'groupingid',
            // 'restrictgroupbutton',
            // 'availabilityconditionsheader',
            // 'availabilityconditionsjson',
            // 'course',
            // 'coursemodule',
            // 'section',
            // 'module',
            // 'modulename',
            // 'instance',
            // 'add',
            // 'update',
            // 'return',
            // 'sr',
            // 'beforemod',
            // 'showonly',
            'coursecontentnotification',
            'buttonar',
        ];

        foreach ($elementstoremove as $element) {
            if ($mform->elementExists($element)) {
                $mform->removeElement($element);
            }
        }
    }
}
