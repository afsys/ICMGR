<?//DON'T PUT ANYTHING ABOVE THIS LINE!
    include("../config.php");
    include("classes/userTicket.class.php");
    $ticket = new userTicket(1);
    $ticket->process_request($ticket_form, $restore_form);
?>
<!-- put this line anywhere -->
<?= $ticket_form; ?>
	
	
<?= $restore_form; ?>