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
 * @subpackage blockdefaulters
 * @copyright 2013 Gayatri Venugopal
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
		$mform->addElement('date_time_selector', 'blockfrom', 'Block from');
		$mform->addElement('date_time_selector', 'blocktill', 'Block till');
	}
}
?>