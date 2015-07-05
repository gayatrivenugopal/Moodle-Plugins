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
 * @subpackage blockdefaulters
 * @copyright 2013 Gayatri Venugopal
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Creates table and its fields in the database
 */
 
function xmldb_local_blockdefaulters_upgrade($oldversion=0) {
    global $CFG, $DB;
	
	$dbman = $DB->get_manager(); 
    $result = TRUE;

    if ($oldversion < 2013042713) {
         // Define table blockdefaulters to be created
        $table = new xmldb_table('blockdefaulters');

        // Adding fields to table blockdefaulters
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('username', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('start_timestamp', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('stop_timestamp', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table blockdefaulters
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for blockdefaulters
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
		
		 // Define table autoblock to be created
        $table = new xmldb_table('autoblock');

        // Adding fields to table autoblock
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('category_id', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('start_timestamp', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('stop_timestamp', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('cutoff', XMLDB_TYPE_INTEGER, '5', null, null, null, null);

        // Adding keys to table autoblock
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for autoblock
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // blockdefaulters savepoint reached.
        upgrade_plugin_savepoint(true, 2013042713, 'local', 'blockdefaulters');
    } 
    return $result;
}
?>