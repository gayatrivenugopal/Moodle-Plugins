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
 * file attendance.class.php
 */

require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once("$CFG->libdir/grouplib.php");

class Attendance{

	private $insertFlag = 0;
	
	public function insert_session_in_log($sessionId, $studentId, $statusIdSelected, $statusIdArray, $time, $takenBy, $remarks)
	{
		global $DB;
		global $USER;
		//determine whether the session id corresponds to the group id in which the student is added
		$queryString = "SELECT groupid FROM mdl_attendance_sessions WHERE id = $sessionId";
		$res = $DB->get_records_sql($queryString);
		$groupId = current(current($res));
		//determine whether the student is added in the group
		if(groups_is_member($groupId, $studentId)) {
			$statusIds = array();
			foreach($statusIdArray as $attArray) {
				foreach($attArray as $obj) {
					$statusIds[] = $obj->id;
				}
			}
			if($remarksEntered != null && trim($remarksEntered) != "")
				$remarks .= "due to $remarksEntered";
			$stringStatus = implode(",",$statusIds);
			$queryString = "INSERT INTO mdl_attendance_log(sessionid, studentid, statusid, statusset, timetaken, takenby,
			remarks) VALUES($sessionId, $studentId, $statusIdSelected, '".$stringStatus."', $time, 
				$takenBy, 'Status assigned ".$remarks.". Reassigned by ".$USER->username."')";
			$result = $DB->execute($queryString);
			return $result;
		}
		return null;
	}
	
	public function get_session_ids($attIds, $from, $to)
	{
			global $DB;
			$str = implode(",", $attIds);
			$queryString = "SELECT * FROM mdl_attendance_sessions WHERE attendanceid IN(".$str.") AND sessdate BETWEEN ".$from." AND ".$to;
			$result = $DB->get_records_sql($queryString);
			return $result;
	}
	
	public function get_enrolled_courses()
	{
		global $DB, $USER;
		$queryString = "SELECT courseid FROM mdl_enrol WHERE id IN (SELECT enrolid FROM mdl_user_enrolments WHERE userid = ".$USER->id.")";
		$result = $DB->get_records_sql($queryString);
		return $result;	
	}

	public function get_enrolled_courses_student($studentId)
	{
		global $DB, $USER;
		$queryString = "SELECT courseid FROM mdl_enrol WHERE id IN (SELECT enrolid FROM mdl_user_enrolments WHERE userid = ".$studentId.")";
		$result = $DB->get_records_sql($queryString);
		return $result;	
	}
	
	public function student_in_session($studentId, $sessionId)
	{
		global $DB;
		$queryString = "SELECT statusid FROM mdl_attendance_log WHERE studentid = $studentId && sessionid = $sessionId";
		//echo $queryString;
		$result = $DB->get_records_sql($queryString);
		if(count($result) == 0)
			return 0;
		$queryString = "SELECT acronym FROM mdl_attendance_statuses WHERE id = ".current(current($result));
		$result = $DB->get_records_sql($queryString);
		if(strtoupper(current(current($result))) == "P")
		{
			return -1;
		}
		return 1;
	}

	public function get_acronym_from_status($statusId) {
		global $DB;
		$queryString = "SELECT acronym FROM mdl_attendance_statuses WHERE id = $statusId";
		$result = $DB->get_records_sql($queryString);
		return strtoupper(current(current($result)));
	}
	
	public function get_session_date($sessionId)
	{
		global $DB;
		$queryString = "SELECT sessdate FROM mdl_attendance_sessions WHERE id = $sessionId";
		$result = $DB->get_records_sql($queryString);
		return current(current($result));
	}
	
	public function set_session_description($sessionId, $description)
	{
		global $DB;
		$queryString = "UPDATE mdl_attendance_sessions SET description = '".$description."' WHERE id IN ($sessionId)";
		$result = $DB->execute($queryString);
		return $result;
	}
	
	public function get_session_details($attIds)
	{
			global $DB;
			$str = implode(",", $attIds);
			$queryString = "SELECT * FROM mdl_attendance_sessions WHERE attendanceid IN(".$str.")";
			$result = $DB->get_records_sql($queryString);
			return $result;		
	}
	
	public function get_courses($id, $coursetable, $enroltable, $userenroltable)
	{
		global $DB, $USER;
		if($id == null)
		{
			return array();
		}
		else
		{
			if(count($id)>1)
				$queryString = "SELECT * FROM mdl_course WHERE visible = 1 AND category IN(".implode(",",$id).")";
			else
				$queryString = "SELECT * FROM mdl_course WHERE visible = 1 AND category =".$id[0];
			$result = $DB->get_records_sql($queryString);
			if($result == null)
			{
				return array();
			}
			else
			{
				if(is_siteadmin($USER))
				{
					return $result;
				}
				//get enrol id for each course
				$tempResult = $result;
				$result = array();
				foreach($tempResult as $record)
				{
					$queryString = "SELECT id FROM mdl_enrol WHERE courseid = ".$record->id;
					$enrolResult = $DB->get_records_sql($queryString);
					foreach($enrolResult as $enrolId)
					{
						$queryString = "SELECT userid FROM mdl_user_enrolments WHERE enrolId = ".$enrolId->id." AND userid = ".$USER->id;
						
						$userEnrolResult = $DB->get_records_sql($queryString);
						if(count($userEnrolResult) != 0)
						{
							$result[] = $record;
						}
					}
				}
				//result contains only the courses in which the user is enrolled
				return $result;		
			}
		}
	}
	
	public function get_att_ids($idArray)
	{
			global $DB;
			$str = implode(",", $idArray);
			$queryString = "SELECT * FROM mdl_attforblock WHERE course IN(".$str.")";
			$result = $DB->get_records_sql($queryString);
			return $result;		
	}

	public function get_att_id_from_course($courseId)
	{
			global $DB;
			$queryString = "SELECT id FROM mdl_attforblock WHERE course = $courseId";
			$result = $DB->get_records_sql($queryString);
			return $result;		
	}

	public function get_att_from_sessions($sessionId)
	{
			global $DB;
			$attendanceIds = array();
			foreach($sessionId as $sessId)
			{
				$queryString = "SELECT attendanceid FROM mdl_attendance_sessions WHERE id = $sessId";
				$result = $DB->get_records_sql($queryString);
				//store unique attendance IDs
				if(!(in_array(current(current($result)), $attendanceIds)))
					$attendanceIds[] = current(current($result));
			}
			return $attendanceIds;		
	}
	
	public function is_enrolled($studentId, $courseId)
	{
			global $DB;
			$queryString = "SELECT id FROM mdl_enrol WHERE courseid = $courseId";
			$result = $DB->get_records_sql($queryString);
			$enrolIds = array();
			foreach($result as $object)
			{
				$enrolIds[] = $object->id;
			}
			$enrolId = implode(",",$enrolIds);
			$queryString = "SELECT id FROM mdl_user_enrolments WHERE enrolid IN (".$enrolId.") AND userid = $studentId";
			$result = $DB->get_records_sql($queryString);
			if(count($result) == 0)
				return false;				
			return true;
	}
	
	public function session_exists_in_log($sessionId, $log)
	{
		foreach($log as $record)
		{
			if($record->sessionid == $sessionId)
				return true;
		}
		return false;
	}
	
	public function get_number_of_sessions($attendanceIds)
	{
		global $DB;
		$noOfSessions = array();
		foreach($attendanceIds as $attId)
		{
			$queryString = "SELECT count(*) FROM mdl_attendance_sessions WHERE attendanceid = $attId";
			$result = $DB->get_records_sql($queryString);
			$noOfSessions[] = current(current($result));
		}
		return $noOfSessions;
	}
	
	public function get_course_from_id($courseId)
	{
		global $DB;
		$queryString = "SELECT fullname FROM mdl_course WHERE id = $courseId";
		$result = $DB->get_records_sql($queryString);
		return current(current($result));
	}

	public function get_course_from_session($sessionId)
	{
		global $DB;
		$queryString = "SELECT attendanceid FROM mdl_attendance_sessions WHERE id = $sessionId";
		$result = $DB->get_records_sql($queryString);
		$attendanceId = current(current($result));
		$queryString = "SELECT course FROM mdl_attforblock WHERE id = $attendanceId";
		$result = $DB->get_records_sql($queryString);
		return $this->get_course_from_id(current(current($result)));
	}

	public function get_time_from_session($sessionId)
	{
		global $DB;
		$queryString = "SELECT sessdate FROM mdl_attendance_sessions WHERE id = $sessionId";
		$result = $DB->get_records_sql($queryString);
		return current(current($result));
	}
	
	public function delete_blank_session($sessionId, $reason)
	{
		global $DB;
		//get course ID using attendance ID
		$queryString = "SELECT attendanceid FROM mdl_attendance_sessions WHERE id = $sessionId";
		$result = $DB->get_records_sql($queryString);
		$attendanceId = current(current($result));
		$queryString = "SELECT course FROM mdl_attforblock WHERE id = $attendanceId";
		$result = $DB->get_records_sql($queryString);
		$courseId = current(current($result));
		
		//get session date
		$queryString = "SELECT sessdate FROM mdl_attendance_sessions WHERE id = $sessionId";
		$result = $DB->get_records_sql($queryString);
		$sessDate = current(current($result));
		
		$queryString = "DELETE FROM mdl_attendance_sessions WHERE id = $sessionId";
		$result = $DB->execute($queryString);
		if($result == 1)
		{
			//add to log
			$this->add_blank_session_deletion_log($courseId, $sessDate, $reason);
		}
		return $result;
	}
	
	public function add_blank_session_deletion_log($courseId, $sessdate, $reason)
	{
		global $DB, $USER;
		$record = new stdClass();
		$record->courseid = $courseId;
		$record->sessdate = $sessdate;
		$record->deletedby = $USER->username;
		$record->deletedon = time();
		$record->reason = $reason;
		$result = $DB->insert_record("deleted_blank_sessions_log", $record, false);
		return $result;
	}
	
	public function get_lastrecord_in_deleted_sessions_log()
	{
		global $DB;
		$queryString = "SELECT * FROM mdl_deleted_blank_sessions_log WHERE id = (SELECT MAX(id) FROM mdl_deleted_blank_sessions_log)";
		$result = $DB->get_records_sql($queryString);
		return $result;
	}
	
	public function get_deleted_blank_sessions_log()
	{
		global $DB;
		$queryString = "SELECT * FROM mdl_deleted_blank_sessions_log";
		$result = $DB->get_records_sql($queryString);
		return $result;
	}
	
	public function is_empty_deleted_blank_sessions_log()
	{
		global $DB;
		$queryString = "SELECT count(*) FROM mdl_deleted_blank_sessions_log";
		$result = $DB->get_records_sql($queryString);
		if(current(current($result)) == 0)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
	
	public function get_status_from_att($attendanceIds)
	{
			global $DB;
			//Structure of $statusIds: key correpsonds to attendance ID, value is an array, whose elements are objects of stdClass containing a status ID and the corresponding acronym
			/* Eg. 
			[15] => Array
			(
				[0] => stdClass Object
					(
						[id] => 62
						[acronym] => P
					)
				[1] => stdClass Object
					(
						[id] => 63
						[acronym] => A
					)
				[2] => stdClass Object
					(
						[id] => 64
						[acronym] => C
					)
				[3] => stdClass Object
					(
						[id] => 65
						[acronym] => X
					)
			)
			*/
			$statusIds = array();
			foreach($attendanceIds as $attId)
			{
				$statusArray = array();
				$statusDetailsObject = new stdClass(); //empty object
				$queryString = "SELECT id, acronym FROM mdl_attendance_statuses WHERE attendanceid = $attId";
				$result = $DB->get_records_sql($queryString);
				
				foreach($result as $statusValues)
				{	
					$statusDetailsObject->id = $statusValues->id;
					$statusDetailsObject->acronym = $statusValues->acronym;
					$statusArray[] = $statusDetailsObject;
					$statusDetailsObject = new stdClass(); //empty object
				}
				$statusIds[$attId] = $statusArray;
			}
			return $statusIds;
	}

	
	public function get_course_from_att($attIdArray)
	{
			global $DB;
			$courses = array();
			foreach($attIdArray as $attId)
			{
				$queryString = "SELECT course FROM mdl_attforblock WHERE id = $attId";
				$result = $DB->get_records_sql($queryString);
				foreach($result as $courseId)
				{
					$queryString = "SELECT idnumber, fullname FROM mdl_course WHERE id = ".$courseId->course;
					$result = $DB->get_records_sql($queryString);
					$courses[current($result)->idnumber] = current($result)->fullname;
				}
			}
			return $courses;
	}
	
	public function get_course_name_from_att($attId)
	{
			global $DB;
			$queryString = "SELECT course FROM mdl_attforblock WHERE id = $attId";
			//echo $queryString;
			$result = $DB->get_records_sql($queryString);
			foreach($result as $courseId)
			{
					$queryString = "SELECT fullname FROM mdl_course WHERE id = ".$courseId->course;
					$result = $DB->get_records_sql($queryString);
					return current($result)->fullname;
			}			
	}

	public function get_log_details($sessions)
	{
			global $DB;
			$sessionIds = implode(",",$sessions);
			$queryString = "SELECT id,studentid,sessionid,statusid FROM mdl_attendance_log WHERE sessionid IN (".$sessionIds.")";
			$result = $DB->get_records_sql($queryString);
			return $result;
	}

	public function get_student_status($sessionArray)
	{
			global $DB;
			$str = implode(",", $sessionArray);
			$queryString = "SELECT * FROM mdl_attendance_log WHERE sessionid IN(".$str.")";
			$result = $DB->get_records_sql($queryString);
			return $result;		
	}

	public function get_student_status_in_sessions($sessionArray, $studentId)
	{
			global $DB;
			$str = implode(",", $sessionArray);
			$queryString = "SELECT * FROM mdl_attendance_log WHERE sessionid IN(".$str.") AND studentid = $studentId";
			$result = $DB->get_records_sql($queryString);
			return $result;		
	}
	
	public function get_att_id($sessionId)
	{
		global $DB;
		$queryString = "SELECT attendanceid FROM mdl_attendance_sessions WHERE id = $sessionId";
		$result = $DB->get_records_sql($queryString);
		return current(current($result));	
	}

	public function get_status_details($statusId)
	{
			global $DB;
			$queryString = "SELECT * FROM mdl_attendance_statuses WHERE id = ".$statusId;
			$result = $DB->get_records_sql($queryString);
			return $result;		
	}

	public function get_user_id($username)
	{
		global $DB;
		$queryString = "SELECT id FROM mdl_user WHERE username = $username";
		$result = $DB->get_records_sql($queryString);
		return $result;
	}
	
	public function get_id_from_status($statusIds, $selStatus)
	{
		$statusId = array();
		foreach($statusIds as $attendance)
		{
			foreach($attendance as $statusDetails)
			{
				if(strtoupper($statusDetails->acronym) == strtoupper($selStatus))
				{
					$statusId[] = $statusDetails->id;
				}
			}
		}
		return $statusId;
	}
	
	public function get_statuses($statusIds)
	{
		$statuses = array();
		foreach($statusIds as $attendance)
		{
			$statusId = array();
			foreach($attendance as $statusDetails)
			{
				$statusId[] = $statusDetails->id;
			}
			$statuses[] = $statusId;
		}
		return $statuses;
	}
	
	public function insert_status($sessionId, $studentId, $statusId, $statuses, $remarksEntered)
	{
		global $DB;
		global $USER;
		$queryString = "SELECT timetaken FROM mdl_attendance_log WHERE sessionid = $sessionId";
		$result = $DB->get_records_sql($queryString);
		if($result == null)
			return $result;
		else
		{
			$timetaken = current(current($result));
			if($remarksEntered != null && trim($remarksEntered) != "")
				$remarks .= "due to $remarksEntered";
			$queryString = "INSERT INTO mdl_attendance_log (sessionid,studentid,statusid,statusset,timetaken,takenby,remarks) 
			VALUES($sessionId,$studentId,$statusId,'".$statuses."',".$timetaken.",".$USER->id.",'Status reassigned ".$remarks.". Reassigned by ".$USER->username."')";
			//echo $queryString;
			$result = $DB->execute($queryString);
		//	print_object($result);
		}
		return $result;
	}
	
	public function update_status($sessionid, $studentid, $statusId, $remarksEntered)
	{
		global $DB;
		global $USER;
	
		//determine whether the session id corresponds to the group id in which the student is added
		$queryString = "SELECT groupid FROM mdl_attendance_sessions WHERE id = $sessionid";
		$res = $DB->get_records_sql($queryString);
		$groupId = current(current($res));
		//determine whether the student is added in the group
		if(groups_is_member($groupId, $studentid) || $groupId == 0) {
			if($remarksEntered != null && trim($remarksEntered) != "")
				$remarks .= "due to $remarksEntered";
			$queryString = "UPDATE mdl_attendance_log SET statusid = $statusId, remarks = 'Status reassigned ".$remarks.". Reassigned by ".$USER->username."'
			WHERE sessionid = $sessionid AND studentid = $studentid";
			$result = $DB->execute($queryString);
			return $result;
		}
		else echo "here";
		return null;		
	}
	
	public function deletePastRecords()
	{
		global $DB;
		$query = "stop_timestamp < ".time();
		$DB->delete_records_select(get_string('tablename', 'local_attendance'), $query);
		/***************************Pass records to be blocked to Moodle Server for Exam*******************************************************/
					//	forward_data();
		/**********************************************************************************/
	}

	public function insertIntoTable($users, $from, $to)
	{
		global $DB;
		$tableData = null;
		$tableData = array();
		$success = 0;
		foreach($users as $entry)
		{	
			$name=$entry->username;
			$queryString = "SELECT COUNT(*) FROM mdl_blockdefaulters WHERE username LIKE '".$name."' AND start_timestamp = ".$from." AND stop_timestamp = ".$to;
			//$where = "username LIKE '".$name."' AND start_timestamp = ".$from." AND stop_timestamp = ".$to;
			//echo $queryString;
			$result = $DB->count_records_sql($queryString);
			if(($result) > 0)
			{
				echo "<font color=\"red\">".$name." has already been blocked for the specified duration.<br/></font>";
			}
			else
			{
				global $insertFlag;
				$insertFlag = 1;
				$record = new stdClass();
				$record->start_timestamp = $from;
				$record->stop_timestamp = $to;
				$record->username = $name;
				$DB->insert_record(get_string('tablename', 'local_attendance'), $record);
				/***************************Pass records to be blocked to Moodle Server for Exam*******************************************************/
						$this->forward_data($record);
				/**********************************************************************************/
			}
			$tableData[] = array($name);
		}
		return $tableData;
	}

	public function forward_data($record)
	{
	 foreach($record as $key=>$value)
	 {
		if($key == 'username')
			$username = $value;
		else if($key == 'start_timestamp')
			$start = $value;
		else if($key == 'stop_timestamp')
			$stop = $value;
	 }
	 // Submit those variables to the server
	$post_data = array('username' => $username,
	'start' => $start,
	'stop' => $stop);
	//var_dump($post_data);
	// Send request
	$result = $this->post_request('http://10.10.21.10/block.php', $post_data);
	//Display result
	//echo $result;
	}

	public function post_request($url, $data) {
	 
		// Convert the data array into URL Parameters like a=b&foo=bar etc.
		$data = http_build_query($data);
		//var_dump($data);
		// parse the given URL
		$url = parse_url($url);
	 
		if ($url['scheme'] != 'http') { 
			die('Error: Only HTTP request are supported !');
		}
	 
		// extract host and path:
		$host = $url['host'];
		$path = $url['path'];
	 
		// open a socket connection on port 80 - timeout: 30 sec
		$fp = fsockopen($host, 80, $errno, $errstr, 30);
	 
		if ($fp){
	 
			// send the request headers:
			fputs($fp, "POST $path HTTP/1.1\r\n");
			fputs($fp, "Host: $host\r\n"); 
			fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
			fputs($fp, "Content-length: ". strlen($data) ."\r\n");
			fputs($fp, "Connection: close\r\n\r\n");
			fputs($fp, $data);
	 
			$result = ''; 
			while(!feof($fp)) {
				// receive the results of the request
				$result .= fgets($fp, 128);
			}
		}
		else { 
			return array(
				'status' => 'err', 
				'error' => "$errstr ($errno)"
			);
		}
		//var_dump($result);
		// close the socket connection:
		fclose($fp);
		// split the result header from the content
		$result = explode("\r\n\r\n", $result, 2);
		return $result[1];
	}
}
?>
