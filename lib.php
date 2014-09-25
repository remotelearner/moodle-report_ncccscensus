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
 * Library of report functions.
 *
 * @package   report_ncccscensus
 * @author    Sean O'Hagan <sean.ohagan@remote-learner.net>
 * @copyright 2014 Remote Learner - http://www.remote-learner.net/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir.'/formslib.php');

/**
* ACTION_VIEW - represents viewing the HTML version of the report
*/
define('ACTION_VIEW', 1);

/**
* ACTION_PDF - represents downloading the report in PDF format
*/
define('ACTION_PDF', 2);

/**
* ACTION_CSV - represents downloading the report in CSV format
*/
define('ACTION_CSV', 3);

/**
* EXCLUDE_GROUP_MEMBERS - flag to determine whether group member should be excluded from report
*/
define('EXCLUDE_GROUP_MEMBERS', 0);

/**
 * Class to define the report search form
 *
 * @see moodleform
 */
class ncccscensus_setup_query_form extends moodleform {

    /**
     * __construct
     *
     * @param mixed $actionurl the action URL of the form
     * @param mixed $cid the course ID
     */
    public function __construct($actionurl, $cid) {
        $this->cid = $cid;
        parent::__construct($actionurl);
    }

    /**
     * Method that defines all of the elements of the form.
     *
     */
    public function definition() {
        global $DB, $USER;

        $course = $DB->get_record('course', array('id' => $this->cid));

        $mform =& $this->_form;
        $mform->addElement('header', 'header', get_string('querytitle', 'report_ncccscensus'));
        $mform->addElement('hidden', 'id', $this->cid);
        $mform->setType('id', PARAM_INT);

        // Add the course information.
        $mform->addElement('hidden', 'course', $this->cid);
        $mform->setType('course', PARAM_INT);
        $mform->addElement('static', 'course_label', get_string('course', 'report_ncccscensus'), $course->fullname);

        // Determine which groups to display, if any, based on the value of $userid.
        $context = context_course::instance($course->id);
        if (has_capability('moodle/site:accessallgroups', $context)) {
            $userid = 0;
        } else if (has_capability('moodle/course:managegroups', $context)) {
            $userid = $USER->id;
        } else {
            $userid = false;
        }

        if ($userid !== false && ($grouprecs = groups_get_all_groups($course->id, $userid, 0, 'g.id, g.name'))) {
            $groups = array();

            // Build the groups array.
            foreach ($grouprecs as $grouprec) {
                $groups[$grouprec->id] = $grouprec->name;
            }
            if (has_capability('moodle/site:accessallgroups', $context)) { // Could have checked for $user==0 but this is safer.
                // Add the "All groups" option.
                $groups = array('0' => get_string('allgroups', 'report_ncccscensus')) + $groups;
            }
        } else {
            // Create an N/A option for the groups dropdown.
            $groups = array(get_string('na', 'report_ncccscensus'));

            // Add a hidden element to flag that the dropdown should be disabled.
            $mform->addElement('hidden', 'disablegroups', true);
            $mform->setType('disablegroups', PARAM_BOOL);
        }

        // Add the groups dropdown.
        $mform->addElement('select', 'group', get_string('groupselector', 'report_ncccscensus'), $groups);

        // Disable the groups dropdown if the hidden element's value is 1.
        $mform->disabledIf('group', 'disablegroups', 'eq', 1);

        $mform->addElement('date_selector', 'startdate', get_string('from'));
        $mform->addElement('date_selector', 'enddate', get_string('to'));

        $mform->addElement('html', '<br>');

        $bview  =& $mform->createElement('radio', 'action', '', get_string('viewreport', 'report_ncccscensus'), ACTION_VIEW);
        $bdlpdf =& $mform->createElement('radio', 'action', '', get_string('downloadreportpdf', 'report_ncccscensus'), ACTION_PDF);
        $bdlcsv =& $mform->createElement('radio', 'action', '', get_string('downloadreportcsv', 'report_ncccscensus'), ACTION_CSV);

        $actions = array($bview, $bdlpdf, $bdlcsv);
        $mform->addGroup($actions, 'action', get_string('action', 'report_ncccscensus'), array(' '), false);
        $mform->setDefault('action', ACTION_VIEW);

        $mform->addElement('html', '<br>');

        $bsubmit =& $mform->createElement('submit', 'submitbutton', get_string('getreport', 'report_ncccscensus'));
        $breset  =& $mform->createElement('reset', 'resetbutton', get_string('revert'));
        $bcancel =& $mform->createElement('cancel');

        $submits = array($bsubmit, $breset, $bcancel);
        $mform->addGroup($submits, 'submits', '&nbsp;', array(' '), false);
    }
}

/**
 * Class to define the bulk report search form
 *
 * @see moodleform
 */
class ncccscensus_setup_bulk_form extends moodleform {
    /** @var string Comma seperate list of sections to show categories,courses,teachers */
    private $actions = null;
    /**
     * Constructor to setup form.
     *
     * @param $pageurl Page url for form.
     * @param $actions Actions to show on form.
     */
    public function __construct($pageurl, $actions) {
        $this->actions = $actions;
        parent::__construct($pageurl);
    }

    /**
     * Method that defines all of the elements of the form.
     *
     */
    public function definition() {
        $mform =& $this->_form;
        $mform->addElement('header', 'header', get_string('querytitle', 'report_ncccscensus'));
        $mform->addElement('hidden', 'categories', '');
        $mform->setType('categories', PARAM_RAW);
        $mform->addElement('hidden', 'courses', '');
        $mform->setType('courses', PARAM_RAW);
        $mform->addElement('hidden', 'teachers', '');
        $mform->setType('teachers', PARAM_RAW);

        $actions = preg_split('/,/', $this->actions);
        if (empty($actions[0])) {
            $actions = array('categories', 'courses', 'teachers');
        }
        foreach ($actions as $action) {
            switch ($action) {
                case "categories":
                    $mform->addElement('text', 'categoryids', 'Categories', array('size' => '40'));
                    $mform->setType('categoryids', PARAM_RAW);
                    $mform->addElement('html', '<div id="categories_list" class="box generalbox" style="display: none"></div>');
                break;
                case "courses":
                    $mform->addElement('text', 'coursesids', 'Courses', array('size' => '40'));
                    $mform->setType('coursesids', PARAM_RAW);
                    $mform->addElement('html', '<div id="courses_list" class="box generalbox" style="display: none"></div>');
                break;
                case "teachers":
                    $mform->addElement('text', 'teacherids', 'Teachers', array('size' => '40'));
                    $mform->setType('teacherids', PARAM_RAW);
                    $mform->addElement('html', '<div id="teachers_list" class="box generalbox" style="display: none"></div>');
                break;
            }
        }
        $mform->addElement('date_selector', 'startdate', get_string('from'));
        $mform->addElement('date_selector', 'enddate', get_string('to'));

        $mform->addElement('html', '<br>');

        $bsubmit =& $mform->createElement('submit', 'submitbutton', get_string('generatereport', 'report_ncccscensus'));
        $breset  =& $mform->createElement('reset', 'resetbutton', get_string('revert'));
        $bcancel =& $mform->createElement('cancel');

        $submits = array($bsubmit, $breset, $bcancel);
        $mform->addGroup($submits, 'submits', '&nbsp;', array(' '), false);
    }
}

/**
 * Cron job to generate the bulk report pdf's and zip.
 *
 * @return void
 * @uses $CFG, $DB
 */
function report_ncccscensus_cron() {
    global $DB;
    $proccess = $DB->get_records('ncccscensus_batch', array('status' => 0));
    foreach ($proccess as $batch) {
        report_ncccscensus_process_batch($batch->id);
    }
}

/**
 * Generates the bulk report pdf's and zip.
 *
 * @return void
 * @uses $CFG, $DB
 */
