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
 * Extend the Moodle navigation for the plugin.
 *
 * @param global_navigation $navigation Navigation object.
 * @return void
 */
function local_setcheck_extend_navigation(global_navigation $navigation) {
    $node = $navigation->add(
        get_string('pluginname', 'local_setcheck'),
        new moodle_url('/local/setcheck/index.php'),
        navigation_node::TYPE_CUSTOM,
        null,
        null,
        new pix_icon('i/settings', '')
    );
    $node->showinflatnavigation = true;
}

/**
 * Hook function to extend assignment settings form.
 *
 * @param MoodleQuickForm $formwrapper The form wrapper
 * @param MoodleQuickForm $mform The actual form
 */
function local_setcheck_coursemodule_standard_elements($formwrapper, $mform) {
    global $PAGE;
    if ($PAGE->pagetype !== 'mod-assign-mod' || !$formwrapper instanceof moodleform_mod) {
        return; // Do nothing if it's not an assignment form.
    }

    $current = $formwrapper->get_current();
    if (isset($current->modulename) && $current->modulename === 'assign') {
        require_once(__DIR__ . '/assignment_form_hook.php');
        local_setcheck_assignment_form_hook($mform);
    }
}

/**
 * Hook function to handle assignment settings form submission.
 *
 * @param stdClass $data Form data
 */
function local_setcheck_coursemodule_edit_post_actions($data) {
    global $PAGE;
    if ($PAGE->pagetype !== 'mod-assign-mod' || !isset($data->modulename) || $data->modulename !== 'assign') {
        return $data; // Do nothing if it's not related to assignment.
    }

    require_once(__DIR__ . '/assignment_form_hook.php');
    local_setcheck_assignment_form_submit($data);

    return $data;
}

/**
 * Extends the secondary navigation for categories.
 *
 * @param navigation_node $parentnode The parent node where the new link will be added.
 */
function local_setcheck_extend_navigation_category_settings($navigation, $coursecategorycontext) {
    $title = get_string('create_template', 'local_setcheck');
    $categoryid = $coursecategorycontext->instanceid; // This will give the actual category ID.
    $path = new moodle_url("/local/setcheck/create_template.php", [
        'pagecontextid' => $coursecategorycontext->id,
        'categoryid' => $categoryid,
        'contextlevel' => 'category',
    ]);
    $settingsnode = navigation_node::create($title,
                                            $path,
                                            navigation_node::TYPE_SETTING,
                                            null,
                                            'setcheckcreatetemplate',
                                            new pix_icon('i/settings', ''));
    if (isset($settingsnode)) {
        $settingsnode->set_force_into_more_menu(true);
        $navigation->add_node($settingsnode);
    }
}

function local_setcheck_extend_navigation_course($navigation, $course, $coursecontext) {
    $title = get_string('create_template', 'local_setcheck');
    $courseid = $course->id;
    $path = new moodle_url("/local/setcheck/create_template.php", [
        'pagecontextid' => $coursecontext->id,
        'courseid' => $courseid,
        'contextlevel' => 'course',
    ]);
    $settingsnode = navigation_node::create($title,
                                            $path,
                                            navigation_node::TYPE_SETTING,
                                            null,
                                            'setcheckcreatetemplate',
                                            new pix_icon('i/settings', ''));
    if (isset($settingsnode)) {
        $settingsnode->set_force_into_more_menu(true);
        $navigation->add_node($settingsnode);
    }
}
