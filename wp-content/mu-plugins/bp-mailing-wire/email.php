<?php
/*
 * email.php
 *
 * using :
 * @(#) $Header: /home/mlemos/cvsroot/pop3/test_pop3.php,v 1.4 2004/09/30 20:01:18 mlemos Exp $
 *
 * Community Email interface
 * 
 */
/*
 * session_start();
if(!session_is_registered('LT_LOGIN')){
  header('Location: http://www.lezardtwist.org');
}
*/
global $content;

require("email/pop3.php");
require("email/sasl.php");

$pop3=new pop3_class;
$pop3->hostname="mail.XXXXXXX";         /* POP 3 server host name              */
$pop3->port=110;                        /* POP 3 server host port              */
$user="XXXXXXXX";                       /* Authentication user name            */
$password="XXXXXXXX";                   /* Authentication password             */
$pop3->realm="";                        /* Authentication realm or domain      */
$pop3->workstation="";                  /* Workstation for NTLM authentication */
$apop=0;                                /* Use APOP authentication             */
$pop3->authentication_mechanism="USER"; /* SASL authentication mechanism       */
$pop3->debug=0;                         /* Output debug information            */
$pop3->html_debug=0;                    /* Debug information is in HTML        */
$debug = false;
if (isset($HTTP_GET_VARS['DEBOGAGE'])){
  $debug=true;
}
$debugtext='';
$action = 'LIST';
$content='';
$offset=1;
$count=42;

switch($HTTP_GET_VARS['action']){
 case 'SHOW': 
   if (isset($HTTP_GET_VARS['MID'])){
	   $action='SHOW';
   } 
   break;
 case 'ATTA':
   break;   if (isset($HTTP_GET_VARS['MID'])){
	 $action='ATTA';
   } 

}
if (isset($HTTP_GET_VARS['OFFSET'])){
 $offset=$HTTP_GET_VARS['OFFSET'] * 1;
} 

if (isset($HTTP_GET_VARS['NR'])){
 $count=$HTTP_GET_VARS['NR'] * 1;
} 

function parse_from ($from){
  $q='';
  $elements = imap_mime_header_decode($from);
  for ($i=0; $i<count($elements); $i++) {
	//   echo "Charset: {$elements[$i]->charset}\n";
   $q.=htmlentities($elements[$i]->text);
}
  return ($q);
}


function message_list($pop3,$messages,$size,$result,$offset){
  global $content;
  if (count ($result) != $messages)
	$content .="<b>count ($result) : ".count ($result)." != $messages !!!</b><br/>";
  $content .="<H2> $messages Messages au total (taille : $size)</H2><br/>";
  $myresult=array();
  $message_nr=$offset;
  while($messages+$offset>$message_nr)
	{
	  $from='';$subject='';$date='';
	  $message_nr++;
	  if(($error=$pop3->RetrieveMessage($message_nr,$headers,$body,0))=="")
		{
		  for($line=0;$line<count($headers);$line++){
			if (strpos($headers[$line],'From: ')===0)
			  $from=substr($headers[$line],6);
			if (strpos($headers[$line],'Subject: ')===0)
			  $subject=substr($headers[$line],8);
			if (strpos($headers[$line],'Date: ')===0)
			  $date=substr($headers[$line],6);
		  }
		  $myresult[$message_nr]='<span id="message_from">De <a href=\'mailto:'.$from.'\'>'.parse_from($from).'</a>, le '.$date.'</span><br/>
<span id="message_subject">'.$subject."</span><br/><br/>\n";
		}
	  else {
		$myresult[$message_nr]='Erreur lors de la r&eacute,ception des en-t&ecirc;tes du message '.$message_nr.'<br/>
Message d\'erreur : '.$error.'<br/><br/>';
		  }
	}

  $i = 1;
  foreach ($myresult as $k=>$v){
	$content.= '<span id="message_list"><a href="email.php?action=SHOW&MID='.$k.'">Message '.$i.'</a></span><br/>
'.$v;
	$i++;
  }
}


