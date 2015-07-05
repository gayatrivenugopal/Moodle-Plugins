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

session_start();
class Courses_Form extends moodleform {
	private $result;
	private $courseId;
	private $perct;
	private $mform;
	private $resultFlag;
	
    public function definition() {
        global $CFG;
        $this->mform = $this->_form;
    }
	
	public function set_elements($courses)
	{
		$categForm = new Category_Form();
		$select = $this->mform->addElement('select', 'courses', get_string('courses','local_attendance'), $courses);
		$select->setMultiple(true);
		$select->setSize(count($courses));
		$select->setSelected(0);
		
		$this->mform->addElement('submit', 'block', get_string('get_sessions','local_attendance'));
	}
	
	public function display_no_attendance()
	{
		echo "<center><font color=\"red\">No attendance record found.</font></center>";
	}
	
	public function display_no_sessions()
	{
		echo "<center><font color=\"red\">No sessions found.</font></center>";
	}
}
?>