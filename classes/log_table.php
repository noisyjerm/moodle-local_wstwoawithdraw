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

namespace local_wstwoawithdraw;

defined('MOODLE_INTERNAL') || die();

// Load tablelib because this is not autoloaded.
require_once("{$CFG->libdir}/tablelib.php");
use html_writer;

/**
 * Log table class.
 *
 * @package     local_wstwoawithdraw
 * @copyright   2025 Te Wānanga o Aotearoa
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class log_table extends \table_sql {
    /**
     * table constructor.
     *
     * Constructs the TWOA report table with the given parameters.
     *
     * @param string $uniqueid Some sort of unique id for this table object.
     * @param \moodle_url $baseurl The url including any query strings. This will help generate urls for paging what is output.
     * @param array $params Any parameters that need to be passed to the sql query.
     */
    public function __construct($uniqueid, \moodle_url $baseurl, $params = []) {
        global $CFG;
        // Set the id of this table.
        parent::__construct($uniqueid);

        $this->define_baseurl($baseurl);

        // Set the columns as per the constructor.
        $columns = ['student', 'action', 'shortname', 'datechanged'];
        $this->define_columns($columns);

        // Set the column headers as per the constructor.
        foreach ($columns as $column) {
            $headers[] = get_string('colheader_' . $column, 'local_wstwoawithdraw');
        }
        $this->define_headers($headers);

        // Is this table downloadable?
        $this->is_downloadable(true);

        // Let's make the table download button show up where it is defined in the constructor.
        $this->show_download_buttons_at([TABLE_P_BOTTOM]);

        // Set the sql for this table.
        $strwithdrawn = get_string('action_withdrawn', 'local_wstwoawithdraw');
        $strpending = get_string('action_pending', 'local_wstwoawithdraw');
        $strsuspended = get_string('action_suspended', 'local_wstwoawithdraw');
        $sqlfields  = "log.id,
                      CONCAT(u.firstname, ' ', u.lastname) student,
                      CASE
                          WHEN ue.timeend IS NULL THEN '$strwithdrawn'
                          WHEN ue.timeend = 0 THEN '$strsuspended'
                      ELSE
                          '$strpending'
                      END AS action,
                      c.shortname,
                      log.timecreated datechanged";
        $sqlfrom    = "{logstore_standard_log} log
                      LEFT JOIN {enrol} e ON (e.courseid = log.courseid AND e.enrol = 'manual')
                      LEFT JOIN {user_enrolments} ue ON (ue.userid = log.relateduserid AND ue.enrolid = e.id)
                      JOIN {course} c ON c.id = log.courseid
                      JOIN {user} u ON u.id = log.relateduserid";
        // Build the WHERE.
        $sqlwhere = "log.origin = 'ws'
                    AND log.contextlevel IN (40,50)
                    AND log.target = 'user_enrolment'";

        $sqlparams = [];

        $this->set_sql($sqlfields, $sqlfrom, $sqlwhere, $sqlparams);

    }

    /**
     * Show a readable date in the date column,
     *
     * @param object $value
     * @return mixed
     */
    public function col_datechanged($value) {
        return date("d/m/Y h:m", $value->datechanged);
    }
}