function report_ncccscensus_process_batch($batch) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/moodlelib.php');
    // Retrieve first 50 reports to generate at a time and prevent cron job from lasting longer than 5 minutes.
    $proccess = $DB->get_records('ncccscensus_reports', array('batchid' => $batch, 'status' => 0), '', '*', 0, 50);
    $files = array();
    $tempdirused = false;
    $dir = make_temp_directory("ncccscensus$batch", false);
    foreach ($proccess as $report) {
        // Create form data.
        $formdata = new stdClass;
        $formdata->id = $report->course;
        $formdata->group = 0;
        $formdata->startdate = $report->reportstartdate;
        $formdata->enddate = $report->reportenddate;
        // Save report pdf.
        $date = usergetdate(time(), get_user_timezone());
        $filename = date('MdY_Hi', mktime($date['hours'], $date['minutes'], 0, $date['mon'], $date['mday'], $date['year']));
        $filename = $filename.'-'.$report->course.'.pdf';
        // Get temporary file location.
        $fullfilename = $dir.'/'.$filename;
        if (true === ncccscensus_generate_report($formdata, ACTION_PDF, $fullfilename)) {
            $files[$filename] = $fullfilename;
            $report->filename = $filename;
            $report->fullfilename = $fullfilename;
            // Indicate that there was a pdf generated, if not remove as remove in zip will not be called.
            $tempdirused = true;
            $report->status = 1;
        } else {
            $report->status = 2;
        }
        // Mark report generation as complete.
        $DB->update_record('ncccscensus_reports', $report);
    }
    if (!$tempdirused) {
        rmdir($dir);
    }
    ncccscensus_generate_bulk_zip($batch);
}

/**
 * Generates list of zip files in array.
 *
 * @param int $batch id of batch
 * @return array|bool False on no files to add to zip, array on success
 * @uses $CFG, $DB
 */
function ncccscensus_get_zip_files($batch) {
    global $DB;
    $files = array();
    $filerecords = $DB->get_records('ncccscensus_reports', array('batchid' => $batch, 'status' => 1));
    foreach ($filerecords as $file) {
        if (!empty($file->filename)) {
            $files[$file->filename] = $file->fullfilename;
        }
    }
    if (count($files) > 0) {
        return $files;
    }
    return false;
}

/**
 * Generates list of zip files in array.
 *
 * @param int $batch id of batch
 * @return array|bool False on no files to add to zip, array on success
 * @uses $CFG, $DB
 */
function ncccscensus_generate_bulk_zip($batch) {
    global $DB, $USER;
    // Check if last report.
    $left = $DB->count_records('ncccscensus_reports', array('batchid' => $batch, 'status' => 0));
    if ($left !== 0) {
        return false;
    }
    $files = ncccscensus_get_zip_files($batch);
    if (!is_array($files)) {
         return false;
    }

    $date = usergetdate(time(), get_user_timezone());
    $filename = date('MdY_Hi', mktime($date['hours'], $date['minutes'], 0, $date['mon'], $date['mday'], $date['year']));
    $filename = $filename.'-'.$batch.'.zip';
    $fs = get_file_storage();

    // Check to see if file exists.
    $contextid = context_system::instance()->id;
    $file = $fs->get_file($contextid, 'report_ncccscensus', 'archive', $batch, '/report_ncccscensus/', $filename);
    if (!$file) {
        // Prepare file record object.
        $fileinfo = array(
            'contextid' => context_system::instance()->id,
            'component' => 'report_ncccscensus',
            'filearea' => 'archive',
            'itemid' => $batch,
            'filepath' => '/report_ncccscensus/',
            'filename' => $filename);
        $file = $fs->create_file_from_string($fileinfo, '');
    }

    $parentpath = $file->get_parent_directory()->get_filepath();
    $filepath = explode('/', trim($file->get_filepath(), '/'));
    $filepath = array_pop($filepath);

    // Generate zip.
    $zipper = get_file_packer('application/zip');

    $record = $DB->get_record('ncccscensus_batch', array('id' => $batch));
    $contextid = context_system::instance()->id;
    $path = 'report_ncccscensus';
    $newfile = $zipper->archive_to_storage($files, $contextid, $path, 'archive', $batch, $parentpath, $filename, $USER->id);
    if ($newfile) {
        // Mark batch as complete.
        $record->zipfile = $filename;
    }
    $record->status = 1;
    $DB->update_record('ncccscensus_batch', $record);
    $info = array();
    // Delete pdf files.
    foreach ($files as $filename => $fullfilename) {
        @unlink($fullfilename);
        $info = pathinfo($fullfilename);
    }
    if (!empty($info['dirname'])) {
        @rmdir($info['dirname']);
    }
}

/**
 * Performs the bulk report function.
 *
 * @param array $formdata the form data
 * @return bool False on failure
 * @uses $DB
 */
function ncccscensus_generate_bulk_report($formdata) {
    global $DB;
    $courses = ncccscensus_get_courses($formdata);
    if (!(is_array($courses) && count($courses) > 0)) {
        return false;
    }
    // Generate random batch id.
    $report = new stdClass;
    $report->starttime = usertime(time(), get_user_timezone());
    $batchid = $DB->insert_record('ncccscensus_batch', $report);
    foreach ($courses as $course) {
        $report = new stdClass;
        $report->batchid = $batchid;
        $report->course = $course;
        $report->starttime = usertime(time(), get_user_timezone());
        $report->reportstartdate = $formdata->startdate;
        $report->reportenddate = $formdata->enddate;
        $DB->insert_record('ncccscensus_reports', $report);
    }
    return $batchid;
}


/**
 * Delete all reports.
 *
 * @return void
 * @uses $DB
 */
function ncccscensus_bulk_report_delete_all() {
    global $DB;
    $batches = $DB->get_records('ncccscensus_batch', null);
    foreach ($batches as $batch) {
        ncccscensus_bulk_report_cancel($batch->id);
    }
}

/**
 * Cancel bulk report generation or delete if complete.
 *
 * @param string $batchid The batchid for the bulk report
 * @return void
 * @uses $DB
 */
function ncccscensus_bulk_report_cancel($batchid) {
    global $DB;
    $files = ncccscensus_get_zip_files($batchid);
    $batch = $DB->get_record('ncccscensus_batch', array('id' => $batchid));
    $reports = $DB->get_records('ncccscensus_reports', array('batchid' => $batchid));
    $DB->delete_records('ncccscensus_batch', array('id' => $batchid));
    $DB->delete_records('ncccscensus_reports', array('batchid' => $batchid));
    if (!empty($batch->zipfile)) {
        $fs = get_file_storage();
        // Check to see if file exists.
        $path = 'report_ncccscensus';
        $contextid = context_system::instance()->id;
        $file = $fs->get_file($contextid, $path, 'archive', $batch->id, '/report_ncccscensus/', $batch->zipfile);
        // Delete it if it exists.
        if ($file) {
            $file->delete();
        }
    } else {
        if ($files == false) {
            return;
        }
        $info = array();
        // Delete temporary pdf files.
        foreach ($files as $filename => $fullfilename) {
            if (file_exists($fullfilename)) {
                @unlink($fullfilename);
            }
            $info = pathinfo($fullfilename);
        }
        if (!empty($info['dirname']) && file_exists($info['dirname'])) {
            @rmdir($info['dirname']);
        }
    }
}

/**
 * Retrieves a single bulk report status.
 *
 * @param string $batchid The batchid for the bulk report
 * @return bool|array False on failure, array with bulk report status
 * @uses $DB
 */
