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
 * Returns list of teachers matching query.
 *
 * @package   report_ncccscensus
 * @author    Remote-Learner.net Inc
 * @copyright 2014 Remote Learner - http://www.remote-learner.net/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');
require_once($CFG->dirroot.'/lib/coursecatlib.php');

require_login();
$context = context_system::instance();
require_capability('report/ncccscensus:view', $context);

$query = required_param('q', PARAM_TEXT);
$callback = required_param('callback', PARAM_TEXT);
$results = report_ncccscensus_category_search($query);
$json = json_encode($results);
header('Content-Type: application/javascript');
echo "$callback($json)";
