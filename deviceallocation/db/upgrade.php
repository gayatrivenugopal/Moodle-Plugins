<?php
//This file is part of Moodle - http://moodle.org/.
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
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    local
 * @subpackage blockusers
 * @copyright 2013 Gayatri Venugopal
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Creates table and its fields in the database
 */
 
function xmldb_local_deviceallocation_upgrade($oldversion=0) {
    global $CFG, $DB;
	
	$dbman = $DB->get_manager(); 
    $result = TRUE;

    if ($oldversion < 2015013001) {
    
	 // Define table deviceallocation to be created.
        $table = new xmldb_table('deviceallocation');

        // Adding fields to table deviceallocation.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('deviceid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('issuedate', XMLDB_TYPE_CHAR, '150', null, XMLDB_NOTNULL, null, null);
        $table->add_field('returndate', XMLDB_TYPE_CHAR, '150', null, null, null, null);
        $table->add_field('remarks', XMLDB_TYPE_CHAR, '300', null, null, null, null);

        // Adding keys to table deviceallocation.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for deviceallocation.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

	 // Define table device to be created.
        $table = new xmldb_table('device');

        // Adding fields to table device.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('sno', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('macaddress', XMLDB_TYPE_CHAR, '150', null, XMLDB_NOTNULL, null, null);
        $table->add_field('descr', XMLDB_TYPE_CHAR, '300', null, null, null, null);

        // Adding keys to table device.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for device.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

	 // Define table deviceusage to be created.
        $table = new xmldb_table('deviceusage');

        // Adding fields to table deviceusage.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('macaddress', XMLDB_TYPE_CHAR, '150', null, XMLDB_NOTNULL, null, null);
        $table->add_field('application', XMLDB_TYPE_CHAR, '200', null, XMLDB_NOTNULL, null, null);
        $table->add_field('start_time', XMLDB_TYPE_INTEGER, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('stop_time', XMLDB_TYPE_INTEGER, '100', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table deviceusage.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for deviceusage.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

	

        // deviceallocation savepoint reached
        upgrade_plugin_savepoint(true, 2015013001, 'local', 'deviceallocation');
    } 
    return $result;
}
?>
