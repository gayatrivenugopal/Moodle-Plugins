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
require_once('calculate.class.php');
require_once('aggregate_form.class.php');

ob_start();
session_start();
$timezone = "Asia/Calcutta";
if(function_exists('date_default_timezone_set')) date_default_timezone_set($timezone);

class Settings_Form extends moodleform {
	private $coursetable = "mdl_course";
	private $courseId;
	private $perct;
	private $id;
	private $mform;
	private $resultFlag;
	
    public function definition() {
        global $CFG;
        $this->mform = $this->_form;
		$this->id = $_SESSION['categ'];
		$this->set_elements();
    }
	
	public function set_elements()
	{
		//get list of courses (fullname and idnumber)
		$courses = array();
		$this->courseId = array();
		$categForm = new Category_Form();
		$this->result = $this->get_courses();
		if(count($this->result) != 0)
		{
			$this->set_results(1);
			foreach($this->result as $entry)
			{
				$courses[] = $entry->idnumber." ".$entry->fullname;
				$this->courseId[] = $entry->id;
			}
		}
		else
		{
			$this->set_results(0);
		}
		
		$select = $this->mform->addElement('select', 'courses', get_string('courses','local_attendance'), $courses);
		$select->setMultiple(true);
		$select->setSelected(0);
		$select->setSize(count($courses));
		$this->perct = range(1,100);
		foreach($this->perct as $key)
		{
			$this->perct[$key-1].="%";
		}
		
		$this->mform->addElement('date_time_selector', 'considerfrom', get_string('considerfrom','local_attendance'));
		$this->mform->addElement('date_time_selector', 'considertill', get_string('considertill','local_attendance'));
		
		$this->mform->addElement('date_time_selector', 'programmestart', "Programme started on");
		
		$this->mform->addElement('submit', 'block', "View Defaulters");
	}
	
	public function get_id($index)
	{
		return $this->courseId[$index];
	}
	
	public function set_results($flag)
	{
		$this->resultFlag = $flag;
	}
	
	public function get_results($flag)
	{
		return $this->resultFlag;
	}
	
	public function get_courses()
	{
		global $DB;
		if($this->id == null)
		{
			return array();
		}
		else
		{
			if(count($this->id)>1)
				$queryString = "SELECT * FROM ".$this->coursetable." WHERE category IN(".implode(",",$this->id).")";
			else
				$queryString = "SELECT * FROM ".$this->coursetable." WHERE category =".$this->id[0];
			$result = $DB->get_records_sql($queryString);
			if($result == null)
			{
				return array();
			}
			else
			{
				return $result;		
			}
		}
	}
}

$insertFlag = 0;
function get_session_ids($attIds, $from, $to)
{
		global $DB;
		$str = implode(",", $attIds);
		$queryString = "SELECT * FROM mdl_attendance_sessions WHERE attendanceid IN(".$str.") AND sessdate BETWEEN ".$from." AND ".$to;
		$result = $DB->get_records_sql($queryString);
		return $result;		
}

function get_usernames($idArray)
{
		global $DB;
		$str = implode(",", $idArray);
		$queryString = "SELECT id, username, firstname, lastname FROM mdl_user WHERE id IN(".$str.")";
		$result = $DB->get_records_sql($queryString);
		return $result;		
}

function get_att_ids($idArray)
{
		global $DB;
		$str = implode(",", $idArray);
		$queryString = "SELECT * FROM mdl_attforblock WHERE course IN(".$str.")";
		$result = $DB->get_records_sql($queryString);
		return $result;		
}

function get_student_status($sessionArray)
{
		global $DB;
		$str = implode(",", $sessionArray);
		$queryString = "SELECT * FROM mdl_attendance_log WHERE sessionid IN(".$str.")";
		$result = $DB->get_records_sql($queryString);
		return $result;		
}
function get_status_details($statusId)
{
		global $DB;
		$queryString = "SELECT * FROM mdl_attendance_statuses WHERE id = ".$statusId;
		$result = $DB->get_records_sql($queryString);
		return $result;		
}

