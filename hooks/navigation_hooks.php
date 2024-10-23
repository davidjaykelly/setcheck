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
 *
 * Hooks related to navigation.
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
function local_setcheck_hook_navigation($navigation) {
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
 * Extends the secondary navigation for categories.
 *
 * @param navigation_node $parentnode The parent node where the new link will be added.
 */
function local_setcheck_hook_navigation_category_settings($navigation, $coursecategorycontext) {
    $title = get_string('manage_activity_templates', 'local_setcheck');
    $categoryid = $coursecategorycontext->instanceid; // This will give the actual category ID.
    $createpath = new moodle_url("/local/setcheck/pages/create_template.php", [
        'pagecontextid' => $coursecategorycontext->id,
        'categoryid' => $categoryid,
        'contextlevel' => 'category',
    ]);
    $managepath = new moodle_url("/local/setcheck/manage_templates.php", [
        'pagecontextid' => $coursecategorycontext->id,
        'categoryid' => $categoryid,
        'contextlevel' => 'category',
    ]);
    $settingsnode = navigation_node::create($title,
                                            $managepath,
                                            navigation_node::TYPE_SETTING,
                                            null,
                                            'setcheckcreatetemplate',
                                            new pix_icon('i/settings', ''));
    if (isset($settingsnode)) {
        $settingsnode->set_force_into_more_menu(true);
        $navigation->add_node($settingsnode);
    }
}

/**
 * Extends the secondary navigation for courses.
 *
 * @param navigation_node $parentnode The parent node where the new link will be added.
 */
function local_setcheck_hook_navigation_course($navigation, $course, $coursecontext) {
    $title = get_string('manage_activity_templates', 'local_setcheck');
    $courseid = $course->id;
    $createpath = new moodle_url("/local/setcheck/pages/create_template.php", [
        'pagecontextid' => $coursecontext->id,
        'courseid' => $courseid,
        'contextlevel' => 'course',
    ]);
    $managepath = new moodle_url("/local/setcheck/manage_templates.php", [
        'pagecontextid' => $coursecontext->id,
        'courseid' => $courseid,
        'contextlevel' => 'course',
    ]);
    $settingsnode = navigation_node::create($title,
                                            $managepath,
                                            navigation_node::TYPE_SETTING,
                                            null,
                                            'setcheckcreatetemplate',
                                            new pix_icon('i/settings', ''));
    if (isset($settingsnode)) {
        $settingsnode->set_force_into_more_menu(true);
        $navigation->add_node($settingsnode);
    }
}
