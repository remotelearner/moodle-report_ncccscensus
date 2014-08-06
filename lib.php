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
 * Class to define the report search form
 *
 * @see moodleform
 */
class ncccscensus_setup_query_form extends moodleform {

    /**
    * ACTION_VIEW - represents viewing the HTML version of the report
    */
    const ACTION_VIEW = 1;

    /**
    * ACTION_DLPDF - represents downloading the report in PDF format
    */
    const ACTION_DLPDF = 2;

    /**
    * ACTION_DLCSV - represents downloading the report in CSV format
    */
    const ACTION_DLCSV = 3;

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

            // Build the gruops array.
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

        $bview  =& $mform->createElement('radio', 'action', '', get_string('viewreport', 'report_ncccscensus'),
                                         self::ACTION_VIEW);
        $bdlpdf =& $mform->createElement('radio', 'action', '', get_string('downloadreportpdf', 'report_ncccscensus'),
                                         self::ACTION_DLPDF);
        $bdlcsv =& $mform->createElement('radio', 'action', '', get_string('downloadreportcsv', 'report_ncccscensus'),
                                         self::ACTION_DLCSV);

        $actions = array($bview, $bdlpdf, $bdlcsv);
        $mform->addGroup($actions, 'action', get_string('action', 'report_ncccscensus'), array(' '), false);
        $mform->setDefault('action', self::ACTION_VIEW);

        $mform->addElement('html', '<br>');

        $bsubmit =& $mform->createElement('submit', 'submitbutton', get_string('getreport', 'report_ncccscensus'));
        $breset  =& $mform->createElement('reset', 'resetbutton', get_string('revert'));
        $bcancel =& $mform->createElement('cancel');

        $submits = array($bsubmit, $breset, $bcancel);
        $mform->addGroup($submits, 'submits', '&nbsp;', array(' '), false);
    }
}

/**
 * Performs the report function.
 *
 * @param object $mform    The form object
 * @param int $type     The report type
 * @return bool Success/failure
 */
function ncccscensus_generate_report($mform, $type) {
    // Stub function for now.
    return true;
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
