function AddMessage2Page(nickname, message, rec_time, service_command) { 
	// conver text into HTML 
	message = message
			.replace(/</g, '&lt;').replace(/>/g, '&gt;')
			.replace(/\n/g, '<br />');
	
	// add message
	var time = document.createElement('EM');
	time.appendChild(document.createTextNode('(' + rec_time + ')'));
	
	var msg  = $('messages');
	var div  = document.createElement('div');
	
	if (service_command) {
		div.appendChild(document.createTextNode(message));
	}
	else {
		var nick = document.createElement('span');
		nick.style.fontWeight = 'bold';
		nick.appendChild(document.createTextNode(nickname + ' '));
		nick.appendChild(time);
		nick.appendChild(document.createTextNode(': '));
		div.appendChild(nick);
		div.innerHTML = div.innerHTML + message;
		div.className = 'lc_message ' + (user.nickname == nickname ? 'lc_agent': 'lc_requester');
	}
	
	msg.appendChild(div);
	
	
	// scroll message window to down
	if (typeof msg.doScroll == 'function') msg.doScroll('scrollbarPageDown');
	else msg.scrollTop = msg.scrollHeight - msg.clientHeight;
	
	// update status
	last_message_time = rec_time;
	UpdateOpponentsStatus();
}


function ExecServiceCommand(service_command, params) {
	switch (service_command) {
		case 'session_closed':
			clearInterval(timer_id);
			$('send_button').disabled = true;
			$('new_message').disabled = true;
			$('new_message').style.backgroundColor = '#D4D0C8';
			if ($('transfer_to')) $('transfer_to').disabled = true;
			if ($('pred_responses_sel')) $('pred_responses_sel').disabled = true;
			SetStatus('session_closed');
			break;
			
		case 'user_logged_in':
			if (params.user_id != user.user_id) {
				SetStatus('chatting');
				opponents[params.user_id] = params;
				UpdateOpponentsStatus();
			}
			else {
				// TODO: update user status
				// TODO: turn on controls
			}
			break;
			
		case 'user_logged_out':
			if (params.user_id != user.user_id) {
				//SetStatus('waiting_for_opponent');
				delete opponents[params.user_id];
				UpdateOpponentsStatus();
			}
			else {
				SetStatus('logged_out');
				//location.href = 'index.php?module=LiveChat&action=ChatWindow';
				// TODO: update user status
				
				$('send_button').disabled = true;
				$('new_message').disabled = true;
				$('new_message').style.backgroundColor = '#D4D0C8';
				if ($('transfer_to')) $('transfer_to').disabled = true;
				if ($('pred_responses_sel')) $('pred_responses_sel').disabled = true;
				
				user.logged_out = true;
			}
			break;
		/*
		case 'opponent_logged_in':
			SetStatus('active');
			break;
			
		case 'ping_timeout':
			$('send_button').disabled = true;
			clearInterval(timer_id);
			SetStatus('ping timeout');
			break;
		case 'ping_timein':
			SetStatus('active');
			break; */
		
	} 
}

function GetOponentMessages() {
	if (user.logged_out == true) return;
	
	var params = {
		sid            : user.sid,
		user_id        : user.user_id,
		write_activity : GetLastActionTime('lc_message')
	};
		
	var req = new Ajax.Request(
		'index.php',
		{
			asynchronous: false,
			method: 'post',
			parameters: 'module=LiveChat&action=ChatWindowAjax&func=getUserMessages&params=' + encodeURIComponent(JSON.stringify(params))
		}
	);
	
	//alert(req.transport.responseText);
	var res = req.evalJSON();
	if (res.errorCode == 0) {
		var len = res.messages.length;
		for (var i = 0; i < len; i++) {
			if (user.logged_out == true) return;
			
			if (!(res.messages[i].service_command == 'user_logged_in' && res.messages[i].message_params.user_id == user.user_id)) {
				// skip current user log in message
				AddMessage2Page(res.messages[i].nickname, res.messages[i].message, res.messages[i].rec_time, res.messages[i].service_command);
			}
			ExecServiceCommand(res.messages[i].service_command, res.messages[i].message_params);
		}
		if (len > 0) ring_play('message');
		
		var status = '';
		if (typeof res.opponents != 'undefined') {
			for (var opp_id in res.opponents) {
				// alert(opp_id);
				var opp = res.opponents[opp_id];
				if (opp.write_activity <= 2000) {
					status += opp.nickname + ': typing... ';
				}
			}
		}
		
		$('status_text').innerHTML = status;
	}
	
	
	//xajax_GetOponentMessages(user.user_id, user.user_key, user.sid, user.messages_from, GetLastActionTime('lc_message'));
}


function InitWindow() {
	WriteMessages(); 
}


function LcLogout(is_button) {
	is_button = is_button || false;
	
	user.message = user.nickname + ' logged out';
	user.message_params =  {user_id: user.user_id, nickname: user.nickname};
	user.service_command = 'user_logged_out';
	var res = SendMessage(user);
	/*
	var lo_xmlhttp = is_ie ? new ActiveXObject("MSXML2.XMLHTTP") : new XMLHttpRequest();
	var url = "index.php?module=LiveChat&action=CloseSession&user_id=" + user.user_id + "&user_key=" + user.user_key ;
	lo_xmlhttp.open("GET", url, false);

	try {
		lo_xmlhttp.send(null); 
		if (lo_xmlhttp.responseText == 'Ok!' && is_button) {
			window.close();
		}
	} catch(e) {}*/
}

