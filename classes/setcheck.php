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
 * @package    local_setcheck
 * @copyright  2024 David Kelly
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_setcheck;

defined('MOODLE_INTERNAL') || die();

class setcheck {
    public static function apply_template($template_id, $assignment_id) {
        global $DB;
        
        $template = $DB->get_record('local_setcheck_templates', array('id' => $template_id));
        $assignment = $DB->get_record('assign', array('id' => $assignment_id));

        if (!$template || !$assignment) {
            return ['error' => 'Invalid template or assignment'];
        }

        $settings = json_decode($template->settings, true);
        if (!$settings) {
            return ['error' => 'Invalid template settings'];
        }

        // Apply template settings to assignment
        $update = new \stdClass();
        $update->id = $assignment->id;
        foreach ($settings as $key => $value) {
            $update->$key = $value;
        }

        // Save assignment
        $DB->update_record('assign', $update);

        return ['success' => 'Template applied successfully'];
    }

    public static function check_settings($template_id, $assignment_id) {
        global $DB;
        
        $template = $DB->get_record('local_setcheck_templates', array('id' => $template_id));
        $assignment = $DB->get_record('assign', array('id' => $assignment_id));

        if (!$template || !$assignment) {
            return ['error' => 'Invalid template or assignment'];
        }

        $settings = json_decode($template->settings, true);
        if (!$settings) {
            return ['error' => 'Invalid template settings'];
        }

        $errors = [];
        foreach ($settings as $key => $value) {
            if ($assignment->$key != $value) {
                $errors[] = "Mismatch in setting '$key'";
            }
        }

        return ['errors' => $errors];
    }

    public static function amend_errors($template_id, $assignment_id) {
        $result = self::apply_template($template_id, $assignment_id);
        if (isset($result['error'])) {
            return $result;
        }
        return ['success' => 'All errors amended'];
    }

    public static function get_templates() {
        global $DB;
        return $DB->get_records('local_setcheck_templates', null, 'name ASC');
    }

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

    public static function get_templates_for_course($courseid) {
        global $DB;

        echo '<pre> HELLO: <br>';
        echo '</pre>';

        // Fetch templates specifically assigned to the given course ID.
        $templates = $DB->get_records('local_setcheck_templates', ['courseid' => $courseid]);

        $templateoptions = [];
        foreach ($templates as $template) {
            $templateoptions[$template->id] = $template->name;
        }

        return $templateoptions;
    }

}
