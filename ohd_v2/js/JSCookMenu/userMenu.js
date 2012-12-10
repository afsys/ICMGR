<script language="JavaScript1.2">
{assign var="user_rights" value=$user->getAttribute('user_rights')}
{assign var="user_data"   value=$user->getAttribute('user_data')}
<!--

var myMenu =
[
	[null,'<div class="hmenu_itm main">{trans str="Home"}</div>','index.php?module=UserArea&action=Index','_self','User Home Directory'],
	[null,'<div class="hmenu_itm">{trans str="New Ticket"}</div>','index.php?module=Tickets&action=TicketsAdd','_self',''],
	[null,'<div class="hmenu_itm">{trans str="My Tickets"}</div>','index.php?module=UserArea&action=LookupTicket','_self',''],	
	[null,'<div class="hmenu_itm">{trans str="Knowledge Base"}</div>','index.php?module=KnowledgeBase&action=Categories','_self',''],	
	//[null,'<div class="hmenu_itm">Tutorial/Manual</div>',"javascript:void(window.open('http://www.omnihelpdesk.com/tutorial1.htm','tutorial','menubar=no,toolbar=no, width = 700,scrollbars = 1'));",'_self',''],
	//[null,'<div class="hmenu_itm">&nbsp;</div>',"javascript:void(0);",'_self',''],
	[null,'<div class="hmenu_itm">{trans str="Register"}</div>','index.php?module=UserArea&action=Register','_self','Register'],
	[null,'<div class="hmenu_itm logout">{trans str="Not Logged In"}</div>','javascript:void();','_self','Not Logged In']
	
];

ohdDrawMenu();

-->
</script>