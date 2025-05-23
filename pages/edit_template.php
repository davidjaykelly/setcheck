<?php
require_once(dirname(__DIR__, 3) . '/config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');

class edit_template_form extends moodleform {
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('text', 'name', get_string('name', 'local_setcheck'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required');

        $mform->addElement('textarea', 'description', get_string('description', 'local_setcheck'));
        $mform->setType('description', PARAM_TEXT);

        $mform->addElement('textarea', 'settings', get_string('settings', 'local_setcheck'));
        $mform->setType('settings', PARAM_RAW);
        $mform->addRule('settings', null, 'required');

        $this->add_action_buttons();
    }
}

admin_externalpage_setup('local_setcheck_manage_templates');

$id = optional_param('id', 0, PARAM_INT);

$PAGE->set_url(new moodle_url('/local/setcheck/edit_template.php', array('id' => $id)));
$PAGE->set_title($id ? get_string('edit_template', 'local_setcheck') : get_string('add_template', 'local_setcheck'));
$PAGE->set_heading($id ? get_string('edit_template', 'local_setcheck') : get_string('add_template', 'local_setcheck'));

$mform = new edit_template_form();

if ($id) {
    $template = $DB->get_record('local_setcheck_templates', array('id' => $id), '*', MUST_EXIST);
    $mform->set_data($template);
}

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/setcheck/pages/manage_templates.php'));
} else if ($data = $mform->get_data()) {
    $template = new stdClass();
    $template->name = $data->name;
    $template->description = $data->description;
    $template->settings = $data->settings;
    $template->timemodified = time();

    if ($id) {
        $template->id = $id;
        $DB->update_record('local_setcheck_templates', $template);
    } else {
        $template->timecreated = time();
        $DB->insert_record('local_setcheck_templates', $template);
    }

    redirect(new moodle_url('/local/setcheck/pages/manage_templates.php'));
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
