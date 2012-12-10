in_array = function(itm, arr)
{
	for (var i = 0; i < arr.length; i++)
	{
		if (arr[i] == itm) return true;
	}
	return false;
}


// AJAX
var is_ie = navigator.userAgent.match('MSIE') ? true : false;            
var xmlhttp;
if (is_ie) xmlhttp = new ActiveXObject("MSXML2.XMLHTTP");
else xmlhttp = new XMLHttpRequest();


function SubmitSearch()
{
    var inp = document.getElementById('search_value');
    if (inp.value == '') {
		alert(trans.ENTER_SEARCH_CRITERIA);
        inp.focus();
        return;
    }
    document.getElementById('search_form').submit();
}

function SubmitSearch2()
{
    var inp = document.getElementById('search_value2');
    if (inp.value == '') {
		alert(trans.ENTER_SEARCH_CRITERIA);
        inp.focus();
        return;
    }
    document.getElementById('search_form2').submit();
}


function findParent(node, tagName) 
{
	return node.tagName == tagName?node:findParent(node.parentNode, tagName);
}

function checkAllBoxes(obj) 
{
	elems = findParent(obj,"TABLE").getElementsByTagName("INPUT");
	for (i=0;i<elems.length;i++) if (elems[i].type == "checkbox") elems[i].checked = obj.checked;
}

function SetFilter(f_name, f_value, f_params)
{
	f_params = f_params || 'jjjjjjj';
	var form = document.getElementById('filter_form');
	var inp = form.elements['filter['+f_name+']'];
	inp.value = f_value;
	form.elements['set_filter'].value = 1;
	form.elements['filter_ex_params'].value = f_params;
	document.getElementById('filter_form').submit();
}

// OHD SPECIAL
function ohdDrawMenu() {
	var div = document.getElementById('ohd_menu');
	if (div) {
		if (!div.installed) {
			cmDraw('ohd_menu', myMenu, 'hbr', cmThemeOffice, 'ThemeOffice');
			div.installed = true;
		}
	}
}
	

// WINDOWS FUNCTION

function ShowWindow(item_id, params)
{
	var itm = document.getElementById(item_id);

	itm.style.display = 'block';
	
	if (in_array(params, 'center')) {
		itm.style.top  = ((Math.round(document.body.clientHeight - itm.scrollHeight) / 2) + document.body.scrollTop) + 'px';
		itm.style.left = (Math.round(document.body.clientWidth  - itm.scrollWidth ) / 2) + 'px';
	}
	
	return itm;
}

function HideWnd(item_id)
{
	var tbl = document.getElementById(item_id);
	tbl.style.display = 'none';
	var shld = document.getElementById('shield_frame');
	shld.style.display = 'none';
}

function cancelBuble(e)
{
	if (!e) var e = window.event;
	e.cancelBubble = true;
	if (e.stopPropagation) e.stopPropagation();
}

function ApplyRichEditor($ta_id)
{
	var oFCKeditor = new FCKeditor($ta_id);
	oFCKeditor.BasePath   = "lib/FCKeditor/" ;
	oFCKeditor.ToolbarSet = "OHD";
	//oFCKeditor.Config['SkinPath'] = FCKConfig.BasePath + 'editor/skins/silver/' ;
	oFCKeditor.ReplaceTextarea();
	
	var re_link = document.getElementById($ta_id + '_re')
	if (re_link) re_link.style.display = 'none';
}





// NEW LC FUNCTIONS
var lc_curr_user_data   = null;
var lc_flashTimerId     = null; 
var lc_chatWindowHnds   = new Object();
var lc_prevUserStatuses = new Object(); 
var lc_reqTimerId       = null;
var lc_rings_more       = 0;

function lcSwithOnOff()
{
	var enable = !lcGetUserStatusPE.enabled;
	
	// save option
	var req = new Ajax.Request(
		'index.php?module=Manage&action=UserPreferencesAjaxRouter&func=switchLiveChat&params=' + 
		encodeURIComponent(JSON.stringify({'enable': enable})), {}
	);
	
	lcInitState(enable);
}

function lcInitState(enabled)
{
	if (lc_reqTimerId != null) {
		clearTimeout(lc_reqTimerId);
		lc_reqTimerId = null;
	}

	if (enabled) {
		$('lc_enable').value = 'Disable';
		$('lc_enable').checked = true;
		$('lc_state_status').innerHTML = 'Enabled';
		$('lc_state_status').style.marginRight = '4px';
		$('lc_state_status').style.color = '#37CA00';
		
		lcGetUserStatusPE.enable();
		lcGetUserStatusPE.onTimerEvent();
	}
	else {
		$('lc_enable').checked = false;
		$('lc_state_status').innerHTML = 'Disabled';
		$('lc_state_status').style.marginRight = '0';
		$('lc_state_status').style.color = '#CA0000';
		lcGetUserStatusPE.disable();
	}
}