function message_show($pop3,$messages,$size,$result,$mid){
  global $content;
  if(($error=$pop3->RetrieveMessage($mid,$headers,$body,-1))=="")
	{
	  for($line=0;$line<count($headers);$line++){
		if (strpos($headers[$line],'From: ')===0)
		  $from=substr($headers[$line],6);
		if (strpos($headers[$line],'Subject: ')===0)
		  $subject=substr($headers[$line],8);
		if (strpos($headers[$line],'Date: ')===0)
			  $date=substr($headers[$line],6);
	  }
	  $content='<H3 style="color:#444444"><a href="email.php">Retour &agrave; la liste des messages</a></H3>
<span id="message_from">De <a href="mailto:'.$from.'">'.parse_from($from).'</a></span>, le '.$date.'<br/>
<span id="message_subject">'.$subject."</span><br/><br/>\n";
	  foreach ($body as $line){
		$content.=$line.'<br/>
';
	  }
	}
  else {
	$myresult[$message_nr]='Erreur lors de la r&eacute,ception des en-t&ecirc;tes du message '.$message_nr.'<br>
Message d\'erreur : '.$error.'<br/><br/>';
  }
  
}


function message_atta($pop,$messages,$size,$result,$mid){
global $content;
}


if(($error=$pop3->Open())=="")
{
  error_log("Connected to the POP3 server: ".$pop3->hostname);
  if(($error=$pop3->Login($user,$password,$apop))=="")
	{
	  error_log("User $user logged in.");
	  if(($error=$pop3->Statistics($messages,$size))=="")
		{
		  error_log("There are $messages messages in the mail box with a total of $size bytes.");
		  $result=$pop3->ListMessages("",0);
		  if(GetType($result)=="array")
			{

				for(Reset($result),$message=0;$message<count($result);Next($result),$message++)
				  $debugtext.= "<PRE>Message ".Key($result)." - ".$result[Key($result)]." bytes.</PRE>\n";
				$result=$pop3->ListMessages("",1);
				if(GetType($result)=="array")
				  {
					for(Reset($result),$message=0;$message<count($result);Next($result),$message++)
					  $debugtext.= "<PRE>Message ".Key($result).", Unique ID - \"".$result[Key($result)]."\"</PRE>\n";
					$message_nr=0;
			  if($action ==='LIST'){
				message_list($pop3,$count,$size,$result,$offset);
			  }
			  else{
				if($action == 'SHOW'){
				  message_show($pop3,$messages,$size,$result,$HTTP_GET_VARS['MID']);
				}
				else{
				  if($action == 'ATTA'){
					message_atta($pop3,$messages,$size,$result,$HTTP_GET_VARS['MID']);
				  }
				}
			  }
			  if($debug){
					while($messages>$message_nr)
					  {
						$message_nr++;
						if(($error=$pop3->RetrieveMessage($message_nr,$headers,$body,32))=="")
						  {
							$debugtext.= "<PRE>Message $message_nr:\n---Message headers starts below---</PRE>\n";
							for($line=0;$line<count($headers);$line++)
							  $debugtext.= "<PRE>".HtmlSpecialChars($headers[$line])."</PRE>\n";
							$debugtext.= "<PRE>---Message headers ends above---\n---Message body debug starts below---</PRE>\n";
							// $debugtext.= "<pre>";print_r($body);$debugtext.= "</pre>";
							$debugtext.= "<PRE>---Message body debug ends above---\n---Message body starts below---</PRE>\n";
							for($line=0;$line<count($body);$line++)
							  $debugtext.= "<PRE>".HtmlSpecialChars($body[$line])."</PRE>\n";
							$debugtext.= "<PRE>---Message body ends above---</PRE>\n";
/*							if(($error=$pop3->DeleteMessage($message_nr))=="")
							  {
								$debugtext.= "<PRE>Marked message 1 for deletion.</PRE>\n";
								if(($error=$pop3->ResetDeletedMessages())=="")
								  {
									$debugtext.= "<PRE>Resetted the list of messages to be deleted.</PRE>\n";
								  }
							  }*/
						  }
					  }
			  }
					if($error==""
					   && ($error=$pop3->Close())=="")
					  $debugtext.= "<PRE>Disconnected from the POP3 server &quot;".$pop3->hostname."&quot;.</PRE>\n";
					
				  }
				else
				  $error=$result;
			}
		  else
			$error=$result;
		}
	}
}
