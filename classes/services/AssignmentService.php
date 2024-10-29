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

/**
 * Helper class to manage the creation of dummy assignments.
 */
class AssignmentService {

    /**
     * Create a dummy assignment inside a hidden course.
     * @param int $courseid The course ID.
     * @return $assignmentid The assignment ID.
     */
    public static function create_assignment($courseid) {
        global $DB;

        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

        // Fetch the module ID for the 'assign' module.
        $module = $DB->get_record('modules', ['name' => 'assign'], '*', MUST_EXIST);
        $moduleid = $module->id;

        // Initialize moduleinfo with basic settings.
        $moduleinfo = new \stdClass();
        $moduleinfo->modulename = 'assign';
        $moduleinfo->module = $moduleid;
        $moduleinfo->course = $courseid;
        $moduleinfo->section = 0;
        $moduleinfo->visible = 0;
        $moduleinfo->name = 'Template Assignment for Setcheck';
        $moduleinfo->intro = 'This is the introductory text for the template assignment.';
        $moduleinfo->introformat = 1;
        $moduleinfo->grade = 100;

        // Retrieve and apply assignment config defaults.
        $assignconfig = get_config('assign');
        foreach ($assignconfig as $key => $value) {
            if (!isset($moduleinfo->$key)) {
                $moduleinfo->$key = $value;
            }
        }

        // Manually apply date-based settings, as these are typically specific to each assignment instance.
        $moduleinfo->allowsubmissionsfromdate = time();
        $moduleinfo->duedate = time() + (7 * 24 * 60 * 60);  // 1 week from now.
        $moduleinfo->cutoffdate = time() + (14 * 24 * 60 * 60);  // 2 weeks from now.

        // Create and save the assignment instance in the database.
        $course = $DB->get_record('course', ['id' => $courseid]);
        $moduleinfo = add_moduleinfo($moduleinfo, $course);
        $assignmentid = $moduleinfo->instance;

        // Store the assignment ID in the plugin config for reference.
        set_config('assignmentid', $assignmentid, 'local_setcheck');

        return $assignmentid;
    }

    /**
     * Set the course module properties.
     * @param object $cm The course module object.
     * @param int $courseid The course ID.
     * @return object The course object.
     */
    public static function set_course_module_properties($cm, $courseid) {
        global $DB;

        $cm->coursemodule = $cm->id;  // Set the coursemodule property.
        $cm->course = $courseid;       // Set the course property if it's not already set.
        $cm->visible = $cm->visible ?? 0;  // Set visibility if not already set.
        $cm->idnumber = '';
        $cm->completiongradeitemnumber = -1; // No specific grade item for completion.
        $cm->availability = json_encode([]); // No availability conditions.
        $cm->deletioninprogress = 0; // Module is not in the process of being deleted.
        $cm->lang = ''; // Default or unspecified language.

        return $course = $DB->get_record('course', ['id' => $courseid]);
    }

}
