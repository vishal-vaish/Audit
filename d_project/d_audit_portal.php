<?php
  ob_start();
  ?>
<?php 
  require("../vicidial/dbconnect_mysqli.php");
  require("../vicidial/functions.php");
  
  $PHP_AUTH_USER=$_SERVER['PHP_AUTH_USER'];
  $PHP_AUTH_PW=$_SERVER['PHP_AUTH_PW'];
  $PHP_SELF=$_SERVER['PHP_SELF'];
  if (isset($_GET["list_id"]))				{$list_id=$_GET["list_id"];}
  	elseif (isset($_POST["list_id"]))		{$list_id=$_POST["list_id"];}
  if (isset($_GET["DB"]))						{$DB=$_GET["DB"];}
  	elseif (isset($_POST["DB"]))			{$DB=$_POST["DB"];}
  if (isset($_GET["submit"]))					{$submit=$_GET["submit"];}
  	elseif (isset($_POST["submit"]))		{$submit=$_POST["submit"];}
  if (isset($_GET["SUBMIT"]))					{$SUBMIT=$_GET["SUBMIT"];}
  	elseif (isset($_POST["SUBMIT"]))		{$SUBMIT=$_POST["SUBMIT"];}
  if (isset($_GET["group_id"]))				{$group_id=$_GET["group_id"];}
  	elseif (isset($_POST["group_id"]))		{$group_id=$_POST["group_id"];}
  if (isset($_GET["download_type"]))			{$download_type=$_GET["download_type"];}
  	elseif (isset($_POST["download_type"]))	{$download_type=$_POST["download_type"];}
  
  if (strlen($shift)<2) {$shift='ALL';}
  if ($group_id=='SYSTEM_INTERNAL') {$download_type='systemdnc';}
  
  $report_name = 'Download List';
  $db_source = 'M';
  

  $stmt = "SELECT use_non_latin,outbound_autodial_active,slave_db_server,reports_use_slave_db,custom_fields_enabled,enable_languages,language_method,active_modules FROM system_settings;";
  $rslt=mysql_to_mysqli($stmt, $link);
  if ($DB) {echo "$stmt\n";}
  $qm_conf_ct = mysqli_num_rows($rslt);
  if ($qm_conf_ct > 0)
  	{
  	$row=mysqli_fetch_row($rslt);
  	$non_latin =					$row[0];
  	$outbound_autodial_active =		$row[1];
  	$slave_db_server =				$row[2];
  	$reports_use_slave_db =			$row[3];
  	$custom_fields_enabled =		$row[4];
  	$SSenable_languages =			$row[5];
  	$SSlanguage_method =			$row[6];
  	$active_modules =				$row[7];
  	}
  
  if ($non_latin < 1)
  	{
  	$PHP_AUTH_USER = preg_replace('/[^-_0-9a-zA-Z]/', '', $PHP_AUTH_USER);
  	$PHP_AUTH_PW = preg_replace('/[^-_0-9a-zA-Z]/', '', $PHP_AUTH_PW);
  	}
  else
  	{
  	$PHP_AUTH_PW = preg_replace("/'|\"|\\\\|;/","",$PHP_AUTH_PW);
  	$PHP_AUTH_USER = preg_replace("/'|\"|\\\\|;/","",$PHP_AUTH_USER);
  	}
  $list_id = preg_replace('/[^-_0-9a-zA-Z]/','',$list_id);
  $group_id = preg_replace('/[^-_0-9a-zA-Z]/','',$group_id);
  $download_type = preg_replace('/[^-_0-9a-zA-Z]/','',$download_type);
  
  $stmt="SELECT selected_language from vicidial_users where user='$PHP_AUTH_USER';";
  if ($DB) {echo "|$stmt|\n";}
  $rslt=mysql_to_mysqli($stmt, $link);
  $sl_ct = mysqli_num_rows($rslt);
  if ($sl_ct > 0)
  	{
  	$row=mysqli_fetch_row($rslt);
  	$VUselected_language =		$row[0];
  	}
  
  $auth=0;
  $auth_message = user_authorization($PHP_AUTH_USER,$PHP_AUTH_PW,'',1,0);
  if ($auth_message == 'GOOD')
  	{$auth=1;}

