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
 * Handles uploading files
 *
 * @package   report_ncccscensus
 * @author    Sean O'Hagan <sean.ohagan@remote-learner.net>
 * @copyright 2014 Remote Learner - http://www.remote-learner.net/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/report/ncccscensus/upload_image_form.php');

require_login();

$context = context_system::instance();
require_capability('moodle/site:config', $context);

$reportname = 'report_ncccscensus';

$struploadimage = get_string('uploadimage', $reportname);
$struploaderror = get_string('uploaderror', $reportname);

$PAGE->set_url('/admin/settings.php', array('section' => 'reportncccscensus'));
$PAGE->set_pagelayout('admin');
$PAGE->set_context($context);
$PAGE->set_title($struploadimage);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add($struploadimage);

$uploadform = new report_ncccscensusreport_upload_image_form();

if ($uploadform->is_cancelled()) {
    redirect($PAGE->url);
} else if ($data = $uploadform->get_data()) {
    // Ensure the directory for storing is created.
    $uploaddir = "report/ncccscensus/pix/$data->imagetype";
    $filename = $uploadform->get_new_filename('ncccscensusreportimage');
    make_upload_directory($uploaddir);
    $destination = $CFG->dataroot.'/'.$uploaddir.'/'.$filename;
    if (!$uploadform->save_file('ncccscensusreportimage', $destination, true)) {
        print_error($struploaderror);
    }

    redirect($PAGE->url, get_string('changessaved'));
}

echo $OUTPUT->header();
echo $uploadform->display();
echo $OUTPUT->footer();
