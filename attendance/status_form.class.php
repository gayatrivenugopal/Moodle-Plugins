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
session_start();

class Status_Form extends moodleform {
	private $courseId;
	private $id;
	private $mform;
	private $resultFlag;
	private $prnArray;
	private $courses;
	
    public function definition() {
        global $CFG;
        $this->mform = $this->_form;
		$this->id = $_SESSION['categ'];
		$this->resultFlag = 0;
    }
	
	public function set_elements($result)
	{
		//get list of courses (fullname and idnumber)
		$this->courses = array();
		$this->courseId = array();
		$categForm = new Category_Form();
		$attObj=  new Attendance();
		$this->result = $result;
		if(count($this->result) != 0)
		{
			$this->set_results(1);
			$_SESSION['results'] = 1;
			foreach($this->result as $entry)
			{
				$this->courses[] = $entry->idnumber." ".$entry->fullname;
				$this->courseId[] = $entry->id;
			}
		}
		else
		{
			$_SESSION['results'] = 0;
			$this->set_results(0);
		}
		
		$prns = array();
		
		$prns = $this->get_prns();
		array_splice($prns, 0, 0, "PRN");
		$this->prnArray = $prns;
		$select = $this->mform->addElement('select', 'prns', get_string('select_prn','local_attendance'), $prns);
		
		$select = $this->mform->addElement('select', 'courses', get_string('courses','local_attendance'), $this->courses);
		$select->setMultiple(true);
		$select->setSelected(0);
		$select->setSize(count($this->courses));
		
		$this->mform->addElement('date_time_selector', 'considerfrom', get_string('considerfrom','local_attendance'));
		$this->mform->addElement('date_time_selector', 'considertill', get_string('considertill','local_attendance'));
		
		$radioarray=array();
		$radioarray[] =$this->mform->createElement('radio', 'status', '', "X", "X");
		$radioarray[] =$this->mform->createElement('radio', 'status', '', "P", "P");
		$radioarray[] =$this->mform->createElement('radio', 'status', '', "A", "A");
		$this->mform->setDefault('status', "X");
		$this->mform->addGroup($radioarray, 'radioar', get_string('status', 'local_attendance'), array(' '), false);
		$this->mform->addElement('text', 'remarks', get_string('remark', 'local_attendance'));
		$this->mform->addElement('submit', 'block', get_string('set_status','local_attendance'));
	}
	
	public function get_prnArray()
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
	
	public function return_courses()
	{
		return $this->courses;
	}
}
?>