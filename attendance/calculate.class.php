<?php
class Calculate
{
	function low_consolidated_attendance($considerto, $considerfrom, $idArray, $studentEntry)
	{//echo $considerfrom;
	global $DB;
	$sessionId = array();
	$attId = array();
	$sessionId = array();
	$studentId = array();
	$statusId = array();
	$statusArray = array();
	$studentGrades = array();
	$studentMaxGrades = array();
	$studentAtt = array();
	$res = get_att_ids($idArray);
			
	foreach($res as $entry)
	{
		$attId[] = $entry->id;
		
	}
	if(count($attId) != 0)
	{
		//block users not satisfying the minimum criterion
		$sessions = get_session_ids($attId, $considerfrom, $considerto);

		foreach($sessions as $entry)
		{
			$sessionId[] = $entry->id;
		}
		//echo "<font size=\"1\" color=\"white\">";
		//$c=array();
		print_object($c);
		//echo "</font>";
		if(count($sessionId) == 0)
		{//echo "count is 0".
			//echo "<center><font color=\"red\">No records found.</font></center>";
		}
		else
		{
			$res = get_student_status($sessionId);
			if(count($res) != 0)
			{
				foreach($res as $entry)
				{ 
					$studentId[] = $entry->studentid;
					$statusId[] = $entry->statusid;
				}
				
				foreach($statusId as $entry)
				{
					$res = get_status_details($entry);
					$statusArray[$entry] = $res;
				}
				
				
				foreach($studentId as $keyStudent=>$entry)
				{
					if($entry == $studentEntry)
					{
						
						$statusGrade = 0;
				
						foreach($statusArray as $key=>$val)
						{
							if($statusId[$keyStudent] == $key && ($statusArray[$key][$key]->acronym != "x" && $statusArray[$key][$key]->acronym != "X") && ($statusArray[$key][$key]->acronym != "c" && $statusArray[$key][$key]->acronym != "C"))
							{
								if(!($studentMaxGrades[$studentEntry]))
									$studentMaxGrades[$studentEntry] = 1;
								else
									$studentMaxGrades[$studentEntry]++;
									
									
								foreach($val as $gradeObject)
								{
									$statusGrade = (int)$gradeObject->grade;
								}
							}
						}
						if(!isset($studentGrades[$studentEntry]))
							$studentGrades[$studentEntry] = $statusGrade;
						else
							$studentGrades[$studentEntry] += (int)$statusGrade;
						//break;
					}
				} 
				
				foreach($studentGrades as $keyCurr=>$entry)
				{	
					foreach($studentMaxGrades as $keyMax=>$val)
					{
						if($keyMax == $keyCurr)
							$studentAtt[$keyCurr] = number_format(((float)$entry/(float)$val)*100,2);
					}
				}
				foreach($studentAtt as $key => $entry)
				{
				/*********************To find students with TNG************************************/
					if(ceil($entry) < 75)
					{ //echo ceil($entry);
					//print_object($o);
					//echo $entry;
						return $entry;
					}
					else	
						return false;
				}
				return false;
			}
			return false;
		}
		return false;
	}
	return false;
 }
}
?>