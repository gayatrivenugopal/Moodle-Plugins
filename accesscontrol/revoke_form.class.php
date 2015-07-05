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
 
class revoke_form extends moodleform {
	private $mform;
	
    public function definition() {
	        global $CFG;
			$this->mform = $this->_form;
	}
		
	public function set_elements()
	{
		foreach($_SESSION['usernames'] as $username) {
			$this->mform->addElement('checkbox', $username, $username);
			$this->mform->setDefault($username, 'true');
		}
		if(isset($_SESSION['usernames'])) {
			$this->mform->addElement('submit', 'block', get_string('revokeaccess', 'local_accesscontrol'));
		}
	}	
}
?>
