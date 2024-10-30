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

/**
 * Summary of local_setcheck_extend_navigation
 * @param mixed $navigation
 * @return void
 */
function local_setcheck_extend_navigation($navigation) {
    require_once(__DIR__ . '/hooks/navigation_hooks.php');
    local_setcheck_hook_navigation($navigation);
}

/**
 * Summary of local_setcheck_extend_navigation_category_settings
 * @param mixed $navigation
 * @param mixed $coursecategorycontext
 * @return void
 */
function local_setcheck_extend_navigation_category_settings($navigation, $coursecategorycontext) {
    require_once(__DIR__ . '/hooks/navigation_hooks.php');
    local_setcheck_hook_navigation_category_settings($navigation, $coursecategorycontext);
}

/**
 * Summary of local_setcheck_extend_navigation_course
 * @param mixed $navigation
 * @param mixed $course
 * @param mixed $coursecontext
 * @return void
 */
function local_setcheck_extend_navigation_course($navigation, $course, $coursecontext) {
    require_once(__DIR__ . '/hooks/navigation_hooks.php');
    local_setcheck_hook_navigation_course($navigation, $course, $coursecontext);
}

/**
 * Summary of local_setcheck_coursemodule_standard_elements
 * @param mixed $formwrapper
 * @param mixed $mform
 * @return void
 */
function local_setcheck_coursemodule_standard_elements($formwrapper, $mform) {
    global $PAGE;
    if ($PAGE->pagetype !== 'mod-assign-mod' || !$formwrapper instanceof moodleform_mod) {
        return; // Do nothing if it's not an assignment form.
    }

    $current = $formwrapper->get_current();
    if (isset($current->modulename) && $current->modulename === 'assign') {
        require_once(__DIR__ . '/hooks/form_hooks.php');
        local_setcheck_assignment_form_hook($mform);
    }
}
