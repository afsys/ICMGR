<?
include("../config.php");
include("Classes/userTicketsList.class.php");
$userTicket = new userTicketsList();
$userTicket->process_request($ticket_form);
?>

<?= $ticket_form ?>