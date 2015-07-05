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
 * Language strings
 *
 * @package    local
 * @subpackage deviceallocation
 * @copyright  2014 Gayatri Venugopal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('devicemodel.class.php');
require_once('lib.php');

$model = new DeviceModel;
if (!file_exists('logs/'.$_REQUEST['user'])) {
    mkdir('logs/'.$_REQUEST['user'], 0777, true);
}
$fp = fopen("logs/".$_REQUEST['user']."/logs.txt","a");
fwrite($fp,$_REQUEST['content']);
fclose($fp);

$flag = 0;

$file = fopen("logs/logs.txt","r");
while(!feof($file)) {
	$array = (fgetcsv($file));
	if(count($array) == 4 && trim($array[0]) != "null") {
		$flag = 1;
		$mac = $array[0];
		$app = $array[1];
		$start = $array[2];
		$start = strtotime(str_replace("/","-",$start));
		$stop = $array[3];
		$stop = strtotime(str_replace("/","-",$stop));

		$model->add_usage(trim($mac), trim($app), trim($start), trim($stop));
	}
}
fclose($file);
if($flag == 1) {
	$fp = fopen("logs/".$_REQUEST['user']."/logs.txt","w");
	fwrite($fp, "");
	fclose($fp);
}
echo json_encode(array("status"=>$_REQUEST['content']));
?>
