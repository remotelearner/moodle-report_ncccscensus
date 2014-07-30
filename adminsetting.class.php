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
 * Creates an upload form on the settings page
 *
 * @package   report_ncccscensus
 * @author    Sean O'Hagan <sean.ohagan@remote-learner.net>
 * @copyright 2014 Remote Learner - http://www.remote-learner.net/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir.'/adminlib.php');

/**
 * Class extends admin setting class to allow/process an uploaded file
 *
 * @see admin_setting_configtext
 */
class ncccscensusreport_admin_setting_upload extends admin_setting_configtext {

    /**
     * Constructor for this class.
     *
     * @see admin_setting_configtext::__construct()
     */
    public function __construct($name, $visiblename, $description, $defaultsetting) {
        parent::__construct($name, $visiblename, $description, $defaultsetting, PARAM_RAW, 50);
    }

    /**
     * Overrides the parent class's method.
     *
     * @return string An XHTML string for the upload setting
     * @see admin_setting_configtext::output_html()
     */
    public function output_html($data, $query='') {
        // Create a dummy var for this field.
        $this->config_write($this->name, '');

        return format_admin_setting($this, $this->visiblename,
                html_writer::link(new moodle_url('/report/ncccscensus/upload_image.php'), get_string('upload')),
                $this->description, true, '', null, $query);
    }
}