function ncccscensus_bulk_report_status($batchid) {
    global $DB;
    $data = array();
    $data['totalcourses'] = $DB->count_records('ncccscensus_reports', array('batchid' => $batchid));
    if (empty($data['totalcourses']) || $data['totalcourses'] === 0) {
        return false;
    }
    $data['totalcomplete'] = $DB->count_records('ncccscensus_reports', array('batchid' => $batchid, 'status' => 1));
    $data['totalwaiting'] = $data['totalcourses'] - $data['totalcomplete'];
    $record = $DB->get_record('ncccscensus_reports', array('batchid' => $batchid), 'starttime');
    $data['starttime'] = userdate($record->starttime);
    return $data;
}


/**
 * Retrieves a all bulk report status.
 *
 * @param string $batchid The batchid for the bulk report
 * @return array with bulk report status
 * @uses $DB
 */
function ncccscensus_bulk_report_status_all() {
    global $DB;
    $query = 'SELECT batchid, nb.zipfile, COUNT(*) totalcourses, SUM(nr.status = 0) totalwaiting,';
    $query .= ' SUM(nr.status = 1) totalcomplete, nr.starttime';
    $query .= ' FROM {ncccscensus_reports} nr, {ncccscensus_batch} nb WHERE nb.id = nr.batchid';
    $query .= ' GROUP BY nr.batchid ORDER BY nr.starttime DESC LIMIT 200';
    $all = $DB->get_records_sql($query);
    foreach ($all as $key => $value) {
        $all[$key]->starttime = userdate($all[$key]->starttime);
    }
    return $all;
}

/**
 * Locates courses in categories.
 *
 * @param array $categories List of ids of categories.
 * @return bool|array False on failure, Array of courses on success
 * @uses $DB
 */
function ncccscensus_get_category_courses($categories, $limittocourses = false) {
    global $DB;
    if (count($categories) == 0) {
        return false;
    }
    $categorycourses = Array();
    $tempcategory = $DB->get_in_or_equal($categories);
    $limittosql = "";
    if (is_array($limittocourses) && count($limittocourses) > 0) {
        $limittoin = $DB->get_in_or_equal($limittocourses);
        $limittosql = " AND id $limittoin[0] ";
        foreach ($limittoin[1] as $temp) {
            array_push($tempcategory[1], $temp);
        }
    }
    $tempcourses = $DB->get_records_sql("SELECT id FROM {course} WHERE category $tempcategory[0] $limittosql ", $tempcategory[1]);
    foreach ($tempcourses as $course) {
        $categorycourses[] = $course->id;
    }
    if (count($categorycourses) == 0) {
        return false;
    }
    return $categorycourses;
}

/**
 * Locates courses to report on.
 *
 * @param array $formdata the form data
 * @return bool|array False on failure, Array of courses on success
 * @uses $DB
 */
function ncccscensus_get_courses($formdata) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/accesslib.php');

    if (empty($formdata->courses) && empty($formdata->teachers) && empty($formdata->categories)) {
         // No data to select courses.
         return false;
    }

    if (empty($formdata->courses) && empty($formdata->teachers) && !empty($formdata->categories)) {
        // Show all courses in the categories selected.
        $categories = preg_split('/,/', $formdata->categories);
        return ncccscensus_get_category_courses($categories);
    }

    if (!empty($formdata->courses) && empty($formdata->teachers)) {
        // Only courses selected.
        $courses = preg_split('/,/', $formdata->courses);
        if (count($courses) > 0) {
            return $courses;
        }
        return false;
    }

    $teachercourses = Array();
    if (!empty($formdata->teachers)) {
        // Load courses by teacher.
        $tempin = preg_split('/,/', $formdata->teachers);
        if (count($tempin) > 0) {
            $teachers = $DB->get_in_or_equal($tempin);
            $teachers = $DB->get_in_or_equal($tempin);
            $query = "SELECT DISTINCT c.instanceid courseid FROM {role_assignments} ra,";
            $query .= " {context} c, {role_capabilities} rc WHERE rc.capability in (?, ?, ?, ?)";
            $query .= " AND rc.roleid = ra.roleid AND c.id = ra.contextid AND c.contextlevel = ".CONTEXT_COURSE;
            $query .= " AND ra.userid $teachers[0]";
            array_unshift($teachers[1], 'mod/quiz:grade');
            array_unshift($teachers[1], 'mod/assignment:grade');
            array_unshift($teachers[1], 'mod/forum:rate');
            array_unshift($teachers[1], 'mod/glossary:rate');
            $tempcourses = $DB->get_records_sql($query, $teachers[1]);
            foreach ($tempcourses as $course) {
                $teachercourses[] = $course->courseid;
            }
        }
    }

    if (!empty($formdata->courses) && count($teachercourses) > 0) {
        // If there is courses selected and teachers, only show the common courses.
        $tempcourses = preg_split('/,/', $formdata->courses);
        $courses = Array();
        foreach ($tempcourses as $course) {
            if (in_array($course, $teachercourses)) {
                $courses[] = $course;
            }
        }
        if (count($courses) > 0) {
            return $courses;
        } else {
            return false;
        }
    }

    // Categories and teachers selected.
    if (!empty($formdata->categories) && count($teachercourses) > 0 && empty($formdata->courses)) {
        $categories = preg_split('/,/', $formdata->categories);
        return ncccscensus_get_category_courses($categories, $teachercourses);
    }

    if (count($teachercourses) > 0) {
        // Only teacher(s) are selected.
        return $teachercourses;
    }

    return false;
}

/**
 * Performs the report function.
 *
 * @param array $formdata the form data
 * @param int $type the report type
 * @param string $saveto File to save the pdf report to.
 * @return bool False on failure
 * @uses $CFG, $DB
 */
