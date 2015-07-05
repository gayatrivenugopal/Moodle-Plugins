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
 
class Download_Form extends moodleform {
	const EXCEL = 0;
	const OPENOFFICE = 1;
	const TEXT = 2;
	private $category;

    public function definition() {
	        global $CFG;
			$mform = $this->_form;
			$select = $mform->addElement('select', 'format', get_string('format','local_blockdefaulters'), array(
			get_string('excel','local_blockdefaulters'),
			get_string('openoffice','local_blockdefaulters'),
			get_string('text','local_blockdefaulters')));
			$mform->addElement('submit', 'block', get_string('download','local_blockdefaulters'));
		}
	public function set_list($list)
	{
		$this->category = $list;
	}
	public function get_list()
	{
		return $this->category;
	}
}
?>
