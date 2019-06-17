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
// MERCHANTABILITY or FITNESS FOR A PstareportULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * datum report
 *
 * @package    report
 * @subpackage datum
 * @copyright  2019 by FFP
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/report/datum/lib.php');

require_login();

$id = optional_param('id', 0, PARAM_INT);// Course ID.
$url = new moodle_url("/report/datum/index.php", $params);

$PAGE->set_url('/report/datum/index.php', array('id' => $id));
$PAGE->set_pagelayout('report');

$context = context_system::instance();
require_capability('report/datum:view', $context);

$stradministration = get_string('administration');
$strreports = get_string('reports');

$PAGE->set_title(get_string('pluginname', 'report_datum'));
$PAGE->set_heading(userdate(time()));

echo $OUTPUT->header();

// output

echo $OUTPUT->heading('<i class="fa fa-clock-o" aria-hidden="true"></i> '.userdate(time()).' '
    .get_string('pluginname', 'report_datum'));

$mycourses = get_courses();

$table = new html_table();
$table->head = array(get_string('course', 'report_datum'), get_string('shortname', 'report_datum'), get_string('visible', 'report_datum'));
foreach ($mycourses as $mycourse) {
    if ($mycourse->id > 1) { // courseid = 0 is System
        $myurl = html_writer::link(
                new moodle_url('/report/datum/datum.php', ['id' => $mycourse->id]),
               $mycourse->fullname);
        $table->data[] = array( '<i class="fa fa-cog fa-spin fa-2x fa-fw"></i> '.$myurl, $mycourse->shortname, $mycourse->visible);

    }

}

echo html_writer::start_tag('div', array('class' => 'myclass'));
echo html_writer::table($table);
echo html_writer::end_tag('div');

\core\notification::info(get_string('selectcourse', 'report_datum'));


// output

echo $OUTPUT->footer();