if ($auth > 0)
	{
	$stmt="SELECT count(*) from vicidial_users where user='$PHP_AUTH_USER' and user_level >= 7;";
	if ($DB) {echo "$stmt\n";}
	$rslt=mysql_to_mysqli($stmt, $link);
	$row=mysqli_fetch_row($rslt);
	$admin_auth=$row[0];
	
if ($admin_auth < 1)
		{
		Header ("Content-type: text/html; charset=utf-8");
		echo "Not Authorized !!\n";
		exit;
		}
}
  
  if ($auth < 1)
  	{
  	$VDdisplayMESSAGE = _QXZ("Login incorrect, please try again");
  	if ($auth_message == 'LOCK')
  		{
  		$VDdisplayMESSAGE = _QXZ("Too many login attempts, try again in 15 minutes");
  		Header ("Content-type: text/html; charset=utf-8");
  		echo "$VDdisplayMESSAGE: |$PHP_AUTH_USER|$auth_message|\n";
  		exit;
  		}
  	if ($auth_message == 'IPBLOCK')
  		{
  		$VDdisplayMESSAGE = _QXZ("Your IP Address is not allowed") . ": $ip";
  		Header ("Content-type: text/html; charset=utf-8");
  		echo "$VDdisplayMESSAGE: |$PHP_AUTH_USER|$auth_message|\n";
  		exit;
  		}
  	Header("WWW-Authenticate: Basic realm=\"CONTACT-CENTER-ADMIN\"");
  	Header("HTTP/1.0 401 Unauthorized");
  	echo "$VDdisplayMESSAGE: |$PHP_AUTH_USER|$PHP_AUTH_PW|$auth_message|\n";
  	exit;
  	}

$stmt="SELECT user_group AS user_group FROM vicidial_users WHERE user='$PHP_AUTH_USER';";
$rslt=mysql_to_mysqli($stmt, $link);
$lists_to_print = mysqli_num_rows($rslt);
while ($row = mysqli_fetch_row($rslt)) {
$d_user_group=$row[0];
}

$stmt="SELECT allowed_campaigns from vicidial_user_groups where user_group='$d_user_group';";
$rslt=mysql_to_mysqli($stmt, $link);
$row=mysqli_fetch_row($rslt);
if ( (!preg_match("/ALL-CAMPAIGNS/i",$row[0])) )
	{
	$LOGallowed_campaignsSQL = preg_replace('/\s-/i','',$row[0]);
	$LOGallowed_campaignsSQL = preg_replace('/\s/i',"','",$LOGallowed_campaignsSQL);
	$d_allowd_camp=$LOGallowed_campaignsSQL;
	}

$Today = shell_exec("echo $(date --date='-0 minutes' '+%Y-%m-%d')");

$Day_Start=$_GET['s_date'];
$Day_End=$_GET['f_date'];
$D_Search=$_GET['d_search'];
$D_Next_Count=$_GET['d_next_count'];

$D_Agent=$_GET['d_agent'];
$D_Camp=$_GET['d_camp'];
$D_Status=$_GET['d_status'];
$D_Phone=$_GET['d_phone'];
$D_Audit_Status=$_GET['d_audit_status'];


if ((!empty($Day_Start)) && (!empty($Day_End))) {
$Day_Start=$_GET['s_date'];
$Day_End=$_GET['f_date'];
$Deep_Selected_Day=('('.$Day_Start.' - '.$Day_End.')');
} else {
$Day_Start=shell_exec("echo $(date --date='-0 minutes' '+%Y-%m-%d')");
$Day_End=shell_exec("echo $(date --date='-0 minutes' '+%Y-%m-%d')");
$Deep_Selected_Day=('('.$Day_Start.' - '.$Day_End.')');
}

if ($D_Agent == "ALL") {
$D_Agent_Cond=" ";
} else {
$D_Agent_Cond="AND dvl.user = '$D_Agent'";
}

if ($D_Camp == "ALL") {
$D_Camp_Cond=" ";
} else {
$D_Camp_Cond="AND dvl.campaign_id = '$D_Camp'";
}

if ($D_Status == "ALL") {
$D_Status_Cond=" ";
} else {
$D_Status_Cond="AND dvl.status = '$D_Status'";
}

if((empty($D_Phone) )) {
$D_Phone_Cond=" ";
} else {
$D_Phone_Cond="AND (dvl.phone_number = '$D_Phone')";
}

if ($D_Search == "ListEntry") {
$D_Limit_Start="0";
$D_Limit_End=$D_Limit_Start+10;
$D_Limit_Show="10";
$D_Next_Count="1";
} 
if ($D_Search == "NextEntry") {
$D_Limit_Start=$D_Next_Count*10;
$D_Limit_End=$D_Limit_Start+10;
$D_Limit_Show="10";
$D_Next_Count=$D_Next_Count+1;
}


if ($D_Audit_Status == "Yes") {
$D_Audit_Status_Cond="AND dplug01.d_audit_status = '$D_Audit_Status'";
} elseif ($D_Audit_Status == "No") {
$D_Audit_Status_Cond="AND dplug01.d_audit_status IS NULL";
} else {
$D_Audit_Status_Cond=" ";
}

?>
<html>
  <head>
