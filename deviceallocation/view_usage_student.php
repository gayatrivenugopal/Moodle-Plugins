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
require_once('view_usage_student_form.class.php');
require_once('select_sno_form.class.php');
require_once('devicemodel.class.php');
require_once('lib.php');

session_start();

$PAGE->set_title(get_string('view_usage', 'local_deviceallocation'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('view_usage', 'local_deviceallocation'));

$mform = new View_Usage_Student_Form();
global $snoForm;
$snoForm = new Select_Sno_Form();

if($mform->get_data() == null && $snoForm->get_data() == null) {
	$mform->display();
} else if($mform->get_data() != null) {
	$usernameKey = get_string('username', 'local_deviceallocation');
	$usageFromKey = get_string('usagefrom', 'local_deviceallocation');
	$position = $mform->get_data()->$usernameKey;
	$_SESSION['username'] = $_SESSION['usernames'][$position];
	$_SESSION['usagefrom'] = $mform->get_data()->from;
	global $snoForm;
	$snoForm = new Select_Sno_Form();
	$snoForm->display();
} else {
	$model = new DeviceModel();
	global $snoForm;
	$snoKey = get_string('snokey', 'local_deviceallocation');
	$position = $snoForm->get_data()->$snoKey;
	$_SESSION['userSelectedSno'] = $_SESSION['userSno'][$position];
	//retrieve mac address from sno
	$macAddress = $model->get_mac_address_from_sno($_SESSION['userSelectedSno']);
	//retrieve usage by mac address
	$usage = $model->get_usage($macAddress, $_SESSION['usagefrom']);
	$name = $model->get_firstname_lastname_from_username($_SESSION['username']);
	echo "<Center><h4>".$name->firstname." ".$name->lastname."<br/><br/>Serial No. ".$_SESSION['userSelectedSno']."</h4><br/>";
	$table = new html_table();
	//$table->tablealign = 'center';
	$table->attributes['class'] = 'generaltable';
	$table->head = array(get_string('appname', 'local_deviceallocation'),get_string('start_time','local_deviceallocation'),get_string('stop_time', 'local_deviceallocation'));
	foreach($usage as $array) {
		$tableData[] = array($array->application, date("d/m/Y H:i:s", $array->start_time), date("d/m/Y H:i:s", $array->stop_time));
	}
	$table->data =  $tableData;
	echo html_writer::table($table);
	echo "</center>";

	unset($_SESSION['username']);
	unset($_SESSION['usernames']);
	unset($_SESSION['usagefrom']);
	unset($_SESSION['userSelectedSno']);

}
echo $OUTPUT->footer();

?>
