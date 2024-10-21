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
 * External API for local_setcheck plugin.
 *
 * @package    local_setcheck
 * @copyright  2024 David Kelly
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_setcheck;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php'); // Include the Moodle external library.

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Class external
 * Handles external API calls for the local_setcheck plugin.
 */
class external extends external_api {

    /**
     * Define the parameters required for getting the template.
     *
     * @return external_function_parameters
     */
    public static function get_template_parameters() {
        return new external_function_parameters(
            [
                'templateid' => new external_value(PARAM_INT, 'The ID of the template to retrieve'),
            ]
        );
    }

    /**
     * Fetch the template data dynamically based on the template ID provided.
     *
     * @param int $templateid The ID of the template to fetch
     * @return array An array representing the template settings, including HTML IDs
     * @throws \invalid_response_exception
     */
    public static function get_template($templateid) {
        global $DB;

        // Validate incoming parameters.
        $params = self::validate_parameters(self::get_template_parameters(), ['templateid' => $templateid]);

        // Fetch the template record by ID.
        $template = $DB->get_record('local_setcheck_templates', ['id' => $params['templateid']], '*', MUST_EXIST);

        // Decode the settings JSON.
        $settings = json_decode($template->settings, true);

        if (!is_array($settings)) {
            throw new \invalid_response_exception('Invalid template settings data.');
        }

        // Add the template name as part of the response.
        $settings['name'] = $template->name;

        // Store template settings in a static property for generating return structure.
        self::$templatesettings = $settings;

        return $settings;
    }

    /**
     * Static variable to store settings temporarily for dynamic return structure generation.
     * @var $templatesettings
     *
     */
    private static $templatesettings = [];

    /**
     * Define the return structure dynamically based on the template settings.
     *
     * @return external_single_structure The dynamic return structure
     */
    public static function get_template_returns() {
        // Initialize the return structure with the 'name' field.
        $fields = [
            'name' => new external_value(PARAM_TEXT, 'Template name'),
        ];

        // If template settings were retrieved, add them dynamically.
        if (!empty(self::$templatesettings) && is_array(self::$templatesettings)) {
            foreach (self::$templatesettings as $key => $value) {
                // Determine the data type to use (e.g., PARAM_RAW for general, PARAM_INT for numeric).
                $paramtype = is_numeric($value) ? PARAM_INT : PARAM_RAW;
                $fields[$key] = new external_value($paramtype, "Value for field $key", VALUE_OPTIONAL);
            }
        }

        // Return an external structure with fields defined based on available data.
        return new external_single_structure($fields);
    }
}