<script src="php_calendar/scripts1.js" type="text/javascript"></script>
</head>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>">
<TABLE BORDER="2" align="center">
 <TR>
  <TD align="center" colspan = "6"><font color="#0A0A2A" size="5"><b><i>!!..Audit Portal..!!</i></b></font></TD>
 </TR>
 <TR>
  <TD align="center" colspan = "6"><b><i> From 
  <input type="text" size="10" name="s_date" id="sdate" value=<? echo "$Day_Start"; ?>>
  <a href="javascript:viewcalendar1()"><img src="php_calendar/calendar.png" width="25" height="25 alt="" align="middle"/></a>
   To <input type="text" size="10" name="f_date" id="fdate" value=<? echo "$Day_End"; ?>>
  <a href="javascript:viewcalendar2()"><img src="php_calendar/calendar.png" width="25" height="25 alt="" align="middle"/></a>
  </i></b></TD>
 </TR>
  <TR>
 <TD align="center" colspan = "1"><b><i>AgentID</i></b></TD>
 <TD align="center" colspan = "1"><b><i>Campaign</i></b></TD> 
 <TD align="center" colspan = "1"><b><i>Disposion</i></b></TD>
 <TD align="center" colspan = "1"><b><i>Customer Phone</i></b></TD>
 <TD align="center" colspan = "2"><b><i>Audit Status</i></b></TD>


 </TR>
   <TR>
<TD align="center"><b><i>
<select size="0" name="d_agent" required>
<?php
if((empty($D_Agent) )) {
echo "<option value='ALL' selected>ALL</option>";
} else {
echo "<option value='$D_Agent' selected>$D_Agent</option>";
echo "<option value='ALL'>ALL</option>";
}
$stmt="SELECT DISTINCT(user) AS user FROM vicidial_users WHERE user_level = '1' AND user_group='$d_user_group' AND user NOT IN ('VDCL', 'VDAD') ORDER BY user ASC;";
$rslt=mysql_to_mysqli($stmt, $link);
$lists_to_print = mysqli_num_rows($rslt);
while ($row = mysqli_fetch_row($rslt)) {
echo "<option value='$row[0]'>$row[0]</option>";
} 
?> 
</select>
</i></b></TD>
<TD align="center"><b><i>
<select size="0" name="d_camp" required>
<?php
if((empty($D_Camp) )) {
echo "<option value='ALL' selected>ALL</option>";
} else {
echo "<option value='$D_Camp' selected>$D_Camp</option>";
echo "<option value='ALL'>ALL</option>";
}
$stmt="SELECT DISTINCT(campaign_id) AS campaign_id FROM vicidial_campaigns WHERE campaign_id IN('$d_allowd_camp') ORDER BY campaign_id ASC;";
$rslt=mysql_to_mysqli($stmt, $link);
$lists_to_print = mysqli_num_rows($rslt);
while ($row = mysqli_fetch_row($rslt)) {
echo "<option value='$row[0]'>$row[0]</option>";
} 
?> 
</select>
</i></b></TD> 
<TD align="center"><b><i>
<select size="0" name="d_status" required>
<?php
if((empty($D_Status) )) {
echo "<option value='ALL' selected>ALL</option>";
} else {
echo "<option value='$D_Status' selected>$D_Status</option>";
echo "<option value='ALL'>ALL</option>";
}
$stmt="
(SELECT DISTINCT(status) AS status FROM vicidial_statuses WHERE selectable = 'Y')
UNION
(SELECT DISTINCT(status) AS status FROM vicidial_campaign_statuses WHERE selectable = 'Y')
ORDER BY status
;";
$rslt=mysql_to_mysqli($stmt, $link);
$lists_to_print = mysqli_num_rows($rslt);
while ($row = mysqli_fetch_row($rslt)) {
echo "<option value='$row[0]'>$row[0]</option>";
} 
?> 
</select>
</i></b></TD> 
  <TD align="center" colspan = "1"><b><i>
 <input id="find" type="text" name="d_phone" style="width:100%;resize:none" size="5" maxlength="10" minlength="9" placeholder="Optional"/> 
  </i></b></TD>
  

  <TD align="center"><b><i>
<select size="0" name="d_audit_status" required>
<?php
if($D_Audit_Status == "Yes") {
echo "<option value='Yes'>Audited Calls</option>";
} elseif ($D_Audit_Status == "No") {
echo "<option value='No'>Non Audited Calls</option>";
} else {
echo "<option value='All'>Ignore - Show All Calls</option>";
}
?>
<option value='All'>Ignore - Show All Calls</option>
<option value='Yes'>Audited Calls</option>
<option value='No'>Non Audited Calls</option>
</select>
</i></b></TD>
 </TR>
  <TR>
   </TR>

<TR>
<TD align="center" colspan = "6"><b><i>
<input type='hidden' id='d_search' name='d_search' value='ListEntry'>
<button type="submit" name="submit" value="Submit" >Search</button>
</i></b></TD>
</TR>
</form>
</TABLE>


<TABLE BORDER="2" align="center" id='d_report'>

