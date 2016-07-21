<?php
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
 * Handles the setup and display of the bulk report.
 *
 * @package   report_ncccscensus
 * @author    Remote-Learner.net Inc
 * @copyright 2014 Remote Learner - http://www.remote-learner.net/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');

require_login();
$context = context_system::instance();
require_capability('report/ncccscensus:view', $context);

$PAGE->set_url('/report/ncccscensus/status.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('reportstatus', 'report_ncccscensus'));
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('admin');
$PAGE->navbar->add(get_string('reportlink', 'report_ncccscensus'));

echo $OUTPUT->header();
echo $OUTPUT->box_start();
echo '<h1>'.get_string('reportstatus', 'report_ncccscensus').'</h1>';
$reports = report_ncccscensus_bulk_report_status_all();
if (is_array($reports) && count($reports) > 0) {
    echo '<center><table class="admintable generaltable">';
    echo '<tr><th class="header c0 leftalign" style="" scope="col"><b>';
    echo get_string('starttime', 'report_ncccscensus').'</b></th>';
    echo '<th class="header c0 leftalign" style="" scope="col"><b>'.get_string('totalcourses', 'report_ncccscensus').'</b></th>';
    echo '<th class="header c0 leftalign" style="" scope="col"><b>'.get_string('status').'</b></th>';
    echo '<th class="header c0 leftalign" style="" scope="col"></th>';
    echo '<th class="header c0 leftalign" style="" scope="col"></th>';
    echo '</tr>';
    foreach ($reports as $key => $value) {
        echo '<tr><td>'.$value->starttime.'</td>';
        if ($value->totalwaiting > 0) {
            echo '<td>'.$value->totalcourses.'</td>';
        } else {
            echo '<td>'.$value->totalcomplete.'</td>';
        }
        if ($value->totalwaiting > 0) {
            echo '<td><span style="color: red">'.get_string('processing', 'report_ncccscensus').'</span></td>';
        } else {
            echo '<td><span style="color: green">'.get_string('completed', 'report_ncccscensus').'</span></td>';
        }
        if ($value->totalwaiting > 0) {
            echo '<td></td>';
        } else {
            if (!empty($value->zipfile)) {
                $url = new moodle_url('/report/ncccscensus/download.php', array('batchid' => $key));
                echo '<td><a href="'.$url.'">'.get_string('download').'</a></td>';
            } else {
                echo '<td>'.get_string('nocourses', 'report_ncccscensus').'</td>';
            }
        }
        $url = new moodle_url('/report/ncccscensus/cancel.php', array('batchid' => $key));
        if ($value->totalwaiting > 0) {
            echo '<td><a href="'.$url.'"';
            echo " onclick=\"return confirm('".get_string('confirmcancel', 'report_ncccscensus');
            echo '\')">'.get_string('cancel').'</a></td></tr>';
        } else {
            echo '<td><a href="'.$url.'"';
            echo " onclick=\"return confirm('".get_string('confirmdelete', 'report_ncccscensus');
            echo '\')">'.get_string('delete').'</a></td></tr>';
        }
    }
    echo '</table>';
    $url = new moodle_url('/report/ncccscensus/cancel.php', array('batchid' => $key, 'mode' => 'all'));
    echo '<a href="'.$url.'"';
    echo " onclick=\"return confirm('".get_string('confirmdeleteall', 'report_ncccscensus');
    echo '\')">'.get_string('deleteall', 'report_ncccscensus').'</a>';
    echo '</center>';
} else {
    echo '<div style="text-align: center; width: 100%;"><h3>'.get_string('noreports', 'report_ncccscensus').'</h3>';
    $url = new moodle_url('/report/ncccscensus/start.php', array('c' => 1));
    echo '<p><a href="'.$url.'">'.get_string('continue').'</a></p></div>';
}
echo $OUTPUT->box_end();

$reports = $DB->count_records('report_ncccscensus_batch');
echo $OUTPUT->box_start();
echo "<ul>";
$url = new moodle_url('/report/ncccscensus/status.php');
if ($reports) {
    echo "<li><a href=\"$url\">".get_string('pastreports', 'report_ncccscensus')."</a></li>";
}
$url = new moodle_url('/report/ncccscensus/start.php', array('c' => 1));
echo "<li><a href=\"$url\">".get_string('startover', 'report_ncccscensus')."</a></li></ul>";
echo $OUTPUT->box_end();

echo $OUTPUT->footer();
