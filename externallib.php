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
 * External function for fetching templates from the database.
 *
 * @package    local_setcheck
 * @copyright  2024 David Kelly
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_setcheck\external;

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/externallib.php");

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use local_setcheck\services\TemplateService;

/**
 * Class get_templates for fetching templates from the database.
 */
class get_template extends external_api {

    /**
     * Define the parameters for the external function.
     * @return external_function_parameters
     */
    public static function get_template_parameters() {
        return new external_function_parameters([
            'templateid' => new external_value(PARAM_INT, 'The ID of the specific template to retrieve', VALUE_REQUIRED),
        ]);
    }

    /**
     * Description of the external function.
     * @return string
     */
    public static function get_template_description() {
        return 'Retrieves a template based on a specific template ID';
    }

    /**
     * Retrieve templates based on the given template ID.
     *
     * @param int $templateid
     * @return array
     * @throws \invalid_parameter_exception
     */
    public static function get_template($templateid) {
        $params = self::validate_parameters(self::get_template_parameters(), ['templateid' => $templateid]);

        // Fetch template from the database.
        $template = TemplateService::get_template($params['templateid']);

        if (empty($template)) {
            throw new \moodle_exception('notemplatesfound', 'local_setcheck');
        }

        return $template;
    }

    /**
     * Define the structure of the returned data.
     * @return external_multiple_structure
     */
    public static function get_template_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Template ID'),
                'name' => new external_value(PARAM_TEXT, 'Template name'),
                'description' => new external_value(PARAM_TEXT, 'Template description'),
                'settings' => new external_multiple_structure( // Define settings as a nested structure
                    new external_single_structure([
                        'html_id' => new external_value(PARAM_TEXT, 'HTML ID of the form field'),
                        'value' => new external_value(PARAM_RAW, 'Value of the form field')
                    ])
                ),
            ])
        );
    }

    /**
     * Description of the returned data structure.
     * @return string
     */
    public static function get_template_returns_description() {
        return 'Returns the details of the template including ID, name, description, and settings in JSON format';
    }
}
