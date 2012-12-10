// TIME TRACKING
var tt_message_id = null;
var tt_reload = true;
function AddTimerTracking(message_id, reload)
{
	tt_reload = reload || false;
	tt_message_id = message_id;
	var cnt = ShowWindow('time_tracking_div', ['center', 'shiled']);
}

function AddTimerTrackingSbt()
{
	var params = {
		user_id:     curr_user_id,
		ticket_id:   ticket_id,
		message_id:  tt_message_id,
		worked:      parseFloat(document.getElementById('tt_worked').value),
		chargeable:  parseFloat(document.getElementById('tt_chargeable').value),
		billable:    parseFloat(document.getElementById('tt_billable').value),
		payable:     parseFloat(document.getElementById('tt_payable').value)
	};

	if (isNaN(params.worked) || params.worked <= 0) {
		alert(trans.WORKER_VALUE_IS_INCORECT);
		return;
	}
	if (!isNaN(params.chargeable)) {
		if (!isNaN(params.billable)) {
			if (params.billable > params.chargeable) {
				alert(trans.BILLABLE_VALUE_IS_INCORECT);
				return;
			}
			
			if (!isNaN(params.payable)) {
				if (params.payable > params.billable) {
					alert(trans.PAYABLE_VALUE_IS_INCORECT);
					return;
				}
			}
			else {
				params.payable = 0;
			}
		}
		else {
			params.billable = params.payable = 0;
		}
	}
	else {
		params.chargeable = params.billable = params.payable = 0;
	}

	//alert([params.worked, params.chargeable, params.billable, params.payable]);
	
	var paramas_str = "";
	for (var p in params) paramas_str += '&' + p + '=' + params[p];
	
	xmlhttp.open("GET", "index.php?module=Tickets&action=TicketsAddTimeTracking" + paramas_str, true);
	xmlhttp.onreadystatechange = AddTimerTrackingSbt_Reps;
	xmlhttp.send(null); 
	return;
}

function AddTimerTrackingSbt_Reps()
{
	if (xmlhttp.readyState == 4) 
	{
		eval(xmlhttp.responseText);
		if (tt_reload) location.reload(true);
		tt_message_id = null;
		HideWnd('time_tracking_div');
	}
}

function AddTT(tt_id, tt_type, limit) {
	var res;
	if (!(res = window.prompt('Enter value:', 0)) || res == 0) {
		return;
	}
	
	var val = parseFloat(res);
	if (isNaN(val)) {
		alert(trans.VALUE_SHOULD_BE_REAL_NUM);
		return;
	}
	
	if (limit && val > limit) {
		alert(trans.VALUE_COULD_NOT_BE_GREATER_THAN + limit);
		return;
	}
	
	
	var params = {
		user_id:     curr_user_id,
		ticket_id:   ticket_id,
		tt_id:       tt_id,
		tt_type:     tt_type,
		val:         val
	};
	var params_str = "";
	for (var p in params) params_str += '&' + p + '=' + params[p];
		
	location.href = 'index.php?module=Tickets&action=TicketsTTAddValue' + params_str;
}


function DelTT(tt_id) {
	if (window.confirm('Are you shure?'))
	{
		var params = {
			ticket_id:   ticket_id,
			tt_id:       tt_id
		};
		var params_str = "";
		for (var p in params) params_str += '&' + p + '=' + params[p];

		location.href = 'index.php?module=Tickets&action=TicketsTTDelete' + params_str;
	}
}
	





// ============================================================================
// = CANNED EMAILS
// ============================================================================
var user_email = null;
var user_name = null;
var current_select = null;

