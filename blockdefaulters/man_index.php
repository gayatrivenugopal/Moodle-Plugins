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

require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once('download_form.class.php');
require_once('category_form.class.php');

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
		
		$select = $this->mform->addElement('select', 'courses', get_string('courses','local_blockdefaulters'), $courses);
		$select->setMultiple(true);
		$select->setSelected(0);
		$select->setSize(count($courses));
		$this->perct = range(1,100);
		foreach($this->perct as $key)
		{
			$this->perct[$key-1].="%";
		}
		$select = $this->mform->addElement('select', 'minatt', get_string('mincriterion','local_blockdefaulters'), $this->perct);
		$select->setSelected(74);
		
		$this->mform->addElement('date_time_selector', 'considerfrom', get_string('considerfrom','local_blockdefaulters'));
		$this->mform->addElement('date_time_selector', 'considertill', get_string('considertill','local_blockdefaulters'));
		
		$this->mform->addElement('date_time_selector', 'blockfrom', get_string('blockfrom','local_blockdefaulters'));
		$this->mform->addElement('date_time_selector', 'blocktill', get_string('blocktill','local_blockdefaulters'));
		
		$this->mform->addElement('submit', 'block', get_string('blockdefaulters','local_blockdefaulters'));
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
			//echo $queryString;
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
		//echo $from."<br>".$to."<br/>";
		$queryString = "SELECT * FROM mdl_attendance_sessions WHERE attendanceid IN(".$str.") AND sessdate BETWEEN ".$from." AND ".$to;
		//echo $queryString;
		$result = $DB->get_records_sql($queryString);
		return $result;		
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

function get_att_ids($idArray)
{
		global $DB;
		$str = implode(",", $idArray);
		$queryString = "SELECT * FROM mdl_attforblock WHERE course IN(".$str.")";
		//echo $queryString;
		$result = $DB->get_records_sql($queryString);
		return $result;		
}

function get_student_status($sessionArray)
{
		global $DB;
		$str = implode(",", $sessionArray);
		$queryString = "SELECT * FROM mdl_attendance_log WHERE sessionid IN(".$str.")";
		//echo $queryString;
		$result = $DB->get_records_sql($queryString);
		return $result;		
}
function get_status_details($statusId)
{
		global $DB;
		$queryString = "SELECT * FROM mdl_attendance_statuses WHERE id = ".$statusId;
		//echo $queryString;
		$result = $DB->get_records_sql($queryString);
		return $result;		
}

function deletePastRecords()
{
	global $DB;
	$query = "stop_timestamp < ".time();
	$DB->delete_records_select(get_string('tablename', 'local_blockdefaulters'), $query);
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
		//$where = "username LIKE '".$name."' AND start_timestamp = ".$from." AND stop_timestamp = ".$to;
	//	echo $queryString;
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
			$DB->insert_record(get_string('tablename', 'local_blockdefaulters'), $record);
			/***************************Pass records to be blocked to Moodle Server for Exam*******************************************************/
		//			forward_data($record);
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


$PAGE->set_title(get_string('blockdefaulters', 'local_blockdefaulters'));
$PAGE->set_heading(get_string('blockdefaulters', 'local_blockdefaulters'));

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('blockdefaulters', 'local_blockdefaulters'));

$categoryForm = new Category_Form();
$downloadForm = new Download_Form();
$mform = new Settings_Form();

