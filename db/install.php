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
 * Installation procedure for the Setcheck plugin.
 *
 * @package    local_setcheck
 * @copyright  2024 David Kelly
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->dirroot . '/mod/assign/lib.php'); // Required for assignment settings.
require_once($CFG->libdir . '/adminlib.php');

/**
 * Post installation procedure for creating a hidden course and assignment.
 *
 * @package    local_setcheck
 * @copyright  2024 David Kelly
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function xmldb_local_setcheck_install() {
    global $DB, $CFG;

    // Create the hidden course for templates if it doesn't exist.
    $courseid = get_config('local_setcheck', 'courseid');
    if (!$courseid) {
        $courseconfig = new stdClass();
        $courseconfig->fullname = 'Template Course for Setcheck';
        $courseconfig->shortname = 'setcheck_template_course_' . time(); // Unique shortname.
        $courseconfig->category = 1; // Default category.
        $courseconfig->visible = 0; // Hidden course.

        $course = create_course($courseconfig);
        $courseid = $course->id; // Store course ID.
        set_config('courseid', $courseid, 'local_setcheck');
    }
}
