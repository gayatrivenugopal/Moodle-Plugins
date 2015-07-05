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
require_once('update_form.class.php');
require_once('update_allocation_form.class.php');
require_once('devicemodel.class.php');
require_once('lib.php');

session_start();

$PAGE->set_title(get_string('update_allotted_device', 'local_deviceallocation'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('update_allotted_device', 'local_deviceallocation'));

$mform = new Update_Form();

$updateAllocationForm = new Update_Allocation_Form();
if($mform->get_data() == null && !isset($_SESSION['issueDateArray'])) {
	unset($_SESSION['username']);
	unset($_SESSION['snoArray']);
	unset($_SESSION['issueDateArray']);
	unset($_SESSION['returnDateArray']);
	unset($_SESSION['remarksArray']);
	$mform->display();
} else if($mform->get_data() != null) {
	$model = new DeviceModel();
	$usernamekey = get_string('username', 'local_deviceallocation');

	$userid = $model->get_user_id($_SESSION['usernames'][$mform->get_data()->$usernamekey]);

	$username =  $_SESSION['usernames'][$mform->get_data()->$usernamekey];
	$snoArray = $model->get_allotted_sno_from_userid($userid);
	$issueDateArray = array();
	$returnDateArray = array();
	$remarksArray = array();

	//$issuedate = $model->get_issuedate($deviceid, $userid);
	//$returndate = $model->get_returndate($deviceid, $userid);
	//$remarks = $model->get_remarks($deviceid, $userid);

	foreach($snoArray as $sno) {
		$deviceid = $model->get_device_id_from_sno($sno)->id;
		$issueDateArray[$sno] = $model->get_issuedate($deviceid, $userid);
		$returnDateArray[$sno] = $model->get_returndate($deviceid, $userid);
		$remarksArray[$sno] = $model->get_remarks($deviceid, $userid);
	}
	
	$_SESSION['username'] = $username;
	$_SESSION['snoArray'] = $snoArray;
	$_SESSION['issueDateArray'] = $issueDateArray;
	$_SESSION['returnDateArray'] = $returnDateArray;
	$_SESSION['remarksArray'] = $remarksArray;
	global $updateAllocationForm;
	$updateAllocationForm = new Update_Allocation_Form();
	$updateAllocationForm->display();
} else {
	global $updateAllocationForm;	
	foreach($updateAllocationForm->get_data() as $keyName=>$val) {
		if(strpos($keyName, "submitbutton") === 0) {
			$position = substr($keyName, strpos($keyName, "submitbutton")+12);
			break;
		}
	}
	$model = new DeviceModel();
	$returnValue = $model->update_allocation($updateAllocationForm->get_data(), $_SESSION['snoArray'], $_SESSION['username'], $position);
	if($returnValue[0] == -2) {
		echo "<center><font color='red'>The device with serial number ".$returnValue[1]." does not exist.</font></center>";	
	} else if($returnValue[0] == -3) {
		echo "<center><font color='red'>The device with serial number ".$returnValue[1]." has already been allotted to a user.</center></font>";				
	} else if($returnValue[0] == -4) {
		echo "<center><font color='red'>The device with serial number ".$returnValue[1]." has already been allotted to the user.</font></center>";				
	}

	$updateAllocationForm = new Update_Allocation_Form();
	$updateAllocationForm->display();
	unset($_SESSION['username']);
	unset($_SESSION['snoArray']);
	unset($_SESSION['issueDateArray']);
	unset($_SESSION['returnDateArray']);
	unset($_SESSION['remarksArray']);
}

echo $OUTPUT->footer();

?>
