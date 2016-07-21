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

$c = optional_param('c', '', PARAM_RAW);
if (!empty($c)) {
    if (!get_config('report_ncccscensus', 'showhelpsplash')) {
        redirect(new moodle_url('/report/ncccscensus/bulk.php'));
    }
}

$PAGE->set_url('/report/ncccscensus/start.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('setupquery', 'report_ncccscensus'));
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('admin');
$PAGE->navbar->add(get_string('reportlink', 'report_ncccscensus'));

echo $OUTPUT->header();

echo "<h1>".get_string('howtoselect', 'report_ncccscensus')."</h1>";
echo "<p>".get_string('howtoselectinstructions', 'report_ncccscensus')."</p>";

echo $OUTPUT->box_start();
$bulkurl = new moodle_url('/report/ncccscensus/bulk.php', array('actions' => 'categories'));
echo "<a href=\"$bulkurl\">".get_string('selectcategories', 'report_ncccscensus')."</a>";
echo " - ".get_string('selectcategoriesdesc', 'report_ncccscensus');
echo $OUTPUT->box_end();

echo $OUTPUT->box_start();
$bulkurl = new moodle_url('/report/ncccscensus/bulk.php', array('actions' => 'categories,courses'));
echo "<a href=\"$bulkurl\">".get_string('selectcategoriescourses', 'report_ncccscensus')."</a>";
echo " - ".get_string('selectcategoriescoursesdesc', 'report_ncccscensus');
echo $OUTPUT->box_end();

echo $OUTPUT->box_start();
$bulkurl = new moodle_url('/report/ncccscensus/bulk.php', array('actions' => 'courses'));
echo "<a href=\"$bulkurl\">".get_string('selectcourses', 'report_ncccscensus')."</a>";
echo " - ".get_string('selectcoursesdesc', 'report_ncccscensus');
echo $OUTPUT->box_end();

echo $OUTPUT->box_start();
$bulkurl = new moodle_url('/report/ncccscensus/bulk.php', array('actions' => 'teachers'));
echo "<a href=\"$bulkurl\">".get_string('selectteachers', 'report_ncccscensus')."</a>";
echo " - ".get_string('selectteachersdesc', 'report_ncccscensus');
echo $OUTPUT->box_end();

echo $OUTPUT->box_start();
$bulkurl = new moodle_url('/report/ncccscensus/bulk.php', array('actions' => 'teachers,courses'));
echo "<a href=\"$bulkurl\">".get_string('selectteacherscourses', 'report_ncccscensus')."</a>";
echo " - ".get_string('selectteacherscoursesdesc', 'report_ncccscensus');
echo $OUTPUT->box_end();

echo $OUTPUT->box_start();
$bulkurl = new moodle_url('/report/ncccscensus/bulk.php', array('actions' => 'categories,courses,teachers'));
echo "<a href=\"$bulkurl\">".get_string('selectallauto', 'report_ncccscensus');
echo "</a> - ".get_string('selectallautodesc', 'report_ncccscensus');
echo $OUTPUT->box_end();

$reports = $DB->count_records('report_ncccscensus_batch');
if ($reports) {
    echo $OUTPUT->box_start();
    echo "<ul>";
    $statusurl = new moodle_url('/report/ncccscensus/status.php');
    echo "<li><a href=\"$statusurl\">".get_string('pastreports', 'report_ncccscensus')."</a></li>";
    echo $OUTPUT->box_end();
}

echo $OUTPUT->footer();
