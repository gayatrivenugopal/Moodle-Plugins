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
 * file index.php
 * index page to view block users page.
 */

//$attId - array that contains attendance IDs
//$sessionId - array that contains session Ids
//$statusArray - array that contains grades of all the status(es) of students

require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once('category_form.class.php');
require_once('view_log_form.class.php');
require_once('attendance.class.php');

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
		$attObj=  new Attendance();
		$this->result = $attObj->get_courses($_SESSION['categ']);
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
		//$select = $this->mform->addElement('select', 'minatt', get_string('mincriterion','local_attendance'), $this->perct);
		//$select->setSelected(74);
		
		$this->mform->addElement('date_time_selector', 'considerfrom', get_string('deletesessionsfrom','local_attendance'));
		$this->mform->addElement('date_time_selector', 'considertill', get_string('deletesessionstill','local_attendance'));
		
		//$this->mform->addElement('date_time_selector', 'blockfrom', get_string('blockfrom','local_attendance'));
		//$this->mform->addElement('date_time_selector', 'blocktill', get_string('blocktill','local_attendance'));
		$this->mform->addElement('text', 'reason', get_string('reason_deletion','local_attendance'));
		
		$this->mform->addElement('submit', 'block', get_string('delete_sessions','local_attendance'));
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

$PAGE->set_title(get_string('pluginname', 'local_attendance'));
$PAGE->set_heading(get_string('delete_sessions', 'local_attendance'));

echo $OUTPUT->header();
$categoryForm = new Category_Form();
$mform = new Settings_Form();
$logform = new View_Log_Form();
$attObj = new Attendance();

if($logform->get_data() == null)
	echo $OUTPUT->heading(get_string('delete_sessions', 'local_attendance'));


if($categoryForm->get_data() == null && $mform->get_data() == null && $logform->get_data() == null)
{
	$_SESSION['categ'] = null;
	$categoryForm->display();
}
else if($logform->get_data() != null)
{
	echo $OUTPUT->heading(get_string('deleted_sessions', 'local_attendance'));
	$table = new html_table();
	$tableData = array();
	$table->attributes['class'] = 'generaltable';
	$table->head = array("Course","Session Details","Deleted On","Deleted By","Reason");
	$logDetails = $attObj->get_deleted_blank_sessions_log();
	foreach($logDetails as $logRecord)
	{
		$tableData[] = array($attObj->get_course_from_id($logRecord->courseid), date("d-F-Y h:i",$logRecord->sessdate), 
		date("d-F-Y h:i",$logRecord->deletedon), $logRecord->deletedby, $logRecord->reason);
	}
	
	$table->data =  $tableData;
	echo "<center>".html_writer::table($table)."</center>";
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
			
			$res = $attObj->get_att_ids($idArray);
			
			foreach($res as $entry)
			{
				$attId[] = $entry->id;
				
			}
			if(count($attId) != 0)
			{
				//block users not satisfying the minimum criterion
				$sessions = $attObj->get_session_ids($attId, $considerfrom, $considerto);
				
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
					echo "<center><font color=\"red\">No records found.</font></center>";
				}
				else
				{
					//search the attendance log for each session
					$error = 0;
					$successfullyDeletedSession = array();
					$blankSessions = 1;
					foreach($sessionId as $id)
					{
						$idArray = array($id);
						$res = $attObj->get_student_status($idArray);
						if(count($res) == 0)
						{
							//Blank Session Found
							//get attendance ID for this session
							//$attendanceId = $attObj->get_att_id($id);
							//delete record from mdl_attendance_sessions and add entry in blank_sessions_deleted_log
							if($attObj->delete_blank_session($id, $mform->get_data()->reason) == false)
							{
								$error = 1;
							}
							else
							{
								//get details of sessions deleted, with the course names
								$successfullyDeletedSession[] = $attObj->get_lastrecord_in_deleted_sessions_log();
							}
						}
						else
						{
							$blankSessions = 0;
						}
					}
					if($blankSessions == 0)
						echo "<center><font color=\"red\">Could not find any blank session!</font></center>";
					if(count($successfullyDeletedSession) != 0)
					{
						echo "<center><font color=\"green\">The following sessions have been deleted<br/><br/></font>";
						$tableData = array();
						
						foreach($successfullyDeletedSession as $recordArray)
						{
							foreach($recordArray as $record)
							{
								$tableData[] = array($attObj->get_course_from_id($record->courseid), 
								date("d-F-Y h:i",$record->sessdate));
							}
						}
						$table = new html_table();
						$table->attributes['class'] = 'generaltable';
						$table->head = array("Course","Session Details");
						$table->data =  $tableData;
						echo html_writer::table($table);
						echo "</center>";
					}
					if($error == 1)
					{
						echo "<center><font color=\"red\">Could not delete all blank sessions. Please try again!</font></center>";
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
if(is_siteadmin() && $logform->get_data() == null)
	{
		if(!($attObj->is_empty_deleted_blank_sessions_log()))
		{ 
			echo "<br/><br/>";
			$logform->display();
		}
	}
echo $OUTPUT->footer();
?>
