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
 
 require_once('../../config.php');
 global $CFG;
 $filename = $_REQUEST['filename'];
 
 if($_REQUEST['format'] == "text")
 {
	header("Content-type: text/plain"); 
    header('Content-Disposition: inline; filename="'.$filename.'"'); 
    header("Content-Transfer-Encoding: Binary"); 
    header("Content-length: ".filesize($filename)); 
    header('Content-Disposition: attachment; filename="'.$filename.'"'); 
    readfile($filename);
	unlink($filename);
 }
 else
 {
	if($_REQUEST['format'] == "excel")
	{
		require_once("$CFG->libdir/excellib.class.php");
		$workbook = new MoodleExcelWorkbook(); //("-");
	}
	else
	{
		 require_once("$CFG->libdir/odslib.class.php");
		 $workbook = new MoodleODSWorkbook();
	}
	// Sending HTTP headers
    $workbook->send($filename);
	// Creating the first worksheet
    $myxls =& $workbook->add_worksheet(get_string('attendancereport','local_attendance'));
	// format types
    $formatbc =& $workbook->add_format();
    $formatbc->set_bold(1);
	//write headings
    $myxls->write(0, 0, "PRN", $formatbc);
    $myxls->write(0, 1, "Name", $formatbc);
    $myxls->write(0, 2, "Attendance", $formatbc);
	$myxls->write(0, 3, "Sessions Attended", $formatbc);
	$myxls->write(0, 4, "Total Sessions", $formatbc);
	
	//i denotes row index
	$i = 1;
	if($_SESSION['blocked'] == 0)
	{
		foreach ($_SESSION['safeStudentsData'] as $cell) {
			$myxls->write($i, 0, $cell[0]);
			$myxls->write($i, 1, $cell[1]);
			$myxls->write($i, 2, (float)$cell[2]."%");
			$myxls->write($i, 3, $cell[3]);
			$myxls->write($i, 4, $cell[4]);
			$i++;
		}
	}
	else
	{
		foreach ($_SESSION['safeStudentsData'] as $cell) {
			$myxls->write($i, 0, $cell[0]);
			$myxls->write($i, 1, $cell[1]);
			$myxls->write($i, 2, (float)$cell[2]."%");
			$myxls->write($i, 3, (float)$cell[3]);
			$myxls->write($i, 4, (float)$cell[4]);
			$i++;
		}
		foreach ($_SESSION['tableData'] as $cell) {
			$myxls->write($i, 0, $cell[0]);
			$myxls->write($i, 1, $cell[1]);
			$myxls->write($i, 2, (float)$cell[2]."%");
			$myxls->write($i, 3, (float)$cell[3]);
			$myxls->write($i, 4, (float)$cell[4]);
			$i++;
		}
	}
	$workbook->close();
 }
?>