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
 * Web services for TWOA withdraw student webservice.
 *
 * @package    local_wstwoawithdraw
 * @copyright  2023 Te Wānanga o Aotearoa
 * @author     Jeremy FitzPatrick
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_wstwoawithdraw;

// No direct access.
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/lib/enrollib.php');
require_once($CFG->dirroot . '/cohort/lib.php');

/**
 * Unit tests for Local WS TWOA Withdraw plugin removestudent external webservice.
 * @covers \local_wstwoawithdraw\external\removestudent
 */
final class removestudent_test extends \advanced_testcase {
    /**
     * Tests the response when sent a userid that is not found.
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     *
     */
    public function test_local_wstwoawithdraw_nouser(): void {
        global $DB;
        // Get the user with the highest id.
        $lastusers = $DB->get_records('user', null, 'id DESC', 'id', 0, 1);
        // Add some to their id so we know the user doesn't exist.
        $notauserid = array_pop($lastusers)->id + 1000;

        $result = external\local_wstwoawithdraw_removestudent::unenrolstudent($notauserid, 1234);
        $expected = ['success' => false, 'comment' => get_string('studentnotfound', 'local_wstwoawithdraw')];
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the response when sent idnumber not matching any cohort idnumber.
     *
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function test_local_wstwoawithdraw_nocohort(): void {
        global $DB;
        $userid = 2; // We know this should exist.
        // Make sure we have a number that does not match a cohort idnumber.
        $notacohortidnumber = 1027;
        while ($DB->get_record('cohort', ['idnumber' => $notacohortidnumber]) !== false) {
            $notacohortidnumber += 1000;
        }

        $result = external\local_wstwoawithdraw_removestudent::unenrolstudent($userid, $notacohortidnumber);
        $expected = ['success' => false, 'comment' => get_string('cohortnotfound', 'local_wstwoawithdraw')];
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the response when the student is not in the given cohort.
     *
     * @return void
     * @throws \coding_exception
     */
    public function test_local_wstwoawithdraw_notincohort(): void {
        $this->resetAfterTest(true);
        // Create a cohort.
        $cohort = $this->getDataGenerator()->create_cohort(['idnumber' => 1027]);
        // Create a user.
        $user = $this->getDataGenerator()->create_user();

        $result = external\local_wstwoawithdraw_removestudent::unenrolstudent($user->id, $cohort->idnumber);
        $expected = ['success' => false, 'comment' => get_string('notincohort', 'local_wstwoawithdraw')];
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the response when the student has not been graded and has not accessed.
     * @return void
     * @throws \coding_exception
     * @runInSeparateProcess
     */
    public function test_local_wstwoawithdraw_notgraded_notaccessed(): void {
        $this->resetAfterTest(true);
        // Create a cohort.
        $cohort = $this->getDataGenerator()->create_cohort(['idnumber' => 1027]);
        // Create a user.
        $user = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $user->id);
        // Create a course.
        $course = $this->getDataGenerator()->create_course();
        $assign = $this->getDataGenerator()->create_module('assign', ['course' => $course]);
        // Add the enrolment method.
        $cohortenrolment = enrol_get_plugin('cohort');
        $fields = [
            'name'              => 'testcohort1027',
            'status'            => ENROL_INSTANCE_ENABLED,
            'roleid'            => 5,
            'id'                => 0,
            'courseid'          => $course->id,
            'type'              => 'cohort',
        ];
        $cohortenrolment->add_instance($course, $fields);

        $result = external\local_wstwoawithdraw_removestudent::unenrolstudent($user->id, $cohort->idnumber);
        $this->assertEquals(true, $result['success']);
    }

    /**
     * Tests the response when the student has not been graded but has accessed.
     * @return void
     * @throws \coding_exception
     * @runInSeparateProcess
     */
    public function test_local_wstwoawithdraw_notgraded_hasaccessed(): void {
        global $DB;
        $this->resetAfterTest(true);
        // Create a cohort.
        $cohort = $this->getDataGenerator()->create_cohort(['idnumber' => 1027]);
        // Create a user.
        $user = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $user->id);
        // Create a course.
        $course = $this->getDataGenerator()->create_course();
        $assign = $this->getDataGenerator()->create_module('assign', ['course' => $course]);
        // Add the enrolment method.
        $cohortenrolment = enrol_get_plugin('cohort');
        $fields = [
            'name'              => 'testcohort1027',
            'status'            => ENROL_INSTANCE_ENABLED,
            'roleid'            => 5,
            'courseid'          => $course->id,
            'type'              => 'cohort',
        ];
        $cohortenrolment->add_instance($course, $fields);

        // The user has accessed the course.
        $record = [
            'userid' => $user->id,
            'courseid' => $course->id,
            'timeaccess' => time(),
        ];
        $DB->insert_record('user_lastaccess', $record);

        $result = external\local_wstwoawithdraw_removestudent::unenrolstudent($user->id, $cohort->idnumber);
        $this->assertEquals(true, $result['success']);
    }

    /**
     * Tests the response when the student has been graded.
     * @return void
     * @throws \coding_exception
     * @runInSeparateProcess
     */
    public function test_local_wstwoawithdraw_graded(): void {
        $categoryfullname = 'Assessed grade category';
        $categoryidnumber = 'WTMPR103';

        $this->resetAfterTest(true);
        // Create a cohort.
        $cohort = $this->getDataGenerator()->create_cohort(['idnumber' => 1027]);
        // Create a user.
        $user = $this->getDataGenerator()->create_user();
        cohort_add_member($cohort->id, $user->id);
        // Create a course.
        $course = $this->getDataGenerator()->create_course();
        $assign = $this->getDataGenerator()->create_module('assign', ['course' => $course]);
        // Add the enrolment method.
        $cohortenrolment = enrol_get_plugin('cohort');
        $fields = [
            'name'              => 'testcohort1027',
            'status'            => ENROL_INSTANCE_ENABLED,
            'roleid'            => 5,
            'id'                => 0,
            'courseid'          => $course->id,
            'type'              => 'cohort',
        ];
        $cohortenrolment->add_instance($course, $fields);

        // Grade the student.
        $gradecategory = $this->getDataGenerator()->create_grade_category([
            'courseid' => $course->id,
            'fullname' => $categoryfullname,
            'idnumber' => $categoryidnumber,
        ]);

        $gradeitem = $this->getDataGenerator()->create_grade_item([
            'courseid' => $course->id,
            'categoryid' => $gradecategory->id,
            'itemtype' => 'mod',
            'itemmodule' => 'assign',
            'itemname' => 'Assignment',
            'iteminstance' => $assign->id,
            'gradetype' => 1,
        ]);

        $params = new \stdClass();
        $params->itemid = $gradeitem->id;
        $params->userid = $user->id;
        $params->rawgrade = 88;
        $params->rawgrademax = 100;
        $params->rawgrademin = 0;

        new \grade_grade($params, false);

        $result = external\local_wstwoawithdraw_removestudent::unenrolstudent($user->id, $cohort->idnumber);
        $this->assertEquals(true, $result['success']);
    }
}
