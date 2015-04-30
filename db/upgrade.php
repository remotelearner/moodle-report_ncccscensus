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
 * Upgrade code containing changes to the plugin data table.
 *
 * @package    report_ncccscensus
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2014 Remote Learner.net Inc http://www.remote-learner.net
 */
function xmldb_report_ncccscensus_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2014073101) {

        // Define table ncccscensus_reports to be created.
        $table = new xmldb_table('ncccscensus_reports');

        // Adding fields to table ncccscensus_reports.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('batchid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('filename', XMLDB_TYPE_CHAR, '255', null, null, null, '0');
        $table->add_field('fullfilename', XMLDB_TYPE_CHAR, '255', null, null, null, '0');
        $table->add_field('starttime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('reportstartdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('reportenddate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table ncccscensus_reports.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table ncccscensus_reports.
        $table->add_index('course', XMLDB_INDEX_NOTUNIQUE, array('course'));

        // Conditionally launch create table for ncccscensus_reports.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table ncccscensus_batch to be created.
        $table = new xmldb_table('ncccscensus_batch');

        // Adding fields to table ncccscensus_batch.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('starttime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('zipfile', XMLDB_TYPE_CHAR, '255', null, null, null, '0');

        // Adding keys to table ncccscensus_batch.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table ncccscensus_batch.
        $table->add_index('status', XMLDB_INDEX_NOTUNIQUE, array('status'));

        // Conditionally launch create table for ncccscensus_batch.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Ncccscensus savepoint reached.
        upgrade_plugin_savepoint(true, 2014073101, 'report', 'ncccscensus');
    }
    
    return true;
}
