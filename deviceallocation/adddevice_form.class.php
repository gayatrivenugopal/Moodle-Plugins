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
 * @subpackage deviceallocation
 * @copyright 2014 Gayatri Venugopal
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */ 

require_once("$CFG->libdir/formslib.php");

class adddevice_form extends moodleform {

    	public function definition() {
	        global $CFG;
		$mform = $this->_form;
		$mform->addElement('text', get_string('snokey', 'local_deviceallocation'), get_string('sno', 'local_deviceallocation'));
		$mform->addRule(get_string('snokey', 'local_deviceallocation'), get_string('sno_missing', 'local_deviceallocation'), 'required', null, 'server');
		$mform->addElement('text', get_string('mackey', 'local_deviceallocation'), get_string('mac', 'local_deviceallocation'));
			$mform->addRule(get_string('mackey', 'local_deviceallocation'), get_string('mac_missing', 'local_deviceallocation'), 'required', null, 'server');
		$mform->addElement('text', get_string('desckey', 'local_deviceallocation'), get_string('desc', 'local_deviceallocation'));
		$mform->addElement('submit', 'submitbutton', get_string('add_device', 'local_deviceallocation'));
	}
}

?>
