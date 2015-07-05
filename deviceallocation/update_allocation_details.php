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
require_once('select_sno_form.class.php');
require_once('update_allocation_form.class.php');
require_once('update_allocation_details_form.class.php');
require_once('devicemodel.class.php');
require_once('lib.php');

session_start();

$PAGE->set_title(get_string('update', 'local_deviceallocation'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('update', 'local_deviceallocation'));

$mform = new Update_Form();
global $selectSnoForm;
$selectSnoForm = new Select_Sno_Form();
global $updateAllocationDetailsForm;
$updateAllocationDetailsForm = new Update_Allocation_Details_Form();
if($mform->get_data() == null && $selectSnoForm->get_data() == null && !isset($_SESSION['selectedSno'])) {
	$mform->display();
	
	unset($_SESSION['username']);
	unset($_SESSION['snoArray']);
	unset($_SESSION['issueDateArray']);
	unset($_SESSION['returnDateArray']);
	unset($_SESSION['remarksArray']);
	unset($_SESSION['username']);
	unset($_SESSION['snoArray']);
	unset($_SESSION['issueDateArray']);
	unset($_SESSION['returnDateArray']);
	unset($_SESSION['remarksArray']);
} else if($mform->get_data() != null) {
	$model = new DeviceModel();
	$usernamekey = get_string('username', 'local_deviceallocation');

	$userid = $model->get_user_id($_SESSION['usernames'][$mform->get_data()->$usernamekey]);

	$username =  $_SESSION['usernames'][$mform->get_data()->$usernamekey];
	$snoArray = $model->get_sno_from_userid($userid);
	//$issueDateArray = array();
	//$returnDateArray = array();
	//$remarksArray = array();

	//$issuedate = $model->get_issuedate($deviceid, $userid);
	//$returndate = $model->get_returndate($deviceid, $userid);
	//$remarks = $model->get_remarks($deviceid, $userid);

	/*foreach($snoArray as $sno) {
		$deviceid = $model->get_device_id_from_sno($sno)->id;
		$issueDateArray[$sno] = $model->get_issuedate($deviceid, $userid);
		$returnDateArray[$sno] = $model->get_returndate($deviceid, $userid);
		$remarksArray[$sno] = $model->get_remarks($deviceid, $userid);
	}*/
	
	$_SESSION['username'] = $username;
	//$_SESSION['issueDateArray'] = $issueDateArray;
	//$_SESSION['returnDateArray'] = $returnDateArray;
	//$_SESSION['remarksArray'] = $remarksArray;
	global $selectSnoForm;
	$selectSnoForm = new Select_Sno_Form();
	$selectSnoForm->display();
} else if(isset($_SESSION['username']) && !isset($_SESSION['selectedSno'])) {
	global $selectSnoForm;
	$selectedSnoPosition = $selectSnoForm->get_data()->sno;
	$_SESSION['selectedSno'] = $_SESSION['userSno'][$selectedSnoPosition];
	
	$model = new DeviceModel();
	$deviceid = $model->get_device_id_from_sno($_SESSION['selectedSno']);
	
	$userid = $model->get_user_id($_SESSION['username']);

	$_SESSION['issueDate'] = date("d-m-Y", $model->get_issuedate($deviceid->id, $userid));
	$_SESSION['returnDate'] = date("d-m-Y", $model->get_returndate($deviceid->id, $userid));
	$_SESSION['remarks'] = $model->get_remarks($deviceid->id, $userid);
	
//$returnValue = $model->update_allocation_details($updateAllocationForm->get_data()->sno);/*
	/*if($returnValue[0] == -2) {
		echo "<center><font color='red'>The device with serial number ".$returnValue[1]." does not exist.</font></center>";	
	} else if($returnValue[0] == -3) {
		echo "<center><font color='red'>The device with serial number ".$returnValue[1]." has already been allotted to a user.</center></font>";				
	} else if($returnValue[0] == -4) {
		echo "<center><font color='red'>The device with serial number ".$returnValue[1]." has already been allotted to the user.</font></center>";				
	}
*/
	global $updateAllocationDetailsForm;
	$updateAllocationDetailsForm = new Update_Allocation_Details_Form();
	$updateAllocationDetailsForm->display();
	//unset($_SESSION['username']);
	unset($_SESSION['snoArray']);
	unset($_SESSION['issueDateArray']);
	unset($_SESSION['returnDateArray']);
	unset($_SESSION['remarksArray']);
} else {
	global $updateAllocationDetailsForm;
	$updateAllocationDetailsForm = new Update_Allocation_Details_Form();
	$updateAllocationDetailsForm->display();
	if($updateAllocationDetailsForm->get_data()->issuedate > $updateAllocationDetailsForm->get_data()->returndate || 
	$updateAllocationDetailsForm->get_data()->issuedate > time() ||
	$updateAllocationDetailsForm->get_data()->returndate > time()) {
		echo "<center><font color='red'>Please select valid dates.</font></center><br/>";
	} else {
		//issuedate, returndate, remarks
		$model = new DeviceModel();
		if($model->update_allocation_details($_SESSION['username'], $_SESSION['selectedSno'], $updateAllocationDetailsForm->get_data()->issuedate, $updateAllocationDetailsForm->get_data()->returndate, $updateAllocationDetailsForm->get_data()->remarks) == true) {
			echo "<center><font color='green'>Record with serial number ".$_SESSION['selectedSno']." has been updated.</font></center><br/>";
		}
		unset($_SESSION['selectedSno']);
		unset($_SESSION['username']);
	}
}

echo $OUTPUT->footer();

?>
