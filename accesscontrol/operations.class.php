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
 * @subpackage accesscontrol
 * @copyright 2014 Gayatri Venugopal
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * file operations.php
 * consists of functions used to fetch and store records from/to the database table.
 */
 
require_once('../../config.php');

class Operations {
	
	function add_user($users) {
		global $DB;
		$successfulAddition = array();
		foreach($users as $username) {
			$queryString = "INSERT INTO ".get_string('tablename', 'local_accesscontrol')."(username) VALUES('".$username."')";
			if($DB->execute($queryString)) {
				$successfulAddition[] = $username;
			}
		}
		return $successfulAddition;
	}
	
	function remove_user($username) {
		global $DB;
		$successfulDeletion = false;
		$queryString = "DELETE FROM ".get_string('tablename', 'local_accesscontrol')." WHERE username = '".$username."'";
		if($DB->execute($queryString)) {
			$successfulDeletion = true;
		}
		return $successfulDeletion;
	}
	
	function user_exists($username) {
		global $DB;
		$username = trim($username);
		$queryString = "SELECT id FROM ".get_string("usertablename", "local_accesscontrol")." WHERE username = '".$username."'";
		$result = $DB->get_records_sql($queryString);
		if(count($result) != 0) {
			return true;
		}
		return false;
	}
	
	function user_added($username) {
		global $DB;
		$queryString = "SELECT id FROM ".get_string("tablename", "local_accesscontrol")." WHERE username = '".$username."'";
		$result = $DB->get_records_sql($queryString);
		if(count($result) != 0) {
			return true;
		}
		return false;
	}
	
	function get_users() {
		global $DB;
		$queryString = "SELECT username FROM ".get_string("tablename", "local_accesscontrol");
		$result = $DB->get_records_sql($queryString);
		if(count($result) != 0) {
			return array_keys($result);
		}
		return null;
	}
}
 ?>