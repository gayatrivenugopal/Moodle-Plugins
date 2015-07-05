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
session_start();

class Attendance_Status_Report_Form extends moodleform {
	private $courseId;
	private $id;
	private $mform;
	private $resultFlag;
	private $prnArray;
	private $courses;
	
    public function definition() {
        global $CFG;
        $this->mform = $this->_form;
	$this->set_elements();
    }
	
	public function set_elements($result)
	{
		$prns = array();
		
		$prns = $this->get_prns();
		array_splice($prns, 0, 0, "PRN");
		$this->prnArray[] = $prns;
		$select = $this->mform->addElement('select', 'prns', get_string('select_prn','local_attendance'), $prns);
				
		$this->mform->addElement('date_time_selector', 'considerfrom', get_string('considerfrom','local_attendance'));
		$this->mform->addElement('date_time_selector', 'considertill', get_string('considertill','local_attendance'));
		
		$this->mform->addElement('submit', 'block', get_string('generate_report','local_attendance'));
	}
	
	public function get_prn_array()
	{
		return $this->prnArray;
	}
	
	public function get_prns()
	{
		global $DB;
		$queryString = "SELECT username FROM mdl_user WHERE id IN (SELECT userid FROM mdl_role_assignments WHERE roleid = (SELECT DISTINCT(id) from mdl_role WHERE shortname = 'student'))";
		$result = $DB->get_records_sql($queryString);
		foreach($result as $id)
		{
			$prns[] = $id->username;
		}
		return $prns;
	}
	
}
?>