function ncccscensus_generate_report($formdata, $type = ACTION_VIEW, $saveto = false) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/moodlelib.php');
    $reportname = 'report_ncccscensus';

    $cid = $formdata->id;

    // In case the form is hacked, set a default startdate to today at midnight.
    if (empty($formdata->startdate)) {
        $formdata->startdate = usergetmidnight(time(), get_user_timezone());
    }

    // In case the form is hacked, set a default enddate to today at midnight.
    if (empty($formdata->enddate)) {
        $formdata->enddate = $formdata->startdate;
    }

    // Advance enddate to tomorrow's midnight.
    $formdata->enddate += DAYSECS - 1;

    // This flag determines if we should display grouped users or not.
    $nogroups = isset($formdata->disablegroups) ? true : false;

    if ($nogroups) {
        $group = false;
    } else {

        // If group specified, do some validation.
        $group = isset($formdata->group) ? $formdata->group : false;

        // In case the form is hacked, the group could be invalid.
        if ($group === false || $group < 0) {
            throw new ncccscensus_exception('cannotfindgroup');
        }

        if ($group > 0) {
            // Validate the group ID.
            if (!groups_group_exists($group)) {
                throw new ncccscensus_exception('cannotfindgroup');
            }

            // Validate the group ID with respect to the course ID.
            $groupdata = groups_get_course_data($cid);
            $groupfound = false;
            foreach ($groupdata->groups as $groupobject) {
                if ($groupobject->id == $group) {
                    $groupfound = true;
                    break;
                }
            }
            if (!$groupfound) {
                throw new ncccscensus_exception('invalidgroupid');
            }

            // User could still hack form to view a group that they don't have the capability to see.
            $context = context_course::instance($cid);
            if (has_capability('moodle/site:accessallgroups', $context)) {
                $userid = 0;
            } else if (has_capability('moodle/course:managegroups', $context)) {
                $userid = $USER->id;
            } else {
                $userid = false;
            }

            if ($userid === false) {
                throw new ncccscensus_exception('invalidgroupid');
            }

            if ($userid != 0) {
                $grouprecs = groups_get_all_groups($course->id, $userid, 0, 'g.id, g.name');
                $groupnotfound = true;
                foreach ($grouprecs as $grouprec) {
                    if ($grouprec->id == $group) {
                        $groupnotfound = false;
                        break;
                    }
                }
                if ($groupnotfound) {
                    throw new ncccscensus_exception('invalidgroupid');
                }
            }
        }
    }

    $users = array();
    if ($nogroups) {
        $users = ncccscensus_get_users($cid, EXCLUDE_GROUP_MEMBERS);
    } else if ($group > 0) {
        $users = ncccscensus_get_users($cid, $group);
    } else {
        $users = ncccscensus_get_users($cid);
    }

    $results = ncccscensus_build_grades_array($cid, $users, $formdata->startdate, $formdata->enddate);

    if (empty($results)) {
        return false;
    }

    if ($type == ACTION_VIEW) {
        $headers = array('student' => get_string('studentfullnamehtml', $reportname));
        $showstudentid = ncccscensus_check_field_status('showstudentid', 'html');
    } else if ($type == ACTION_CSV) {
        $headers = array('student' => get_string('studentfullnamecsv', $reportname));
        $showstudentid = ncccscensus_check_field_status('showstudentid', 'csv');
    } else {
        $headers = array('student' => get_string('studentfullnamepdf', $reportname));
        $showstudentid = ncccscensus_check_field_status('showstudentid', 'pdf');
    }

    if ($showstudentid) {
        $headers['studentid'] = get_string('studentid', $reportname);
    }
    $headers['activity'] = get_string('activityname', $reportname);
    $headers['module'] = get_string('activitymodule', $reportname);
    $headers['status'] = get_string('submissionstatus', $reportname);
    $headers['datesubmitted'] = get_string('submissiondate', $reportname);
    $headers['grade'] = get_string('grade', $reportname);
    $headers['gradedate'] = get_string('gradedate', $reportname);

    $context = context_course::instance($cid);
    $namesarrayview = array();
    $namesarraypdf = array();
    $instructors = ' - ';
    $viewlink = ': <a href="'.$CFG->wwwroot.'/user/view.php?id=';

    if (!empty($CFG->coursecontact)) {
        $coursecontactroles = explode(',', $CFG->coursecontact);
        sort($coursecontactroles);
        // If a user has multiple roles, we do not want to show user multiple times as a contact.
        $teachers = array();
        foreach ($coursecontactroles as $roleid) {
            $roleid = (int)$roleid;
            if ($users = get_role_users($roleid, $context, true)) {
                $role = $DB->get_record('role', array('id' => $roleid));
                $rolename = format_string(role_get_name($role, $context));
                foreach ($users as $teacher) {
                    // The $teachers array tracks whether a user is already a course contact.
                    if (!isset($teachers[$teacher->id])) {
                        $teachers[$teacher->id] = true;
                        $fullname = fullname($teacher, has_capability('moodle/site:viewfullnames', $context));
                        $namesarrayview[] = $rolename.$viewlink.$teacher->id.'&amp;course='.SITEID.'">'.$fullname.'</a>';
                        $namesarraycsv[]  = $rolename.': '.$fullname;
                        $namesarraypdf[]  = $rolename.': '.$fullname;
                    }
                }
            }
        }
    }

    if ($type != ACTION_PDF) {
        if ($type == ACTION_VIEW) {
            // Create legend for HTML view.
            $legend = new html_table();
            $legend->head = array(get_string('legend', $reportname));
            $legend->headspan = array(2);
            $legendrow1colour = new html_table_cell();
            $legendrow1colour->style = 'width: 50px; background-color: '.get_config('report_ncccscensus', 'gradeoverridecolour');
            $legendrow1[] = $legendrow1colour;
            $legendrow1[] = get_string('legendgradeoverride', $reportname);
            $legendrow2colour = new html_table_cell();
            $legendrow2colour->style = 'width: 50px; background-color: '.get_config('report_ncccscensus', 'gradenogradecolour');
            $legendrow2[] = $legendrow2colour;
            $legendrow2[] = get_string('legendnograde', $reportname);
            $legend->data = array($legendrow1, $legendrow2);
            $legendalign = array('center', 'left');
            $legend->align = $legendalign;
        }
        $table = new html_table();
        $table->head = $headers;

        $align = array('left');
        $numheaders = count($headers);
        for ($i = 1; $i < $numheaders; $i++) {
            $align[] = 'center';
        }
        $table->align = $align;

        $table->data = array();

        foreach ($results as $result) {
            $datum = array();
            $datum[] = $result->student;
            if ($showstudentid) {
                $datum[] = $result->studentid;
            }
            $datum[] = $result->activity;
            $datum[] = $result->module;
            $status = $result->status;
            $grade = $result->grade;

            if ($type == ACTION_VIEW && $grade == get_string('nograde', $reportname)) {
                $specialstatus = new html_table_cell($status);
                $specialstatus->style = 'background-color: '.get_config('report_ncccscensus', 'gradenogradecolour');
                $status = $specialstatus;
            } else if ($type == ACTION_VIEW && $result->overridden) {
                $specialstatus = new html_table_cell($status);
                $specialstatus->style = 'background-color: '.get_config('report_ncccscensus', 'gradeoverridecolour');
                $status = $specialstatus;
            }

            $datum[] = $status;
            $datum[] = $result->submitdate;
            if ($type == ACTION_VIEW && $grade == get_string('nograde', $reportname)) {
                $nograde = new html_table_cell($grade);
                $nograde->style = 'background-color: '.get_config('report_ncccscensus', 'gradenogradecolour');
                $grade = $nograde;
            } else if ($type == ACTION_VIEW && $result->overridden) {
                $overriddengrade = new html_table_cell($grade);
                $overriddengrade->style = 'background-color: '.get_config('report_ncccscensus', 'gradeoverridecolour');
                $grade = $overriddengrade;
            }
            $datum[] = $grade;
            $datum[] = $result->date;
            $table->data[] = $datum;
        }
    }

    $course = $DB->get_record('course', array('id' => $cid));

    if ($group > 0) {
        $groupname = groups_get_group_name($group);
    }

    $datestring = 'n/j/y';
    $reportrange = date($datestring, $formdata->startdate).' - '.date($datestring, $formdata->enddate);

    if ($type != ACTION_VIEW) {
        $date = usergetdate(time(), get_user_timezone());
        $filename  = 'CensusRpt2_';
        $filename .= date('MdY_Hi', mktime($date['hours'], $date['minutes'], 0, $date['mon'], $date['mday'], $date['year']));
    }

    if ($type == ACTION_VIEW) {

        if (ncccscensus_check_field_status('showcoursename', 'html')) {
            echo '<b>'.get_string('coursetitle', $reportname).':</b> '.$course->fullname.'<br>';
        }

        if (ncccscensus_check_field_status('showcoursecode', 'html')) {
            echo '<b>'.get_string('coursecode', $reportname).':</b> '.$course->shortname.'<br>';
        }

        // Only show course ID if present.
        if (ncccscensus_check_field_status('showcourseid', 'html') && $course->idnumber !== '') {
            echo '<b>'.get_string('courseid', $reportname).':</b> '.$course->idnumber.'<br>';
        }

        if (ncccscensus_check_field_status('showteachername', 'html')) {
            if (!empty($namesarrayview)) {
                $instructors = implode(', ', $namesarrayview);
                echo '<b>'.get_string('instructor', $reportname).':</b> '.$instructors.'<br>';
            }
        }

        echo '<b>'.get_string('reportrange', $reportname).':</b> '.$reportrange.'<br>';

        if (isset($groupname)) {
            echo '<b>'.get_string('section', $reportname).':</b> '.$groupname.'<br>';
        } else {
            echo '<b>'.get_string('section', $reportname).':</b> '.get_string('allgroupspdf', $reportname).'<br>';
        }

        echo '<br>';
        echo html_writer::table($table);
        echo '<div id="studentfootnote" style="font-size:10px;">'.get_string('studentfootnote', $reportname).'</div>';

        echo '<br>';
        echo html_writer::table($legend);

        echo '<br><div align="center"><a href="'.$CFG->wwwroot.'/report/ncccscensus/index.php?id='.$formdata->id.'">';
        echo get_string('backtoreport', 'report_ncccscensus').'</a></div>';

    } else if ($type == ACTION_PDF) {

        $topheaders = array();
        $topheaders['student']    = get_string('student', $reportname);
        $topheaders['activity']   = get_string('activity', $reportname);
        $topheaders['submission'] = get_string('submission', $reportname);
        $topheaders['grade']      = get_string('grade', $reportname);

        $bottomheaders = array();
        $bottomheaders['student'] = array('fullname' => get_string('studentfullnamepdf', $reportname));
        $showstudentid = ncccscensus_check_field_status('showstudentid', 'pdf');
        if ($showstudentid) {
            $bottomheaders['student']['id'] = get_string('studentidpdf', $reportname);
        }
        $bottomheaders['activity']   = array('name'   => get_string('activityname', $reportname),
                                             'module' => get_string('activitymodule', $reportname));
        $bottomheaders['submission'] = array('status' => get_string('submissionstatus', $reportname),
                                             'date'   => get_string('submissiondate', $reportname));
        $bottomheaders['grade']      = array('grade'  => get_string('grade', $reportname),
                                             'date'   => get_string('gradedatepdf', $reportname));

        require_once('report.class.php');
        $censusreport = new report();
        $censusreport->topheaders = $topheaders;
        $censusreport->bottomheaders = $bottomheaders;
        $censusreport->data = array();

        foreach ($results as $result) {
            $fieldarray = array();
            $fieldarray['studentfullname'] = $result->student;
            if ($showstudentid) {
                $fieldarray['studentid'] = $result->studentid;
            }
            $fieldarray['activityname'] = $result->activity;
            $fieldarray['activitymodule'] = $result->module;
            $fieldarray['submissionstatus'] = $result->status;
            $fieldarray['submissiondate'] = $result->submitdate;
            $fieldarray['gradegrade'] = $result->grade;
            $fieldarray['gradedate'] = $result->date;
            $censusreport->data[] = array('data' => $fieldarray, 'override' => ($result->overridden != 0) ? true : false,
                    'nograde' => ($result->grade == get_string('nograde', $reportname) ? true : false));
        }

        $censusreport->filename = $filename.'.pdf';

        if (ncccscensus_check_field_status('showcoursename', 'pdf')) {
            $censusreport->top[] = array(get_string('coursetitlepdf', $reportname).':', $course->fullname);
        }

        if (ncccscensus_check_field_status('showcoursecode', 'pdf')) {
            $censusreport->top[] = array(get_string('coursecodepdf', $reportname).':', $course->shortname);
        }

        if (ncccscensus_check_field_status('showcourseid', 'pdf') && $course->idnumber !== '') {
            $censusreport->top[] = array(get_string('courseid', $reportname).':', $course->idnumber);
        }

        if (ncccscensus_check_field_status('showteachername', 'pdf')) {
            if (!empty($namesarrayview)) {
                $instructors = implode(', ', $namesarrayview);
            }
            $censusreport->top[] = array(get_string('instructor', $reportname).':', strip_tags($instructors));
        }

        $censusreport->top[] = array(get_string('reportrangepdf', $reportname).':', $reportrange);

        if (isset($groupname)) {
            $censusreport->top[] = array(get_string('group', $reportname).':', $groupname);
        } else if ($group !== false) {
            $censusreport->top[] = array(get_string('group', $reportname).':', get_string('allgroupspdf', $reportname));
        }

        if (ncccscensus_check_field_status('showsignatureline', 'pdf')) {
            $censusreport->signatureline = true;
        }

        if (ncccscensus_check_field_status('showdateline', 'pdf')) {
            $censusreport->dateline = true;
        }

        if ($footermessage = get_config('report_ncccscensus', 'footermessage')) {
            $censusreport->bottom .= $footermessage;
        }
        $censusreport->download($saveto);
        return true;

    } else if ($type == ACTION_CSV) {

        if (!empty($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
            header('Expires: 0');
            header('Cache-Control: private, pre-check=0, post-check=0, max-age=0, must-revalidate');
            header('Connection: Keep-Alive');
            header('Content-Language: '.current_language());
            header('Keep-Alive: timeout=5, max=100');
            header('Pragma: no-cache');
            header('Pragma: expires');
            header('Expires: Mon, 20 Aug 1969 09:23:00 GMT');
            header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        }
        header('Content-Transfer-Encoding: ascii');
        header('Content-Disposition: attachment; filename='.$filename.'.csv');
        header('Content-Type: text/comma-separated-values');

        $output = fopen('php://output', 'w');

        fputcsv($output, array(get_string('ncccscensusreport_title', 'report_ncccscensus')));
        fputcsv($output, array());

        if (ncccscensus_check_field_status('showcoursename', 'csv')) {
            fputcsv($output, array(get_string('coursetitle', $reportname), $course->fullname));
        }

        if (ncccscensus_check_field_status('showcoursecode', 'csv')) {
            fputcsv($output, array(get_string('coursecode', $reportname), $course->shortname));
        }

        if (ncccscensus_check_field_status('showcourseid', 'csv') && ($course->idnumber !== '')) {
            fputcsv($output, array(get_string('courseid', $reportname), $course->idnumber));
        }

        if (!empty($namesarrayview) && ncccscensus_check_field_status('showteachername', 'csv')) {
            fputcsv($output, array_merge(array(get_string('instructor', $reportname)), $namesarraycsv));
        }

        fputcsv($output, array(get_string('reportrange', $reportname), $reportrange));

        if (isset($groupname)) {
            fputcsv($output, array(get_string('section', $reportname), $groupname));
        } else {
            fputcsv($output, array(get_string('section', $reportname), get_string('allgroups', $reportname)));
        }

        fputcsv($output, array());
        fputcsv($output, $table->head);

        foreach ($table->data as $row) {
            fputcsv($output, $row);
        }

        $showsignatureline = ncccscensus_check_field_status('showsignatureline', 'csv');
        $showdateline = ncccscensus_check_field_status('showdateline', 'csv');

        if ($showsignatureline || $showdateline) {
            fputcsv($output, array());

            if ($showsignatureline) {
                fputcsv($output, array(get_string('certified', $reportname)));
                fputcsv($output, array(get_string('signature', $reportname).get_string('underscores', $reportname)));
            }

            if ($showdateline) {
                fputcsv($output, array(get_string('date').get_string('underscores', 'report_ncccscensus')));
            }
        }

        fclose($output);
    }
}

