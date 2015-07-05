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

/**
 * Adds module specific settings to the settings block
 */

	function local_attendance_extends_navigation(global_navigation $navigation) {
		global $CFG, $USER, $PAGE;
		
		 if (!$settingsnav = $PAGE->__get('settingsnav')) {
			return;
		}	
		global $COURSE;
		$context = get_context_instance(CONTEXT_COURSE,$COURSE->id);
		if(has_capability('moodle/grade:viewall', $context)) {
			$nodeBlock = $settingsnav->add(get_string('pluginname', 'local_attendance'));
			$nodeBlock->add("View Attendance Status Report",new moodle_url($CFG->wwwroot.'/local/attendance/attendance_status_report.php'));
			$nodeBlock->add("View Consolidated Attendance",new moodle_url($CFG->wwwroot.'/local/attendance/index.php'));
			$nodeBlock->add("View Defaulters",new moodle_url($CFG->wwwroot.'/local/attendance/tng.php'));
			$nodeBlock->add("View Defaulters who attempted tests",new moodle_url($CFG->wwwroot.'/local/attendance/tng_quiz.php'));
			$nodeBlock->add("Assign Status",new moodle_url($CFG->wwwroot.'/local/attendance/status.php'));
			$nodeBlock->add("Delete Blank Sessions",new moodle_url($CFG->wwwroot.'/local/attendance/delete_session.php'));
			//if(has_capability('moodle/course:manageactivities', $context))
			{
				$nodeBlock->add("Add Session Description",new moodle_url($CFG->wwwroot.'/local/attendance/session_description.php'));
			}
		}
	}
?>