function SelectEmail(email, name)
{

	user_email = email;
	user_name  = name;
	document.getElementById('table_select_email_hdr').innerHTML = 'Select email for user: ' + email;
	
	var tbl = document.getElementById('table_select_email');
	//for (i = tbl.rows.length-2; i > 1; i--) tbl.deleteRow(i);    
	
	//tbl.style.width = '500px';
	tbl.style.display = 'block';

	var shld = document.getElementById('shield_frame');
	shld.style.height = tbl.scrollHeight + 'px';
	shld.style.width  = (tbl.scrollWidth + 2)  + 'px';
	shld.style.top  = tbl.style.top  = ((Math.round(document.body.clientHeight - tbl.scrollHeight) / 2) + 30) + 'px';
	shld.style.left = tbl.style.left = (Math.round(document.body.clientWidth  - tbl.scrollWidth ) / 2) + 'px';
	if (is_ie) shld.style.display = 'block';
	
	ShowEmailsList(); // delete
}


function ShowEmailsList()
{
	var cat_id = document.getElementById('ce_category').value;
	xmlhttp.open("GET", "index.php?module=CannedEmails&action=GetCategoryItems&cat_id=" + cat_id, true);
	xmlhttp.onreadystatechange = ShowEmailsList_Reps;
	xmlhttp.send(null); 
	return;
}

function ShowEmailsList_Reps()
{
	if (xmlhttp.readyState == 4) 
	{
		var tbl = document.getElementById('table_select_email');
		var list_cnt = document.getElementById('canned_emails_list');
		list_cnt.innerHTML = '';
		// for (i = tbl.rows.length-2; i > 1; i--) tbl.deleteRow(i);
		eval(xmlhttp.responseText);
		var cls = 'c1';
		for (i = 0; i < cat_items.length; i++) {
			var div = document.createElement('DIV');
			//div.className = cls;
			if (cat_items[i].highlighted) div.style.backgroundColor = (cls == 'c1' ? '#E7FFE6' : '#D5FFD2');
			else div.style.backgroundColor = (cls == 'c1' ? '#F3F3F3' : '#E8E8E8');
			div.style.padding = div.style.margin = '2px';
			var a  = document.createElement('A');
			a.innerHTML = cat_items[i].caption;
			a.href = "javascript:SendEmail(" + cat_items[i].id + ");";
			div.appendChild(a);
			list_cnt.appendChild(div);

			cls = cls == 'c1' ? 'c2' : 'c1';
		}
		
		var shld = document.getElementById('shield_frame');
		shld.style.height = tbl.scrollHeight + 'px';
		shld.style.width  = (tbl.scrollWidth + 2)  + 'px';
		shld.style.top  = tbl.style.top  = ((Math.round(document.body.clientHeight - tbl.scrollHeight) / 2) + 30) + 'px';
		shld.style.left = tbl.style.left = (Math.round(document.body.clientWidth  - tbl.scrollWidth ) / 2) + 'px';
		
	}    
}

function SendEmail(email_id)
{
	xmlhttp.open("GET", "modules/CannedEmails/actions/get_email_preview.php?email_id=" + email_id + 
						"&user_email=" + user_email + "&user_name=" + user_name + "&ticket_id=" + ticket_id, true);
	xmlhttp.onreadystatechange = SendEmail_Reps;
	xmlhttp.send(null);    
	
}