/**
 * Fetches users based on a supplied course and group.
 *
 * @param mixed $course the course to search for users in
 * @param mixed $group the group to search for users in
 * @return array of users
 * @uses $CFG
 */
function ncccscensus_get_users($course, $group = null) {

    global $CFG;
    require_once($CFG->libdir.'/accesslib.php');

    $excludegroupmembers = false;
    if ($group === EXCLUDE_GROUP_MEMBERS) {
        $excludegroupmembers = true;
        $group = null; // Set group to null to retrieve all users, then filter out group members.
    }

    /* The use of $CFG->gradebookroles and get_role_users() was suggested here:     */
    /* https://tracker.remote-learner.net/browse/NCCCSDEV-19?focusedCommentId=94023 */
    $roles = explode(',', $CFG->gradebookroles);
    $context = context_course::instance($course);
    $users = array();
    foreach ($roles as $role) {
        $roleusers = get_role_users($role, $context, false, 'u.id', null, true, $group);
        foreach ($roleusers as $roleuser) {
            $users[] = $roleuser->id;
        }
    }
    $users = array_unique($users);

    // Filter out group members if any.
    if ($excludegroupmembers) {
        $groupusers = array();
        $groupdata = groups_get_course_data($course);
        foreach ($groupdata->groups as $groupobject) {
            foreach ($roles as $role) {
                $roleusers = get_role_users($role, $context, false, 'u.id', null, true, $groupobject->id);
                foreach ($roleusers as $roleuser) {
                    $groupusers[] = $roleuser->id;
                }
            }
        }
        $groupusers = array_unique($groupusers);
        $users = array_diff($users, $groupusers);
    }

    return $users;
}

