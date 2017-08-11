<?php
$db = mysqli_connect("localhost","username","password","database");
// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
/* connect to server */
$username = '<mail>';
$password = '<password>'; 
$hostname = '<domain name>';  

$inbox = imap_open('{'.$hostname .':143/notls}INBOX',$username ,$password ) or die('Cannot connect to domain:' . imap_last_error());
$emails = imap_search($inbox,'ALL');

if($emails)
	{
		  $output = '';
		  rsort($emails);
		  foreach($emails as $email_number)
		  {
			$overview = imap_fetch_overview($inbox,$email_number,0);
			$message = imap_fetchbody($inbox,$email_number,1.1);			
			$structure = imap_fetchstructure($inbox,$email_number);
			$subject= $overview[0]->subject;			
						
			$attachments = array();
			  for($i = 0; $i < count($structure->parts); $i++) 
			  {				
				$attachments[$i] = array(
								 'is_attachment' => false,
								 'filename' => '',
								 'name' => '',
								 'attachment' => '');
				if($structure->parts[$i]->ifdparameters) 
				{
				  foreach($structure->parts[$i]->dparameters as $object) 
				  {
					if(strtolower($object->attribute) == 'filename') 
					{
					  $attachments[$i]['is_attachment'] = true;
					  $attachments[$i]['filename'] = $object->value;
					}
				  }
				}
				if($structure->parts[$i]->ifparameters)
				{
				  foreach($structure->parts[$i]->parameters as $object) 
				  {
					if(strtolower($object->attribute) == 'name') 
					{
					  $attachments[$i]['is_attachment'] = true;
					  $attachments[$i]['name'] = $object->value;
					}
				  }
				}
				if($attachments[$i]['is_attachment']) 
				{
				  $attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, $i+1);
				  if($structure->parts[$i]->encoding == 3) 
				  {
					$attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
				  }
				  elseif($structure->parts[$i]->encoding == 4) 
				  {
				$attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
				  }
				}				
			 } 
	        }
	     }
 if($emails)
	{
		  $output = '';
		  rsort($emails);
		  foreach($emails as $email_number)
		  {
			$overview = imap_fetch_overview($inbox,$email_number,0);
			$message = imap_fetchbody($inbox,$email_number, 1);
				
			$check = imap_mailboxmsginfo($inbox);
			echo "Messages before delete: " . $check->Nmsgs . "<br />\n";

			imap_delete($inbox, 1);

			$check = imap_mailboxmsginfo($inbox);
			echo "Messages after  delete: " . $check->Nmsgs . "<br />\n";

			imap_expunge($inbox);

			$check = imap_mailboxmsginfo($inbox);
			echo "Messages after expunge: " . $check->Nmsgs . "<br />\n";
		  }
	}

imap_close($inbox);
?>