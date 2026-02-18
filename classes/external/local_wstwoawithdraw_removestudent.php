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
 * Stuff for local_wstwoawithdraw.
 *
 * @package     local_wstwoawithdraw
 * @author      Jeremy FitzPatrick <jeremy.fitzpatrick@twoa.ac.nz>>
 * @copyright   2025, Te Wānanga o Aotearoa
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_wstwoawithdraw\external;

// No direct access.
defined('MOODLE_INTERNAL') || die();

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;

require_once($CFG->dirroot.'/cohort/lib.php');
require_once($CFG->dirroot.'/lib/grade/grade_item.php');
require_once($CFG->dirroot.'/lib/grade/grade_grade.php');
require_once($CFG->dirroot.'/lib/grade/constants.php');
require_once($CFG->dirroot.'/enrol/manual/externallib.php');


/**
 * Class local_wstwoawithdraw_removestudent
 */
class local_wstwoawithdraw_removestudent extends external_api {
    /** @var string Regular expression to match the grade category id on */
    const GRADECAT_PATTERN = '/(W[A-Z]{4}\d{3}|Q\d{5})(\.{1}\d{1,2})?/';
    /** @var int Delay before completely removing student from the course. */

    /**
     *
     * @return \external_function_parameters
     */
    public static function unenrolstudent_parameters() {
        return new external_function_parameters (
            [
                'userid'      => new external_value(
                    PARAM_INT,
                    'The Moodle id of the user to be unenrolled',
                    VALUE_REQUIRED,
                    0
                ),
                'cohortid'      => new external_value(
                    PARAM_INT,
                    'The Moodle id of the cohort which the student is to be removed from',
                    VALUE_REQUIRED,
                    0
                ),
                'withdrawalstatus' => new external_value(
                    PARAM_ALPHA,
                    'The enrolement status in the SMS of the student. Should be W, X or pseudo status D',
                    VALUE_REQUIRED,
                    'W'
                ),
                'testing' => new external_value(
                    PARAM_BOOL,
                    'No changes made.',
                    VALUE_OPTIONAL,
                    0
                ),
            ]
        );
    }

