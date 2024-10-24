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

namespace local_setcheck\form;

use moodleform;

/**
 * Form for creating assignment templates in local_setcheck plugin.
 */
class assign_template_form extends moodleform {
    /**
     * @var object Course module object.
     */
    protected $cm;

    /**
     * @var int Course ID.
     */
    protected $courseid;

    /**
     * Constructor to accept course module and course ID.
     *
     * @param \moodle_url $actionurl URL for the form action.
     * @param object $cm Course module object.
     * @param int $courseid Course ID.
     */
    public function __construct($actionurl, $cm, $courseid) {
        $this->cm = $cm;
        $this->courseid = $courseid;
        parent::__construct($actionurl);
    }

    /**
     * Defines the form structure.
     */
    public function definition() {
        $mform = $this->_form;
        $mform->updateAttributes(['id' => 'create_template_form']);

        // Add template fields.
        \local_setcheck\services\FormService::add_template_fields($mform);

        // Add assignment form fields dynamically.
        \local_setcheck\services\FormService::add_assign_form_fields($mform, $this->courseid, $this->cm);

        // Remove unwanted elements.
        \local_setcheck\services\FormService::remove_unwanted_elements($mform);

        // Add button array.
        \local_setcheck\services\FormService::add_button_array($mform);
    }
}
