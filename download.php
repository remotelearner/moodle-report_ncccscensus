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

$batchid = required_param('batchid', PARAM_INT);

$record = $DB->get_record('report_ncccscensus_batch', array('id' => $batchid));
if ($record->status !== 0) {
    $fs = get_file_storage();
    // Check to see if file exists.
    $contextid = $context->id;
    $file = $fs->get_file($contextid, 'report_ncccscensus', 'archive', $batchid, '/report_ncccscensus/', $record->zipfile);
    if ($file) {
        $info = pathinfo($record->zipfile);
        send_stored_file($file, 86400, 0, true);
    } else {
        echo 'Error, zip file is missing';
    }
} else {
    echo 'Error, report is still processing';
}
