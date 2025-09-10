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
 * Web services for TWOA grade report.
 *
 * @package    gradereport_twoa
 * @copyright  2023 Te Wānanga o Aotearoa
 * @author     Jeremy FitzPatrick
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_wstwoawithdraw_removestudent' => [
        'classname' => 'local_wstwoawithdraw\external\local_wstwoawithdraw_removestudent',
        'methodname' => 'unenrolstudent',
        'classpath' => 'local/wstwoawithdraw/classes/external/local_wstwoawithdraw_removestudent.php',
        'description' => 'Safely removes a student from a course',
        'type' => 'write',
        'capabilities' => 'moodle/cohort:assign,enrol/manual:enrol',
        'ajax' => false,
    ],
];

// We define the services to install as pre-built services. A pre-build service is not editable by administrator.
$services = [
    'TWOA tauira withdrawn API' => [
        'shortname'          => 'twoa_withdraw',
        'functions'         => [
            'local_wstwoawithdraw_removestudent',
        ],
        'restrictedusers'   => 1,
        'enabled'           => 1,
    ],
];