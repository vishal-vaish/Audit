<?php 
  # Added By Deep
  
  require("../vicidial/dbconnect_mysqli.php");
  require("../vicidial/functions.php");

        if (isset($_GET["d_activity"]))						{$d_activity=$_GET["d_activity"];}
                elseif (isset($_POST["d_activity"]))			{$d_activity=$_POST["d_activity"];}
        $d_activity=preg_replace("/\'|\"|\\\\|;|/","",$d_activity);
        if (isset($_GET["d_action_code"]))						{$d_action_code=$_GET["d_action_code"];}
                elseif (isset($_POST["d_action_code"]))			{$d_action_code=$_POST["d_action_code"];}
        $d_action_code=preg_replace("/\'|\"|\\\\|;|/","",$d_action_code);		
	
$Deep_d_title=$_GET['d_title'];
$Deep_d_first_name=$_GET['d_first_name'];
$Deep_d_middle_initial=$_GET['d_middle_initial'];
$Deep_d_last_name=$_GET['d_last_name'];
$Deep_d_address1=$_GET['d_address1'];
$Deep_d_address2=$_GET['d_address2'];
$Deep_d_address3=$_GET['d_address3'];
$Deep_d_city=$_GET['d_city'];
$Deep_d_state=$_GET['d_state'];
$Deep_d_province=$_GET['d_province'];
$Deep_d_postcode=$_GET['d_postcode'];
$Deep_d_alt_phone=$_GET['d_alt_phone'];
$Deep_d_email=$_GET['d_email'];
$d_call_status=$_GET['d_call_status'];
$d_call_quality=$_GET['d_call_quality'];
$d_agent_feedback=$_GET['d_agent_feedback'];
$d_cust_feedback=$_GET['d_cust_feedback'];
$d_audio_quality=$_GET['d_audio_quality'];
$d_auditor_comments=$_GET['d_auditor_comments'];
$d_uniqueid=$_GET['d_uniqueid'];
$d_lead_id=$_GET['d_lead_id'];
$d_recording_id=$_GET['d_recording_id'];
$d_rec_length_in_sec=$_GET['d_rec_length_in_sec'];
$d_rec_length_in_min=$_GET['d_rec_length_in_min'];
$d_rec_filename=$_GET['d_rec_filename'];
$d_campaign_id=$_GET['d_campaign_id'];
$d_length_in_sec=$_GET['d_length_in_sec'];
$d_call_date=$_GET['d_call_date'];
$d_user=$_GET['d_user'];
$d_user_group=$_GET['d_user_group'];
$d_auditor=$_GET['d_auditor'];
$d_phone_number=$_GET['d_phone_number'];
$d_status=$_GET['d_status'];
$dr_s_date=$_GET['dr_s_date'];
$dr_f_date=$_GET['dr_f_date'];
$dr_d_agent=$_GET['dr_d_agent'];
$dr_d_camp=$_GET['dr_d_camp'];
$dr_d_status=$_GET['dr_d_status'];
$dr_d_phone=$_GET['dr_d_phone'];
$dr_d_audit_status=$_GET['dr_d_audit_status'];
$dr_d_next_count=$_GET['dr_d_next_count'];
$dr_d_next_count=$dr_d_next_count-1;


if ($d_action_code == 'AuditUpdate')
        {
		
$stmt="INSERT INTO d_plug_01_audit_portal 
SET 
uniqueid = '$d_uniqueid',
lead_id = '$d_lead_id',
recording_id = '$d_recording_id',
d_rec_length_in_sec = '$d_rec_length_in_sec',
d_rec_length_in_min = '$d_rec_length_in_min',
d_rec_filename = '$d_rec_filename',
campaign_id = '$d_campaign_id',
call_date = '$d_call_date',
length_in_sec = '$d_length_in_sec',
status = '$d_status',
user = '$d_user',
user_group = '$d_user_group',
d_auditor = '$d_auditor',
d_audit_status = 'Yes',
d_call_status = '$d_call_status',
d_call_quality = '$d_call_quality',
d_agent_feedback = '$d_agent_feedback',
d_cust_feedback = '$d_cust_feedback',
d_recording_status = '$d_audio_quality',
phone_number = '$d_phone_number',
title = '$Deep_d_title',
first_name = '$Deep_d_first_name',
middle_initial = '$Deep_d_middle_initial',
last_name = '$Deep_d_last_name',
address1 = '$Deep_d_address1',
address2 = '$Deep_d_address2',
address3 = '$Deep_d_address3',
city = '$Deep_d_city',
state = '$Deep_d_state',
province = '$Deep_d_province',
postal_code = '$Deep_d_postcode',
alt_phone = '$Deep_d_alt_phone',
email = '$Deep_d_email',
called_count = '0',
d_auditor_comments = '$d_auditor_comments'
;";
	
$rslt=mysql_to_mysqli($stmt, $link);

header("Location: d_audit_portal.php?d_audit_portal.php?s_date=$dr_s_date&f_date=$dr_f_date&d_agent=$dr_d_agent&d_camp=$dr_d_camp&d_status=$dr_d_status&d_phone=$dr_d_phone&d_audit_status=$dr_d_audit_status&d_search=NextEntry&d_next_count=$dr_d_next_count");
        }

?>
