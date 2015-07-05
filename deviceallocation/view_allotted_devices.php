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
require_once('devicemodel.class.php');
require_once('lib.php');

session_start();

$PAGE->set_title(get_string('view_allotted_devices', 'local_deviceallocation'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('view_allotted_devices', 'local_deviceallocation'));

$model = new DeviceModel();
$table = new html_table();
$table->tablealign = 'center';
$table->attributes['class'] = 'generaltable';
$table->head = array(get_string('sno', 'local_deviceallocation'), get_string('mac','local_deviceallocation'), get_string('desc', 'local_deviceallocation'), get_string('username', 'local_deviceallocation'), get_string('fname', 'local_deviceallocation'), get_string('lname', 'local_deviceallocation'), get_string('issuedate', 'local_deviceallocation'), get_string('returndate', 'local_deviceallocation'));
$devices = $model->get_allotted_devices();
sort($devices);

foreach($devices as $array) {
	$tableData[] = array($array->sno, $array->macaddress, $array->descr, $array->username, ucwords(strtolower($array->firstname)), ucwords(strtolower($array->lastname)), date("d/M/Y", $array->issuedate), ($array->returndate != null)? date("d/M/Y", $array->returndate):"");
}
$table->data =  $tableData;
echo html_writer::table($table);

echo $OUTPUT->footer();

?>
