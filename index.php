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
 * Handles the setup and display of the report.
 *
 * @package   report_ncccscensus
 * @author    Sean O'Hagan <sean.ohagan@remote-learner.net>
 * @copyright 2014 Remote Learner - http://www.remote-learner.net/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');

$cid = required_param('id', PARAM_INT); // Course ID.

if ($cid == SITEID || !$course = $DB->get_record('course', array('id' => $cid))) {
    print_error('cannotfindcourse');
}

require_login($course);

$context = context_course::instance($cid);
require_capability('report/ncccscensus:view', $context);

$PAGE->set_url('/report/ncccscensus/index.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('setupquery', 'report_ncccscensus'));
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('report');
$PAGE->navbar->add(get_string('reportlink', 'report_ncccscensus'));

$mform = new ncccscensus_setup_query_form($PAGE->url, $cid);

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot.'/course/view.php?id='.$cid);
    die();
}

$action = optional_param('action', $mform::ACTION_VIEW, PARAM_INT);

if ($action == $mform::ACTION_VIEW) {
    echo $OUTPUT->header();
    echo $OUTPUT->box_start();
}

if ($mform->get_data()) {

    if (ncccscensus_generate_report($mform, $action) === false) {
        if ($action !== $mform::ACTION_VIEW) {
            echo $OUTPUT->header();
            echo $OUTPUT->box_start();
            $mform->display();
        }

        echo $OUTPUT->notification(get_string('nodatafound', 'report_ncccscensus'), 'notifysuccess');

        if ($action !== $mform::ACTION_VIEW) {
            echo $OUTPUT->box_end();
            echo $OUTPUT->footer();
        }
    }

} else {
    $mform->display();
}

if ($action == $mform::ACTION_VIEW) {
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
}