/**
 * Checks whether we're supposed to show a field
 *
 * @param string $field the field to check
 * @param string $type optional subfield (eg. pdf, csv, etc.)
 * @return bool whether to show the field
 * @uses $CFG
 */
function ncccscensus_check_field_status($field, $type = '') {
    global $CFG;
    require_once($CFG->libdir.'/moodlelib.php');

    $status = false;

    $configvalue = get_config('report_ncccscensus', $field);
    if (!empty($type)) {
        $values = explode(',', $configvalue);
        foreach ($values as $value) {
            if ($value == $type) {
                $status = true;
                break;
            }
        }
    } else if (!empty($configvalue)) {
        $status = true;
    }

    return $status;
}

/**
 * Build the array of grades for the report.
 *
 * @param int   $courseid    The course record ID.
 * @param mixed $users       An array of user IDs.
 * @param int   $startdate   The start date for the time period to fetch logs.
 * @param int   $enddate     The end date for the time period to fetch logs.
 * @return array An array of user course log information.
 * @uses $CFG, $DB
 */
function ncccscensus_build_grades_array($courseid, $users, $startdate, $enddate) {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/lib/gradelib.php');
    require_once($CFG->dirroot.'/lib/grade/constants.php');
    require_once($CFG->dirroot.'/lib/grade/grade_item.php');
    require_once($CFG->dirroot.'/mod/quiz/locallib.php');

    $reportname = 'report_ncccscensus';
    $context = context_course::instance($courseid);
    $results = array();
    $gis     = array();

    if (empty($users)) {
        $users = 'null';
    } else {
        $users = implode(',', $users);
    }

    // Pass #1 - Get any graded forum post records from the DB.
    $sql = 'SELECT u.id AS userid, fp.id AS postid, gi.id AS giid, u.firstname, u.lastname, u.idnumber, gg.overridden,
                   fp.message, gi.itemname, gg.finalgrade, fp.created AS timesubmitted, fp.modified AS timecreated,
                   u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename
              FROM {forum_posts} fp
        INNER JOIN {forum_discussions} fd ON fd.id = fp.discussion
        INNER JOIN {forum} f ON f.id = fd.forum
        INNER JOIN {grade_items} gi ON gi.iteminstance = fd.forum
         LEFT JOIN {grade_grades} gg ON gg.itemid = gi.id AND gg.userid = fp.userid
        INNER JOIN {user} u ON u.id = fp.userid AND fp.userid in ('.$users.')
             WHERE fd.course = :courseid
                   AND f.assessed > 0
                   AND fp.userid != 0
                   AND gi.itemmodule = "forum"
                   AND fp.created >= :timestart
                   AND fp.created <= :timeend
          GROUP BY fp.userid, u.id, fp.id, gi.id, u.firstname, u.lastname, u.idnumber, fp.message, gi.itemname, gg.finalgrade,
                   fp.created
          ORDER BY fp.created ASC, u.lastname ASC, u.firstname ASC';

    $dbparams = array(
        'courseid'  => $courseid,
        'timestart' => $startdate,
        'timeend'   => $enddate
    );

    $rs = $DB->get_recordset_sql($sql, $dbparams);

    $datestring = 'n/j/y';
    foreach ($rs as $record) {
        if (empty($gis[$record->giid])) {
            $gis[$record->giid] = new grade_item(array('id' => $record->giid));
        }

        // Only record the oldest record found.
        if (empty($results[$record->userid]) || ($record->timecreated < $results[$record->userid]->timecreated)) {
            if (empty($record->finalgrade)) {
                $grade = get_string('nograde', $reportname);
                $date  = '';
            } else {
                $grade = grade_format_gradevalue($record->finalgrade, $gis[$record->giid]);
                $date  = date($datestring, $record->timecreated);
            }

            $result = new stdClass;
            $result->userid      = $record->userid;
            $result->lastname    = $record->lastname;
            $result->firstname   = $record->firstname;
            $result->student     = fullname($record);
            $result->studentid   = $record->idnumber;
            $result->activity    = $record->itemname;
            $result->module      = get_string('moduleforum', $reportname);
            $result->status      = get_string('submissionstatusna', $reportname); // No status info required for 'forum'.
            $result->submitdate  = date($datestring, $record->timesubmitted);
            $result->grade       = $grade;
            $result->overridden  = $record->overridden;
            $result->timecreated = $record->timecreated;
            $result->date        = $date;
            $results[$record->userid] = $result;
        }
    }

    unset($rs);

    // Pass #2 - Get any graded glossary entries from the DB.
    $sql = 'SELECT u.id AS userid, ent.id AS entid, gi.id AS giid, u.firstname, u.lastname, u.idnumber, gi.itemname,
                   gg.finalgrade, ent.timecreated AS timesubmitted, ent.timemodified AS timecreated, gg.overridden,
                   u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename
              FROM {glossary_entries} ent
        INNER JOIN {glossary} glos ON ent.glossaryid = glos.id
        INNER JOIN {grade_items} gi ON gi.iteminstance = glos.id
         LEFT JOIN {grade_grades} gg ON gg.itemid = gi.id AND gg.userid = ent.userid
        INNER JOIN {user} u ON u.id = ent.userid AND ent.userid in ('.$users.')
             WHERE glos.course = :courseid
                   AND glos.assessed > 0
                   AND ent.userid != 0
                   AND gi.itemmodule = "glossary"
                   AND ent.timecreated >= :timestart
                   AND ent.timecreated <= :timeend';

    $dbparams = array(
        'courseid'  => $courseid,
        'timestart' => $startdate,
        'timeend'   => $enddate
    );

    $rs = $DB->get_recordset_sql($sql, $dbparams);

    foreach ($rs as $record) {
        if (empty($gis[$record->giid])) {
            $gis[$record->giid] = new grade_item(array('id' => $record->giid));
        }

        // Only record the oldest record found.
        if (empty($results[$record->userid]) || ($record->timecreated < $results[$record->userid]->timecreated)) {
            if (empty($record->finalgrade)) {
                $grade = get_string('nograde', $reportname);
                $date  = '';
            } else {
                $grade = grade_format_gradevalue($record->finalgrade, $gis[$record->giid]);
                $date  = date($datestring, $record->timecreated);
            }

            $result = new stdClass;
            $result->userid      = $record->userid;
            $result->lastname    = $record->lastname;
            $result->firstname   = $record->firstname;
            $result->student     = fullname($record);
            $result->studentid   = $record->idnumber;
            $result->activity    = $record->itemname;
            $result->module      = get_string('moduleglossary', $reportname);
            $result->status      = get_string('submissionstatusna', $reportname); // No status info required for 'glossary'.
            $result->submitdate  = date($datestring, $record->timesubmitted);
            $result->grade       = $grade;
            $result->overridden  = $record->overridden;
            $result->timecreated = $record->timecreated;
            $result->date        = $date;
            $results[$record->userid] = $result;
        }
    }

    unset($rs);

    // Pass #3 - Get any graded assignment entries from the DB.
    $sql = 'SELECT u.id AS userid, s.id AS entid, gi.id AS giid, u.firstname, u.lastname, u.idnumber, s.status,
                   gi.itemname, gg.finalgrade, s.timecreated AS timesubmitted, ag.timemodified AS timegraded, u.alternatename,
                   s.timemodified AS timecreated, gg.overridden, u.firstnamephonetic, u.lastnamephonetic, u.middlename
              FROM {assign_submission} s
        INNER JOIN {assign} a ON s.assignment = a.id
        INNER JOIN {grade_items} gi ON gi.iteminstance = a.id
         LEFT JOIN {grade_grades} gg ON gg.itemid = gi.id AND gg.userid = s.userid
         LEFT JOIN {assign_grades} ag ON ag.assignment = a.id AND ag.userid = s.userid
        INNER JOIN {user} u ON u.id = s.userid AND s.userid in ('.$users.')
             WHERE a.course = :courseid
                   AND s.userid != 0
                   AND gi.itemmodule = "assign"
                   AND s.timemodified >= :timestart
                   AND s.timemodified <= :timeend';

    $dbparams = array(
        'courseid'  => $courseid,
        'timestart' => $startdate,
        'timeend'   => $enddate
    );

    $rs = $DB->get_recordset_sql($sql, $dbparams);

    foreach ($rs as $record) {
        if (empty($gis[$record->giid])) {
            $gis[$record->giid] = new grade_item(array('id' => $record->giid));
        }

        // Only record the oldest record found.
        if (empty($results[$record->userid]) || ($record->timecreated < $results[$record->userid]->timecreated)) {
            if (empty($record->finalgrade)) {
                $grade = get_string('nograde', $reportname);
                $date  = '';
            } else {
                $grade = grade_format_gradevalue($record->finalgrade, $gis[$record->giid]);
                $date  = date($datestring, $record->timegraded);
            }

            $result = new stdClass;
            $result->userid      = $record->userid;
            $result->lastname    = $record->lastname;
            $result->firstname   = $record->firstname;
            $result->student     = fullname($record);
            $result->studentid   = $record->idnumber;
            $result->activity    = $record->itemname;
            $result->module      = get_string('moduleassignment', $reportname);
            $result->status      = get_string('submissionstatus'.$record->status, $reportname);
            $result->submitdate  = date($datestring, $record->timesubmitted);
            $result->grade       = $grade;
            $result->overridden  = $record->overridden;
            $result->timecreated = $record->timecreated;
            $result->date        = $date;
            $results[$record->userid] = $result;
        }
    }

    unset($rs);

    // Pass #4 - Get any graded quiz from the DB.
    $sql = 'SELECT u.id AS userid, q.id AS qid, gi.id AS giid, u.firstname, u.lastname, u.idnumber, q.state,
                   gi.itemname, gg.finalgrade, q.timefinish AS timesubmitted, q.timemodified AS timecreated, gg.overridden,
                   qg.timemodified AS timegraded, u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename
              FROM {quiz_attempts} q
        INNER JOIN {quiz} qu ON q.quiz = qu.id
        INNER JOIN {grade_items} gi ON gi.iteminstance = qu.id
         LEFT JOIN {grade_grades} gg ON gg.itemid = gi.id AND gg.userid = q.userid
         LEFT JOIN {quiz_grades} qg ON qg.quiz = qu.id AND qg.userid = q.userid
        INNER JOIN {user} u ON u.id = q.userid AND q.userid in ('.$users.')
             WHERE qu.course = :courseid
                   AND q.state not in ("'.quiz_attempt::IN_PROGRESS.'", "'.quiz_attempt::ABANDONED.'")
                   AND q.userid != 0
                   AND gi.itemmodule = "quiz"
                   AND q.timemodified >= :timestart
                   AND q.timemodified <= :timeend';

    $dbparams = array(
        'courseid'  => $courseid,
        'timestart' => $startdate,
        'timeend'   => $enddate
    );

    $rs = $DB->get_recordset_sql($sql, $dbparams);

    foreach ($rs as $record) {
        if (empty($gis[$record->giid])) {
            $gis[$record->giid] = new grade_item(array('id' => $record->giid));
        }

        // Only record the oldest record found.
        if (empty($results[$record->userid]) || ($record->timecreated < $results[$record->userid]->timecreated)) {
            if (empty($record->finalgrade)) {
                $grade = get_string('nograde', $reportname);
                $date  = '';
            } else {
                $grade = grade_format_gradevalue($record->finalgrade, $gis[$record->giid]);
                $date  = date($datestring, $record->timegraded);
            }

            $result = new stdClass;
            $result->userid      = $record->userid;
            $result->lastname    = $record->lastname;
            $result->firstname   = $record->firstname;
            $result->student     = fullname($record);
            $result->studentid   = $record->idnumber;
            $result->activity    = $record->itemname;
            $result->module      = get_string('modulequiz', $reportname);
            $result->status      = get_string('submissionstatus'.$record->state, $reportname);
            $result->submitdate  = date($datestring, $record->timesubmitted);
            $result->grade       = $grade;
            $result->overridden  = $record->overridden;
            $result->timecreated = $record->timecreated;
            $result->date        = $date;
            $results[$record->userid] = $result;
        }
    }

    unset($rs);

    // Add in users without activity if desired.
    if (ncccscensus_check_field_status('showallstudents')) {
        $sql = 'SELECT u.id as userid, u.lastname, u.firstname, u.idnumber, u.firstnamephonetic, u.lastnamephonetic, u.middlename,
                       u.alternatename
                  FROM {user} u
                 WHERE u.id in ('.$users.')';

        $rs = $DB->get_recordset_sql($sql);

        foreach ($rs as $record) {
            if (empty($results[$record->userid])) {
                $result = new stdClass;
                $result->userid      = $record->userid;
                $result->lastname    = $record->lastname;
                $result->firstname   = $record->firstname;
                $result->student     = fullname($record);
                $result->studentid   = $record->idnumber;
                $result->activity    = get_string('noactivitycompleted', $reportname);
                $result->module      = '';
                $result->status      = get_string('submissionstatusna', $reportname);
                $result->submitdate  = '';
                $result->grade       = get_string('nograde', $reportname);
                $result->overridden  = false;
                $result->timecreated = 0;
                $result->date        = '';
                $results[$record->userid] = $result;
            }
        }

        unset($rs);
    }

    // Sort the resulting data by using a "lastname ASC, firstname ASC" sorting algorithm.
    usort($results, 'ncccscensus_results_sort');

    return $results;
}

