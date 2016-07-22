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

$PAGE->set_url('/report/ncccscensus/bulk.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('setupquery', 'report_ncccscensus'));
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('admin');
$PAGE->navbar->add(get_string('reportlink', 'report_ncccscensus'));
$jsmodule = array(
    'name' => 'moodle-report_ncccsautocomplete-ncccscensus',
    'fullpath' => '/report/ncccscensus/js/ncccsautocomplete.js',
    'requires' => array('autocomplete', 'autocomplete-filters', 'autocomplete-highlighters')
);
$PAGE->requires->js_init_call('init_ncccsautocomplete', null, false, $jsmodule);

$actions = optional_param('actions', '', PARAM_TEXT);
$mform = new report_ncccscensus_setup_bulk_form($PAGE->url, $actions);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/admin/'));
    die();
}

$statusurl = new moodle_url('/report/ncccscensus/status.php');
echo $OUTPUT->header();

if ($formdata = $mform->get_data()) {
    $result = report_ncccscensus_generate_bulk_report($formdata);
    if ($result === false) {
        echo $OUTPUT->box_start();
        $mform->display();
        echo $OUTPUT->box_end();
    } else {
        echo $OUTPUT->box_start();
        echo '<h1>'.get_string('reportqueued', 'report_ncccscensus').'</h1><br>';
        echo '<div style="text-align: center; width: 100%;">';
        echo '<a href="'.$statusurl.'">'.get_string('viewreportstatus', 'report_ncccscensus').'</a>';
        echo '<br></div>';
        echo $OUTPUT->box_end();
    }
} else {
    $mform->display();
}

$reports = $DB->count_records('report_ncccscensus_batch');
echo $OUTPUT->box_start();
echo "<ul>";
if ($reports) {
    echo "<li><a href=\"$statusurl\">".get_string('pastreports', 'report_ncccscensus')."</a></li>";
}
$starturl = new moodle_url('start.php', array('c' => '1'));
echo "<li><a href=\"$starturl\">".get_string('startover', 'report_ncccscensus')."</a></li></ul>";
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