function lcFlashMakeStep() 
{
	var btn = $('lc_user_req');
	btn.style.backgroundColor = btn.flashed ? '#FF7133' : '#FBCECF';
	btn.flashed = !btn.flashed;
}

function lcJoinToChat(sid)
{
	lc_rings_more = 0;
	sid = this.sid;
	var url = 'index.php?module=LiveChat&action=JoinToChat&sid=' + sid;
	
	if ((typeof lc_chatWindowHnds[sid] == 'undefined') || lc_chatWindowHnds[sid].closed) {
		lc_chatWindowHnds[sid] = window.open(url, 'lcChatWindow' + sid, 'resizable=yes,scrollbars=yes,status=yes,width=590px,height=420px');
	}
	lc_chatWindowHnds[sid].focus();
	
	if (lc_chatWindowHnds[sid] && lc_chatWindowHnds[sid].document) {
		// lc_setChatingStatus(true);
	}
	
	// $('lc_user_req').blur();
}

function lcUpdateAgentReqStatuses(req, json)
{
	if (!lcGetUserStatusPE.enabled) return;
	var currUserStatuses = new Object(); 
	var have_new_sessions = false;
	
	// set current statuses
	var agent_statuses = json.agent_statuses;
	for (var i = 0; i < agent_statuses.length; i++) {
		var sid = agent_statuses[i].sid;
		currUserStatuses[sid] = agent_statuses[i];
		
		// new session
		if (typeof lc_prevUserStatuses[sid] == 'undefined') {
			if (agent_statuses[i].status == 'requesting') have_new_sessions = true;
			var btn = document.createElement('INPUT');
			btn.type = 'button';
			btn.id = 'lc_status_btn_' + sid;
			btn.className = 'flat_btn';
			btn.value = 'SID: ' + sid;
			btn.value = agent_statuses[i].sdata.name;
			if (btn.clientWidth < 50) btn.style.width = '50px';
			btn.style.marginRight = '3px';
			btn.sid = sid;
			btn.onclick = lcJoinToChat;
			
			$('lc_statuses_cnt').appendChild(btn);
			lcApplyNewSessionStatus(sid, agent_statuses[i].status);
		}
		else if (lc_prevUserStatuses[sid].status != agent_statuses[i].status) {
			lcApplyNewSessionStatus(sid, agent_statuses[i].status);
		}
	}
	
	if (have_new_sessions) {
		lc_rings_more = lc_rings_more_init;
		lcAgentRequest(true);
	}

	// remove just closed sessions
	for (sid in lc_prevUserStatuses) {
		if (typeof currUserStatuses[sid] == 'undefined') {
			Element.remove($('lc_status_btn_' + sid));
		}
	}
	
	
	lc_prevUserStatuses = currUserStatuses;
}

function lcApplyNewSessionStatus(sid, status) 
{
	var btn = $('lc_status_btn_' + sid);
	switch (status) {
		case 'requesting':
			btn.style.backgroundColor = '#FF7133'; // : '#FBCECF';
			btn.title = 'Requesting';
			break;
		case 'requesting_for_transfer':
			btn.style.backgroundColor = 'orange'; // : '#FBCECF';
			btn.title = 'Requesting for transfer';
			break;

		case 'chating':
			btn.style.backgroundColor = '#D7FBCE';
			break;
		case 'closed':
			btn.style.backgroundColor = 'yellow';
			break;
		default:
			alert(trans.UNKNOWN_STATUS + status);
			break;
	}
}

function lcAgentRequest(state) {
	if (state && lc_rings_more) {
		ring_play('ring'); 
		window.focus();

		clearTimeout(lc_reqTimerId);
		lc_reqTimerId = setTimeout(function() {lcAgentRequest(true);}, 5000);
		lc_rings_more--;
	}
	else {
		clearTimeout(lc_reqTimerId);
		lc_reqTimerId == null;
		lc_rings_more = 0;
	}
}

// play sound
var playing = false;
function ring_play(ring_type) {
	if (is_ie) {
		var embed_movie = '<embed id="player" src="lib/Sounds/' + ring_type + '.mp3" width="0" height="0" autostart="true"></embed>';
		document.getElementById('sound_div').innerHTML = embed_movie;
	}
	else {
		var FO = { movie:"lib/Sounds/" + ring_type + ".swf", width:"300", height:"120", majorversion:"8", build:"0", xi:"true" };
		UFO.create(FO, "sound_div");
	}
}
function ring_stop() {
	document.getElementById('sound_div').innerHTML = ' ';
}
function ring_trigger_play() {
	if (playing == false) {
		play();
		playing = true;
	} else {
		stop();
		playing = false;
	}
}


// 
var key_last_action_times = new Array();
function UpdateLastActionTime(key) {
	key_last_action_times[key] = new Date();
}

function GetLastActionTime(key) {
	r = new Date() - key_last_action_times[key];
	if (isNaN(r)) return null;
	return r;
}