    /**
     * Remove the student from the course after checking for grades.
     * @param integer $userid Moodle's identifier for the student.
     * @param integer $cohortid Moodle's cohort identifier.
     * @param string $withdrawalstatus SMS enrolement status.
     * @return array
     */
    public static function unenrolstudent($userid, $cohortid, $withdrawalstatus, $testing) {
        global $DB;
        $suspended = 0;
        $pending = 0;
        $params  = self::validate_parameters(self::unenrolstudent_parameters(), [
            'userid' => $userid,
            'cohortid' => $cohortid,
            'withdrawalstatus' => $withdrawalstatus,
            'testing' => $testing,
        ]);
        $roleid  = $DB->get_field('role', 'id', ['shortname' => 'student'], MUST_EXIST);
        $cohort  = $DB->get_record('cohort', ['id' => $params['cohortid']], 'id, contextid, visible');
        $student = $DB->get_record('user', ['id' => $params['userid']]);

        // Start with basic checks.
        if (!$student) {
            return ['success' => false, 'comment' => get_string('studentnotfound', 'local_wstwoawithdraw')];
        }
        if (!$cohort) {
            return ['success' => false, 'comment' => get_string('cohortnotfound', 'local_wstwoawithdraw')];
        }
        if (!in_array($params['withdrawalstatus'], ['W', 'X', 'D'])) {
            return ['success' => false, 'comment' => get_string('nostatus', 'local_wstwoawithdraw', $params['withdrawalstatus'])];
        }
        if (!cohort_is_member($cohort->id, $params['userid'])) {
            return ['success' => false, 'comment' => get_string('notincohort', 'local_wstwoawithdraw')];
        }

        // Get the enrolment methods (therefore courses) associated with this cohort.
        $cohortenrolinstances = $DB->get_records('enrol', ['enrol' => 'cohort', 'customint1' => $cohort->id]);
        // Now look for graded activities in the courses this cohort is attached to.
        foreach ($cohortenrolinstances as $enrolmethod) {
            $context = \context_course::instance($enrolmethod->courseid);
            $isgraded = false;
            self::validate_context($context);
            require_capability('enrol/manual:enrol', $context);
            require_capability('moodle/cohort:assign', $context);

            // Prepare the basic data set for enrolment.
            $data = [
                'roleid'   => $roleid,
                'userid'   => $params['userid'],
                'courseid' => $enrolmethod->courseid,
                'suspend'  => 1,
                'timestart' => time(),
            ];

            // We can skip these checks for X and D if allowed in the config.
            if (!get_config('local_wstwoawithdraw', 'skipchecks') || $params['withdrawalstatus'] === 'W') {
                // Get a list of all the assessed activities in this course.
                $allgradeditems = [];
                $gradedcategories = self::get_gradedcategories($enrolmethod->courseid);
                foreach ($gradedcategories as $category) {
                    if ($gradeditems = self::get_gradeditems($category)) {
                        $allgradeditems = array_merge($allgradeditems, $gradeditems);
                    }
                }

                // Is student graded in any of these activities?
                foreach ($allgradeditems as $item) {
                    $grade = \grade_grade::fetch(['itemid' => $item->id, 'userid' => $params['userid']]);

                    if (gettype($grade) === 'object') {
                        $isgraded = true;
                        break;
                    }
                }
            }

            // Get the last time the student accessed the course if ever.
            $lastaccess = $DB->get_field(
                'user_lastaccess',
                'timeaccess',
                ['userid' => $params['userid'], 'courseid' => $enrolmethod->courseid]
            );

            if ($isgraded) {
                // Keep in the course.
                if (!$testing) {
                    \enrol_manual_external::enrol_users([$data]);
                }
                $suspended++;
            } else if ($lastaccess) {
                // Keep in course for grace period.
                $data['timeend'] = time() + get_config('local_wstwoawithdraw', 'graceperiod');
                if (!$testing) {
                    \enrol_manual_external::enrol_users([$data]);
                }
                $pending++;
            }
        }

        // Remove from cohort.
        if (!$testing) {
            cohort_remove_member($cohort->id, $params['userid']);
        }

        // Provide some success status information.
        $comment = (object)[
            'user'      => $student->firstname . ' ' . $student->lastname,
            'suspended' => $suspended,
            'unenrolled' => count($cohortenrolinstances) - $suspended - $pending,
            'pending'    => $pending,
        ];
        return [
            'success' => true,
            'comment' => get_string('summary', 'local_wstwoawithdraw', $comment),
        ];
    }

    /**
     * Describe the returned data structure.
     * @return \external_single_structure
     */
    public static function unenrolstudent_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Did this work ok?'),
            'comment' => new external_value(PARAM_CLEANHTML, 'What actions were taken.'),
        ]);
    }

    /**
     * Gets a list of grade categories that are graded (associated to a kōnae or unit standard).
     * @param integer $courseid
     * @return array
     */
    private static function get_gradedcategories($courseid) {
        $categories = \grade_item::fetch_all(['courseid' => $courseid, 'itemtype' => 'category']);
        if (!$categories) {
            return [];
        }
        foreach ($categories as $key => $category) {
            $isgraded = preg_match(self::GRADECAT_PATTERN, $category->idnumber);
            // Only keep graded categories.
            if ($isgraded !== 1) {
                unset($categories[$key]);
                continue;
            }
        }
        return $categories;
    }

    /**
     * Gets the grade items for a category that is graded.
     *
     * @param \grade_item $category
     * @return array
     */
    private static function get_gradeditems($category) {
        $gradeditems = [];
        if (!empty($category->calculation)) {
            // Get the grade items in the calculation.
            $gipattern = '/(?!gi##)\d+(?=##)/';
            preg_match_all($gipattern, $category->calculation, $giids);
            foreach ($giids[0] as $giid) {
                $gi = \grade_item::fetch(['id' => $giid]);
                array_push($gradeditems, $gi);
            }
        } else {
            // Get the items in this category.
            $gradeditems = \grade_item::fetch_all(['categoryid' => $category->iteminstance]);
        }
        return $gradeditems;
    }
}