<?
if (($D_Search == "ListEntry") || ($D_Search == "NextEntry")) {

$stmt="
SELECT dvl.uniqueid, 
dvl.lead_id, 
dvl.campaign_id, 
dvl.length_in_sec, 
dvl.status,
dvl.phone_number,
dvl.user,
drl.vicidial_id,
drl.lead_id,
drl.recording_id,
drl.location,
dplug01.d_audit_status,
dvl.call_date,
(
SELECT COUNT(*) 
FROM vicidial_log dvl, recording_log drl
WHERE dvl.uniqueid = drl.vicidial_id
$D_Agent_Cond
$D_Camp_Cond
$D_Status_Cond
$D_Phone_Cond
AND (dvl.call_date >= '$Day_Start 00:00:00' and dvl.call_date <= '$Day_End 23:59:59')
AND dvl.user_group='$d_user_group'
) AS Total

FROM vicidial_log dvl 
INNER JOIN recording_log drl ON dvl.uniqueid = drl.vicidial_id
LEFT JOIN d_plug_01_audit_portal dplug01 ON dvl.uniqueid = dplug01.uniqueid
WHERE dvl.uniqueid = drl.vicidial_id
$D_Agent_Cond
$D_Camp_Cond
$D_Status_Cond
$D_Phone_Cond
$D_Audit_Status_Cond
AND (dvl.call_date >= '$Day_Start 00:00:00' and dvl.call_date <= '$Day_End 23:59:59')
AND dvl.user_group='$d_user_group'
LIMIT $D_Limit_Start, $D_Limit_Show
 ;";	


	$rslt=mysql_to_mysqli($stmt, $link);
	$lists_to_print = mysqli_num_rows($rslt);

echo "<TR>"; 
echo "<TD align='center' colspan = '4'><font color='#0A0A2A' size='4'><b><i>Report Date : $Day_Start To $Day_End</i></b></font></TD>"; 
echo "<TD align='center' colspan = '5'><font color='#0A0A2A' size='4'><b><i>Showing Result : $lists_to_print</i></b></font></TD>"; 
echo "</TR>"; 
echo "<TR>"; 
echo "<TD align='center' colspan = '1'><font color='#0A0A2A' size='3'><b><i>Call Date & Time</i></b></font></TD>"; 
echo "<TD align='center' colspan = '1'><font color='#0A0A2A' size='3'><b><i>AgentID</i></b></font></TD>"; 
echo "<TD align='center' colspan = '1'><font color='#0A0A2A' size='3'><b><i>Campaign</i></b></font></TD>"; 
echo "<TD align='center' colspan = '1'><font color='#0A0A2A' size='3'><b><i>Disposion</i></b></font></TD>"; 
echo "<TD align='center' colspan = '1'><font color='#0A0A2A' size='3'><b><i>Customer Phone</i></b></font></TD>"; 
echo "<TD align='center' colspan = '1'><font color='#0A0A2A' size='3'><b><i>Audited</i></b></font></TD>"; 
echo "<TD align='center' colspan = '2'><font color='#0A0A2A' size='3'><b><i>Recording</i></b></font></TD>";  
echo "<TD align='center' colspan = '1'><font color='#0A0A2A' size='3'><b><i>Audit Now</i></b></font></TD>";
echo "</TR>"; 

#$d_bgcolor="Yellow";

		
	$o=0;
$d_serial_count="0";

	while ($lists_to_print > $o)
		{
			$row=mysqli_fetch_row($rslt);
			
			$d_serial_count= $o + 1;	

$d_bgcolor="";

$d_uniqueid=$row[0];
$d_lead_id=$row[1]; 
$d_campaign_id=$row[2]; 
$d_length_in_sec=$row[3]; 
$d_status=$row[4];
$d_phone_number=$row[5];
$d_user=$row[6];
$d_vicidial_id=$row[7];
$d_lead_id=$row[8];
$d_recording_id=$row[9];
$d_location=$row[10];
$d_audit_status=$row[11];
$d_call_date=$row[12];
$d_total=$row[13];

		
echo "<tr bgcolor=$d_bgcolor>";
echo "<td align='center'>$d_call_date</td>";
echo "<td align='center'>$d_user</td>";
echo "<td align='center'>$d_campaign_id</td>";
echo "<td align='center'>$d_status</td>";
echo "<td align='center'>$d_phone_number</td>";
echo "<td align='center'>$d_audit_status</td>";
echo "<td align='center' colspan='2'>
<audio controls='' preload='none'> 
<source src='$d_location' type='audio/wav'> 
<source src='$d_location' type='audio/mpeg'>
No browser audio playback support
</audio>
</td>";
if($d_audit_status == 'Yes') {
echo "<td align='center'>-</a></td>";
} else {
echo "<td align='center'><a href=\"d_audit_portal.php?s_date=$Day_Start&f_date=$Day_End&d_agent=$D_Agent&d_camp=$D_Camp&d_status=$D_Status&d_phone=$D_Phone&d_audit_status=$D_Audit_Status&d_next_count=$D_Next_Count&d_search=AuditNow&d_lead_id=$d_lead_id&d_uniqueid=$d_uniqueid&d_vicidial_id=$d_vicidial_id\">Audit</a></td>";
}
		echo "</tr>";
//		$lists_printed .= "'$row[0]',";

		$o++;
		}

$D_Limit_Start=$D_Limit_Start+1;


		echo "<tr>";
if( ($d_total < $D_Limit_End) && ($d_total >= $D_Limit_Start) )
	{
	    echo "<TD align='center' colspan = '1'><font color='#0A0A2A' size='3'><b><i>-</i></b></font></TD>"; 
		echo "<TD align='center' colspan = '4'><font color='#0A0A2A' size='3'><b><i>Showing Result : $lists_to_print Out Of $d_total</i></b></font></TD>"; 
		echo "<TD align='center' colspan = '4'><font color='#0A0A2A' size='3'><b><i>Showing Result : $D_Limit_Start - $d_total</i></b></font></TD>"; 
} elseif  ( ($d_total < $D_Limit_End) && ($d_total < $D_Limit_Start) )
	{
		echo "<TD align='center' colspan = '9'><font color='#0A0A2A' size='3'><b><i>No Result Found</i></b></font></TD>"; 

	} else {
		echo "<td align='center'><a href=\"d_audit_portal.php?s_date=$Day_Start&f_date=$Day_End&d_agent=$D_Agent&d_camp=$D_Camp&d_status=$D_Status&d_phone=$D_Phone&&d_audit_status=$D_Audit_Status&d_search=NextEntry&d_next_count=$D_Next_Count\">Next</a></td>";
		echo "<TD align='center' colspan = '4'><font color='#0A0A2A' size='3'><b><i>Showing Result : $lists_to_print Out Of $d_total</i></b></font></TD>"; 
		echo "<TD align='center' colspan = '4'><font color='#0A0A2A' size='3'><b><i>Showing Result : $D_Limit_Start - $D_Limit_End</i></b></font></TD>"; 
	}		

	echo "</tr>";

//echo "</table>";
}