/**
 * Makes a string safe for CSV output.
 *
 * Replaces unsafe characters with whitespace and escapes
 * double-quotes within a column value.
 *
 * @param string $input The input string.
 * @return string A CSV export 'safe' string.
 */
function ncccscensus_csv_escape_string($input) {
    $input = str_replace(array("\r", "\n", "\t"), ' ', $input);
    $input = str_replace('"', '""', $input);
    $input = '"'.$input.'"';

    return $input;
}

/**
 * Sorts the results fetched from the main data-gathering function.
 *
 * @param mixed $a a user object
 * @param mixed $b a user object
 */
function ncccscensus_results_sort($a, $b) {
    $a1 = strtolower($a->lastname);
    $b1 = strtolower($b->lastname);
    $a2 = strtolower($a->firstname);
    $b2 = strtolower($b->lastname);

    // Compare the lastname values.
    $comp = strcmp($a1, $b1);

    if ($comp == 0) {
        // If they are equal, return the comparison between the firstname values.
        return strcmp($a2, $b2);
    } else {
        // Otherwise, return the lastname comparison value as-is.
        return $comp;
    }
}

/**
 * Adds a link to the census report in the Course administration block
 *
 * @param mixed $navigation the course administration reports navigation node
 * @param mixed $course the course object
 * @param mixed $context the current context object
 */
function report_ncccscensus_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/ncccscensus:view', $context)) {
        $url = new moodle_url('/report/ncccscensus/index.php', array('id' => $course->id));
        $navigation->add(get_string('pluginname', 'report_ncccscensus'), $url, navigation_node::TYPE_SETTING, null, null,
                new pix_icon('i/report', ''));
    }
}

