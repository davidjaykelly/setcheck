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

namespace local_setcheck\services;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/assign/mod_form.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');

use mod_assign_mod_form;
use moodle_url;

/**
 * Helper class to manage the dynamic loading of assignment form fields.
 */
class FormService {
    /**
     * Adds template fields to the form.
     *
     * @param \MoodleQuickForm $mform The form object.
     * @return void
     */
    public static function add_template_fields($mform) {
        $mform->insertElementBefore(
            $mform->createElement('header', 'template_settings', get_string('template_settings', 'local_setcheck')),
            'general'
        );

        // Add the template name field.
        $mform->insertElementBefore(
            $mform->createElement('text', 'template_name', get_string('template_name', 'local_setcheck')),
            'general'
        );
        $mform->setType('template_name', PARAM_TEXT);
        $mform->addRule('template_name', get_string('required', 'local_setcheck'), 'required', null, 'server');
        $mform->setDefault('template_name', '');

        // Add the template description field.
        $mform->insertElementBefore(
            $mform->createElement(
                'editor', 'template_description',
                get_string('template_description', 'local_setcheck')
            ),
            'general'
            );

        $mform->setType('template_description', PARAM_RAW);
        $mform->setDefault('template_description', '');
    }

    /**
     * Adds button array to the form.
     *
     * @param \MoodleQuickForm $mform The form object.
     * @return void
     */
    public static function add_button_array($mform, $pagecontextid) {
        $contextid = $pagecontextid;
        $redirecturl = new moodle_url('/local/setcheck/pages/manage_templates.php',
                [
                    'contextid' => $contextid,
                ]
            );

        // Add a button array to the form (Save and Cancel buttons).
        $buttonarray = [];
        $buttonarray[] = $mform->createElement('button', 'save_template_button', get_string('save_template', 'local_setcheck'));
        $buttonarray[] = $mform->createElement(
            'button',
            'cancel_template_button',
            get_string('cancel'),
            ['type' => 'button', 'onclick' => 'window.location.href="'.$redirecturl->out(false).'"; return false;']
        );
        // Add the group of buttons to the form.
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
        $mform->setType('template_html_ids', PARAM_RAW);

        $mform->closeHeaderBefore('buttonar');
    }

    /**
     * Sets up a real assignment form with custom submit buttons.
     *
     * @param moodle_url $actionurl The action URL for the form.
     * @param int $courseid The course ID.
     * @param int $contextid The context ID.
     * @return mod_assign_mod_form The modified assignment form.
     */
    public static function setup_real_assignment_form($actionurl, $courseid) {
        // Fetch context information.
        $context = \context_course::instance($courseid);

        // Set up a dummy course module if required.
        $cm = new \stdClass();
        $cm->course = $courseid;

        // Instantiate the assignment form.
        $mform = new mod_assign_mod_form($actionurl, $cm, $courseid, $context);

        return $mform;
    }

    /**
     * Removes unwanted elements from a form.
     * @param \MoodleQuickForm $mform The form object.
     * @param array $elements_to_remove Array of elements to be removed.
     * @return void
     */
    public static function remove_elements_by_name($mform, $elementstoremove) {
        foreach ($elementstoremove as $elementname) {
            if ($mform->elementExists($elementname)) {
                $mform->removeElement($elementname);
            }
        }
    }
}
