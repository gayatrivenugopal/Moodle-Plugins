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
 * @subpackage accesscontrol
 * @copyright 2014 Gayatri Venugopal
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class changesettings_form extends moodleform {

    public function definition() {
	        global $CFG;
			$mform = $this->_form;
	}
		
	public function addElements($users)
	{
		$mform = $this->_form;
		$table = new html_table();
		$table->tablealign = 'center';
		$table->attributes['class'] = 'generaltable';
		$table->head = array(get_string('sno','local_accesscontrol'),get_string('grantaccess', 'local_accesscontrol'));
		$table->data =  $users;
		echo html_writer::table($table);	
		$_SESSION['content'] = null;		
		for($i=1; $i<count($users); $i++)
		{
			$_SESSION['content'][$i-1] = $users[$i][1];
		}
		$mform->addElement('submit', 'submitbutton', get_string('grantaccess', 'local_accesscontrol'));
	}
}
?>