function deletePastRecords()
{
	global $DB;
	$query = "stop_timestamp < ".time();
	$DB->delete_records_select(get_string('tablename', 'local_attendance'), $query);
	/***************************Pass records to be blocked to Moodle Server for Exam*******************************************************/
				//	forward_data();
	/**********************************************************************************/
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
		$queryString = "SELECT COUNT(*) FROM mdl_blockdefaulters WHERE username LIKE '".$name."' AND start_timestamp = ".$from." AND stop_timestamp = ".$to;
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
					forward_data($record);
			/**********************************************************************************/
		}
		$tableData[] = array($name);
	}
	return $tableData;
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
// Send request
$result = post_request('http://10.10.21.10/block.php', $post_data);
}

function post_request($url, $data) {
 
    // Convert the data array into URL Parameters like a=b&foo=bar etc.
    $data = http_build_query($data);
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
	// close the socket connection:
    fclose($fp);
    // split the result header from the content
    $result = explode("\r\n\r\n", $result, 2);
	return $result[1];
}


$PAGE->set_title(get_string('blockdefaulters', 'local_attendance'));
$PAGE->set_heading(get_string('blockdefaulters', 'local_attendance'));

echo $OUTPUT->header();


$categoryForm = new Category_Form();
$downloadForm = new Download_Form();
$aggregateForm = new Aggregate_Form();
$mform = new Settings_Form();

