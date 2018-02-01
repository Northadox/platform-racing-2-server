<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/output_fns.php');

$action = find('action', 'none');
$ban_id = find('ban_id');

try {
	
	//sanity
	if(!isset($ban_id)) {
		throw new Exception('No ban_id');
	}


	//connect
	$db = new DB();


	//make sure you're a moderator
	$mod = check_moderator($db);
	$user_id = $mod->user_id;
	$name = $mod->name;
	$safe_name = addslashes($name);
	
	
	// ------------------------------------------------------------------
	// --- edit an existing ban, then redirect to that ban listing
	// ------------------------------------------------------------------
	if($action === 'edit') {
	
		$safe_ban_id = $db->escape($ban_id);
		$safe_account_ban = $db->escape(0+!!find('account_ban'));
		$safe_ip_ban = $db->escape(0+!!find('ip_ban'));
		$safe_expire_time = $db->escape(find('expire_time'));
		$safe_notes = $db->escape(find('notes'));
	
		//update the ban
		$query = "UPDATE bans
				SET account_ban = '$safe_account_ban',
				ip_ban = '$safe_ip_ban',
				expire_time = UNIX_TIMESTAMP('$safe_expire_time'),
				notes = '$safe_notes',
				modified_time = NOW()
				WHERE ban_id = '$safe_ban_id'
				LIMIT 1";
		$db->query($query, 'ban_update', 'Could not update ban. query: ' . $query);
	
		//action log
		$expire_time = find('expire_time');
		$notes = find('notes');
		$is_account_ban = bintoyesno($safe_account_ban);
		$is_ip_ban = bintoyesno($safe_ip_ban);
		
		if(is_empty($notes)) {
			$disp_notes = "no notes";
		}
		else {
			$disp_notes = "notes: $notes";
		}
	
		//record the change
		$db->call('mod_action_insert', array($mod->user_id, "$mod->name edited ban $ban_id {account_ban: $is_account_ban, ip_ban: $is_ip_ban, expire_time: $expire_time, $disp_notes}", 0, get_ip()));
		
		//redirect to the ban listing
		header("Location: https://pr2hub.com/bans/show_record.php?ban_id=$ban_id");
		die();
	}
	
	
	// --------------------------------------------------------------------------
	// --- display a form containing the current ban data 
	// --------------------------------------------------------------------------
	else {
		$ban = $db->grab_row('ban_select', array($ban_id));
		output_header('Edit Ban');
		output_form($ban);
		output_footer();
	}
}

catch(Exception $e){
	output_header('Error');
	echo 'Error: '.$e->getMessage();
	output_footer();
}

function checked ($value) {
	if ($value === 1) {
		return 'checked="checked"';
	}
	else {
		return '';
	}
}

function bintoyesno($value, $default=0) {
	if ($value === 1 || $default === 1) {
		return 'yes';
	}
	else {
		return 'no';
	}
}

function output_form($ban) {
	
	//check if the boxes are checked
	$ip_checked = checked($ban->ip_ban);
	$acc_checked = checked($ban->account_ban);

	echo "
	<form>
	<input type='hidden' value='edit' name='action'>
	<input type='hidden' value='$ban->ban_id' name='ban_id'>
	<p>Expire Date <input type='text' value='$ban->expire_datetime' name='expire_time'></p>
	<p>IP Ban <input type='checkbox' $ip_checked name='ip_ban'></p>
	<p>Account Ban <input type='checkbox' $acc_checked name='account_ban'></p>
	<p>Notes <textarea rows='4' cols='50' name='notes'>$ban->notes</textarea>
	<p><input type='submit' value='submit'></p>
	</form>";

}


?>