/**
 * Searches for teachers using a substring match.
 *
 * @param string $query The substring to search for.
 * @param array $courses An array of Arrays with indicies 'id' and 'name'.
 * @param array $coursecategories An array of Arrays with indicies 'id' and 'name'.
 * @return array An array of matching teachers or returns an array with a no matches found string.
 */
function report_ncccscensus_teacher_search($query, $courses = array(), $coursecategories = array()) {
    global $DB;

    $results = array();
    $users = array();

    if (empty($coursecategories) && empty($courses)) {
        $contextlevels = null;
        // Get roles with capability.
        $roles = get_roles_with_capability('moodle/grade:edit', CAP_ALLOW);

        $roles = array_keys($roles);

        // Retrieving all user columns because the user object needs to be passed through to Moodle's fullname() function.
        $rolelist = $DB->get_in_or_equal($roles);
        $rolelist[1][] = "%{$query}%";
        $rolelist[1][] = "%{$query}%";
        $rolelist[1][] = "%{$query}%";

        $sql = "SELECT ra.id AS raid, u.*, ra.contextid
                  FROM {role_assignments} ra
                  JOIN {user} u ON u.id = ra.userid
                 WHERE ra.roleid {$rolelist[0]}
                       AND (u.username LIKE ?
                       OR u.firstname LIKE ?
                       OR u.lastname LIKE ?)";
        $records = $DB->get_records_sql($sql, $rolelist[1]);

        $usersadded = array();
        foreach ($records as $record) {
            if (isset($usersadded[$record->id])) {
                continue;
            }

            if (has_capability('moodle/grade:edit', context::instance_by_id($record->contextid), $record->id)) {
                $usersadded[$record->id] = 0;
                $results[] = array('id' => $record->id, 'name' => fullname($record)." ({$record->username})");
            }
        }

    } elseif ((empty($coursecategories) && !empty($courses)) || (!empty($coursecategories) && !empty($courses))) {
        // Search of users within a course.

        $courses = array_map('report_ncccscensus_format_category_data', $courses);
        $results = report_ncccscensus_get_users_with_capability_in_contexts($courses, 'moodle/grade:edit', $query);

    } elseif (!empty($coursecategories) && empty($courses)) {
        // Search for courses within a category and for users within those courses.

        // Format coursecategories so that it's only an array of course category ids.
        $coursecategories = array_map('report_ncccscensus_format_category_data', $coursecategories);

        // Get an array of courses in the course category.
        $courses = ncccscensus_get_category_courses($coursecategories);

        $results = report_ncccscensus_get_users_with_capability_in_contexts($courses, 'moodle/grade:edit', $query);
    }

    if (empty($results)) {
        return array(array('name' => get_string('noresults', 'report_ncccscensus')." $query"));
    }

    return $results;
}

/**
 * This function iterates over an array of courses, finds users who have the capability in the course context
 * and returns an array of arrays where the inner array of user data having indicies 'id' and 'name'.
 * @param array $courseids An array of course ids.
 * @param string $capability A Moodle capability string.
 * @param string $query A search string.
 * @return array An array of arrays where the inner array has indicies 'id' and 'name'.
 */
function report_ncccscensus_get_users_with_capability_in_contexts($courseids, $capability, $query) {
    $results = array();
    $resultsid = array();

    if (!is_array($courseids)) {
        return $results;
    }

    foreach($courseids as $courseid) {
        if (empty($courseid)) {
            continue;
        }

        $users = get_users_by_capability(context_course::instance($courseid), 'moodle/grade:edit', 'u.*');

        if (empty($users)) {
            continue;
        }
        // Use a call back function to format user data.
        $acquery = array_fill(0, count($users), $query);
        $userdata = array_map('report_ncccscensus_format_teacher_user_data', $users, $acquery);

        foreach ($userdata as $data) {
            if (!isset($data['id']) || in_array($data['id'], $resultsid)) {
                continue;
            }

            array_push($resultsid, $data['id']);
            $results[] = $data;
        }
    }

    return $results;
}

/**
 * This callback function returns only the id of the array.  This is strictly a helper function.
 * @param array $data An array with an 'id' index.
 * @return int The id value
 */
function report_ncccscensus_format_category_data($data) {
    return $data['id'];
}

/**
 * This callback function checks if $userquery string is contained in the Moodle user object's firstname, lastname or username
 * property.  If the user query exists then user's full name and user id is returned in an array.  Otherwise an empty
 * array is returned.
 * @param object $user A Moodle user object represented.
 * @param string $userquery A string of text.
 * @return array An array with indicies 'name' and 'id'.
 */
function report_ncccscensus_format_teacher_user_data($user, $userquery) {
    $contains = false === stripos($user->firstname, $userquery) ? false : true;
    $contains |= false === stripos($user->lastname, $userquery) ? false : true;
    $contains |= false === stripos($user->username, $userquery) ? false : true;

    if ($contains) {
        return array('id' => $user->id, 'name' => fullname($user)." ({$user->username})");
    }

    return array();
}

/**
 * Searches for courses using a substring match and optionally limits by category.
 *
 * @param string $query The substring to search for.
 * @param array $categoryquery Array of categories to search for.
 * @return array Matching courses or returns no matches found.
 */
function report_ncccscensus_course_search($query, $categoryquery) {
    global $DB;
    $time = usertime(time(), get_user_timezone());
    $param = array($time, $time, "%$query%");
    $categorysql = "";

    if (is_array($categoryquery) && count($categoryquery) > 0) {
        $tempcategories = array();
        foreach ($categoryquery as $category) {
            array_push($tempcategories, $category['id']);
        }
        $categories = $DB->get_in_or_equal($tempcategories);
        $categorysql = " AND category $categories[0] ";
        foreach ($categories[1] as $temp) {
            array_push($param, $temp);
        }
    }

    $sqlquery = 'SELECT DISTINCT c.id, c.fullname FROM {course} c, {enrol} e, {user_enrolments} ue WHERE';
    $sqlquery .= ' e.courseid = c.id AND ue.enrolid = e.id ';
    $sqlquery .= ' AND ue.timestart < ? AND (ue.timeend > ? OR ue.timeend = 0)';
    $sqlquery .= ' AND c.fullname LIKE ? '.$categorysql;
    $courses = $DB->get_records_sql($sqlquery, $param);
    $results = array();

    foreach ($courses as $course) {
        array_push($results, array('id' => $course->id, 'name' => $course->fullname));
    }
    if (count($results) === 0) {
        array_push($results, array('name' => get_string('noresults', 'report_ncccscensus')." $query"));
    }
    return $results;
}

/**
 * Searches for categories using a substring match.
 *
 * @param string $query The substring to search for.
 * @return array Matching categories or returns no matches found.
 */
function report_ncccscensus_category_search($query) {
    global $DB;
    $sqlquery = 'SELECT id, name FROM {course_categories} WHERE name LIKE ? ';
    $categories = $DB->get_records_sql($sqlquery, array("%$query%"));
    $results = array();
    foreach ($categories as $category) {
        $category = coursecat::get($category->id);
        $parents = $category->get_parents();
        $categorynames = array();
        foreach ($parents as $categoryid) {
            $tempcategory = coursecat::get($categoryid);
            array_push($categorynames, $tempcategory->get_formatted_name());
        }
        array_push($categorynames, $category->get_formatted_name());
        $name = array_pop($categorynames);
        if (count($categorynames) > 0) {
            $name = array_pop($categorynames).' / '.$name;
        }
        array_push($results, array('id' => $category->id, 'name' => $name));
    }
    if (count($results) === 0) {
        array_push($results, array('name' => get_string('noresults', 'report_ncccscensus')." $query"));
    }
    return $results;
}

/**
 * Class: ncccscensus_exception
 *
 * @see moodle_exception
 */
class ncccscensus_exception extends moodle_exception {
}
