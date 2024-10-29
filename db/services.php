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
 * [Your custom description here]
 *
 * @package    local_setcheck
 * @copyright  2024 David Kelly
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_setcheck_get_template' => [
        'classname'   => 'local_setcheck\external\get_template',
        'methodname'  => 'get_template',
        'classpath'   => 'local/setcheck/externallib.php',
        'description' => 'Retrieve templates for a specific context',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities' => 'moodle/site:config',
    ],
];

$services = [
    'Setcheck Service' => [
        'functions' => ['local_setcheck_get_template'],
        'restrictedusers' => 0,
        'enabled' => 1,
    ],
];
