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
 * Strings for language 'en' for local_wstwoawithdraw.
 *
 * @package     local_wstwoawithdraw
 * @author      Jeremy FitzPatrick <jeremy.fitzpatrick@twoa.ac.nz>>
 * @copyright   2025, Te Wānanga o Aotearoa
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// No direct access.
defined('MOODLE_INTERNAL') || die();

// Default langstring.
$string['pluginname']       = 'TWOA withdraw process webservices';

// Strings for course stuff.
$string['cohortnotfound']   = 'Cohort not found.';
$string['notincohort']      = 'The student was not found in this cohort.';
$string['studentnotfound']  = 'No student was not found with this id.';
$string['summary']          = 'Suspended from {$a->suspended} courses, unenrolled from {$a->unenrolled} courses.';

// Strings for privacy API.
$string['privacy:metadata'] = 'The local webservices withdraw plugin does not store any personal data.';