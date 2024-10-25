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

/**
 * Helper class to manage the creation of dummy assignments.
 */
class AssignmentService {

    /**
     * Create a dummy assignment inside a hidden course.
     * @param int $courseid The course ID.
     * @return void
     */
    public static function create_assignment($courseid) {
        global $DB;

        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

        // Fetch the module ID for the 'assign' module.
        $module = $DB->get_record('modules', ['name' => 'assign'], '*', MUST_EXIST);
        $moduleid = $module->id; // Get the 'assign' module ID.

        // Proceed to create the assignment inside the hidden course.
        $moduleinfo = new \stdClass();
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
