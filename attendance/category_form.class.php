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

class Category_Form extends moodleform {
	private $idnumbers;
	private $keys;
	private $list;
	
    public function definition() {
	        global $CFG;
			$this->idnumbers = array("030121","030122","030141","030142");
			$mform = $this->_form;
			$categories = $this->get_categories();
			$this->list = $categories;
			$this->keys = array_keys($categories);
		//	sort($this->keys);
			$select = $mform->addElement('select', 'programme', get_string('programmes','local_attendance'), array_values($categories));
			$select->setMultiple(true);
			$select->setSelected(0);
			$select->setSize(count($categories));
			$mform->addElement('submit', 'block', get_string('getcourses','local_attendance'));
	}
	
	public function get_list()
	{
		return $this->list;
	}
	
	public function get_key($position)
	{
		return $this->keys[$position];
	}
	
	public function get_categories()
	{
		global $DB;
		$programmeId = array();
		$batchId = array();
		$semId = array();
		$categ = array();
		$queryString = "SELECT id, name FROM mdl_course_categories WHERE visible = 1 AND idnumber IN(".implode(",",$this->idnumbers).")";
		$result = $DB->get_records_sql($queryString);
		foreach($result as $entry)
		{
			$categ[] = $entry->name;
			$programmeId[] = $entry->id;
		}
		
		//get batches		
		$programmeIndex = 0;
		$i = 0;
		$programme = $categ;
		foreach($programmeId as $entry)
		{
			$queryString = "SELECT id, name FROM mdl_course_categories WHERE visible = 1 AND parent =".$entry;
			$result = $DB->get_records_sql($queryString);
			foreach($result as $batch)
			{
				$categ[$i] = $programme[$programmeIndex] . " " . $batch->name;
				$batchId[] = $batch->id;
				$i++;
			}
			$programmeIndex++;
		}
		$programmeIndex = 0;
		$i = 0;
		$programme = $categ;
		$categ = array();
		foreach($batchId as $entry)
		{
			$queryString = "SELECT id, name FROM mdl_course_categories WHERE visible = 1 AND parent =".$entry;
			error_log($queryString);
			$result = $DB->get_records_sql($queryString);
			foreach($result as $semester)
			{
				$categ[$semester->id] = $programme[$programmeIndex] . " " . $semester->name;
				$semId[] = $semester->id;
				$i++;
			}
			$programmeIndex++;
		}
		return $categ;
	}
	
	public function no_courses()
	{
		echo "<center><font color=\"red\">Could not find courses.</font></center>";
	}
}
?>
