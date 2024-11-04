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
 * Hooks related to the assignment form.
 *
 * @package    local_setcheck
 * @copyright  2024 David Kelly
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_setcheck\services\TemplateService;


/**
 * Hook to add template selection to the assignment settings form.
 *
 * This function adds a template selection dropdown and an "Apply Template" button to the
 * assignment settings form. It also adds JavaScript to handle the template application.
 *
 * @package    local_setcheck
 * @param MoodleQuickForm $mform The Moodle form object where elements are added
 * @throws dml_exception If an error occurs while accessing the database
 */
function local_setcheck_assignment_form_hook($mform) {
    global $DB, $PAGE;

    // Get all templates.
    $course = $DB->get_record('course', ['id' => $PAGE->course->id]);
    $categoryid = $course->category;
    $templatescat = TemplateService::get_templates_for_category_from_module($course, $categoryid);
    $templatescourse = TemplateService::get_templates_for_course($PAGE->course->id);

    // Merge templates into an associative array.
    $templates = $templatescat + $templatescourse;

    // Add template selection dropdown at the top of the form.
    $mform->insertElementBefore(
        $mform->createElement('header', 'setcheck_header', get_string('setcheck_header', 'local_setcheck')),
        'general'
    );

    // Populate $option array for createElement.
    $option = [0 => get_string('select_template_option', 'local_setcheck')];
    foreach ($templates as $id => $name) {
        $option[$id] = $name;
    }

    // Create the dropdown select element.
    $select = $mform->createElement('select', 'setcheck_template', get_string('select_template', 'local_setcheck'), $option);

    $mform->insertElementBefore($select, 'general');
    $mform->addHelpButton('setcheck_template', 'select_template', 'local_setcheck');

    // Add apply template button, make it a button instead of submit to prevent form submission.
    $applybutton = $mform->createElement('button', 'apply_setcheck_template', get_string('apply_template', 'local_setcheck'));
    $mform->insertElementBefore($applybutton, 'general');
    $mform->disabledIf('apply_setcheck_template', 'setcheck_template', 'eq', 0);

    // Add JavaScript to set data-templateid attributes.
    $PAGE->requires->js_amd_inline("
        require(['jquery'], function($) {
            $('#id_setcheck_template option').each(function() {
                var value = $(this).val();
                if (value && value !== '0') {
                    $(this).attr('data-templateid', value);
                }
            });
        });
    ");

    // Add JavaScript for template application logic.
    // The JS will handle clicking the "Apply Template" button without submitting the form.
    $PAGE->requires->js_call_amd('local_setcheck/apply_template', 'init', []);
}