###################################################
if (($D_Search == "AuditNow")) {

$d_uniqueid=$_GET['d_uniqueid'];
$d_lead_id=$_GET['d_lead_id'];
$d_vicidial_id=$_GET['d_vicidial_id'];

$stmt="
SELECT dvl.uniqueid, 
dvl.lead_id, 
dvl.campaign_id, 
dvl.length_in_sec, 
dvl.status,
dvl.phone_number,
dvl.user,
drl.length_in_sec,
drl.filename,
drl.recording_id,
drl.location,
drl.length_in_min,
dvl.user_group, 
dvl.call_date
FROM vicidial_log dvl, recording_log drl
WHERE dvl.uniqueid = drl.vicidial_id
AND dvl.uniqueid = '$d_uniqueid'
AND dvl.lead_id = '$d_lead_id'
AND drl.vicidial_id = '$d_vicidial_id'
AND dvl.user_group='$d_user_group'
LIMIT 1
 ;";	


	$rslt=mysql_to_mysqli($stmt, $link);
	$lists_to_print = mysqli_num_rows($rslt);

echo "<TR>"; 
echo "<TD align='center' colspan = '4'><font color='#0A0A2A' size='3'><b><i>Report Date : $Day_Start To $Day_End</i></b></font></TD>"; 
echo "<TD align='center' colspan = '5'><font color='#0A0A2A' size='3'><b><i>Showing Result : $lists_to_print</i></b></font></TD>"; 
echo "</TR>"; 
echo "<TR>"; 
echo "<TD align='center' colspan = '1'><font color='#0A0A2A' size='3'><b><i>Call Date & Time</i></b></font></TD>"; 
echo "<TD align='center' colspan = '1'><font color='#0A0A2A' size='3'><b><i>AgentID</i></b></font></TD>"; 
echo "<TD align='center' colspan = '1'><font color='#0A0A2A' size='3'><b><i>Campaign</i></b></font></TD>"; 
echo "<TD align='center' colspan = '1'><font color='#0A0A2A' size='3'><b><i>Disposion</i></b></font></TD>"; 
echo "<TD align='center' colspan = '1'><font color='#0A0A2A' size='3'><b><i>Customer Phone</i></b></font></TD>"; 
echo "<TD align='center' colspan = '1'><font color='#0A0A2A' size='3'><b><i>Auditing By</i></b></font></TD>"; 
echo "<TD align='center' colspan = '2'><font color='#0A0A2A' size='3'><b><i>Recording</i></b></font></TD>";  
echo "<TD align='center' colspan = '1'><font color='#0A0A2A' size='3'><b><i>Go Back</i></b></font></TD>";
echo "</TR>"; 

		
	$o=0;
$d_serial_count="0";

	while ($lists_to_print > $o)
		{
			$row=mysqli_fetch_row($rslt);
			
			$d_serial_count= $o + 1;	

$d_bgcolor="";

$d_uniqueid=$row[0];
$d_lead_id=$row[1]; 
$d_campaign_id=$row[2]; 
$d_length_in_sec=$row[3]; 
$d_status=$row[4];
$d_phone_number=$row[5];
$d_user=$row[6];
$d_rec_length_in_sec=$row[7];
$d_rec_filename=$row[8];
$d_recording_id=$row[9];
$d_location=$row[10];
$d_rec_length_in_min=$row[11];
$d_user_group=$row[12];
$d_call_date=$row[13];
		
echo "<tr bgcolor=$d_bgcolor>";
echo "<td align='center'>$d_call_date</td>";
echo "<td align='center'>$d_user</td>";
echo "<td align='center'>$d_campaign_id</td>";
echo "<td align='center'>$d_status</td>";
echo "<td align='center'>$d_phone_number</td>";
echo "<td align='center'>$PHP_AUTH_USER</td>";
echo "<td align='center' colspan='2'>
<audio controls='' preload='none'> 
<source src='$d_location' type='audio/wav'> 
<source src='$d_location' type='audio/mpeg'>
No browser audio playback support
</audio>
</td>";
$dr_d_next_count=$d_next_count-1;
echo "<td align='center'><a href=\"d_audit_portal.php?s_date=$Day_Start&f_date=$Day_End&d_agent=$D_Agent&d_camp=$D_Camp&d_status=$D_Status&d_phone=$D_Phone&d_audit_status=$D_Audit_Status&d_next_count=$D_Next_Count&d_search=NextEntry&d_next_count=$d_next_count\">Click To Return</a></td>";

		echo "</tr>";
		$o++;
		}


$stmt="SELECT 
lead_id,
vendor_lead_code,
list_id,
gmt_offset_now,
phone_code,
phone_number,
title,
first_name,
middle_initial,
last_name,
address1,
address2,
address3,
city,
state,
province,
postal_code,
country_code,
gender,
date_of_birth,
alt_phone,
email,
security_phrase,
comments,
entry_date,
modify_date,
status,
source_id,
called_since_last_reset,
called_count,
last_local_call_time,
rank,
owner,
entry_list_id
FROM vicidial_list 
WHERE lead_id = '$d_lead_id'
LIMIT 1
 ;";	


	$rslt=mysql_to_mysqli($stmt, $link);
	$lists_to_print = mysqli_num_rows($rslt);

$o=0;
$d_serial_count="0";

	while ($lists_to_print > $o)
		{
			$row=mysqli_fetch_row($rslt);
			
			$d_serial_count= $o + 1;
			
$Deep_lead_id=$row[0];
$Deep_vendor_id=$row[1];
$Deep_list_id=$row[2];
$Deep_gmt_offset_now=$row[3];
$Deep_phone_code=$row[4];
$Deep_phone_number=$row[5];
$Deep_title=$row[6];
$Deep_first_name=$row[7];
$Deep_middle_initial=$row[8];
$Deep_last_name=$row[9];
$Deep_address1=$row[10];
$Deep_address2=$row[11];
$Deep_address3=$row[12];
$Deep_city=$row[13];
$Deep_state=$row[14];
$Deep_province=$row[15];
$Deep_postal_code=$row[16];
$Deep_country_code=$row[17];
$Deep_gender=$row[18];
$Deep_date_of_birth=$row[19];
$Deep_alt_phone=$row[20];
$Deep_email=$row[21];
$Deep_security_phrase=$row[22];
$Deep_comments=$row[23];
$Deep_comments=$row[23];
$Deep_entry_date=$row[24];
$Deep_modify_date=$row[25];
$Deep_status=$row[26];
$Deep_source_id=$row[27];
$Deep_called_since_last_reset=$row[28];
$Deep_called_count=$row[29];
$Deep_last_local_call_time=$row[30];
$Deep_rank=$row[31];
$Deep_owner=$row[32];
$Deep_entry_list_id=$row[33];
		$o++;
		}

echo "<form action='d_actions.php'>";
echo " <TR bgcolor=yellow>";
echo "  <TD align='center' colspan = '9'><font  size='5'><b><i>Audit Panel</i></b></font></TD>";
echo " </TR>";

echo "    <TR bgcolor='#FF7F50'>";
echo " <TD align='center' colspan = '9'><font color='#0A0A2A' size='3'><b><i>Customer Information</i></b></font></TD>";
echo "   </TR>";

echo "<TR>";
echo " <TR bgcolor='#69F0AE'>";
echo " <TD align='center' colspan = '1'><font color='#0A0A2A' size='3'><b><i>Title</i></b></font></TD>";
echo " <TD align='center' colspan = '2'><font color='#0A0A2A' size='3'><b><i>First Name</i></b></font></TD>";
echo " <TD align='center' colspan = '1'><font color='#0A0A2A' size='3'><b><i>Middle Name</i></b></font></TD>";
echo " <TD align='center' colspan = '2'><font color='#0A0A2A' size='3'><b><i>Last Name</i></b></font></TD>";
echo " <TD align='center' colspan = '1'><font color='#0A0A2A' size='3'><b><i>Address 1</i></b></font></TD>";
echo " <TD align='center' colspan = '1'><font color='#0A0A2A' size='3'><b><i>Address 2</i></b></font></TD>";
echo " <TD align='center' colspan = '1'><font color='#0A0A2A' size='3'><b><i>Address 3</i></b></font></TD>";
echo " </TR>";
echo "<TR>";
echo " <TD align='center' colspan = '1'><input type='text' name='d_title' value='$Deep_title' style='width:100%;resize:none' size='15' maxlength='4'  /></TD>";
echo " <TD align='center' colspan = '2'><input type='text' name='d_first_name' value='$Deep_first_name' style='width:100%;resize:none' size='15' maxlength='30'  /></TD>";
echo " <TD align='center' colspan = '1'><input type='text' name='d_middle_initial' value='$Deep_middle_initial' style='width:100%;resize:none' size='15' maxlength='1' /></TD>";
echo " <TD align='center' colspan = '2'><input type='text' name='d_last_name' value='$Deep_last_name' style='width:100%;resize:none' size='15' maxlength='30' /></TD>";
echo " <TD align='center' colspan = '1'><input type='text' name='d_address1' value='$Deep_address1' style='width:100%;resize:none' size='15' maxlength='100' /></TD>";
echo " <TD align='center' colspan = '1'><input type='text' name='d_address2' value='$Deep_address2' style='width:100%;resize:none' size='15' maxlength='100' /></TD>";
echo " <TD align='center' colspan = '1'><input type='text' name='d_address3' value='$Deep_address3' style='width:100%;resize:none' size='15' maxlength='100' /></TD>";
echo " </TR>";

echo "  <TR bgcolor='#69F0AE'>";
echo " <TD align='center' colspan = '1'><font color='#0A0A2A' size='3'><b><i>City</i></b></font></TD>";
echo " <TD align='center' colspan = '1'><font color='#0A0A2A' size='3'><b><i>State</i></b></font></TD>";
echo " <TD align='center' colspan = '1'><font color='#0A0A2A' size='3'><b><i>Province</i></b></font></TD>";
echo " <TD align='center' colspan = '1'><font color='#0A0A2A' size='3'><b><i>Postcode</i></b></font></TD>";
echo " <TD align='center' colspan = '1'><font color='#0A0A2A' size='3'><b><i>Telephone</i></b></font></TD>";
echo " <TD align='center' colspan = '1'><font color='#0A0A2A' size='3'><b><i>Alternate No</i></b></font></TD>";
echo " <TD align='center' colspan = '3'><font color='#0A0A2A' size='3'><b><i>Email</i></b></font></TD>";
echo " </TR>";
echo "<TR>";
echo " <TD align='center' colspan = '1'><input type='text' name='d_city' value='$Deep_city' style='width:100%;resize:none' size='15' maxlength='50'/></TD>";
echo " <TD align='center' colspan = '1'><input type='text' name='d_state' value='$Deep_state' style='width:100%;resize:none' size='2'  maxlength='2'/></TD>";
echo " <TD align='center' colspan = '1'><input type='text' name='d_province' value='$Deep_province' style='width:100%;resize:none' size='15' maxlength='50' /></TD>";
echo " <TD align='center' colspan = '1'><input type='text' name='d_postcode' value='$Deep_postal_code' style='width:100%;resize:none' size='15' maxlength='10' /></TD>";
echo " <TD align='center' colspan = '1'><font color='#0A0A2A' size='3'><b><i>$d_phone_number</i></b></font></TD>";
echo " <TD align='center' colspan = '1'><input type='text' name='d_alt_phone' value='$Deep_alt_phone' style='width:100%;resize:none' size='15' maxlength='12' minlength='9' /></TD>";
echo " <TD align='center' colspan = '3'><input type='text' name='d_email' value='$Deep_email' style='width:100%;resize:none' size='70' /></TD>";
echo " </TR>";

echo " <TR bgcolor='#FF7F50'>";
echo " <TD align='center' colspan = '9'><font color='#0A0A2A' size='3'><b><i>Call Observation</i></b></font></TD>";
echo " </TR>";

echo " <TR>";
echo " <TD align='center' colspan = '2' bgcolor='81D4FA'><font color='#0A0A2A' size='3'><b><i>Call Status</i></b></font></TD> ";
echo " <TD align='center' colspan = '2' bgcolor='81D4FA'><font color='#0A0A2A' size='3'><b><i>Call Quality</i></b></font></TD> ";
echo " <TD align='center' colspan = '2' bgcolor='81D4FA'><font color='#0A0A2A' size='3'><b><i>Agent Feedback</i></b></font></TD> ";
echo " <TD align='center' colspan = '2' bgcolor='81D4FA'><font color='#0A0A2A' size='3'><b><i>Customer Feedback</i></b></font></TD> ";
echo " <TD align='center' colspan = '1' bgcolor='81D4FA'><font color='#0A0A2A' size='3'><b><i>Audio Quality</i></b></font></TD> ";
echo " </TR>";
echo " <TD align='center' colspan = '2'><b><i>";
echo "<select size='0' name='d_call_status' required>";
echo "<option value='Pass' selected>Pass</option>";
echo "<option value='Fail'>Fail</option>";
echo "</select>";
echo "</i></b></TD>";
echo " <TD align='center' colspan = '2'><b><i>";
echo "<select size='0' name='d_call_quality' required>";
echo "<option value='Worse'>Worse</option>";
echo "<option value='Poor'>Poor</option>";
echo "<option value='Average'>Average</option>";
echo "<option value='Good' selected>Good</option>";
echo "<option value='Best'>Best</option>";
echo "<option value='Top'>Top</option>";
echo "</select>";
echo "</i></b></TD>";
echo " <TD align='center' colspan = '2'><b><i>";
echo "<select size='0' name='d_agent_feedback' required>";
echo "<option value='Required'>Required</option>";
echo "<option value='NotRequired' selected>Not Required</option>";
echo "</select>";
echo "</i></b></TD>";
echo " <TD align='center' colspan = '2'><b><i>";
echo "<select size='0' name='d_cust_feedback' required>";
echo "<option value='Irate'>Irate</option>";
echo "<option value='Abusive'>Abusive</option>";
echo "<option value='Polite'>Polite</option>";
echo "<option value='Sarcastic'>Sarcastic</option>";
echo "<option value='Good' selected>Good</option>";
echo "</select>";
echo "</i></b></TD>";
echo " <TD align='center' colspan = '1'><b><i>";
echo "<select size='0' name='d_audio_quality' required>";
echo "<option value='Bad'>Bad</option>";
echo "<option value='Average'>Average</option>";
echo "<option value='Good' selected>Good</option>";
echo "</select>";
echo "</i></b></TD>";
echo "  </TR>";

echo " <TR bgcolor='#81D4FA'>";
echo " <TD align='center' colspan = '9'><font color='#0A0A2A' size='3'><b><i>Comment Box</i></b></font></TD>";
echo " </TR>";

echo "<TR>";
echo " <TD colspan = '9'><textarea name='d_auditor_comments' style='width:100%;resize:none' placeholder='Overall Comments'></textarea> </TD>";
echo " </TR>";
echo "<TR bgcolor=Yellow>";
echo "<TD align='center' colspan = '9'><b><i>";
echo "<input type='hidden' name='d_uniqueid' value='$d_uniqueid'>";
echo "<input type='hidden' name='d_lead_id' value='$d_lead_id'>";
echo "<input type='hidden' name='d_recording_id' value='$d_recording_id'>";
echo "<input type='hidden' name='d_rec_length_in_sec' value='$d_rec_length_in_sec'>";
echo "<input type='hidden' name='d_rec_length_in_min' value='$d_rec_length_in_min'>";
echo "<input type='hidden' name='d_rec_filename' value='$d_rec_filename'>";
echo "<input type='hidden' name='d_campaign_id' value='$d_campaign_id'>";
echo "<input type='hidden' name='d_length_in_sec' value='$d_length_in_sec'>";
echo "<input type='hidden' name='d_call_date' value='$d_call_date'>";
echo "<input type='hidden' name='d_user' value='$d_user'>";
echo "<input type='hidden' name='d_user_group' value='$d_user_group'>";
echo "<input type='hidden' name='d_auditor' value='$PHP_AUTH_USER'>";
echo "<input type='hidden' name='d_phone_number' value='$d_phone_number'>";
echo "<input type='hidden' name='d_status' value='$d_status'>";
echo "<input type='hidden' name='dr_s_date' value='$Day_Start'>";
echo "<input type='hidden' name='dr_f_date' value='$Day_End'>";
echo "<input type='hidden' name='dr_d_agent' value='$D_Agent'>";
echo "<input type='hidden' name='dr_d_camp' value='$D_Camp'>";
echo "<input type='hidden' name='dr_d_status' value='$D_Status'>";
echo "<input type='hidden' name='dr_d_phone' value='$D_Phone'>";
echo "<input type='hidden' name='dr_d_audit_status' value='$D_Audit_Status'>";
echo "<input type='hidden' name='dr_d_next_count' value='$D_Next_Count'>";
echo "<input type='hidden' name='d_action_code' value='AuditUpdate'>";
echo "<button type='submit' name='submit' value='Submit' >Submit</button>";
echo "</i></b></TD>";
echo "</TR>";
echo " </form>";
}

?>
</TABLE>
</html>