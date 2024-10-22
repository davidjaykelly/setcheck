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
 * UPDATE
 *
 * @package    local_setcheck
 * @copyright  2024 David Kelly
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_setcheck\services;

/**
 * Summary of setcheck
 */
class TemplateService {

    /**
     * Summary of get_templates_for_category
     * @param mixed $course
     * @param mixed $categoryid
     * @return array
     */
    public static function get_templates_for_category($course, $categoryid) {
        global $DB;

        // Get the course category.
        $category = \core_course_category::get($course->category);

        // Get all ancestor categories up to the root, and also get the current category.
        $categories = array_values($category->get_parents()); // Get all ancestor categories.
        $categories[] = $categoryid; // Include the current category.

        // Retrieve templates for the current and ancestor categories.
        list($sql, $params) = $DB->get_in_or_equal($categories, SQL_PARAMS_NAMED);
        $templates = $DB->get_records_select('local_setcheck_templates', "categoryid $sql", $params);

        // Transform the result into an ID => name array for the dropdown.
        $templateoptions = [];
        foreach ($templates as $template) {
            $templateoptions[$template->id] = $template->name;
        }

        return $templateoptions;
    }

    /**
     * Summary of get_templates_for_course
     * @param mixed $courseid
     * @return array
     */
    public static function get_templates_for_course($courseid) {
        global $DB;

        // Fetch templates specifically assigned to the given course ID.
        $templates = $DB->get_records('local_setcheck_templates', ['courseid' => $courseid]);

        $templateoptions = [];
        foreach ($templates as $template) {
            $templateoptions[$template->id] = $template->name;
        }

        return $templateoptions;
    }

}
