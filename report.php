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
 * Displays the history of withdrawals.
 *
 * @package     local_wstwoawithdraw
 * @copyright   2025 Te Wānanga o Aotearoa
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
// Include the locallib for this plugin.
require_login();

$status    = optional_param('status', 100, PARAM_INT);
$download  = optional_param('download', '', PARAM_ALPHA);
$perpage   = optional_param('perpage', 100, PARAM_INT);

// Set the current page url.
$currentpageurl = new moodle_url('/local/wstwoawithdraw/report.php');

// Set where the user gets redirected to on error and when downloading.
$redirectto = new \moodle_url('/local/wstwoawithdraw/report.php');

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url($currentpageurl);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('pluginname', 'local_wstwoawithdraw'));
$PAGE->set_heading(get_string('pluginname', 'local_wstwoawithdraw'));

// This is the normal requirements.
require_capability('report/log:view', $context);

// Set the name of the report. This will be the name used for the exported file and the id of the table element.
$reportname = get_string('pluginname', 'local_wstwoawithdraw') . ' - ' .
    get_string('history', 'local_wstwoawithdraw') . ' - ' .


// Set the base url.
$reportbaseurl = new \moodle_url("{$CFG->wwwroot}/local/wstwoawithdraw/report.php", []);

// Now let's get the report.
$report = new \local_wstwoawithdraw\log_table('withdrawlogreport', $reportbaseurl, []);

if ($download) {
    // Tell the report object that we are downloading something.
    $report->is_downloading($download, $reportname, $reportname);

    // Now download the report.
    $report->out(30, false);

    // And then redirect to the main report page.
    redirect($currentpageurl);
}

// And send things to the screen.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('history', 'local_wstwoawithdraw'));
// Show the report here.
$report->out($perpage, false);

echo $OUTPUT->footer();
