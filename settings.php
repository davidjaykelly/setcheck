<?php
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $ADMIN->add('localplugins', new admin_category('local_setcheck', get_string('pluginname', 'local_setcheck')));

    $ADMIN->add('local_setcheck', new admin_externalpage('local_setcheck_manage_templates',
        get_string('manage_templates', 'local_setcheck'),
        new moodle_url('/local/setcheck/manage_templates.php')
    ));

    $ADMIN->add('local_setcheck', new admin_externalpage('local_setcheck_create_template',
        get_string('create_template', 'local_setcheck'),
        new moodle_url('/local/setcheck/create_template.php')
    ));

    $settings = new admin_settingpage('local_setcheck_general', get_string('generalsettings', 'local_setcheck'));
    $ADMIN->add('local_setcheck', $settings);

    $settings->add(new admin_setting_heading('local_setcheck_templates',
        get_string('templates', 'local_setcheck'),
        get_string('templates_desc', 'local_setcheck')));
}