function SendEmail_Reps()
{
	if (xmlhttp.readyState == 4) 
	{
		document.getElementById('table_select_email').style.display = 'none';
		
		var tbl = document.getElementById('table_send_email');
		
		for (i = tbl.rows.length-2; i > 0; i--) tbl.deleteRow(i);
		eval(xmlhttp.responseText);
		
		for (i = 0; i < preview_items.length; i++)
		{
			var tr = tbl.insertRow(2*i+1);
			tr.className = 'c2';
			var td = tr.insertCell(0);
			td.vAlign = "top";
			td.innerHTML = 
				'<strong>Subj:</strong> <input id=\'user[' + i +'][subj]\' name=\'user[' + i +'][subj]\' type="text" style="border: none; background: none; width: 400px" value="' + preview_items[i].caption + '" />' +
				'<br /><strong>To:</strong> ' + preview_items[i].user_name + ' <span style="color: #8A8A8A;">&lt;' + preview_items[i].user_email + '&gt;</span> <input id=\'user[' + i +'][email]\' name=\'user[' + i +'][email]\' type="hidden" value="' + preview_items[i].user_email + '" />';

			var tr = tbl.insertRow(2*i+2);
			tr.className = 'c1';
			var td = tr.insertCell(0);
			var ta = document.createElement('textarea');
			ta.id = 'user[' + i + '][message]';
			ta.name = 'user[' + i + '][message]';
			ta.style.border = 'none';
			ta.style.background = 'none';
			ta.rows = 16;
			ta.cols = 86;
			ta.style.width = '100%';
			ta.value = preview_items[i].message;
			td.appendChild(ta);
		}
		
		tbl.style.display = 'block';
		var shld = document.getElementById('shield_frame');
		shld.style.height = tbl.scrollHeight + 'px';
		shld.style.width  = (tbl.scrollWidth + 2)  + 'px';
		shld.style.top  = tbl.style.top  = ((Math.round(document.body.clientHeight - tbl.scrollHeight) / 2) + 35) + 'px';
		shld.style.left = tbl.style.left = (Math.round(document.body.clientWidth  - tbl.scrollWidth ) / 2) + 'px';
	}    
}


function RealSendEmail()
{
	var subj = document.getElementById('user[0][subj]');
	var email = document.getElementById('user[0][email]');    
	var message = document.getElementById('user[0][message]');
	
	var req = "index.php?module=CannedEmails&action=SendEmails&subj=" + encodeURI(subj.value) + 
			  "&email=" + encodeURI(email.value) + "&message=" + encodeURI(message.value) + 
			  "&ticket_id=" + ticket_id + "&posted_by=" + curr_user_id;

	xmlhttp.open("GET", req, true);
	xmlhttp.onreadystatechange = RealSendEmail_Reps;
	xmlhttp.send(null);      
}

function RealSendEmail_Reps()
{
	if (xmlhttp.readyState == 4) {
		if (xmlhttp.responseText != "OK!") {
			alert(xmlhttp.responseText); 
		}
		else {
			alert(trans.EMAIL_HAS_BEEN_SENT)
		}
		
		HideWnd('table_send_email');
	}
}












function Post(post_type)
{
	document.getElementById('submit_type').value = post_type;
	document.getElementById('post_form').submit();
}

function SetOption(opt_type)
{
	document.getElementById('requestStatus').innerHTML = '<span style="color: #EA7500">Saving ' + opt_type + '...</span>';
	var sel = document.getElementById('opt_sel_' + opt_type);
	var btn = document.getElementById('opt_btn_' + opt_type);

	var value = sel.options[sel.selectedIndex].value;
	var url = "index.php?module=Tickets&action=TicketsOptionsSave&ticket_id=" + ticket_id + "&type=" + encodeURI(opt_type) + "&value=" + encodeURI(value) + "&user_id=" + curr_user_id + "&nocache=" + Math.floor(Math.random()*0x7FFFF);
	// alert(url);
	xmlhttp.open("GET", url, true);
	xmlhttp.onreadystatechange = SavePreferences_Reps;
	xmlhttp.send(null);   
	
	btn.disabled = true; 
}

function CheckOption(opt_type)
{
	var sel = document.getElementById('opt_sel_' + opt_type);
	var btn = document.getElementById('opt_btn_' + opt_type);
	var value = sel.options[sel.selectedIndex].value;
	sel.disabled = true;
	SetOption(opt_type);
}

function SavePreferences_Reps() {
	if (xmlhttp.readyState == 4) {
		eval(xmlhttp.responseText);
	}    
}

