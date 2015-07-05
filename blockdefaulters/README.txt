moodle-local_blockusers
====================
A local plugin that allows admin to block multiple users by uploading a csv file containing the usernames of the users to be blocked

Version
-------
1.1.0 (2013042308)

Requires:
--------
Moodle 1.9 or higher

Installation:
------------
Download zip from: https://github.com/innovg/moodle-local_blockusers/tree/master/blockusers
Unzip into the 'local' subfolder of your Moodle install.
Rename the new folder to blockusers.
Add the following lines in moodle/login/index.php starting from line 117:
	$username = $frm->username;
	global $DB;
	$curTime = time();
	$queryString = "SELECT * FROM mdl_blockusers WHERE start_timestamp <= ".$curTime." && ".$curTime." <= stop_timestamp && username like '".$username."'";
	if($DB->record_exists_sql($queryString))
	{
		$errormsg = "You are not permitted to login";
		$errorcode = 3;
	}
	
Visit http://yoursite.com/admin to finish the installation. 

Documentation:
-------------
Please feel free to contribute documentation in the relevant area of
the MoodleDocs wiki.
