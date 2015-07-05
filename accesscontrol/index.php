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
 * @subpackage accesscontrol
 * @copyright 2014 Gayatri Venugopal
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * file index.php
 * index page to view grant access page.
 */

require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once($CFG->libdir.'/csvlib.class.php');
require_once('changesettings_form.class.php');
require_once('operations.class.php');

session_start();
//print_object($_SERVER);

class users_form extends moodleform {

    public function definition() {
        global $CFG;
        $mform = $this->_form;
        $mform->addElement('filepicker', get_string('filename', 'local_accesscontrol'), get_string('file'), null,
                   array('maxbytes' => $maxbytes, 'accepted_types' => array('.txt','.csv')));
		$mform->addElement('submit', 'submitbutton', get_string('import', 'local_accesscontrol'));
    }
	
    function validation($data, $files) {
        return array();
    }
}

$PAGE->set_title(get_string('grantaccess', 'local_accesscontrol'));
$PAGE->set_heading(get_string('grantaccess', 'local_accesscontrol'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('grantaccess', 'local_accesscontrol'));

$mform = new users_form();
$settingsform = new changesettings_form();
if($mform->get_file_content(get_string('filename', 'local_accesscontrol')) != null) //true if file has been imported
{
	$content = $mform->get_file_content(get_string('filename', 'local_accesscontrol'));
	//save to moodledata
	$success = $mform->save_file(get_string('filename', 'local_accesscontrol'), $CFG->dataroot, false);
	//convert csv string to array
	$usernames = explode(",", $content);
	$index = 0;
	$users = array();
	$users[$index++] = array("","");
	foreach($usernames as $user)
	{
		$users[$index++] = array($index-1,$user);
	}
	//Display table and date-time selectors
	$settingsform->addElements($users);
	$settingsform->display();
}
else if($_SESSION['content'] != null) //true if user clicked "Block Users"
{
	$operations = new Operations;
	$usernamesFinal = array();
	$usernames = $_SESSION['content'];
	$_SESSION['content'] = null;
	foreach($usernames as $username) {
		if($operations->user_exists($username)) {
			if(!$operations->user_added($username)) {
				$usernamesFinal[] = $username;
			}
			else {
				echo "<font color='green'>Access is already granted to $username.</font><br/>";
			}
		}
		else {
			echo "<font color='red'>$username is not a valid username.</font><br/>";
		}
	}
	if(count($username) != 0) {
		//add to table in the database
		$successfulAddition = $operations->add_user($usernamesFinal);
		if(count($successfulAddition) == 0) {
			echo "<font color='red'>Could not grant access to any user.</font><br/>";
		}
		else {
			foreach($successfulAddition as $record) {
				echo "<font color='green'>Access granted to $record.</font><br/>";
			}
		}
	}
}
else {
	$mform->display();
}
echo $OUTPUT->footer();

?>