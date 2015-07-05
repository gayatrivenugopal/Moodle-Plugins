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
 
require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once('download_form.class.php');
require_once('category_form.class.php');
require_once('status_form.class.php');
require_once('attendance.class.php');

ob_start();
session_start();
$timezone = "Asia/Calcutta";
if(function_exists('date_default_timezone_set')) date_default_timezone_set($timezone);

	$PAGE->set_title(get_string('blockdefaulters', 'local_attendance'));
	$PAGE->set_heading(get_string('blockdefaulters', 'local_attendance'));

	echo $OUTPUT->header();

	echo $OUTPUT->heading(get_string('set_status', 'local_attendance'));
	
	$attObj = new Attendance();

	$categoryForm = new Category_Form();
	$downloadForm = new Download_Form();
	$mform = new Status_Form();
	$mform->set_elements($attObj->get_courses($_SESSION['categ']));
	
	if($categoryForm->get_data() == null && $mform->get_data() == null && $downloadForm->get_data() == null)
	{
		$_SESSION['categ'] = null;
		$categoryForm->display();
	}
	else if($categoryForm->get_data() != null)
	{
		$pos = $categoryForm->get_data()->programme;
		//$_SESSION['categoryIndex'] = $pos;
		$categoryIds = array();
		
		foreach($pos as $entry)
		{
			$categoryIds[] = $categoryForm->get_key($entry);
		}
		$_SESSION['categ'] = $categoryIds;
		$mform = new Status_Form();
		$mform->set_elements($attObj->get_courses($_SESSION['categ']));
		if($mform->get_results() == 1 || $_SESSION['results'] == 1)
		{
			//$_SESSION['categoryList'] = $categoryForm->get_list();
			$mform->display();
		}
		else
		{
			$_SESSION['categ'] = null;
			$categoryForm->display();
			echo "<center><font color=\"red\">Could not find courses.</font></center>";
		}
	}
	if($mform->get_data() != null) //true if user clicked "Block Defaulters"
	{
		$mform->display();
		$position = optional_param_array('courses', null, PARAM_TEXT);
		$idArray = array();
		$sessionId = array();
		$attId = array();
		$sessionId = array();
		$studentId = array();
		$statusId = array();
		$statusArray = array();
		$studentGrades = array();
		$studentMaxGrades = array();
		$studentAtt = array();
		$blockStudentIds = array();
		$safeStudentIds = array();
		$usernames = array();
		
		//retrieve parameters passed
		$considerFromArray = optional_param_array('considerfrom', null, PARAM_TEXT);
		$considerTillArray = optional_param_array('considertill', null, PARAM_TEXT);
		$blockFromArray = optional_param_array('blockfrom', null, PARAM_TEXT);
		$blockTillArray = optional_param_array('blocktill', null, PARAM_TEXT);
		$considerfrom = make_timestamp($considerFromArray['year'], $considerFromArray['month'], $considerFromArray['day'], $considerFromArray['hour'], $considerFromArray['minute']);
		$considerto = make_timestamp($considerTillArray['year'], $considerTillArray['month'], $considerTillArray['day'], $considerTillArray['hour'], $considerTillArray['minute']);
		$blockfrom = make_timestamp($blockFromArray['year'], $blockFromArray['month'], $blockFromArray['day'], $blockFromArray['hour'], $blockFromArray['minute']);
		$blockto = make_timestamp($blockTillArray['year'], $blockTillArray['month'], $blockTillArray['day'], $blockTillArray['hour'], $blockTillArray['minute']);
		if($mform->get_data()->prns != 0)
		{		
			if($considerto > $considerfrom)
			{
				$prnArray = $mform->get_prnArray();
				$studentPRN = $prnArray[$mform->get_data()->prns];
				//$_SESSION['considerFrom'] = $considerFromArray['day']."-".$considerFromArray['month']."-".$considerFromArray['year'];
				//$_SESSION['considerTill'] = $considerTillArray['day']."-".$considerTillArray['month']."-".$considerTillArray['year'];
				
				if(optional_param_array('courses', null, PARAM_TEXT) == null)
				{
					echo "<center><font color=\"red\">Please select one or more courses.</font></center>";
				}
				else
				{
					$attObj = new Attendance();
					$studentEnrolledCourses = array();
					foreach($position as $pos)
					{
						$idArray[] = $mform->get_id($pos);
					}
					//$idArray contains course IDs
					//get student id from username (PRN)
					$studentId = current(current($attObj->get_user_id($studentPRN)));
					//for each course selected, check if user is enrolled in course
					$courseId = array();
					$sessionIdArray = array();
					foreach($idArray as $key=>$course)
					{
						if($attObj->is_enrolled($studentId, $course))
						{	
							$courseArray = $mform->return_courses();
							$studentEnrolledCourses[] = $courseArray[$key];
							$courseId[] = $key;
							$sessionIdArray[$key] = $course;
						}
					}
					//print_object($studentEnrolledCourses);
					$res = $attObj->get_att_ids($sessionIdArray);
					foreach($res as $entry)
					{
						$attId[] = $entry->id;
						
					}
					if(count($attId) != 0)
					{
						$sessions = $attObj->get_session_ids($attId, $considerfrom, $considerto);
						//print_object($sessions);
						foreach($sessions as $entry)
						{
							$sessionId[] = $entry->id;
						}
						print_object($c);
						if(count($sessionId) == 0)
						{
							echo "<center><font color=\"red\">No records found.</font></center>";
						}
						else
						{
							//get atendance IDs for each course - Assumption: One - to - one correspondence between course and attendance
							$attendanceIds = $attObj->get_att_from_sessions($sessionId);
							//get status for each attendance
							$statusIds = $attObj->get_status_from_att($attendanceIds);
							//get the number of sessions per attendance id
							$noOfSessions = $attObj->get_number_of_sessions($attendanceIds);
							//get studentid, sessionid of all logs taken in the selected timespan
							$log = $attObj->get_log_details($sessionId);
							//$foundFlag = 0;
							if(count($studentEnrolledCourses) == 0)
							{
								echo "<center><font color=\"red\">$studentPRN has not been enrolled in any of the selected courses.</font></center>";
							}
							else
							{
								$statusSelected = $mform->get_data()->status;
								
								//$statusIdSelected = $attObj->get_id_from_status($statusIds, $statusSelected);
								$statuses = $attObj->get_statuses($statusIds);
								$tempLog = $log;
								$log = array();
								foreach($tempLog as $key=>$record)
								{
									if($record->studentid == $studentId)
									{
										//$foundFlag++;
										$log[$key] = $record;
									}
								}
								//$log now contains the logs for the selected student
								//print_object($log);
								$sessionCount = 0;
								foreach($noOfSessions as $value)
								{
									$sessionCount += $value;
								}
								
									$i = 0;
									$sessionCounter = 0;
									$success = 1;
									$oldId = "";
									$proxy = 0;
									
									//check if proxy marked
									foreach($sessionId as $id)
									{
										$newAttId = $attObj->get_att_from_sessions(array($id));
										$statusIdSelected = current($attObj->get_id_from_status($attObj->get_status_from_att($newAttId), $statusSelected));
										//if($sessionCounter >= $noOfSessions[$i])
										if($oldId != $newAttId[0])
										{
											$i++;
											$sessionCounter = 0;
										}
										if($attObj->session_exists_in_log($id,$log))
										{
											//check if student is has an entry in session
											$studentAttendanceStatus = $attObj->student_in_session($studentId, $id);
											/*if($studentAttendanceStatus == -1)//marked Present = Proxy
											{
												//get session date
												$dateOfSession = $attObj->get_session_date($id);
												//echo $dateOfSession;
												//get attendance id
												$studentAttId = current($attObj->get_att_from_sessions(array($id)));
												//get course name
												$courseName = $attObj->get_course_name_from_att($studentAttId);
												echo "<br/><font color='red'>Student marked Present for the session conducted on <b>".date("d-F-Y",$dateOfSession)."</b> at <b>".date("H:i",$dateOfSession)."</b> for <b>$courseName</b> course. Request for change of status could not be completed. Status remains unchanged for all sessions.</font>";
												$proxy = 1;
											}*/
											//if already present, do nothing
											if($studentAttendanceStatus == -1)//marked Present
											{
												//get session date
												$dateOfSession = $attObj->get_session_date($id);
												//echo $dateOfSession;
												//get attendance id
												$studentAttId = current($attObj->get_att_from_sessions(array($id)));
												//get course name
												$courseName = $attObj->get_course_name_from_att($studentAttId);
												/*
												echo "<script> var change = confirm('Student marked Present for the session conducted on ".date("d-F-Y",$dateOfSession)." at ".date("H:i",$dateOfSession)." for ".$courseName." course. Do you want to modify the current status?');</script>";
												echo "<script> if(change == true) var result = 'valid'; else var result = 'invalid';</script>";
												$change = "<script>document.write(result)</script>";
												
												//echo html_entity_decode($change);
												//echo "here".eval("<script>document.write(result)</script>");
												//echo strcmp(eval($change),"valid");
												if(html_entity_decode($change) == "valid") {
													echo "this is valid";
												}
												else*/
												{								
													echo "<br/><font color='red'>Student marked Present for the session conducted on <b>".date("d-F-Y",$dateOfSession)."</b> at <b>".date("H:i",$dateOfSession)."</b> for <b>$courseName</b> course. Status remains unchanged for this session.</font>";
												}
											}
										}
										$oldId = $attendanceIds[$i];
										$sessionCounter ++;
									}
									
									$i = 0;
									$sessionCounter = 0;
									$success = 1;
									$oldId = "";
									if($proxy != 1)
									{
										foreach($sessionId as $id)
										{
											$newAttId = $attObj->get_att_from_sessions(array($id));
											$statusIdSelected = current($attObj->get_id_from_status($attObj->get_status_from_att($newAttId), $statusSelected));
											//if($sessionCounter >= $noOfSessions[$i])
											if($oldId != $newAttId[0])
											{
												$i++;
												$sessionCounter = 0;
											}
											if($attObj->session_exists_in_log($id,$log))
											{
												//check if student is has an entry in session
												$studentAttendanceStatus = $attObj->student_in_session($studentId, $id);
												if($studentAttendanceStatus != 0)
												{/*
													//check if marked Present
													if($studentAttendanceStatus == -1) //marked Present = Proxy
													{
														//get session date
														$dateOfSession = $attObj->get_session_date($id);
														//echo $dateOfSession;
														//get attendance id
														$studentAttId = current($attObj->get_att_from_sessions(array($id)));
														//get course name
														$courseName = $attObj->get_course_name_from_att($studentAttId);
														
														echo "<br/><font color='red'>Student marked Present for the session conducted on <b>".date("d-F-Y",$dateOfSession)."</b> at <b>".date("H:i",$dateOfSession)."</b> for <b>$courseName</b> course. Status remains unchanged."."</font>";
													}
													else*/
													 //if already present, do nothing
													if($studentAttendanceStatus == -1)//marked Present
													{
														//get session date
														$dateOfSession = $attObj->get_session_date($id);
														//echo $dateOfSession;
														//get attendance id
														$studentAttId = current($attObj->get_att_from_sessions(array($id)));
														//get course name
														$courseName = $attObj->get_course_name_from_att($studentAttId);
													//	echo "<br/><font color='red'>Student marked Present for the session conducted on <b>".date("d-F-Y",$dateOfSession)."</b> at <b>".date("H:i",$dateOfSession)."</b> for <b>$courseName</b> course. Status remains unchanged for this session.</font>";
													}
													else
													{
														if($attObj->update_status($id,$studentId, $statusIdSelected, $mform->get_data()->remarks))
														{
															$successfulSessionId[] = $id;
														}
													}
												}
											}
											else {
												global $USER;
												if($attObj->insert_session_in_log($id, $studentId, $statusIdSelected,$attObj->get_status_from_att($newAttId), time(), $USER->id, $mform->get_data()->remarks)) {
													$successfulSessionId[] = $id;
												}
											}
											$oldId = $attendanceIds[$i];
											$sessionCounter ++;
										}
										if($success == 0)
										{
											echo "<font color='red'> Some sessions have not been taken yet. Therefore status for those sessions has not been assigned.</font>";
										}
										$attId = $attObj->get_att_from_sessions($successfulSessionId);
										$courses = $attObj->get_course_from_att($attId);
										
										if(count($courses) != 0)
											echo "<center><font color=\"green\">The attendance status of $studentPRN has been re-assigned to <b>$statusSelected</b> in the following courses:</font>";
										foreach($courses as $id=>$name)
										{
											echo "<br/>$id $name";
										}
										echo "</center>";
										echo "<br/><br/><font color='red'>Note: The attendance status of the student can be re-assigned only if the student is enrolled in the selected course/s.</font>";
										if(($result = checkBlockingStatus($_SESSION['categ'][0])) != null)//if entry exists in autoblock table, block the defaulters
										{
											//var_dump($result);
											foreach($courseId as $id)
											{
												defaulters($attObj,$id,$_SESSION['categ'][0],$result);
											}
										}
									
									}
								
							}							
						}
					}
					else
					{
						echo "<center><font color=\"red\">No attendance record found.</font></center>";
					}
				}
			}
			else
			{
				echo "<center><font color=\"red\">Please select a valid duration.</font></center>";
			}
		}
		else
		{
			echo "<center><font color=\"red\">Please select a PRN from the list.</font></center>";
		}
	}

	echo $OUTPUT->footer();

	function checkBlockingStatus($categId)
	{
		global $DB;
		$queryString = "SELECT id FROM mdl_".get_string('autotablename', 'local_blockdefaulters')." WHERE category_id = $categId";
		$result = $DB->record_exists_sql($queryString);
		if($result)
		{
			$queryString = "SELECT * FROM mdl_".get_string('autotablename', 'local_blockdefaulters')." WHERE category_id = $categId";
			$result = $DB->get_record_sql($queryString);
			return $result;
		}
		else
			return null;
	}

	function deletePastRecords()
	{
		global $DB;
		$query = "stop_timestamp < ".time();
		$DB->delete_records_select(get_string('tablename', 'local_blockdefaulters'), $query);
	}

	function insertIntoTable($users, $from, $to)
	{
		global $DB;
		$tableData = null;
		$tableData = array();
		$success = 0;

		foreach($users as $entry)
		{	
			$name=$entry->username;
			$queryString = "SELECT count(*) FROM mdl_blockdefaulters WHERE username LIKE '".$name."' AND start_timestamp = ".$from." AND stop_timestamp = ".$to;
			
			//$where = "username LIKE '".$name."' AND start_timestamp = ".$from." AND stop_timestamp = ".$to;
			
			//echo $where."<br>";
			//$result = $DB->get_records_select("blockdefaulters",$where);//$DB->count_records_sql($queryString);
			$result = $DB->count_records_sql($queryString);
			if(($result) > 0)
			{
				//echo "<font color=\"red\">".$name." has already been blocked for the specified duration.<br/></font>";
			}
			else
			{
				global $insertFlag;
				$insertFlag = 1;
				$record = new stdClass();
				$record->start_timestamp = $from;
				$record->stop_timestamp = $to;
				$record->username = $name;
				$res = $DB->insert_record(get_string('tablename', 'local_blockdefaulters'), $record);
				/***************************Pass records to be blocked to Moodle Server for Exam*******************************************************/
					//	forward_data($record);
				/**********************************************************************************/
			}
			$tableData[] = array($name);
		}
		return $tableData;
	}

	function forward_unblocking_data($username)
	{
		$post_data = array('username' => $username);
		$result = post_request('http://10.10.21.10/unblock.php', $post_data);
	}

	function forward_data($record)
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
		$result = post_request('http://10.10.21.10/block.php', $post_data);
		//Display result
		//echo $result;
	}

	function post_request($url, $data) {
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

	function get_usernames($idArray)
	{
		global $DB;
		$str = implode(",", $idArray);
		$queryString = "SELECT id, username FROM mdl_user WHERE id IN(".$str.")";
		//echo $queryString;
		$result = $DB->get_records_sql($queryString);
		return $result;		
	}

	function unblock($usernames)
	{
		global $DB;
		$str="";
		//var_dump($usernames);
		foreach($usernames as $entry)
		{
			//echo $entry;
			$queryString = "SELECT * FROM mdl_".get_string('tablename', 'local_blockdefaulters')." WHERE username LIKE '".$entry->username."'";
			//$queryString = "SELECT id FROM mdl_".get_string('autotablename', 'local_blockdefaulters')." WHERE id =0";
			//echo $queryString;
			if($DB->record_exists_sql($queryString))
			{	
				$queryString = "username LIKE '".$entry->username."'";
				$DB->delete_records_select(get_string('tablename', 'local_blockdefaulters'), $queryString);
				/************************************************************/
				//forward_unblocking_data($entry->username);
				/************************************************************/
			}
		}
	}

	function defaulters($attObj,$courseId,$categoryId,$blockingDetails)
	{
	$insertFlag = 0;
	$idArray = array();
	$sessionId = array();
	$attId = array();
	$sessionId = array();
	$studentId = array();
	$statusId = array();
	$statusArray = array();
	$studentGrades = array();
	$studentMaxGrades = array();
	$studentAtt = array();
	$blockStudentIds = array();
	$safeStudentIds = array();
	$usernames = array();
	
	//retrieve parameters passed
	{		
		{
			//retreive courses from the category passed as the first parameter
			global $DB;
			$queryString = "SELECT id FROM mdl_course  WHERE category =".$categoryId;
			$result = $DB->get_records_sql($queryString);
			$idArray = array_keys($result);
			$res = $attObj->get_att_ids($idArray);
			
			foreach($res as $entry)
			{
				$attId[] = $entry->id;
				
			}
			
			if(count($attId) != 0)
			{
				//block users not satisfying the minimum criterion
				$sessions = $attObj->get_session_ids($attId, $blockingDetails->start_timestamp, $blockingDetails->stop_timestamp);
				
				foreach($sessions as $entry)
				{
					$sessionId[] = $entry->id;
				}
				if(count($sessionId) == 0)
				{
					//echo "<center><font color=\"red\">No records found.</font></center>";
				}
				else
				{
						$res = $attObj->get_student_status($sessionId);
						if(count($res) != 0)
						{
							foreach($res as $entry)
							{
								$studentId[] = $entry->studentid;
								$statusId[] = $entry->statusid;
							}
							
							foreach($statusId as $entry)
							{
								$res = $attObj->get_status_details($entry);
								$statusArray[$entry] = $res;
							}
							foreach($studentId as $keyStudent=>$entry)
							{
								$statusGrade = 0;
								foreach($statusArray as $key=>$val)
								{
									if($statusId[$keyStudent] == $key && ($statusArray[$key][$key]->acronym != "x" && $statusArray[$key][$key]->acronym != "X") && ($statusArray[$key][$key]->acronym != "c" && $statusArray[$key][$key]->acronym != "C"))
									{
										if(!isset($studentMaxGrades[$entry]))
											$studentMaxGrades[$entry] = 1;
										else
											$studentMaxGrades[$entry]++;
										foreach($val as $gradeObject)
										{
											$statusGrade = (int)$gradeObject->grade;
										}
									}/* work-around for a scenario if all sessions present on MOODLE for the student are X's.*/
									else if($statusId[$keyStudent] == $key)
									{
										$studentMaxGrades[$entry] = 1;
										$statusGrade=1;
										
										$studentGrades[$entry] = 0;
									}
								}
								if(!isset($studentGrades[$entry]))
									$studentGrades[$entry] = $statusGrade;
								else
									$studentGrades[$entry] += (int)$statusGrade;
							}
							foreach($studentGrades as $keyCurr=>$entry)
							{
								foreach($studentMaxGrades as $keyMax=>$val)
								{
									if($keyMax == $keyCurr)
									{
										$studentAtt[$keyCurr] = number_format(((float)$entry/(float)$val)*100,2);
									}
								}
							}
							foreach($studentAtt as $key => $entry)
							{
								if($entry < $blockingDetails->cutoff)//&& check current time)
								{//block
									$blockStudentIds[$key] = $entry;
								}
								else
								{//unblock
									$safeStudentIds[$key] = $entry;
								}
							}
							if(count($blockStudentIds) == 0)
							{
								//echo "<center><font color=\"red\">No defaulters found.</font></center>";
								$_SESSION['blocked'] = 0;
							}
							else
							{
								$_SESSION['blocked'] = 1;					
								$usernames = get_usernames(array_keys($blockStudentIds));
								$tableData = insertIntoTable($usernames, $blockingDetails->start_timestamp, $blockingDetails->stop_timestamp);
								$data = $tableData;
									foreach($tableData as $key=>$value)
									{
										$name = $value[0];
										foreach($usernames as $subkey=>$users)
										{
											if($users->username == $name)
											{
												$tableData[$key][] = $blockStudentIds[$subkey];
											}
										}
									}
								
									//display table containing blocked users
									$table = new html_table();
									//$table->tablealign = 'center';
									$table->attributes['class'] = 'generaltable';
									$table->head = array(get_string('studentsblocked', 'local_blockdefaulters'),get_string('perct','local_blockdefaulters'));
									$table->data =  $tableData;
									
									{
										if($insertFlag == 1)
											echo "<font color=\"green\">Students who do not meet the set criterion have been blocked from ".$blockFromArray['day']."-".$blockFromArray['month']."-".$blockFromArray['year']." ".$blockFromArray['hour'].":".$blockFromArray['minute']." to ".$blockTillArray['day']."-".$blockTillArray['month']."-".$blockTillArray['year']." ".$blockTillArray['hour'].":".$blockTillArray['minute']."</font>";
									}
									$insertFlag = 0;
									$_SESSION['tableData'] = $tableData;
									//echo html_writer::table($table);
								deletePastRecords();
							}
					
							//Display safe students
							if(count($safeStudentIds) != 0)
							{
								$safeStudentsData = array();
								$usernames = get_usernames(array_keys($safeStudentIds));
								
								//unblock previously blocked, now safe students
								unblock($usernames);
								
								
								foreach($usernames as $entry)
								{	
									$name=$entry->username;
									$safeStudentsData[] = array($name);
								}
								$data = $safeStudentsData;
								foreach($safeStudentsData as $key=>$value)
								{
									$name = $value[0];
									foreach($usernames as $subkey=>$users)
									{
										if($users->username == $name)
										{
											$safeStudentsData[$key][] = $safeStudentIds[$subkey];
											//unblock safe students if blocked
										}
									}
								}
								
								
								
								$table = new html_table();
								//$table->tablealign = 'center';
								$table->attributes['class'] = 'generaltable';
								$table->head = array(get_string('studentssafe', 'local_blockdefaulters'),get_string('perct','local_blockdefaulters'));
								$table->data =  $safeStudentsData;
								$_SESSION['safeStudentsData'] = $safeStudentsData;
								//echo html_writer::table($table);
								
								//$downloadForm = new Download_Form();
								//Download_Form::set_category($mform->get_category());
								//$downloadForm->display();
							}
						}
						else
						{
							//echo "<center><font color=\"red\">No session has been conducted.</font></center>";
						}
				}
			}
			else
			{
				echo "<center><font color=\"red\">No attendance record found.</font></center>";
			}
		}
	}
}
?>