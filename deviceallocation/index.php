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

/**
 * file index.php
 * index page to register devices.
 */

require_once('../../config.php');
require_once('adddevice_form.class.php');
require_once('devicemodel.class.php');
require_once('lib.php');

session_start();

$PAGE->set_title(get_string('add_device', 'local_deviceallocation'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('add_device', 'local_deviceallocation'));

$mform = new adddevice_form();
if($mform->get_data() == null) {
	$mform->display();
} else {
	$model = new DeviceModel();
	if($model->serialno_exists($mform->get_data()->sno)) {
		$mform->display();
		echo "<center><font color='red'>The device is already registered.</font></center>";
	}
	else {
		if($model->add_device($mform->get_data()->sno, $mform->get_data()->mac, $mform->get_data()->desc) > 0) {
			$mform->display();
			echo "<center><font color='green'>Device has been registered.</font></center>";	
		} else {
			echo "<center><font color='red'>Could not register device.</font></center>";	
		}
	}
}
echo $OUTPUT->footer();

?>
