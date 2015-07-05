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
 * @subpackage deviceallocation
 * @copyright 2014 Gayatri Venugopal
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
session_start();

class DeviceModel{

	private $insertFlag = 0;

	public function serialno_exists($sno) {
		global $DB;
		$queryString = "SELECT * FROM mdl_device WHERE sno = '".$sno."'";
		$result = $DB->get_records_sql($queryString);
		return current($result);
	}
	
	public function add_device($sno, $mac, $desc) {
		global $DB;
		$record = new stdClass();
		$record->sno = $sno;
		$record->macaddress = $mac;
		$record->descr = $desc;
		$result = $DB->insert_record("device", $record);
		return $result;
	}
	
	public function get_device_id_from_sno($sno) {
		global $DB;
		$queryString = "SELECT id FROM mdl_device WHERE sno = '".$sno."'";
		$result = $DB->get_records_sql($queryString);
		return current($result);
	}

	public function get_mac_address_from_sno($sno) {
		global $DB;
		$queryString = "SELECT macaddress FROM mdl_device WHERE sno = '".$sno."'";
		$result = $DB->get_records_sql($queryString);
		return current(current($result));	
	}

	public function get_usernames() {
		global $DB;
		$queryString = "SELECT username FROM mdl_user";
		$result = $DB->get_records_sql($queryString);
		return array_keys($result);
	}
	
	public function get_usage($macAddress, $usageFrom) {
		global $DB;
		$queryString = "SELECT id, application, start_time, stop_time FROM mdl_deviceusage WHERE macaddress = ? AND start_time >= ?";
		$result = $DB->get_records_sql($queryString, array($macAddress, intval($usageFrom)));
		return $result;	
	}

	public function get_sno() {
		global $DB;
		$queryString = "SELECT sno FROM mdl_device";
		$result = $DB->get_records_sql($queryString);
		return array_keys($result);
	}

	public function get_sno_from_userid($userid) {
		global $DB;
		$queryString = "SELECT sno FROM mdl_device WHERE id IN (SELECT deviceid FROM mdl_deviceallocation WHERE userid = ".$userid.")";
		$result = $DB->get_records_sql($queryString);
		return array_keys($result);
	}

	public function get_allotted_sno_from_userid($userid) {
		global $DB;
		$queryString = "SELECT sno FROM mdl_device WHERE id IN (SELECT deviceid FROM mdl_deviceallocation WHERE userid = ".$userid." AND returndate is NULL)";
		$result = $DB->get_records_sql($queryString);
		return array_keys($result);
	}

	public function get_device_id($sno) {
		global $DB;
		$queryString = "SELECT id FROM mdl_device WHERE sno = '".$sno."'";
		$result = $DB->get_records_sql($queryString);
		return current(current($result));
	}

	public function get_user_id($username) {
		global $DB;
		$queryString = "SELECT id FROM mdl_user WHERE username = '".$username."'";
		$result = $DB->get_records_sql($queryString);
		return current(current($result));
	}
	
	public function device_allotted_to_user($deviceid, $userid) {
		global $DB;
		$queryString = "SELECT * FROM mdl_deviceallocation WHERE deviceid = '".$deviceid."' AND userid = '".$userid."'";
		$result = $DB->get_records_sql($queryString);
		return current($result);
	}

	public function allot_device($deviceid, $userid, $issuedate, $remarks) {
		global $DB;
		if($this->is_device_allotted_not_returned($deviceid)) {
			return -1;
		}
		else {
			$record = new stdClass();
			$record->deviceid = $deviceid;
			$record->userid = $userid;
			$record->issuedate = $issuedate;
			$record->remarks = $remarks;
			$result = $DB->insert_record("deviceallocation", $record);
			return $result;
		}
	}

	public function get_issuedate($deviceid, $userid) {
		global $DB;
		$queryString = "SELECT issuedate FROM mdl_deviceallocation WHERE deviceid = '".$deviceid."' AND userid = '".$userid."'";
		$result = $DB->get_records_sql($queryString);
		return current($result)->issuedate;
	}

	public function get_returndate($deviceid, $userid) {
		global $DB;
		$queryString = "SELECT returndate FROM mdl_deviceallocation WHERE deviceid = '".$deviceid."' AND userid = '".$userid."'";
		$result = $DB->get_records_sql($queryString);
		return current($result)->returndate;
	}

