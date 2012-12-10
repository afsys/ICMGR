<script language="JavaScript1.2">
{assign var="user_rights" value=$user->getAttribute('user_rights')}
{assign var="user_data"   value=$user->getAttribute('user_data')}
<!--
var myMenu =
[
	// [null,'<div class="hmenu_itm main">{trans str="Home"}</div>','index.php?module=Tickets&action=TicketsList&set_filter=1&filter[special]=opened','_self','{trans str="Show open tickets"}'],
	[null,'<div class="hmenu_itm main" title="{trans str="Show \'Tickets List\' with last filter"}"><div class="hmenu_itm clr" title="{trans str="Clear filter and show Opened Tickets List"}" onclick="location.href = \'index.php?module=Tickets&action=TicketsList&set_filter=1&filter[special]=opened\'">{trans str="CLR"}</div>{trans str="Home"}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>','index.php?module=Tickets&action=TicketsList&prev_filter=1','_self','{trans str="Show tickets with last filter"}'],

	// tickets
	[null,'<div class="hmenu_itm">{trans str="Tickets"}</div>','index.php?module=Tickets&action=TicketsList&prev_filter=1','_self','',
		{if ($user_rights & SR_TICKETS_ADD) == SR_TICKETS_ADD}
		[null,'{trans str="Add New Ticket"}','index.php?module=Tickets&action=TicketsAdd','_self','{trans str="Add New Ticket"}'],
		{/if}
//		[null,'{trans str="Last Activity"}','index.php?module=Tickets&action=TicketsFrontPage','_self','{trans str="Last Activity"}'],
		{if ($user_rights & SR_DOWNLOAD_EMAILS) == SR_DOWNLOAD_EMAILS}
		[null,'{trans str="Download Email"}','index.php?module=EmailPiping&action=Delivery','_self', 'Deliver Tickets from your email account using filtering'],
		{/if}
		
		{if ($user_rights & SR_TICKETS_STATISTIC) == SR_TICKETS_STATISTIC}
		[null,'{trans str="Ticket Statistics"}','','_self','',
			[null,'{trans str="Common"}','index.php?module=Tickets&action=TicketsStatistic','_self','{trans str="Common Ticket Statistics"}'],
			[null,'{trans str="Agents Breakdown / Week-Month"}','index.php?module=Tickets&action=TicketsStatisticAgents','_self','{trans str="Agents Ticket Statistics"}'],
			[null,'{trans str="Agents Breakdown / Last Week"}','index.php?module=Tickets&action=TicketsStatisticAgents&type=LastWeek','_self','{trans str="Agents Ticket Statistics"}']
		],
		{/if}
		
		{if ($user_rights & SR_EMAILS_RESP_LOGS) == SR_EMAILS_RESP_LOGS}
		[null,'{trans str="E-Mails Response Logs"}','index.php?module=System&action=EmailLog','_self', 'E-Mails Response Log'],
		{/if}
		[null,'{trans str="Piping E-Mails Logs"}','index.php?module=EmailPiping&action=EmailsLog','_self', 'Piping E-Mails Logs']
	],

	 // Knowledge Base
	[null,'<div class="hmenu_itm">{trans str="Knowledge Base"}</div>', '', '_self', '{trans str="Knowledge Base"}',
		{if ($user_rights & SR_KB_MANAGE_CATS) == SR_KB_MANAGE_CATS}
		[null,'{trans str="Manage Categories"}','index.php?module=KnowledgeBase&action=Categories','_self','Manage Categories and Items'],     
		[null,'{trans str="Expired Items"}','index.php?module=KnowledgeBase&action=ExpiredItems','_self','Manage Expired Items'],
		{/if}
		{if ($user_rights & SR_KB_APPROVE_CMT) == SR_KB_APPROVE_CMT}
		[null,'{trans str="Approve Comments"}','index.php?module=KnowledgeBase&action=ApproveNotes','_self','Approve Users Comments'],
		{/if}
		[null,'{trans str="Users Form"}','index.php?module=KnowledgeBase&action=UsersForm','_self','{trans str="Users Form"}']
	],
	
	
	// MANAGE
	[null,'<div class="hmenu_itm">{trans str="Manage"}</div>', '', '_self', '{trans str="Manage System"}',
		{if ($user_rights & SR_MNG_ANNOUNCEMENTS) == SR_MNG_ANNOUNCEMENTS}
		[null,'{trans str="Announcements"} ','index.php?module=Manage&action=AnnouncList','_self','Manage Announcements List'],
		{/if}
		{if ($user_rights & SR_MNG_USERS) == SR_MNG_USERS}
		[null,'{trans str="Agents"}','index.php?module=Manage&action=AgentsList','_self','Manage Agents List'],
		{/if}
		{if ($user_rights & SR_MNG_PRODUCTS) == SR_MNG_PRODUCTS}
		[null,'{trans str="Products"}','index.php?module=Manage&action=ProductsList','_self','Manage Products List'],                    
		{/if}
		{if ($user_rights & SR_MNG_GROUPS) == SR_MNG_GROUPS}
		[null,'{trans str="Departments"}','index.php?module=Manage&action=GroupsList','_self','Manage Departments List'],          
		{/if}
		{if ($user_rights & SR_MNG_CANNED_EMAILS) == SR_MNG_CANNED_EMAILS}
		[null,'{trans str="Canned E-mails"}','','_self','',
			[null,'{trans str="Groups"}','index.php?module=CannedEmails&action=GroupsList','_self','Manage Canned E-mails Groups'],
			[null,'{trans str="Templates"}','index.php?module=CannedEmails&action=ShowList','_self','Manage Canned E-mails Templates'],
		]
		{/if}


	],

	// CONFIGURE
	[null,'<div class="hmenu_itm">{trans str="Configure"}</div>', '', '_self', 'Configure System',
		{if ($user_rights & SR_CONF_TICKETS) == SR_CONF_TICKETS}
		[null,'{trans str="Ticket"}','','_self','Manage Tickets',
			[null,'{trans str="Preferences"}','index.php?module=Manage&action=TicketsPreferencesEdit','_self','Tickets Preferences'],
			[null,'{trans str="Forms"}','index.php?module=Manage&action=TicketsManageForms','_self','Manage Tickets Forms'],
			[null,'{trans str="Notification E-Mails"}','index.php?module=Manage&action=TicketsNotifyEmails','_self','Notification Emails Templates'],
		],                           
		{/if}
		
		[null,'{trans str="Your Preferences"}','index.php?module=Manage&action=UserPreferencesEdit','_self','Manage User Preferences'],
		
		{if ($user_rights & SR_CONF_SYS_PREFS) == SR_CONF_SYS_PREFS}
		[null,'{trans str="System Preferences"}','index.php?module=Manage&action=PreferencesEdit','_self','Manage System Preferences'],
		{/if}
		
		[null,'{trans str="Quick Filters"}','index.php?module=Manage&action=QFiltersEdit','_self','Configure Quick Filters'],
		
		{if $smarty.const.F_LIVECHAT}
		// [null,'{trans str="LiveChat Forms"}','index.php?module=LiveChat&action=Options','_self','LiveChat Forms'],
		[null,'{trans str="LiveChat"}','','_self','LiveChat',
			[null,'{trans str="Forms"}','index.php?module=LiveChat&action=Options','_self','LiveChat Forms'],
			[null,'{trans str="Predefined Responses"}','index.php?module=LiveChat&action=PredResponsesList','_self','Predefined Responses'],
			[null,'{trans str="Conversation Logs"}','index.php?module=LiveChat&action=ConvertationLogs','_self','Convertation Logs'],

		], 
		{/if}

		{if ($user_rights & SR_CONF_PIPING) == SR_CONF_PIPING}
		[null, '{trans str="Email Piping"}', 'index.php?module=EmailPiping&action=ListFilters','_self', 'Email Piping Options',
			[null, '{trans str="Filters"}', 'index.php?module=EmailPiping&action=ListFilters','_self','List Filters'],
			[null,'{trans str="Piping Accounts"}','index.php?module=Manage&action=PipingAccList','_self','Edit Piping Accounts List'],
			[null,'{trans str="Spam Filter"}','index.php?module=EmailPiping&action=SpamFilter','_self','Manage Spam Filters'],
		]
		{/if}
		
	],

			
	// SUPPORT
	[null,'<div class="hmenu_itm">{trans str="Tutorial/Manual"}</div>',"javascript:void(window.open('http://www.omnihelpdesk.com/tutorial1.htm','tutorial','menubar=no,toolbar=no, width = 700,scrollbars = 1'));",'_self','System'],
/*	[null,'<div class="hmenu_itm">{trans str="Support Center"}</div>','index.php?module=System&action=Manual','_self','System',
		[null,'{trans str="Tutorial/Manual"}',"javascript:void(window.open('http://www.omnihelpdesk.com/tutorial1.htm','tutorial','menubar=no,toolbar=no, width = 700,scrollbars = 1'));",'_self','Read the manual'],
		{php}if (checkAdmin()) {{/php}
		[null,'{trans str="Updates"}','index.php?module=Updates&action=UpdateFiles','_self','Update'],
		{php}}{/php}
		
		
	],  */
	[null,'<div class="hmenu_itm logout">{trans str="Logout"}</div>','index.php?module=System&action=AuthenticateUser&logout=1','_self','Logout']
];

//ohdDrawMenu();

-->
</script>