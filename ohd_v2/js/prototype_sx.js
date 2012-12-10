var sxPeriodicalExecuter = Class.create();
sxPeriodicalExecuter.prototype = {
	initialize: function(callback, frequency, enabled) {
		this.callback = callback;
		this.frequency = frequency;
		this.enabled = enabled || true;
		this.currentlyExecuting = false;

		this.registerCallback();
	},

	enable: function() {
		this.enabled = true;
	},
		
	disable: function() {
		this.enabled = false;
	},
		
	registerCallback: function() {
		setInterval(this.onTimerEvent.bind(this), this.frequency * 1000);
	},

	onTimerEvent: function() {
		if (!this.enabled) return;
		if (!this.currentlyExecuting) {
			try {
				this.currentlyExecuting = true;
				this.callback();
			} finally {
				this.currentlyExecuting = false;
			}
		}
	}
}



//HTMLElement.prototype._at = function() { alert(1); };
//alert(HTMLTableElement._at);

var sxForm = Class.create();
sxForm.prototype = {
	initialize: function(options) {
		this.setOptions(options);
		this.cnt = this.createForm();
		this.cnt.style.visibility = 'hidden';
		this.options.parentNode.appendChild(this.cnt);
		this.positionateWindow();
		this.cnt.style.visibility = 'visible';
		setTimeout((function() {this.fillBody();}).bind(this), 10);
	},
	
	createBody: function() {
		var bd = document.createElement('DIV');
		bd.style.textAlign = 'left';


		var l1 = document.createElement('DIV');
		l1.style.margin = '3px';
		l1.appendChild(document.createTextNode('Enter Ticket Number: '));

		var inp = l1.appendChild(document.createElement('INPUT'));
		inp.type = 'text';
		inp.id = 'sxForm_merge_ticketNum';
		l1.appendChild(inp);
		
		l1.appendChild(document.createTextNode(' '));
		var sub = l1.appendChild(document.createElement('INPUT'));
		sub.type = 'button';
		sub.value = 'Merge';
		sub.onclick = function() { if (inp.value) MergeTicketTo(inp.value, false); }
		l1.appendChild(sub);
		
		bd.appendChild(l1);

		var hr = document.createElement('HR');
		hr.noshade = 'noshade';
		hr.size = 1;
		hr.style.padding = hr.style.margin = '0';
		bd.appendChild(hr);
		
		var div = document.createElement('DIV');
		div.id = 'sxForm_merge_elements_div';
		div.style.padding = '3px';
		div.style.overflow = 'auto';
		div.appendChild(document.createTextNode('Loading content. Please wait.'));
		bd.appendChild(div);

		
		return bd;
	},
	
	createHeader: function() {
		var hdr = document.createElement('H3');
		hdr.appendChild(document.createTextNode(this.options.caption));
		return hdr;
	},
	
	createForm: function() {
		var cnt = document.createElement('DIV');
		cnt.style.position   = 'absolute';
		cnt.style.top        = cnt.style.left = '20px';
		cnt.style.padding    = '2px';
		cnt.style.border     = '1px solid black;';
		cnt.style.background = 'white';
		cnt.style.zIndex     = this.options.zIndex;
		
		if (typeof this.options.width  != 'undefined') cnt.style.width  = this.options.width;
		if (typeof this.options.height != 'undefined') cnt.style.height = this.options.height;
		
		cnt.appendChild(this.header = this.createHeader());
		cnt.appendChild(this.body = this.createBody());
		cnt.appendChild(this.footer = this.createFooter());
		
		return cnt;
	},
	
	createFooter: function() {
		var ftr = document.createElement('DIV');
		ftr.style.background = '#E8E8E8';
		var btn = document.createElement('INPUT');
		btn.type = 'button';
		btn.value = 'Cancel';
		_this = this;
		btn.onclick = function() { Element.remove(_this.cnt); }
		ftr.appendChild(btn);
		return ftr;
	},
	
	fillBody: function() {
		var bd = $('sxForm_merge_elements_div');
		Element.remove(bd.firstChild);
		
		bd.appendChild(document.createTextNode('Items for currently selected filter (for quick access):'));
		
		
		var params = {ticket_id: ticket_id};
		var req = new Ajax.Request(
			'index.php?module=Tickets&action=TicketsAjaxRouter&func=getTicketsToMerge&params=' + encodeURIComponent(JSON.stringify(params)), 
			{asynchronous: false}
		);
		
		var res = req.evalJSON();
		if (res == null) {
			alert('There is no result on request.');
			return;
		}
		if (res.errorCode != 0) {
			alert(res.errorMessage);
		}
		
		_this = this;
		res.tickets.each(function(value, index){ _this.insertItem(bd, value); });
	},
		
	insertItem: function(bd, data) {
		var itm = document.createElement('DIV');
		itm.style.padding = '1px 0 0 5px';
		var ref = document.createElement('A');
		ref.href = 'javascript:void(0)';
		ref.onclick = function() { MergeTicketTo(data.ticket_id) }
		ref.appendChild(document.createTextNode(data.ticket_num + ' - ' + data.caption));
		itm.appendChild(ref);
		bd.appendChild(itm);
		if (bd.clientHeight > 170) bd.style.height = '170px';
		return itm;
	},
	
	positionateWindow: function() {
		this.cnt.style.left = (Math.round(document.body.clientWidth - this.cnt.scrollWidth) / 2) + 'px';
		this.cnt.style.top = (Math.round(document.body.clientHeight - this.cnt.scrollHeight) / 2) + 'px';
		this.cnt.style.top = '200px';
	},
	
	setOptions: function(options) {
		this.options = {
			parentNode:   document.body,
			caption:      'sxForm',
			zIndex:      10
		}
		Object.extend(this.options, options || {});
	}
}
