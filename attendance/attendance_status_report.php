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
 * @copyright 2014 Gayatri Venugopal
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */ 
 
require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once('attendance_status_report_form.class.php');
require_once('attendance.class.php');

ob_start();
session_start();
$timezone = "Asia/Calcutta";
if(function_exists('date_default_timezone_set')) date_default_timezone_set($timezone);

$PAGE->set_title(get_string('blockdefaulters', 'local_attendance'));
$PAGE->set_heading(get_string('blockdefaulters', 'local_attendance'));

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('blockdefaulters', 'local_attendance'));

$mform = new Attendance_Status_Report_Form();

if($mform->get_data() == null)
{
	$_SESSION['categ'] = null;
	$mform->display();
}
if($mform->get_data() != null) //true if user clicked "Generate Report"
{
	$enrolledCoursesArray = array();
	$enrolledCoursesAttendance = array();
	$sessionIdsArray = array();

	$mform->display();
	//print_object($mform->get_data());

	if($mform->get_data()->prns == 0) {
		echo "<center><font color=\"red\">Please select a PRN.</font></center>";
	}
	else {
		$position = $mform->get_data()->prns;
		$prnArray = $mform->get_prn_array();
		
		$username = $prnArray[0][$position];
		//retrieve parameters passed
		$considerFromArray = optional_param_array('considerfrom', null, PARAM_TEXT);
		$considerTillArray = optional_param_array('considertill', null, PARAM_TEXT);
	
		$considerfrom = make_timestamp($considerFromArray['year'], $considerFromArray['month'], $considerFromArray['day'], $considerFromArray['hour'], $considerFromArray['minute']);
		$considerto = make_timestamp($considerTillArray['year'], $considerTillArray['month'], $considerTillArray['day'], $considerTillArray['hour'], $considerTillArray['minute']);
	
		if($considerto > $considerfrom)
		{
			$_SESSION['considerFrom'] = $considerFromArray['day']."-".$considerFromArray['month']."-".$considerFromArray['year'];
			$_SESSION['considerTill'] = $considerTillArray['day']."-".$considerTillArray['month']."-".$considerTillArray['year'];	
			$attendance = new Attendance;
			$userId = current(current($attendance->get_user_id($username)));
			$enrolledCoursesArray = array_keys($attendance->get_enrolled_courses_student($userId));

			//loop through courses to collect courses that consist of an attendance activity
			foreach($enrolledCoursesArray as $courseId) {
				$attendanceId = current(current($attendance->get_att_id_from_course($courseId)));
				if(trim($attendanceId) != "") {
					$enrolledCoursesAttendance[$courseId] = $attendanceId;
				}
			}
			//loop through attendance IDs to find sessions between the start and end timestamps
			foreach($enrolledCoursesAttendance as $courseId=>$attendanceId) {

				 $sessionIdsArray[$courseId] = $attendance->get_session_ids(array($attendanceId), $considerfrom, $considerto);
			}
			
//TODO: find other courses in which the student is enrolled and display sessions of those courses
			foreach($sessionIdsArray as $array) {
				foreach($array as $keyId=>$value) {
					$date[(date("d/m/Y", $attendance->get_session_date($keyId)))] .= ",".$keyId;
				}
			}

			//find acronyms
			foreach($date as $key=>$value) {
				$attendanceCount = 0;
				$attendanceSessions = 0;
				echo "<br/><br/><b>$key</b><br/>";
				//get status id
				$sessionArray = explode(",",$value);
				array_shift($sessionArray);
				echo "<table style='width:50%' border='2'><tr>";
				$sessionConducted = 0;
				foreach($attendance->get_student_status_in_sessions($sessionArray, $userId) as $value) {
					echo "<td>".$attendance->get_course_from_session($value->sessionid)."</td>";
					echo "<td>".date('H:i', $attendance->get_time_from_session($value->sessionid))."</td>";
					$statusId = $value->statusid;
					$acronym = $attendance->get_acronym_from_status($statusId);	
					echo "<td>$acronym</td><tr>";
					if(strtolower($acronym) == "p") {
						$attendanceCount++;
					}
					if(strtolower($acronym) != "x" && strtolower($acronym) != "c") {
						$attendanceSessions++;
					}
					$sessionConducted = 1;
				}
				echo "</tr></table>";
				if($sessionConducted == 1) {
					echo "&nbsp;&nbsp;<i>Attendance: ".(((float)$attendanceCount/$attendanceSessions)*100)."%</i><br/>";
				}
				else {
					echo "<i>No session was conducted on this date.</i><br/>";				
				}
			}
		}
		else
		{
			echo "<center><font color=\"red\">Please select a valid duration.</font></center>";
		}
	}
}
echo $OUTPUT->footer();

?>
