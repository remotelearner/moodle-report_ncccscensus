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
 * This is the settings file for the census report, it handles global settings.
 *
 * @package   report_ncccscensus
 * @author    Sean O'Hagan <sean.ohagan@remote-learner.net>
 * @copyright 2014 Remote Learner - http://www.remote-learner.net/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die();
}

if ($ADMIN->fulltree) {

    require_once($CFG->dirroot.'/report/ncccscensus/adminsetting.class.php');

    $reportname = 'report_ncccscensus';

    // Show all students setting.
    $element = 'showallstudents';
    $name = $reportname.'/'.$element;
    $title = get_string($element, $reportname);
    $description = get_string($element.'desc', $reportname);
    $default = '0';
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
    $settings->add($setting);

    // Common labels for a number of multi checkbox elements.
    $multilabels = array(
        'html' => get_string('html', $reportname),
        'pdf'  => get_string('pdf', $reportname),
        'csv'  => get_string('csv', $reportname)
    );

    // Label arrays and default arrays for each of the multicheckbox elements.
    $multicheckboxes = array(
        'showcoursename'    => array('labels' => $multilabels, 'defaults' => array('html' => 1, 'pdf' => 1, 'csv' => 0)),
        'showcoursecode'    => array('labels' => $multilabels, 'defaults' => array('html' => 1, 'pdf' => 1, 'csv' => 0)),
        'showcourseid'      => array('labels' => $multilabels, 'defaults' => array('html' => 0, 'pdf' => 0, 'csv' => 0)),
        'showstudentid'     => array('labels' => $multilabels, 'defaults' => array('html' => 0, 'pdf' => 0, 'csv' => 0)),
        'showteachername'   => array('labels' => $multilabels, 'defaults' => array('html' => 0, 'pdf' => 0, 'csv' => 0)),
        'showsignatureline' => array('labels' =>
                                         array('pdf' => get_string('pdf', $reportname), 'csv' => get_string('csv', $reportname)),
                                     'defaults' => array('html' => 0, 'pdf' => 0, 'csv' => 0)),
        'showdateline'      => array('labels' =>
                                         array('pdf' => get_string('pdf', $reportname), 'csv' => get_string('csv', $reportname)),
                                     'defaults' => array('html' => 0, 'pdf' => 0, 'csv' => 0))
    );

    // Display course name setting.
    $element = 'showcoursename';
    $name = $reportname.'/'.$element;
    $title = get_string($element, $reportname);
    $description = get_string($element.'desc', $reportname);
    $setting = new admin_setting_configmulticheckbox($name, $title, $description, $multicheckboxes[$element]['defaults'],
        $multicheckboxes[$element]['labels']);
    $settings->add($setting);

    // Display course code setting.
    $element = 'showcoursecode';
    $name = $reportname.'/'.$element;
    $title = get_string($element, $reportname);
    $description = get_string($element.'desc', $reportname);
    $setting = new admin_setting_configmulticheckbox($name, $title, $description, $multicheckboxes[$element]['defaults'],
        $multicheckboxes[$element]['labels']);
    $settings->add($setting);

    // Display course ID setting.
    $element = 'showcourseid';
    $name = $reportname.'/'.$element;
    $title = get_string($element, $reportname);
    $description = get_string($element.'desc', $reportname);
    $setting = new admin_setting_configmulticheckbox($name, $title, $description, $multicheckboxes[$element]['defaults'],
        $multicheckboxes[$element]['labels']);
    $settings->add($setting);

    // Display student ID setting.
    $element = 'showstudentid';
    $name = $reportname.'/'.$element;
    $title = get_string($element, $reportname);
    $description = get_string($element.'desc', $reportname);
    $setting = new admin_setting_configmulticheckbox($name, $title, $description, $multicheckboxes[$element]['defaults'],
        $multicheckboxes[$element]['labels']);
    $settings->add($setting);

    // Display teacher names setting.
    $element = 'showteachername';
    $name = $reportname.'/'.$element;
    $title = get_string($element, $reportname);
    $description = get_string($element.'desc', $reportname);
    $setting = new admin_setting_configmulticheckbox($name, $title, $description, $multicheckboxes[$element]['defaults'],
        $multicheckboxes[$element]['labels']);
    $settings->add($setting);

    // Display signature line setting.
    $element = 'showsignatureline';
    $name = $reportname.'/'.$element;
    $title = get_string($element, $reportname);
    $description = get_string($element.'desc', $reportname);
    $setting = new admin_setting_configmulticheckbox($name, $title, $description, $multicheckboxes[$element]['defaults'],
        $multicheckboxes[$element]['labels']);
    $settings->add($setting);

    // Display date line setting.
    $element = 'showdateline';
    $name = $reportname.'/'.$element;
    $title = get_string($element, $reportname);
    $description = get_string($element.'desc', $reportname);
    $setting = new admin_setting_configmulticheckbox($name, $title, $description, $multicheckboxes[$element]['defaults'],
        $multicheckboxes[$element]['labels']);
    $settings->add($setting);

    // Footer message setting.
    $element = 'footermessage';
    $name = $reportname.'/'.$element;
    $title = get_string($element, $reportname);
    $description = get_string($element.'desc', $reportname);
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default, PARAM_TEXT);
    $settings->add($setting);

    // Upload image setting.
    $element = 'uploadimage';
    $name = $reportname.'/'.$element;
    $title = get_string($element, $reportname);
    $description = get_string($element.'desc', $reportname);
    $default = '';
    $setting = new ncccscensusreport_admin_setting_upload($name, $title, $description, $default);
    $settings->add($setting);

    // Currently selected header image.
    $element = 'headerimgname';
    $name = $reportname.'/'.$element;
    $title = $description = get_string($element, $reportname);
    $default = '';
    $optsarry = array('' => '');
    foreach (get_directory_list($CFG->dataroot.'/report/ncccscensus/pix/header') as $imgfile) {
        $optsarry[$imgfile] = $imgfile;
    }
    $setting = new admin_setting_configselect($name, $title, $description, $default, $optsarry);
    $settings->add($setting);

    // Currently selected logo image.
    $element = 'logoimgname';
    $name = $reportname.'/'.$element;
    $title = $description = get_string($element, $reportname);
    $default = '';
    $optsarry = array('' => '');
    foreach (get_directory_list($CFG->dataroot.'/report/ncccscensus/pix/logo') as $imgfile) {
        $optsarry[$imgfile] = $imgfile;
    }
    $setting = new admin_setting_configselect($name, $title, $description, $default, $optsarry);
    $settings->add($setting);

    // Grade override colour setting.
    $element = 'gradeoverridecolour';
    $name = $reportname.'/'.$element;
    $title = get_string($element, $reportname);
    $description = get_string($element.'desc', $reportname);
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
    $settings->add($setting);

    // No grade colour setting.
    $element = 'gradenogradecolour';
    $name = $reportname.'/'.$element;
    $title = get_string($element, $reportname);
    $description = get_string($element.'desc', $reportname);
    $default = '';
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
    $settings->add($setting);

}
