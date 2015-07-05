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
require_once('category_form.class.php');
require_once('courses_form.class.php');
require_once('session_desc_form.class.php');
require_once('attendance.class.php');

$resultFlag = 0;
$courseId = array();

ob_start();
session_start();
$timezone = "Asia/Calcutta";
if(function_exists('date_default_timezone_set')) 
	date_default_timezone_set($timezone);
$PAGE->set_title(get_string('pluginname', 'local_attendance'));
$PAGE->set_heading(get_string('delete_sessions', 'local_attendance'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('set_session_desc', 'local_attendance'));
$attObj = new Attendance();
$categoryForm = new Category_Form();
$mform = new Courses_Form();
$sessionDescForm = new Session_Desc_Form();

$sessionDescForm->set_elements($_SESSION['sessions']);

$result = $attObj->get_courses($_SESSION['categ']);
$courses = array();
$courseId = array();
if(count($result) != 0)
{
	set_results(1);
	foreach($result as $entry)
	{
		$courses[] = $entry->idnumber." ".$entry->fullname;
		$courseId[] = $entry->id;
	}
}
else
{
	set_results(0);
}
$mform->set_elements($courses);
	
if($categoryForm->get_data() == null && $mform->get_data() == null && $sessionDescForm->get_data() == null)
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
	$mform = new Courses_Form();
	$result = $attObj->get_courses($_SESSION['categ']);
	$courses = array();
	$courseId = array();
	if(count($result) != 0)
	{
		set_results(1);
		foreach($result as $entry)
		{
			$courses[] = $entry->idnumber." ".$entry->fullname;
			$courseId[] = $entry->id;
		}
	}
	else
	{
		set_results(0);
	}
	$mform->set_elements($courses);
	if(get_results() == 1)
	{
		$_SESSION['categoryList'] = $categoryForm->get_list();
		$mform->display();
	}
	else
	{
		$_SESSION['categ'] = null;
		$categoryForm->display();
		$categoryForm->no_courses();
	}
}
else if($mform->get_data() != null)
{
		$sessionDescForm = new Session_Desc_Form();
		$coursesSelected = array();
		foreach($mform->get_data()->courses as $courseValue)
		{
			$coursesSelected[] = $courseId[$courseValue];
		}
		$atts = $attObj->get_att_ids($coursesSelected);
		if(count($atts) == 0)
		{
			$mform->display();
			$mform->display_no_attendance();
		}
		else
		{
			$tempAtts = $atts;
			$atts = array();
			$session = array();
			$sessionIdArray = array();
			foreach($tempAtts as $record)
			{
				$atts[] = $record->id;
			}
			$sessionDetails = $attObj->get_session_details($atts);
			if(count($sessionDetails) == 0)
			{
				$mform->display();
				$mform->display_no_sessions();
			}
			else
			{
				foreach($sessionDetails as $record)
				{
					$session[] = date("d-F-Y h:i",$record->sessdate);
					$sessionIdArray[] = $record->id;
				}
				$sessionDescForm->set_elements($session);
				$_SESSION['sessions'] = $session;
				$_SESSION['sessionIds'] = $sessionIdArray;
				$sessionDescForm->display();
			}
		}
}
else if($sessionDescForm->get_data() != null)
{	
	$sessionDescForm = new Session_Desc_Form();
	$sessionDescForm->set_elements($_SESSION['sessions']);
	$sessionDescForm->display();
	$errorFlag = 0;
	$sessionIdString = "";
	foreach($sessionDescForm->get_data()->sessions as $selectedIndex)
	{
		$sessionIdArray[] = $_SESSION['sessionIds'][$selectedIndex];
	}
	if(!($attObj->set_session_description(implode(",",$sessionIdArray), $sessionDescForm->get_data()->topic)))
	{
			$errorFlag = 1;
	}
	if($errorFlag == 1)
	{
		$sessionDescForm->display_error();
	}
	else
	{
		$sessionDescForm->display_success();
	}
}
echo $OUTPUT->footer();

function set_results($flag)
{
	global $resultFlag;
	$resultFlag = $flag;
}
	
function get_results()
{
	global $resultFlag;
	return $resultFlag;
}

?>
