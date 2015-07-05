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

session_start();
class Session_Desc_Form extends moodleform {
	private $mform;
	
    public function definition() {
        global $CFG;
        $this->mform = $this->_form;
    }
	
	public function set_elements($sessions)
	{ 
		$select = $this->mform->addElement('select', 'sessions', get_string('select_sessions','local_attendance'), $sessions);
		$select->setMultiple(true);
		$select->setSelected(0);
		$select->setSize(count($sessions));
		
		$this->mform->addElement('text', 'topic', get_string('topic_covered','local_attendance'));
		
		$this->mform->addElement('submit', 'block', get_string('update','local_attendance'));
	}
	
	public function display_error()
	{
		echo "<center><font color=\"red\">Could not update the session description. Please try again!</font></center>";
	}
	
	public function display_success()
	{
		echo "<center><font color=\"green\">Records have been updated successfully.</font></center>";
	}
}
?>