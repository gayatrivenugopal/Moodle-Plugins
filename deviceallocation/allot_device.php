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

require_once('../../config.php');
require_once('allotdevice_form.class.php');
require_once('devicemodel.class.php');
require_once('lib.php');

session_start();

$PAGE->set_title(get_string('allot_device', 'local_deviceallocation'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('allot_device', 'local_deviceallocation'));

$mform = new AllotDevice_Form();

if($mform->get_data() == null) {
	$mform->display();
} else {
	$model = new DeviceModel();
	$usernamekey = get_string('username', 'local_deviceallocation');
	$serialnokey = get_string('snokey', 'local_deviceallocation');
	$remarkskey = get_string('remarks', 'local_deviceallocation');
	$issuedatekey = get_string('issuedatekey', 'local_deviceallocation');
	
	$deviceid = $model->get_device_id($_SESSION['sno'][$mform->get_data()->$serialnokey]);
	$userid = $model->get_user_id($_SESSION['usernames'][$mform->get_data()->$usernamekey]);
	$remarks = $mform->get_data()->$remarkskey;
	$issuedate = $mform->get_data()->$issuedatekey;
	
	if($model->device_allotted_to_user($deviceid, $userid)) {
		$mform->display();
		echo "<center><font color='red'>The device is already allotted to the user.</font></center>";
	}
	else {
		if($model->allot_device($deviceid, $userid, $issuedate, $remarks) > 0) {
			$mform->display();
			echo "<center><font color='green'>The device has been allotted to the user successfully.</font></center>";
		} else {
			$mform->display();
			echo "<center><font color='red'>The device is already allotted to a user.</font></center>";
		}
	}
}
echo $OUTPUT->footer();

?>
