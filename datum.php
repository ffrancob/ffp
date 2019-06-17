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
// MERCHANTABILITY or FITNESS FOR A PdatumULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Displays different views of the datums.
 *
 * @package    report_datum
 * @copyright  2019 by FFP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/report/datum/lib.php');

require_login();

$id = required_param('id', PARAM_INT); // Course ID.

$PAGE->set_url('/report/datum/datum.php', array('id' => $id));
$PAGE->set_pagelayout('report');

if (!$course = $DB-> get_record('course', array('id' => $id))) {
    print_error('invalidcourse');
}

$context = context_course::instance($course->id);

require_capability('report/datum:view', $context);

$stradministration = get_string('administration');
$strreports = get_string('reports');

$PAGE->set_title(get_string('pluginname', 'report_datum'));
$PAGE->set_heading(userdate(time()));

echo $OUTPUT->header();


// output

$mycourses = get_courses();

echo $OUTPUT->heading('<i class="fa fa-clock-o" aria-hidden="true"></i> '.userdate(time()).' '
.get_string('pluginname', 'report_datum'));

$mycourses = $DB -> get_records_sql('SELECT * FROM {course} WHERE id > 1 ORDER BY category DESC');
$mycourses = get_courses();


$students = get_enrolled_users($context, $withcapability = '', $groupid = 0, $userfields = 'u.*',
$orderby = 'id', $limitfrom = 0, $limitnum = 5000);

$myforum = get_coursemodules_in_course('forum', $id);

$table = new html_table();
$table->head = array('id', 'firstname', 'lastname', 'time', 'activitie');
foreach ($students as $mystudent) {
	$connection = report_datum_myusertime($mystudent->id, $id, $limit = 7200, $f = 1, $iss = 59);
	$ispost = report_datum_ismypost($myforum, $mystudent->id);

	$table->data[] = array( '<i class="fa fa-cog fa-spin fa-2x fa-fw"></i> '.$mystudent->id,
	$mystudent->firstname,
	$mystudent->lastname,
	$connection,
	$ispost);
}

echo html_writer::start_tag('div', array('class' => 'myclass'));
echo html_writer::table($table);
echo html_writer::end_tag('div');

echo $OUTPUT->continue_button(new moodle_url('/report/datum/index.php', array('id' => 0)));


// output

echo $OUTPUT->footer();
