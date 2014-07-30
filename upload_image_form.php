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
 * Handles uploading files
 *
 * @package   report_ncccscensus
 * @author    Sean O'Hagan <sean.ohagan@remote-learner.net>
 * @copyright 2014 Remote Learner - http://www.remote-learner.net/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die;
}

require_once($CFG->libdir.'/formslib.php');

class ncccscensusreport_upload_image_form extends moodleform {

    /**
     * The full name of this report.
     */
    const REPORTNAME = 'report_ncccscensus';

    /**
     * The header image directory.
     */
    const REPORT_IMAGE_HEADER = 'header';

    /**
     * The logo image directory.
     */
    const REPORT_IMAGE_LOGO = 'logo';

    /**
     * Overriding abstract method to define the upload image form.
     *
     * @see moodleform::definition()
     */
    public function definition() {
        $mform =& $this->_form;

        $imagetypes = array(
            self::REPORT_IMAGE_HEADER => get_string('header', self::REPORTNAME),
            self::REPORT_IMAGE_LOGO => get_string('logo', self::REPORTNAME)
        );

        $mform->addElement('select', 'imagetype', get_string('imagetype', self::REPORTNAME), $imagetypes);

        $mform->addElement('filepicker', 'ncccscensusreportimage', '');
        $mform->addRule('ncccscensusreportimage', null, 'required', null, 'client');

        $this->add_action_buttons();
    }

    /**
     * File type validation for selected image files.
     * Overriding dummy stub method.
     *
     * @see moodleform::definition()
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $supportedtypes = array(
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'png' => 'image/png'
        );

        $files = $this->get_draft_files('ncccscensusreportimage');
        if ($files) {
            foreach ($files as $file) {
                if (!in_array($file->get_mimetype(), $supportedtypes)) {
                    $errors['ncccscensusreportimage'] = get_string('unsupportedfiletype', self::REPORTNAME);
                }
            }
        } else {
            $errors['ncccscensusreportimage'] = get_string('nofileselected', self::REPORTNAME);
        }

        return $errors;
    }
}
