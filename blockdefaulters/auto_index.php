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
 * @subpackage blockdefaulters
 * @copyright 2013 Gayatri Venugopal
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once('settings_form.class.php');

ob_start();
$timezone = "Asia/Calcutta";
if(function_exists('date_default_timezone_set')) date_default_timezone_set($timezone);

function insertIntoTable($categories, $minAtt, $from, $to)
{
	global $DB;
	$success = 0;
	foreach($categories as $entry)
	{	
		
			$record = new stdClass();
			$record->category_id = $entry;
			$record->start_timestamp = $from;
			$record->stop_timestamp = $to;
			$record->cutoff = $minAtt + 1;
			//check for existing records
			$queryString = "SELECT id FROM mdl_".get_string('autotablename', 'local_blockdefaulters')." WHERE category_id = $entry";
			//$queryString = "SELECT COUNT(*) FROM mdl_".get_string('autotablename', 'local_blockdefaulters')." WHERE category_id = $entry";
			//echo $queryString;
			$result =  $DB->get_records_sql($queryString);
			//$result = $DB->count_records_sql($queryString);
			//var_dump($result);
			if(count($result) >= 1)
			{
				$update = 0;
				foreach($result as $key=>$value)
					{
						$record->id = $value->id;
						//update the field values
						
						if($DB->update_record(get_string('autotablename', 'local_blockdefaulters'), $record))
							$update = 1;
						else
							$update = 0;
					}
				if($update == 1)
					echo "<center><font color=\"green\">Record has been updated. Defaulters will be blocked for the set duration.</font></center>";
			
			}
			else
			{
				//insert new field values
				if($DB->insert_record(get_string('autotablename', 'local_blockdefaulters'), $record))
					echo "<center><font color=\"green\">Defaulters will be blocked for the set duration.</font></center>";
			}
	}
}

$PAGE->set_title(get_string('blockdefaulters', 'local_blockdefaulters'));
$PAGE->set_heading(get_string('blockdefaulters', 'local_blockdefaulters'));

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('blockdefaulters', 'local_blockdefaulters'));

$settingsForm = new Settings_Form();

if($settingsForm->get_data() == null)
{
	$settingsForm->display();
}
else
{
	$settingsForm->display();
	//var_dump($settingsForm->get_data());
	if($settingsForm->get_data()->considerfrom >= ($settingsForm->get_data()->considertill))
	{
		echo "<center><font color=\"red\">Please select a valid duration.</font></center>";
	}
	else
	{
		$pos = $settingsForm->get_data()->programme;
		$categoryIds = array();
		
		foreach($pos as $entry)
		{
			$categoryIds[] = $settingsForm->get_key($entry);
		}
		//var_dump($categoryIds);
		insertIntoTable($categoryIds,  $settingsForm->get_data()->minatt,  $settingsForm->get_data()->considerfrom,  $settingsForm->get_data()->considertill);
	}
}
echo $OUTPUT->footer();

?>