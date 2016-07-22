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
 * Cancel generation of bulk report.
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
$PAGE->set_title(get_string('setupquery', 'report_ncccscensus'));
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('report');
$PAGE->navbar->add(get_string('reportlink', 'report_ncccscensus'));

$mode = optional_param('mode', '', PARAM_TEXT);

echo $OUTPUT->header();
echo $OUTPUT->box_start();
if ($mode == 'all') {
    echo '<h1>'.get_string('allreportsdeleted', 'report_ncccscensus').'</h1>';
    report_ncccscensus_bulk_report_delete_all();
} else {
    echo '<h1>'.get_string('reportdeleted', 'report_ncccscensus').'</h1>';
    $batchid = required_param('batchid', PARAM_INT);
    report_ncccscensus_bulk_report_cancel($batchid);
}

echo '<div style="text-align: center">';
$statusurl = new moodle_url('/report/ncccscensus/status.php');
echo '<a href="'.$statusurl.'">'.get_string('continue').'</a></div>';
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
