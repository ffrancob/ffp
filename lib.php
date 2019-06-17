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
 * This file contains functions used by the datum reports
 *
 * @package   report_datum
 * @copyright 2019 by FFP
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * This function extends the navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */
function report_datum_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/datum:view', $context)) {
        $url = new moodle_url('/report/datum/index.php', array('id'=>$course->id));
        $navigation->add(get_string('pluginname', 'report_datum'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}


/**
* Returns the total sum of dedication time of the user to the system
* This function returns a estimated dedication time which is configurable by appreciated times between registered logs
* This function has been developed & updated thereupon Blocks:Course dedication lib @author Aday Talavera
* The query is only for 2.7 & upper ver
*
* @param int $u userid
* @param optional int $c courseid to get access time in course otherwise returns access time in system
* @param optional int $limit seconds between logs access otherwise 7200 seconds
* @param optional int $f return in timestamp or human readable format
* @param optional int $iss time in seconds to ignore session, otherwise 59 seconds
* @return dedication time */

function report_datum_myusertime($u, $c = 0, $limit = 7200, $f = 1, $iss = 59) {
        global $DB;

        // Ignore sessions with a duration less than defined value in seconds
        $ignoress = $iss;


        $userid = $u;
        $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);

        $mintime="100000000"; // from time access dated on 1973-03-03
        $maxtime=  time();  // now server time

        if ($c>1) {
          $where = 'userid = :userid AND courseid = :courseid AND timecreated >= :mintime AND timecreated <= :maxtime';
        } else {
          $where = 'userid = :userid AND timecreated >= :mintime AND timecreated <= :maxtime';
        }

        $params = array(
            'userid' => $user->id,
            'courseid' => $c,
            'mintime' => $mintime,
            'maxtime' => $maxtime
        );
        // query for 2.7 & upper, the selected table is logstore_standard_log despite of classic log
        $logs = $DB->get_records_select('logstore_standard_log', $where, $params, 'timecreated ASC', 'id,timecreated,ip');
        $rows = array();
        if ($logs) {
            $previouslog = array_shift($logs);
            $previouslogtime = $previouslog->timecreated;

            $sessionstart = $previouslogtime;
            $ips = array($previouslog->ip => true);

            foreach ($logs as $log) {
                if (($log->timecreated - $previouslogtime) > $limit) {
                    $dedication = $previouslogtime - $sessionstart;
                    // Ignore sessions with a really short duration
                    if ($dedication > $ignoress) {
                        $rows[] = (object) array('start_date' => $sessionstart, 'dedicationtime' => $dedication);
                        $ips = array();
                    }
                    $sessionstart = $log->timecreated;
                }
                $previouslogtime = $log->timecreated;
                $ips[$log->ip] = true;
            }
            $dedication = $previouslogtime - $sessionstart;
            // Ignore sessions with a really short duration
            if ($dedication > $ignoress) {
                $rows[] = (object) array('start_date' => $sessionstart, 'dedicationtime' => $dedication);
            }
        }

        $total_dedication = 0;
        $timededication = 0;

          foreach ($rows as $index => $row) {
              $total_dedication += $row->dedicationtime;
          }

          $timededication = 0;

          $timededication =  report_datum_myformat_dedication($total_dedication);
          if ($f == 1)
          {
            return $timededication;
          } else {
            return $total_dedication;
          }


      }

/**
* Returns in human readable format time a specific number of seconds
*
* @param int $totalsecs seconds to get
* @return time {hours mins secs}  */
function report_datum_myformat_dedication($totalsecs) {

    $totalsecs = abs($totalsecs);

    $str = new stdClass();
    $str->hour = get_string('hour');
    $str->hours = get_string('hours');
    $str->min = get_string('min');
    $str->mins = get_string('mins');
    $str->sec = get_string('sec');
    $str->secs = get_string('secs');

    $hours = floor($totalsecs / HOURSECS);
    $remainder = $totalsecs - ($hours * HOURSECS);
    $mins = floor($remainder / MINSECS);
    $secs = $remainder - ($mins * MINSECS);

    $ss = ($secs == 1) ? $str->sec : $str->secs;
    $sm = ($mins == 1) ? $str->min : $str->mins;
    $sh = ($hours == 1) ? $str->hour : $str->hours;

    $ohours = '';
    $omins = '';
    $osecs = '';

    if ($hours)
        $ohours = $hours . ' ' . $sh;
    if ($mins)
        $omins = $mins . ' ' . $sm;
    if ($secs)
        $osecs = $secs . ' ' . $ss;

    if ($hours)
        return trim($ohours . ' ' . $omins);
    if ($mins)
        return trim($omins . ' ' . $osecs);
    if ($secs)
        return $osecs;
    return '-';
}

/**
 * Get if the user has posted in any forum in a course
 *
 * @param array $myforum course forums
 * @param int $u userid
 * @return html to be display if post in case
 */

function report_datum_ismypost($myforum, $u) {
    unset($ispost);

    foreach ($myforum as $myf) {
        $ispost = forum_get_user_posts($myf->instance, $u);
    }
    if ($ispost) {
        return '<span class="label label-artic">Post</span>';
    } else {
        return '<span class="label label-warning">Not</span>';
    }

}