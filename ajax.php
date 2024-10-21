<?php
define('AJAX_SCRIPT', true);
require_once('../../config.php');

$action = required_param('action', PARAM_ALPHA);
$template_id = required_param('template_id', PARAM_INT);
$assignment_id = required_param('assignment_id', PARAM_INT);

switch ($action) {
    case 'apply':
        $result = \local_setcheck\setcheck::apply_template($template_id, $assignment_id);
        break;
    case 'check':
        $result = \local_setcheck\setcheck::check_settings($template_id, $assignment_id);
        break;
    case 'amend':
        $result = \local_setcheck\setcheck::amend_errors($template_id, $assignment_id);
        break;
    default:
        $result = ['error' => 'Invalid action'];
}

echo json_encode($result);
die();