if($categoryForm->get_data() == null && $aggregateForm->get_data() == null && $mform->get_data() == null && $downloadForm->get_data() == null)
{
	echo $OUTPUT->heading(get_string('blockdefaulters', 'local_attendance'));
	$_SESSION['categ'] = null;
	$categoryForm->display();
}
else if($categoryForm->get_data() != null)
{
	
	echo $OUTPUT->heading(get_string('blockdefaulters', 'local_attendance'));
	$pos = $categoryForm->get_data()->programme;
	$_SESSION['categoryIndex'] = $pos;
	$categoryIds = array();
	
	foreach($pos as $entry)
	{
		$categoryIds[] = $categoryForm->get_key($entry);
	}
	$_SESSION['categ'] = $categoryIds;
	
	$mform = new Settings_Form();
	if($mform->get_results() == 1)
	{
		$_SESSION['categoryList'] = $categoryForm->get_list();
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
	$usernames = array();
	
	//retrieve parameters passed
	$considerFromArray = optional_param_array('considerfrom', null, PARAM_TEXT);
	$considerTillArray = optional_param_array('considertill', null, PARAM_TEXT);
	$programmeStartArray = optional_param_array('programmestart', null, PARAM_TEXT);
	$blockFromArray = optional_param_array('blockfrom', null, PARAM_TEXT);
	$blockTillArray = optional_param_array('blocktill', null, PARAM_TEXT);
	$considerfrom = make_timestamp($considerFromArray['year'], $considerFromArray['month'], $considerFromArray['day'], $considerFromArray['hour'], $considerFromArray['minute']);
	$considerto = make_timestamp($considerTillArray['year'], $considerTillArray['month'], $considerTillArray['day'], $considerTillArray['hour'], $considerTillArray['minute']);
	$programmeStart = make_timestamp($programmeStartArray['year'],$programmeStartArray['month'], $programmeStartArray['day'], $programmeStartArray['hour'], $programmeStartArray['minute']);
	$blockfrom = make_timestamp($blockFromArray['year'], $blockFromArray['month'], $blockFromArray['day'], $blockFromArray['hour'], $blockFromArray['minute']);
	$blockto = make_timestamp($blockTillArray['year'], $blockTillArray['month'], $blockTillArray['day'], $blockTillArray['hour'], $blockTillArray['minute']);
	
	if($considerto > $considerfrom && $programmeStart <= $considerfrom)
	{
		$_SESSION['considerFrom'] = $considerFromArray['day']."-".$considerFromArray['month']."-".$considerFromArray['year'];
		$_SESSION['considerTill'] = $considerTillArray['day']."-".$considerTillArray['month']."-".$considerTillArray['year'];
		
		if(optional_param_array('courses', null, PARAM_TEXT) == null)
		{
			echo $OUTPUT->heading(get_string('blockdefaulters', 'local_attendance'));
			$mform->display();
			echo "<center><font color=\"red\">Please select one or more courses.</font></center>";
		}
		else
		{
			foreach($position as $pos)
			{
				$idArray[] = $mform->get_id($pos);
			}
			
			$res = get_att_ids($idArray);
			
			foreach($res as $entry)
			{
				$attId[] = $entry->id;
				
			}
			if(count($attId) != 0)
			{
				//block users not satisfying the minimum criterion
				$sessions = get_session_ids($attId, $considerfrom, $considerto);
				
				foreach($sessions as $entry)
				{
					$sessionId[] = $entry->id;
				}
				print_object($c);
				if(count($sessionId) == 0)
				{
					echo $OUTPUT->heading(get_string('blockdefaulters', 'local_attendance'));
					$mform->display();
					echo "<center><font color=\"red\">No records found.</font></center>";
				}
				else
				{
					$res = get_student_status($sessionId);
					
					if(count($res) != 0)
					{
						foreach($res as $entry)
						{ 
							$studentId[] = $entry->studentid;
							$statusId[] = $entry->statusid;
						}
						
						foreach($statusId as $entry)
						{
							$res = get_status_details($entry);
							$statusArray[$entry] = $res;
						}
						foreach($studentId as $keyStudent=>$entry)
						{
							$statusGrade = 0;
					
							foreach($statusArray as $key=>$val)
							{
								if($statusId[$keyStudent] == $key && ($statusArray[$key][$key]->acronym != "x" && $statusArray[$key][$key]->acronym != "X") && ($statusArray[$key][$key]->acronym != "c" && $statusArray[$key][$key]->acronym != "C"))
								{
									if(!($studentMaxGrades[$entry]))
										$studentMaxGrades[$entry] = 1;
									else
										$studentMaxGrades[$entry]++;
										
										
									foreach($val as $gradeObject)
									{
										$statusGrade = (int)$gradeObject->grade;
									}
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
									$studentAtt[$keyCurr] = number_format(((float)$entry/(float)$val)*100,2);
							}
						}
						foreach($studentAtt as $key => $entry)
						{
						/*********************To find students with TNG************************************/
							if(ceil($entry) < 75)
							{
								{
									$blockStudentIds[$key] = $entry;
								}
							}
						}
						if(count($blockStudentIds) == 0)
						{
							//DO NOTHING
						}
						else
						{
							echo "<center><font color='#FF6200'><h1>You are requested to click on the button below to find defaulters according to the attendance calculation policy of SICSR</h1></font></center>";
							$aggregateForm->display();
							$start = strtotime("+1 minute",$considerto);
							
							if($considerTillArray['day'] == 15)
							{
								switch($considerTillArray['month'])
								{
									case 1:
									case 3:
									case 5:
									case 7:
									case 8:
									case 10:
									case 12:
											$close = strtotime("+15 days",$start);
											
											break;
									case 2:
											if((($considerTillArray['year'] % 4) == 0) && ((($considerTillArray['year'] % 100) != 0) || (($considerTillArray['year'] %400) == 0)))
												$close = strtotime("+13 days",$start);
											else
												$close = strtotime("+12 days",$start);
											break;
									default:
											$close = strtotime("+14 days",$start);
								}
							}
							else
								$close =  strtotime("+14 days",$start);
						
							$_SESSION['blockedStudentIds'] = $blockStudentIds;
							$_SESSION['considerto'] = $considerto;
							$_SESSION['programmeStart'] = $programmeStart;
							$_SESSION['start'] = $start;
							$_SESSION['close'] = $close;
							$_SESSION['idArray'] = $idArray;
						}
						
								
								
							$blockStudentsData = array();
							$fullnames = array();
							$usernames = get_usernames(array_keys($blockStudentIds));
							
							
							$quizzes = get_quizzes($start, $close);
							$tngList = get_defaulters_attempted_quiz($quizzes, $usernames, $blockStudentIds);
							if(count($tngList) > 0)
							{
								echo "<center><h3>Defaulters and Quiz Attempted</h3></center>";
								$table = new html_table();
								//$table->tablealign = 'center';
								$table->attributes['class'] = 'generaltable';
								$table->head = array("PRN","Name","Attendance during the specified period", "Quiz Attempted", "Grades");
								asort($tngList);
								$table->data =  $tngList;
								echo html_writer::table($table);
							}
							foreach($usernames as $entry)
							{	
								$name=$entry->username;
								$blockStudentsData[] = array($name);
								$fullnames[] = $entry->firstname." ".$entry->lastname;
							}
							$data = $blockStudentsData;
							$i = 0;
							foreach($blockStudentsData as $key=>$value)
							{
								$name = $value[0];
								foreach($usernames as $subkey=>$users)
								{
									if($users->username == $name)
									{
										$blockStudentsData[$key][] = $fullnames[$i++];
										$blockStudentsData[$key][] = $blockStudentIds[$subkey];
									}
								}
							}
					}
					else
					{
						echo $OUTPUT->heading(get_string('blockdefaulters', 'local_attendance'));
						$mform->display();
						echo "<center><font color=\"red\">No session has been conducted.</font></center>";
					}
				}
			}
			else
			{
				echo $OUTPUT->heading(get_string('blockdefaulters', 'local_attendance'));
				$mform->display();
				echo "<center><font color=\"red\">No attendance record found.</font></center>";
			}
		}
	}
	else
	{
		echo $OUTPUT->heading(get_string('blockdefaulters', 'local_attendance'));
		$mform->display();
		echo "<center><font color=\"red\">Please select a valid duration.</font></center>";
	}
}
else if($aggregateForm->get_data() != null)
{
	$idArray = $_SESSION['idArray'];							
	$considerfrom = $_SESSION['programmeStart'];
	$considerto = $_SESSION['considerto'];
	$sessionId = array();
	$studentId = array();
	$statusId = array();
	$statusArray = array();
	$res = get_att_ids($idArray);
			
	foreach($res as $entry)
	{
		$attId[] = $entry->id;
		
	}
	if(count($attId) != 0)
	{
		//block users not satisfying the minimum criterion
		$sessions = get_session_ids($attId, $considerfrom, $considerto);
		
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
			$res = get_student_status($sessionId);
			
			if(count($res) != 0)
			{
				foreach($res as $entry)
				{ 
					$studentId[] = $entry->studentid;
					$statusId[] = $entry->statusid;
				}
				
				foreach($statusId as $entry)
				{
					$res = get_status_details($entry);
					$statusArray[$entry] = $res;
				}
				foreach($studentId as $keyStudent=>$entry)
				{
					$statusGrade = 0;
			
					foreach($statusArray as $key=>$val)
					{
						if($statusId[$keyStudent] == $key && ($statusArray[$key][$key]->acronym != "x" && $statusArray[$key][$key]->acronym != "X") && ($statusArray[$key][$key]->acronym != "c" && $statusArray[$key][$key]->acronym != "C"))
						{
							if(!($studentMaxGrades[$entry]))
								$studentMaxGrades[$entry] = 1;
							else
								$studentMaxGrades[$entry]++;
								
								
							foreach($val as $gradeObject)
							{
								$statusGrade = (int)$gradeObject->grade;
							}
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
							$studentAtt[$keyCurr] = number_format(((float)$entry/(float)$val)*100,2);
					}
				}
				
				foreach($studentAtt as $key => $entry)
				{
					if(ceil($entry) < 75 && in_array($key,array_keys($_SESSION['blockedStudentIds'])))
					{
						$defaulters[$key] = $entry;
					}
				}
			
						if(count($defaulters) != 0)
						{
							$defaultersList = array();
							$fullnames = array();
							$usernames = get_usernames(array_keys($defaulters));							
							$quizzes = get_quizzes($_SESSION['start'], $_SESSION['close']);
							$tngList = get_defaulters_attempted_quiz($quizzes, $usernames, $defaulters);
							if(count($tngList) > 0)
							{
								echo "<center><h3>Defaulters and Quiz Attempted</h3></center>";
								$table = new html_table();
								//$table->tablealign = 'center';
								$table->attributes['class'] = 'generaltable';
								$table->head = array("PRN","Name","Consolidated Attendance Percentage", "Quiz Attempted", "Grades");
								asort($tngList);
								$table->data =  $tngList;
								echo html_writer::table($table);
							}
						}
						else
						{
							echo "<center><font color=\"red\">None of the defaulters have appeared for the tests conducted in the specified period.</font></center>";
						}
			}
		}
	}
	else
	{
		echo "<center><font color=\"red\">No attendance record found.</font></center>";
	}
}
echo $OUTPUT->footer();

function get_quizzes($start, $close)
{
	global $DB;
	$queryString = "SELECT id, name FROM mdl_quiz WHERE timeopen >= $start && timeopen <= $close";
	$result = $DB->get_records_sql($queryString);
	return $result;		
}

function get_defaulters_attempted_quiz($quizzes, $usernames, $blockStudentIds)
{
	global $DB;
	$tngList = array();
	foreach($usernames as $user)
	{
		foreach($quizzes as $quiz)
		{
			$queryString = "SELECT userid FROM mdl_quiz_attempts WHERE userid = ".$user->id." && quiz = ".$quiz->id;
			$result = $DB->get_records_sql($queryString);
			if(count($result) > 0)
			{
				$queryString = "SELECT grade FROM mdl_quiz_grades WHERE userid = ".$user->id." && quiz = ".$quiz->id;
				$grades = current(current($DB->get_records_sql($queryString)));
				$tngList[] = array($user->username, $user->firstname." ".$user->lastname, $blockStudentIds[$user->id], $quiz->name, number_format($grades,3));
			}
		}
	}
	return $tngList;	
}


function low_consolidated_attendance($considerto, $considerfrom, $idArray, $studentEntry)
{
	global $DB;
	$sessionId = array();
	$attId = array();
	$sessionId = array();
	$studentId = array();
	$statusId = array();
	$statusArray = array();
	$studentGrades = array();
	$studentMaxGrades = array();
	$studentAtt = array();
	
	$res = get_att_ids($idArray);
			
	foreach($res as $entry)
	{
		$attId[] = $entry->id;
		
	}
	if(count($attId) != 0)
	{
		//block users not satisfying the minimum criterion
		$sessions = get_session_ids($attId, $considerfrom, $considerto);

		foreach($sessions as $entry)
		{
			$sessionId[] = $entry->id;
		}
		//echo "<font size=\"1\" color=\"white\">";
		//$c=array();
		print_object($c);
		//echo "</font>";
		if(count($sessionId) == 0)
		{
			//DO NOTHING
		}
		else
		{
			$res = get_student_status($sessionId);
			if(count($res) != 0)
			{
				foreach($res as $entry)
				{ 
					$studentId[] = $entry->studentid;
					$statusId[] = $entry->statusid;
				}	
				
				foreach($statusId as $entry)
				{
					$res = get_status_details($entry);
					$statusArray[$entry] = $res;
				}
				
				
				foreach($studentId as $keyStudent=>$entry)
				{
					if($entry == $studentEntry)
					{
						
						$statusGrade = 0;
				
						foreach($statusArray as $key=>$val)
						{
							if($statusId[$keyStudent] == $key && ($statusArray[$key][$key]->acronym != "x" && $statusArray[$key][$key]->acronym != "X") && ($statusArray[$key][$key]->acronym != "c" && $statusArray[$key][$key]->acronym != "C"))
							{
								if(!($studentMaxGrades[$studentEntry]))
									$studentMaxGrades[$studentEntry] = 1;
								else
									$studentMaxGrades[$studentEntry]++;
									
									
								foreach($val as $gradeObject)
								{
									$statusGrade = (int)$gradeObject->grade;
								}
							}
						}
						if(!isset($studentGrades[$studentEntry]))
							$studentGrades[$studentEntry] = $statusGrade;
						else
							$studentGrades[$studentEntry] += (int)$statusGrade;
						//break;
					}
				} 
				
				foreach($studentGrades as $keyCurr=>$entry)
				{	
					foreach($studentMaxGrades as $keyMax=>$val)
					{
						if($keyMax == $keyCurr)
							$studentAtt[$keyCurr] = number_format(((float)$entry/(float)$val)*100,2);
					}
				}
				foreach($studentAtt as $key => $entry)
				{
				/*********************To find students with TNG************************************/
					if(ceil($entry) < 75)
					{ 
						return $entry;
					}
					else	
						return false;
				}
				return false;
			}
			return false;
		}
		return false;
	}
	return false;
}
?>
