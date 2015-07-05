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
 * @subpackage attendance
 * @copyright 2013 Gayatri Venugopal
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Creates table and its fields in the database
 */
 
function xmldb_local_attendance_upgrade($oldversion=0) {
    global $CFG, $DB;
	
	$dbman = $DB->get_manager(); 
    $result = TRUE;

    if ($oldversion < 2013101511) {
        // Define table deleted_blank_sessions_log to be created
        // Define table deleted_blank_sessions_log to be created
        $table = new xmldb_table('deleted_blank_sessions_log');

        // Adding fields to table deleted_blank_sessions_log
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sessdate', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('deletedon', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('deletedby', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('reason', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Adding keys to table deleted_blank_sessions_log
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for deleted_blank_sessions_log
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
		
		 $field = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);

        // Conditionally launch add field id
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		  $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null, 'id');

        // Conditionally launch add field courseid
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		$field = new xmldb_field('sessdate', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null, 'courseid');

        // Conditionally launch add field sessdate
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		$field = new xmldb_field('deletedon', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null, 'sessdate');

        // Conditionally launch add field deletedon
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		 $field = new xmldb_field('deletedby', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null, 'deletedon');

        // Conditionally launch add field deletedby
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		
		$field = new xmldb_field('reason', XMLDB_TYPE_TEXT, null, null, null, null, null, 'deletedby');

        // Conditionally launch add field reason
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
	   
        // blockdefaulters savepoint reached
        upgrade_plugin_savepoint(true, 2013101511, 'local', 'attendance');
    }
    return $result;
}
?>