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
     * @param mixed $categoryid
     * @return array
     */
    public static function get_templates_for_category($categoryid) {
        global $DB;

        // 1. Get all descendant categories (including the current category)
        $descendantcategories = self::getdescendantcategories($categoryid);
        $categoryids = array_keys($descendantcategories);

        // 2. Get all courses within the category and its descendants
        $courses = $DB->get_records_select('course', 'category IN ( ? )', $categoryids);
        $courseids = array_keys($courses);

        // 3. Create separate IN clauses for category IDs and course IDs
        list($categorysql, $categoryparams) = $DB->get_in_or_equal($categoryids, SQL_PARAMS_NAMED);
        list($coursesql, $courseparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);

        // 4. Combine SQL fragments and parameters for the final query
        $sqlwhere = "(categoryid $categorysql OR courseid $coursesql)";
        $params = array_merge($categoryparams, $courseparams);

        // 5. Retrieve templates
        $templates = $DB->get_records_select('local_setcheck_templates', $sqlwhere, $params);

        // 5. Transform the result into an ID => name array
        $templateoptions = [];
        foreach ($templates as $template) {
            $templateoptions[$template->id] = $template->name;
        }

        return $templateoptions;
    }

    /**
     * Summary of get_templates_for_category
     * @param mixed $course
     * @param mixed $categoryid
     * @return array
     */
    public static function get_templates_for_category_from_module($course, $categoryid) {
        global $DB;

        // Get the course category.
        $categoryid = $course->category;
        $category = \core_course_category::get($categoryid);
        // Get all ancestor categories up to the root, and also get the current category.
        $categories = array_values($category->get_parents()); // Get all ancestor categories.
        $categories[] = $categoryid; // Include the current category ID.

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
        $templates = $DB->get_records('local_setcheck_templates', ['courseid' => $courseid], 'name ASC');
        $templates = $DB->get_records('local_setcheck_templates', ['courseid' => $courseid]);

        $templateoptions = [];
        foreach ($templates as $template) {
            $templateoptions[$template->id] = $template->name;
        }

        return $templateoptions;
    }

    /**
     * Recursive function to get all descendant categories
     *
     * @param int $categoryid The category ID to start from
     * @return array An array of category IDs
     */
    private static function getdescendantcategories($categoryid) {
        global $DB;

        $descendants = [$categoryid];
        $children = $DB->get_records_sql("SELECT id FROM {course_categories} WHERE parent = ?", [$categoryid]);

        foreach ($children as $child) {
            $descendants = array_merge($descendants, self::getDescendantCategories($child->id));
        }

        return $descendants;
    }
}
