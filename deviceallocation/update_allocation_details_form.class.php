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

class update_allocation_details_form extends moodleform {

	private $mform;

    	public function definition() {
	        global $CFG;
		$model = new DeviceModel();
		$this->mform = $this->_form;
		
		$issueDate = $_SESSION['issueDate'];
		$returnDate = $_SESSION['returnDate'];
		$remarks = $_SESSION['remarks'];

		$this->mform->addElement('static', get_string('username', 'local_deviceallocation'), $_SESSION['username']);
		$this->mform->addElement('static', get_string('snokey', 'local_deviceallocation'), $_SESSION['selectedSno']);
		
		$this->mform->addElement('static', "issue", get_string('issuedate', 'local_deviceallocation'), $issueDate);
		$this->mform->addElement('date_selector', get_string('issuedatekey','local_deviceallocation'), get_string('issuedate','local_deviceallocation'));

		$this->mform->addElement('static', "return", get_string('returndate', 'local_deviceallocation'), $returndate);
		$this->mform->addElement('date_selector', get_string('returndatekey','local_deviceallocation'), get_string('returndate','local_deviceallocation'));
		$attributes="value=".$remarks;
		$this->mform->addElement('text', get_string('remarkskey', 'local_deviceallocation'), get_string('remarks', 'local_deviceallocation'), $attributes);
		$this->mform->addElement('static', "", "");
		$this->mform->addElement('submit', 'submitbutton'.$i, get_string('update', 'local_deviceallocation'));
	}
}

?>
