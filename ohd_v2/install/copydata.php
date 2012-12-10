<pre>
<?php
	
require_once '../lib/Classes/sx_mysql.class.php';
require_once 'db_config.php';
$db_new = new sxMySQL(DB_HOST, DB_NAME, DB_USER, DB_PASS);
$db_old = new sxMySQL('localhost','hd_team','root','');

// clear database
$db_new->qD('canned_emails', '');
$db_new->qD('canned_emails_categories', '');
$db_new->qD('emails_history', '');
$db_new->qD('groups', '');
$db_new->qD('users', '');

$db_new->qD('tickets', '');
$db_new->qD('tickets_history', '');
$db_new->qD('tickets_messages', '');
$db_new->qD('tickets_products', '');
$db_new->qD('tickets_products_form_fields', '');
$db_new->qD('tickets_products_forms_values', '');

//copy groups
$res = $db_old->q('select * from ohd_groups');
while ($rec = $db_old->fetchAssoc($res)) 
{
	if ($rec['id'] == 1) continue;
	$db_new->qI('groups',array(
			'group_id'      => $rec['id'],
			'group_caption' => $rec['name'],
			'group_comment' => $rec['comment']
		)
	);
}

//copy users
$res = $db_old->q('select * from ohd_users');
while ($rec = $db_old->fetchAssoc($res))
{
	$db_new->qI('users',array(
			'user_id'       => $rec['id'],
			'user_login'    => $rec['username'],
			'user_pass'     => $rec['username'],
			'user_name'     => $rec['firstname'],
			'user_lastname' => $rec['lastname'],
			'user_email'    => $rec['email'],
			'is_sys_admin'  => ($rec['username'] == 'admin')?1:0
		)
	);
}
// copy tickets
$r = $db_old->q('
	SELECT 
	   id AS ticket_id,
	   id AS ticket_num,
	   open_date AS created_at,
	   close_date AS closed_at,
	   status AS status,
	   priority AS priority,
	   description AS type,
	   submitted_by AS creator_user_name, 
	   IF(assigned_to_group=1,NULL,assigned_to_group) as group_id,
	   IF(assigned_to_user=1,NULL,assigned_to_user) as assigned_to,
	   #notify
	   notes AS notes,
	   Problem as description,
	   name AS customer_name,
	   email AS customer_email,
	   name AS caption
	FROM 
	   ohd_tickets
');
while ($data = $db_old->fetchAssoc($r))
{
	$notes = $data["notes"];
	$db_old->query("select max(msg_date) from ohd_messages where tid=".$data["ticket_id"]);
	$data["modified_at"] = $db_old->result();
	if ($data['closed_at'] == '0000-00-00 00:00:00') $data['closed_at'] = 'NULL';
	unset($data["notes"]);
	//put data to ticket 
	$db_new->qI('tickets', $data);
	
	if ($notes != "") {
  	//put notice
  	$db_new->qI('tickets_messages', array(
  		'ticket_id' => $data['ticket_id'],
  		'message_id' => $db_new->getNextId('tickets_messages','message_id',array('ticket_id'=>$data['ticket_id'])),
  		'message_type' => 'note',
  		'message_text' => $notes,
  		'message_creator_user_name' => $data['creator_user_name'],
  		'message_datetime' => $data['created_at']
  	 )
    );
  }
  
  //put messages
	$res = $db_old->q('
		SELECT 
			tid AS ticket_id,
			mid AS message_id,
			message AS message_text,
			username as username,
			DATE_FORMAT(msg_date,\'%Y-%m-%d %H:%i:%s\') AS message_datetime
		FROM 
			ohd_messages 
		WHERE 
			tid='.$data['ticket_id']);
			
	while ($mdata = $db_old->fetchAssoc($res))
	{
  	$db_new->qI('tickets_messages', array(
  		'ticket_id' => $data['ticket_id'],
  		'message_id' => $db_new->getNextId('tickets_messages','message_id',array('ticket_id'=>$data['ticket_id'])),
  		'message_type' => 'message',
  		'message_text' => $mdata['message_text'],
			'message_creator_user_name' => $mdata['username'],
  		'message_datetime' => $mdata['message_datetime']
  	 )
    );

	}
}
	
echo "Ok!";
?>
</pre>