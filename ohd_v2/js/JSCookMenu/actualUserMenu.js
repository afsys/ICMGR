<script language="JavaScript1.2">

{assign var="user_rights" value=$user->getAttribute('user_rights')}
{assign var="user_data"   value=$user->getAttribute('user_data')}
<!--

var myMenu =
[
	// TICKETS
	[null,'<div class="hmenu_itm main">{trans str="Your Tickets"}</div>','index.php?module=Tickets&action=TicketsList','_self','{trans str="Show User Tickets"}'],
	[null,'<div class="hmenu_itm">{trans str="New Ticket"}</div>','index.php?module=Tickets&action=TicketsAdd','_self',''],
	[null,'<div class="hmenu_itm">{trans str="Add Separate Ticket"}</div>','index.php?module=Tickets&action=TicketsAddFromLink','_self',''],
	// [null,'<div class="hmenu_itm">Home Page</div>','index.php?module=UserArea&action=Index','_self','User Home Directory'],

	// KNOWLEDGE BASE
	[null,'<div class="hmenu_itm">{trans str="Knowledge Base"}</div>','index.php?module=KnowledgeBase&action=Categories','_self',''],
	
	// TUTORIAL/MANUAL
//	[null,'<div class="hmenu_itm">Tutorial/Manual</div>',"javascript:void(window.open('http://www.omnihelpdesk.com/tutorial1.htm','tutorial','menubar=no,toolbar=no, width = 700,scrollbars = 1'));",'_self',''],
	[null,'<div class="hmenu_itm">&nbsp;</div>',"javascript:void(0);",'_self',''],


	[null,'<div class="hmenu_itm logout">{trans str="Logout"}</div>','index.php?module=System&action=AuthenticateUser&logout=1','_self','Logout']
];

//ohdDrawMenu();

-->
</script>