<?php
// https://whmcs.community/topic/296515-hook-to-show-recent-imported-ticket-failures-on-support-tickets-page/
// version 1.5

use Illuminate\Database\Capsule\Manager as Capsule; 

add_hook('AdminSupportTicketPagePreTickets', 1, function($vars) {
	$adminid = WHMCS\Session::get("adminid");
	if ($adminid) {
	
	$output = "<div id='recently-blocked'><h2>Recently Blocked Messages   <small><i class='far fa-eye'></i> <a href='systemmailimportlog.php' target='_blank'>SEE ALL</a></small></h2>
		<table id='sortabletbl1' class='datatable' style='width:100%'>
		<tr><th>Date</th><th>Name/Email</th><th>Subject (click to view)</th><th>Status</th></tr>";
	
	foreach (Capsule::table('tblticketmaillog')->where('status', 'not like', '%successful%')->where('email', 'not like', 'mailer-daemon%')->orderBy('id', 'desc')->limit(10)->get() as $msg){

		/* Name */
		if ($msg->name === $msg->email) $name = $msg->name;
		else $name = "{$msg->name} <{$msg->email}>";
		
		/* Date */
		$today = new DateTime();
		$msg_date = new DateTime($msg->date);
		$interval = $today->diff($msg_date);
		$date_interval = abs($interval->format('%R%a'));
		
		if ($date_interval == 0 && $interval->h < date('H')) {
		    $date_interval = 'Today';
		}
		else if ($date_interval == 0 && $interval->h >= date('H')){
		    $date_interval = 'Yesterday';
		}
		else if ($date_interval == 1) $date_interval = 'Yesterday';
		else $date_interval .= ' days ago';

		$output .= "
		<tr>
			
		<td>$date_interval</td><td>$name</td><td><a href=\"/{$GLOBALS['customadminpath']}/logs/system-mail-import-log/record/{$msg->id}\"  class=\"open-modal\" data-modal-title=\"Viewing Email Message Log Entry\">{$msg->subject}</a></td><td>{$msg->status}</td></tr>";
		
	}
	
	return  "$output</table><br /></div>
	<script>
        jQuery(document).ready(function($){ /* Move to bottom of page */
        $('#recently-blocked').appendTo('.content');
        });
    </script>";
	}
});
