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
require_once('operations.class.php');
require_once('revoke_form.class.php');

session_start();

$PAGE->set_title(get_string('revokeaccess', 'local_accesscontrol'));
$PAGE->set_heading(get_string('revokeaccess', 'local_accesscontrol'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('revokeaccess', 'local_accesscontrol'));

$operations = new Operations();

$mform = new Revoke_Form();
$mform->set_elements();

if(!isset($_SESSION['usernames'])) {
	//fetch and display the usernames who have been granted access
	if(($usernames = $operations->get_users()) != null) {
		$_SESSION['usernames'] = $usernames;
		$mform->set_elements();
		$mform->display();
	}
	else {
		echo "<font color='red'>Access has not been granted to any user.</font><br/>";
	}
}
else {
	$_SESSION['usernames'] = null;
	foreach($mform->get_data() as $key=>$value) {
		//check if key is a username and the username is selected
		if(strtolower($key) != "block" && $value == 1) {
			//revoke access
			if($operations->remove_user($key)) {
				echo "<font color='green'>Access revoked from $key.</font><br/>";
			}
			else {
				echo "<font color='red'>Could not revoke access from $key.</font><br/>";
			}
		}
	}
}

//$mform->display();

echo $OUTPUT->footer();

?>