var edit_cnt = null;
var curr_message_id = null;
function EditMessage(message_id)
{
	alert('Not supported yet...');
	return;
	if (curr_message_id == message_id) return;
	var cnt = document.getElementById('tick_msg_' + message_id);
			
	// remove previous edit cnt
	if (edit_cnt)
	{
	}
	else
	{
		edit_cnt = document.createElement('TEXTAREA');
	}
	
	//alert(cnt.scrollWidth);
	edit_cnt.style.width  = '100%';
	edit_cnt.style.height = cnt.scrollHeight + 'px';
	edit_cnt.value = cnt.innerHTML;
	cnt.parentNode.appendChild(edit_cnt);
}



function ChangeSuccess() {
	if (xmlhttp.readyState == 4) {
		current_select.disabled = false;
	}
}


function SetKBInclude(sel)
{
	if (sel.selectedIndex == -1) return;
	var opt = sel.options[sel.selectedIndex];
	if (!isNaN(parseInt(opt.value)))
	{
		var msg = document.getElementById('new_message');
		msg.value = msg.value + '{KB:' + opt.value + '}';
	}
}

function MergeTicket()
{
	new sxForm({caption: 'Ticket Merge', width: '500px'});
	
	return;
	var parent_ticket_id = parseInt(window.prompt('Please enter ticket number to append:', ''));
	if (!isNaN(parent_ticket_id)) {
		
	}
	
}

function MergeTicketTo(ticket_id_to, is_ticket_id) 
{
	if (typeof is_ticket_id == 'undefined') is_ticket_id = true;
	if (ticket_id_to) {
		location.href = 'index.php?module=Tickets&action=TicketsMerge&child_ticket_id=' + ticket_id + 
		                (is_ticket_id ? '&parent_ticket_id=' : '&parent_ticket_num=') + ticket_id_to;
	}
}


function SwithDescView()
{
	var div_desc = document.getElementById('div_desc');
	var link     = document.getElementById('desc_full_view');
	if (link.is_closed) {
		link.innerHTML = 'SHORT VIEW';
		link.style.width = '84px';
		link.style.backgroundImage = 'url(images/resize_up_b.gif)';
		div_desc.style.maxHeight = '';
		div_desc.style.height = div_desc.scrollHeight + 'px';
	}
	else {
		link.innerHTML = 'FULL VIEW';
		link.style.width = '74px';
		link.style.backgroundImage = 'url(images/resize_down_b.gif)';
		div_desc.style.height = max_desc_height + 'px';
		
	}
	
	link.is_closed = !link.is_closed;
}

function OnLoadFunc()
{
	var div_desc_parent = document.getElementById('div_desc_parent');
	var div_desc        = document.getElementById('div_desc');
	if (div_desc.offsetHeight > max_desc_height) div_desc.style.height = max_desc_height + 'px';
	if (div_desc.scrollHeight > max_desc_height) 
	{
		var lnk = $('desc_full_view');
		lnk.style.display = 'block';
		lnk.is_closed = true;
		
		$('desc_full_view_shld').style.display = 'block';
	}
}
window.onload = OnLoadFunc;

function SaveTicketFlag() {
	HighlightTicketFlag();
	var flag = $F('ticket_flag');
	
	var params = {
		ticket_id: ticket_id,
		flags: flag
	}
	
	var req = new Ajax.Request(
		'index.php?module=Tickets&action=TicketsAjaxRouter&func=saveTicketParams&params=' + encodeURIComponent(JSON.stringify(params)), 
		{asynchronous: false}
	);
	
	var res = req.evalJSON();
	if (res.errorCode != 0) {
		alert(res.errorMessage);
	}
	
	alert(res.tickets);
}

function HighlightTicketFlag() 
{
	var flag_item = $('ticket_flag');
	if (flag_item) {
		if (flag_item.selectedIndex > 0) {
			flag_item.style.background = 'red';
			//flag_item.style.border = '1px solid #B00000';
		}
		else {
			flag_item.style.background = '#DDDDDD';
		}
	}
}



