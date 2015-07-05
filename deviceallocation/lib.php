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
 * @subpackag  deviceallocation
 * @copyright 2014 Gayatri Venugopal
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Adds module specific settings to the settings block
 */

	function local_deviceallocation_extends_navigation(global_navigation $navigation) {
		global $CFG, $USER, $PAGE;
		
		 if (!$settingsnav = $PAGE->__get('settingsnav')) {
			return;
		}	
		if (is_siteadmin($USER)) {
			$nodeBlock = $settingsnav->add(get_string('deviceallocation', 'local_deviceallocation'));
			$settingsBlock = $nodeBlock->add(get_string('view_devices', 'local_deviceallocation'), new moodle_url($CFG->wwwroot.'/local/deviceallocation/view_devices.php'));
			$settingsBlock = $nodeBlock->add(get_string('add_device', 'local_deviceallocation'), new moodle_url($CFG->wwwroot.'/local/deviceallocation/index.php'));
			$settingsBlock = $nodeBlock->add(get_string('allot_device', 'local_deviceallocation'), new moodle_url($CFG->wwwroot.'/local/deviceallocation/allot_device.php'));
			$settingsBlock = $nodeBlock->add(get_string('update_allotted_device', 'local_deviceallocation'), new moodle_url($CFG->wwwroot.'/local/deviceallocation/update_allocation.php'));
			$settingsBlock = $nodeBlock->add(get_string('update_allocation_details', 'local_deviceallocation'), new moodle_url($CFG->wwwroot.'/local/deviceallocation/update_allocation_details.php'));
			$settingsBlock = $nodeBlock->add(get_string('view_allotted_devices', 'local_deviceallocation'), new moodle_url($CFG->wwwroot.'/local/deviceallocation/view_allotted_devices.php'));
			$settingsBlock = $nodeBlock->add(get_string('view_usage', 'local_deviceallocation'), new moodle_url($CFG->wwwroot.'/local/deviceallocation/view_usage_student.php'));
		}
	}
?>
