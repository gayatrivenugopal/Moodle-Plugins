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
require_once('devicemodel.class.php');

session_start();

class update_allocation_form extends moodleform {

	private $mform;

    	public function definition() {
	        global $CFG;
		$model = new DeviceModel();
		$this->mform = $this->_form;
		
		$usernames = $model->get_usernames();
		$sno = $model->get_sno();
		
		$_SESSION['usernames'] = $usernames;
		$_SESSION['sno'] = $sno;

		$username = $_SESSION['username'];
		$snoArray = $_SESSION['snoArray'];
		$issueDateArray = $_SESSION['issueDateArray'];
		$returnDateArray = $_SESSION['returnDateArray'];
		$remarksArray = $_SESSION['remarksArray'];

		$this->mform->addElement('static', get_string('username', 'local_deviceallocation'), $username);
		$i = -1;
		foreach($snoArray as $sno) {
			$i++;
			$attributes="value=$sno";
			$this->mform->addElement('text', get_string('snokey', 'local_deviceallocation').$i, get_string('sno', 'local_deviceallocation'), $attributes);
			$this->mform->addRule(get_string('snokey', 'local_deviceallocation').$i, null, 'required');
			$this->mform->addElement('submit', 'submitbutton'.$i, get_string('update_sno', 'local_deviceallocation'));
		}
	}
}

?>