if($categoryForm->get_data() == null && $mform->get_data() == null && $downloadForm->get_data() == null)
{
	$_SESSION['categ'] = null;
	$categoryForm->display();
}
else if($categoryForm->get_data() != null)
{
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
	
	if($considerto > $considerfrom)
	{
		$_SESSION['considerFrom'] = $considerFromArray['day']."-".$considerFromArray['month']."-".$considerFromArray['year'];
		$_SESSION['considerTill'] = $considerTillArray['day']."-".$considerTillArray['month']."-".$considerTillArray['year'];
		if(optional_param_array('courses', null, PARAM_TEXT) == null)
		{
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
						if(ceil($entry) < $mform->get_data()->minatt+1)
						{
							$blockStudentIds[$key] = $entry;
						}
						else
						{
							$safeStudentIds[$key] = $entry;
						}
					}
					if(count($blockStudentIds) == 0)
					{
						echo "<center><font color=\"red\">No defaulters found.</font></center>";
						$_SESSION['blocked'] = 0;
					}
					else
					{
						$_SESSION['blocked'] = 1;					
						$usernames = get_usernames(array_keys($blockStudentIds));
					
						$tableData = insertIntoTable($usernames, $blockfrom, $blockto);
						
						
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
							if($blockto < time() || $blockto < $blockfrom)
							{
								echo "<center><font color=\"red\">Please select a valid duration.</font></center>";
							}
							else
							{
								if($insertFlag == 1)
									echo "<font color=\"green\">Students who do not meet the set criterion have been blocked from ".$blockFromArray['day']."-".$blockFromArray['month']."-".$blockFromArray['year']." ".$blockFromArray['hour'].":".$blockFromArray['minute']." to ".$blockTillArray['day']."-".$blockTillArray['month']."-".$blockTillArray['year']." ".$blockTillArray['hour'].":".$blockTillArray['minute']."</font>";
							}
							$insertFlag = 0;
							$_SESSION['tableData'] = $tableData;
							echo html_writer::table($table);
						deletePastRecords();
					}
					
							//Display safe students
							if(count($safeStudentIds) != 0)
							{
								$safeStudentsData = array();
								$usernames = get_usernames(array_keys($safeStudentIds));
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
										}
									}
								}
								$table = new html_table();
								//$table->tablealign = 'center';
								$table->attributes['class'] = 'generaltable';
								$table->head = array(get_string('studentssafe', 'local_blockdefaulters'),get_string('perct','local_blockdefaulters'));
								$table->data =  $safeStudentsData;
								$_SESSION['safeStudentsData'] = $safeStudentsData;
								echo html_writer::table($table);
								
								$downloadForm = new Download_Form();
								//Download_Form::set_category($mform->get_category());
								$downloadForm->display();
							}
						}
						else
						{
							echo "<center><font color=\"red\">No session has been conducted.</font></center>";
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
else if($downloadForm->get_data() != null)
{
		$categoryCompleteList = array_values($_SESSION['categoryList']);
		$categoryList = array();
		
		foreach($_SESSION['categoryIndex'] as $entry)
		{
			$categoryList[] = $categoryCompleteList[$entry];
		}
		$categoryString = implode(",",$categoryList);
		
		$filename = $categoryString."_".$_SESSION['considerFrom']."to".$_SESSION['considerTill'];//date('h_i_s',time());
		$format = "text";
		if($downloadForm->get_data()->format == Download_Form::EXCEL)
		{
			$format = "excel";
		}
		else if($downloadForm->get_data()->format == Download_Form::OPENOFFICE)
		{
			$format = "ods";
		}
		if($format == "ods" || $format == "excel")
		{
			exportToTable($filename, $format);
		}
		else
		{
			exportToText($filename);
		}
}
echo $OUTPUT->footer();

function exportToTable($filename, $format) {
	global $CFG;

	$filename=str_replace(" ","_",$filename);
    if ($format === 'excel')
	{
	    $filename .= ".xls";	    
    }
	else
	{	   
	    $filename .= ".ods";
    }
	 
	header("Location:$CFG->wwwroot/local/blockdefaulters/download.php?format=".$format."&filename=".$filename);
}


function exportToText($filename)
{
	global $CFG;
	$filename=str_replace(" ","_",$filename);
	$filename .= ".txt";
	($fp = fopen($filename, "w")) or die("error");
	fputcsv($fp,array("PRN","Attendance"));
	if($_SESSION['blocked'] == 0)
	{
		foreach($_SESSION['safeStudentsData'] as $value)
		{
			$value[1].="%";
			fputcsv($fp,$value);
		}
	}
	else
	{
		foreach($_SESSION['safeStudentsData'] as $value)
		{
			$value[1].="%";
			fputcsv($fp,$value);
		}
		foreach($_SESSION['tableData'] as $value)
		{
			$value[1].="%";
			fputcsv($fp,$value);
		}	
	}
	fclose($fp);	
	header("Location:$CFG->wwwroot/local/blockdefaulters/download.php?format=text&filename=".$filename);
}
?>
