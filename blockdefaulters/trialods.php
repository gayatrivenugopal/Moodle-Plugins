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
 
include "ods.php";
define('PROG_CODE_KEY','B');
define('PRN_COLUMN_KEY','B');
define('DIVISION_KEY','D');
define('COURSE_CODE_INDEX',4);
define('HEADING_INDEX',5);
define('STUDENT_LIST_START',6);


/**
 * We unpacking the *.ods files ZIP arhive to find content.xml path
 * 
 * @var String File path of the *.ods file
 * @var String Directory path where we choose to store the unpacked files must have write permissions
 */
 class ImportODS
 {
	private $attId;
	private $courseId;
	
	private $attendanceId;
	private $courseColumns;
	private $courseCodes;
	private $dates;
	private $programmeCode;
	private $idFromCourse = array(); //contains ids(PKs) of all courses found in sheet
	private $enrollmentId = array(); //contains ids(PKs) that indicate enrollment ID that will be used while enrolling to a course
	//private $userId = array(); //contains ids(PKs) that indicate the user IDs of the users enrolled in a course
	private $userId;
	private $attendanceId;
	function parse_file($filename)
	{
		//$path = easy_ods_read::extract_content_xml($filename,"ods");
		//$easy_ods_read = new easy_ods_read(0,$path);
		//$this->attId = $_POST['attid'];
		$this->courseId = $_POST['courseid'];
		echo "here".$this->courseId;
		//$obj = ($easy_ods_read->extract_data("1","20"));
		//var_dump($obj);
		//$this->programmeCode =  $this->get_programme_code($obj);
		//$this->retrieve_course_codes($obj);
		//$this->retrieve_dates($obj);
	
		//$this->courseCodes = array_unique($this->courseCodes);
		//$this->read_student_records($obj);
		//$this->add_attendance_session_in_moodle($obj);
	}
	
	function add_attendance_session_in_moodle($obj)
	{
		$connection = mysqli_connect('localhost','moodle','M00dle123','moodle'); 
		
		echo $this->attId;
		echo $this->courseId;
		//$this->get_id_from_course($obj, $connection);
		//$this->get_enrollment_id_from_course($obj, $connection);
		
		mysql_close($connection);
	}
	
	function get_enrollment_id_from_course($obj, $connection)
	{
		
		foreach($this->enrollmentId as $id)
		{	
			$userId = mysqli_query($connection, "SELECT userid FROM mdl_user_enrolments WHERE enrolid =".(int)$id);
			while($row = mysqli_fetch_array($userId))
			{
				$this->enrollmentId[] = $row['userid']."<BR>";
			}
		}
		
		foreach($this->idFromCourse as $id)
		{
			$this->enrollmentId = array();
			
			echo "Course ID : $id";
			$enrollId = mysqli_query($connection, "SELECT id FROM mdl_enrol WHERE courseid =".(int)$id);
			while($row = mysqli_fetch_array($enrollId))
			{
				$this->enrollmentId[] = $row['id']."<BR>";
				//echo  $row['id']."<br>" ;
				//echo $this->enrollmentId[count($this->enrollmentId)-1];
			}
			//var_dump($this->enrollmentId);
			foreach($this->enrollmentId as $id)
			{echo " enrollment id : $id";
				$userId = mysqli_query($connection, "SELECT userid FROM mdl_user_enrolments WHERE enrolid =".(int)$id);
				while($row = mysqli_fetch_array($userId))
				{
					$userId = $row['userid'];
					break;
					//$this->userId[] = $row['userid']."<BR>";
				}
			}
			echo " user id : $userId";
			$statusId = array();
			$statusIdResult = mysqli_query($connection, "SELECT statusid FROM mdl_attendance_log WHERE studentid =".(int)$userId);
				while($row = mysqli_fetch_array($statusIdResult))
				{
					$statusId[] = $row['statusid'];
					//break;
					//$this->userId[] = $row['userid']."<BR>";
				}
				$statusId = array_unique($statusId);
				var_dump($statusId);
				$query = "SELECT attendanceid FROM mdl_attendance_statuses WHERE id IN (";
				foreach($statusId as $id)
				{
					$query .= "$id,";
				}
				$query = substr_replace($query ,"",-1);
				$query .= ")";
				echo $query;
				//echo " Status id : $statusId";
			//$attendanceIdResult = mysqli_query($connection, "SELECT attendanceid FROM mdl_attendance_statuses WHERE id =".(int)$statusId);
			$attendanceIdResult = mysqli_query($connection, $query);
				while($row = mysqli_fetch_array($attendanceIdResult))
				{
					$attendanceId = $row['attendanceid'];
					echo " Attendance ID : $attendanceId";
					//break;
					//$this->userId[] = $row['userid']."<BR>";
				}
				//echo " Attendance ID : $attendanceId";
			/*	
				$attendanceIdResult = mysqli_query($connection, "SELECT attendanceid FROM mdl_attendance_statuses WHERE id =".(int)$statusId);
				while($row = mysqli_fetch_array($userId))
				{
					$this->attendanceId = $row['attendanceid'];
					break;
					//$this->userId[] = $row['userid']."<BR>";
				}
			}*/
			//echo $this->attendanceId."<br/>";
		}
		//var_dump($this->enrollmentId);
	}
	
	function get_id_from_course($obj, $connection)
	{
		foreach($this->courseCodes as $courseCode)
		{
			$id = mysqli_query($connection, "SELECT id, startdate FROM mdl_course WHERE idnumber = ".$courseCode);
			while($row = mysqli_fetch_array($id))
			{
				if(date("y",$row['startdate']) == date("y",time()))
					$this->idFromCourse[] = $row['id']."<BR>";
			}
		}
	}
	function read_student_records($obj)
	{
		for($i=STUDENT_LIST_START; $i<count($obj); $i++)
		{
			$columnIndex = 0;
			echo "<center>Username : ".$obj[$i][PRN_COLUMN_KEY]."</center><BR/>";
			echo "<b>".$this->courseCodes[$columnIndex]."</b><BR/>";
			
			foreach($this->dates as $datekey=>$datevalue)
			{
				//echo "<br/>datekey : $datekey, coursecolumns columnindex : ".$this->courseColumns[$columnIndex+1]."<br/>";
				if($columnIndex < count($this->courseColumns))
				{
					if((strlen($datekey) == strlen($this->courseColumns[$columnIndex]) && $datekey >= ($this->courseColumns[$columnIndex]))
				|| (strlen($datekey) != strlen($this->courseColumns[$columnIndex]) && $this->string_to_ascii($datekey) >= 
					$this->string_to_ascii($this->courseColumns[$columnIndex]) ))
					{
						$columnIndex += 1;
						echo "<BR/><b>".$this->courseCodes[$columnIndex]."</b><BR/>";
					}
				}
				//use strtotime()
				echo $datevalue." : ".$obj[$i][$datekey]." ";
			}
			echo "<BR/><BR/>";
		}
	}
	
	function string_to_ascii($string)
    {
		$ascii = NULL;
		for ($i = 0; $i < strlen($string); $i++)
		{
		$ascii += ord($string[$i]);
		}
		return $ascii;
    }
	
	function retrieve_dates($obj)
	{
		$this->dateColumns = array();
		$this->dateValues = array();
		$this->courseColumns = array();
		$separate = 0;
		foreach($obj[HEADING_INDEX] as $key=>$value)
		{
			if($separate == 1 && preg_match("/^[0-9]/i",$value))
			{
				$this->courseColumns[] = $key;
				$separate = 0;
			}
			if(preg_match("/^[0-9]/i",$value))
			{
				$this->dates[$key] = $value;
			}
			if($value == "t" || $value == "T" || $value == "a" || $value == "A" ||$value == "p" || $value == "P")
				$separate = 1;				
			
		}
	}
	
	function get_programme_code($obj)
	{
		return substr($obj[HEADING_INDEX][PROG_CODE_KEY],-6,3);
	}
	
	function retrieve_course_codes($obj)
	{
		$this->courseCodes = array();
		
		foreach($obj[COURSE_CODE_INDEX] as $key=>$value)
		{echo $value."<br>";
			if(preg_match("/^[0-9]{3,}/i",$value))
			{
				preg_match("/^[0-9]{3,}/i",$value,$codes);
				if(!(in_array($codes[0],$this->courseCodes)))
				{
					//$this->courseColumns[] = $key;
					$this->courseCodes[] = "30".$this->programmeCode.$codes[0];
				}
			}
		}
		//$this->courseColumns = array_unique($this->courseColumns);		
	}
 }
 
 $ods = new ImportODS;
 $ods->parse_file("BCA.ods");
/**
 * We create the $easy_ods_read object
 *  
 * @param Integer 0 First spreadsheet
 * @param String $path File path of the content.xml file
 * 
 * @return Object $easy_ods_read Object of the class
 */
	

/**
 * We take the needed data from the file
 */
	

?>