function MakeTransferRequest(sel) {
	//AddMessage2Page(null, 'requesting transfer to @TODO', null, 'user_transfer_req');
	var req_user_id  = sel.options[sel.selectedIndex].value;
	var req_nickname = sel.options[sel.selectedIndex].innerHTML;

	user.message = user.nickname + ' requesting transfer to ' + req_nickname;
	user.message_params =  {user_id: user.user_id, nickname: user.nickname, req_user_id: req_user_id, req_nickname: req_nickname};
	user.service_command = 'user_transfer_req';
	var res = SendMessage(user);
}


function SendMessage(params)
{
	if (typeof params.message_params == 'undefined') params.message_params = {};
	params.message_params.active_time =  GetLastActionTime('lc_message');
		
	var req = new Ajax.Request(
		'index.php',
		{
			asynchronous: false,
			method: 'post',
			parameters: 'module=LiveChat&action=ChatWindowAjax&func=sendMessage&params=' + encodeURIComponent(JSON.stringify(params))
		}
			
	);
	
	params.message = params.service_command = null;
	
	//alert(req.transport.responseText);
	return req.evalJSON();
}

function SendMessageButtonClick() 
{
	$('send_button').disabled = true;
	var ta  = $('new_message');
	if (ta.value == '') {
		alert(trans.PLEASE_ENTER_MESSAGE);
		$('send_button').disabled = false;
		ta.focus();
		return;
	}
	
	user.message = ta.value;
	var res = SendMessage(user);
	if (res.errorCode) alert(res.errorMessage);
	else {
		AddMessage2Page(user.nickname, ta.value, res.date);
	}
	
	ta.value = '';
	$('send_button').disabled = false;
	
	/*old: xajax_SendMessage(user.user_id, user.user_key, ta.value); return; */
}


function SetStatus(status) {
	var div = $('user_status');
	
	switch (status) {
		case 'chatting':
			div.innerHTML = 'Chatting';
			div.style.fontWeight = 'bold';
			div.style.color = 'green';
			break;
			
		case 'logged_out':
			div.innerHTML = 'Logged out';
			div.style.fontWeight = 'bold';
			div.style.color = 'silver';
			break;

		case 'session_closed':
			div.innerHTML = 'Session closed';
			div.style.fontWeight = 'bold';
			div.style.color = 'silver';
			break;
			
		case 'waiting_for_opponent':
			div.innerHTML = 'Waiting For Opponent';
			div.style.fontWeight = 'bold';
			div.style.color = '#0099CC';
			break;

			
			
	}
}

var lc_usrs_info_window = null;
function ShowOpponentsInfo() {
	var url = 'index.php?module=LiveChat&action=OpponentsInfo&sid=' + user.sid;
	if (lc_usrs_info_window == null || lc_usrs_info_window.closed) {
		lc_usrs_info_window = window.open(url, 'OpponentsInfoWindow', 'resizable=yes,scrollbars=yes,status=yes,width=500px,height=420px');
	}
	lc_usrs_info_window.focus();
}


function UpdateOpponentsStatus()
{
	var str = '';
	
	for (o in opponents) {
		if (str != '') str += ', ';
		str += opponents[o].nickname;
	}
	$('opponents_list').innerHTML = str;
}


function UpdateTranferAgents()
{
	var req = new Ajax.Request(
		'index.php?module=LiveChat&action=ChatWindowAjax&func=getTechs', 
		{asynchronous: false}
	);
	
	var res = req.evalJSON();
	if (res.errorCode == 0) {
		var select = $('transfer_to');
		
		// remove all except first item
		for (var i = select.options.length-1; i > 0; i--) {
			Element.remove(select.options[i]);
		}
		
		// add new available agents
		for (var i = 0; i < res.available_techs.length; i++) {
			if (res.available_techs[i].user_id == user.agent_id) continue;
			var option = document.createElement('OPTION');
			option.value = res.available_techs[i].user_id;
			option.appendChild(document.createTextNode(res.available_techs[i].user_name + (res.available_techs[i].user_lastname ? ' ' + res.available_techs[i].user_lastname : '')));
			select.appendChild(option);
		}
	}
}

/* ======================= */

function ClearLogWindow() {
	var msg = $('messages');
	msg.innerHTML = '';
}

function CheckTAButton(e) {
	if (!e) e = window.event;
	var keycode = e.keyCode ? e.keyCode : e.which;
	
	// if ((keycode == 13 || keycode == 10) && !e.ctrlKey) $('send_button').click();
	if ((keycode == 13 || keycode == 10)) {
		$('send_button').click();
		return false;
	}
	return true;
}


function SetPredefinedResponce(a) {
	$('new_message').value = $(a.name + '_dd').innerHTML;
}

function InsertPredResponse() {
	$('new_message').value = $F('pred_responses_sel');
}


var lc_uf_window = null;
function ShowUserFootprints() {
	var url = 'index.php?module=LiveChat&action=ShowUserFootPrints';
	if (lc_uf_window == null || lc_uf_window.closed) {
		lc_uf_window = window.open(url, 'UserFootprintsWindow', 'resizable=yes,scrollbars=yes,status=yes,width=500px,height=420px');
	}
	lc_uf_window.focus();
}