	public function get_remarks($deviceid, $userid) {
		global $DB;
		$queryString = "SELECT remarks FROM mdl_deviceallocation WHERE deviceid = '".$deviceid."' AND userid = '".$userid."'";
		$result = $DB->get_records_sql($queryString);
		return current($result)->remarks;
	}

	public function is_device_allotted_not_returned($deviceId) {
		global $DB;
		$queryString = "SELECT * FROM mdl_deviceallocation WHERE deviceid = ".$deviceId." AND returndate IS NULL";
		$result = $DB->get_records_sql($queryString);
		return current($result);
	}
	
	public function update_allocation($object, $snoArray, $username, $position) {
		global $DB;
		$i = 0;
		foreach($object as $key=>$value) {
			if(strpos($key, "sno".$position) === 0) {				
				//changing sno
				if(!$this->serialno_exists($value)) {
					return array(-2, $value);
				} else if($this->device_allotted_to_user($this->get_device_id_from_sno($value)->id, $this->get_user_id($username))) {				return array(-4, $value);
				} else if($this->is_device_allotted_not_returned($this->get_device_id_from_sno($value)->id)) {
					return array(-3, $value);				
				}
				$newDeviceId = $this->get_device_id_from_sno($value)->id;
				$oldDeviceId = $this->get_device_id_from_sno($snoArray[$position])->id;
				$userid = $this->get_user_id($username);
				//fire update query to update the record
				$queryString = "UPDATE mdl_deviceallocation SET deviceid = $newDeviceId WHERE deviceid = $oldDeviceId AND userid = $userid";
				if($DB->execute($queryString) == true) {
					echo "<center><font color='green'>Record with serial number ".$snoArray[$position]." has been updated.</font></center><br/>";				
				}
			}
		}
		$i++;
	}

	public function get_firstname_lastname_from_username($username) {
		global $DB;
		
		$queryString = "SELECT firstname, lastname FROM mdl_user WHERE username = '".$username."'";
		$result = $DB->get_records_sql($queryString);
		return current($result);
	}

	public function get_devices() {
		global $DB;
		/*$queryString = "SELECT mdl_device.sno, mdl_device.macaddress, mdl_device.descr, mdl_user.username, mdl_user.firstname, mdl_user.
lastname, mdl_deviceallocation.returndate FROM mdl_device, mdl_user, mdl_deviceallocation WHERE mdl_device.id = mdl_deviceallocation.deviceid AND mdl_user.id = mdl_deviceallocation.userid";*/
		$queryString = "SELECT sno, macaddress, descr FROM mdl_device";
		$result = $DB->get_records_sql($queryString);
		if(count($result) == 0) {
			$queryString = "SELECT mdl_device.sno, mdl_device.macaddress, mdl_device.descr FROM mdl_device";
			$result = $DB->get_records_sql($queryString);
		}
		return $result;
	}

	public function get_allotted_devices() {
		global $DB;
		$queryString = "SELECT mdl_device.sno, mdl_device.macaddress, mdl_device.descr, mdl_user.username, mdl_user.firstname, mdl_user.
lastname, mdl_deviceallocation.issuedate, mdl_deviceallocation.returndate FROM mdl_device, mdl_user, mdl_deviceallocation WHERE mdl_device.id = mdl_deviceallocation.deviceid AND mdl_user.id = mdl_deviceallocation.userid";
		$result = $DB->get_records_sql($queryString);
		if(count($result) == 0) {
			$queryString = "SELECT mdl_device.sno, mdl_device.macaddress, mdl_device.descr FROM mdl_device";
			$result = $DB->get_records_sql($queryString);
		}
		return $result;
	}

	public function update_allocation_details($username, $sno, $issueDate, $returnDate, $remarks) {
		global $DB;

		$userid = $this->get_user_id($username);
		$deviceid = $this->get_device_id_from_sno($sno)->id;

		//fire update query to update the record
		$queryString = "UPDATE mdl_deviceallocation SET issuedate = '".$issueDate."', returndate = '".$returnDate."', remarks = '".$remarks."' WHERE deviceid = $deviceid AND userid = $userid";

		return $DB->execute($queryString);
	}

	public function add_usage($mac, $app, $start, $stop) {
		global $DB;
		$record = new stdClass();
		$record->macaddress = $mac;
		$record->application = $app;
		$record->start_time = $start;
		$record->stop_time = $stop;
		$result = $DB->insert_record("deviceusage", $record);
	
		print_object($result);
	
		return $result;			
	}
}

?>
