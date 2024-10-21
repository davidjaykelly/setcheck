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
 * Main page for the Setcheck plugin.
 *
 * This file contains the interface for selecting templates and assignments,
 * and applying or checking settings for assignments based on templates.
 *
 * @package    local_setcheck
 * @copyright  2024 David Kelly
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

// Setup the admin page for the Setcheck plugin.
admin_externalpage_setup('local_setcheck');

$PAGE->set_url(new moodle_url('/local/setcheck/index.php'));
$PAGE->set_title(get_string('pluginname', 'local_setcheck'));
$PAGE->set_heading(get_string('pluginname', 'local_setcheck'));

$PAGE->requires->js_call_amd('local_setcheck/main', 'init');

echo $OUTPUT->header();

// Get templates.
$templates = \local_setcheck\setcheck::get_templates();

// Get assignments from the database.
$assignments = $DB->get_records('assign', null, 'name ASC');

// Display the main interface.
?>
<div id="setcheck-container">
    <select id="template-select">
        <option value="">Select a template</option>
        <?php foreach ($templates as $template): ?>
            <option value="<?php echo $template->id; ?>"><?php echo s($template->name); ?></option>
        <?php endforeach; ?>
    </select>
    <select id="assignment-select">
        <option value="">Select an assignment</option>
        <?php foreach ($assignments as $assignment): ?>
            <option value="<?php echo $assignment->id; ?>"><?php echo s($assignment->name); ?></option>
        <?php endforeach; ?>
    </select>
    <button id="apply-settings"><?php echo get_string('apply_settings', 'local_setcheck'); ?></button>
    <button id="check-settings"><?php echo get_string('check_settings', 'local_setcheck'); ?></button>
    <button id="amend-errors"><?php echo get_string('amend_errors', 'local_setcheck'); ?></button>
    <div id="result-container"></div>
</div>

<?php
echo $OUTPUT->footer();
