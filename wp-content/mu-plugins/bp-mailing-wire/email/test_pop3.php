<?php
/*
 * test_pop3.php
 *
 * @(#) $Header: /home/mlemos/cvsroot/pop3/test_pop3.php,v 1.4 2004/09/30 20:01:18 mlemos Exp $
 *
 */

?><HTML>
<HEAD>
<TITLE>Test for Manuel Lemos's PHP POP3 class</TITLE>
</HEAD>
<BODY>
<?php

	require("pop3.php");

  /* Uncomment when using SASL authentication mechanisms */
	/*	*/

	require("sasl.php");

	$pop3=new pop3_class;
	$pop3->hostname="mail.lezardtwist.org";            /* POP 3 server host name              */
	$pop3->port=110;                        /* POP 3 server host port              */
	$user="pop41125";                       /* Authentication user name            */
	$password="sz0zykh1";                   /* Authentication password             */
	$pop3->realm="";                        /* Authentication realm or domain      */
	$pop3->workstation="";                  /* Workstation for NTLM authentication */
	$apop=0;                                /* Use APOP authentication             */
	$pop3->authentication_mechanism="USER"; /* SASL authentication mechanism       */
	$pop3->debug=0;                         /* Output debug information            */
	$pop3->html_debug=0;                    /* Debug information is in HTML        */

	if(($error=$pop3->Open())=="")
	{
		echo "<PRE>Connected to the POP3 server &quot;".$pop3->hostname."&quot;.</PRE>\n";
		if(($error=$pop3->Login($user,$password,$apop))=="")
		{
			echo "<PRE>User &quot;$user&quot; logged in.</PRE>\n";
			if(($error=$pop3->Statistics($messages,$size))=="")
			{
				echo "<PRE>There are $messages messages in the mail box with a total of $size bytes.</PRE>\n";
				$result=$pop3->ListMessages("",0);
				if(GetType($result)=="array")
				{
					for(Reset($result),$message=0;$message<count($result);Next($result),$message++)
						echo "<PRE>Message ",Key($result)," - ",$result[Key($result)]," bytes.</PRE>\n";
					$result=$pop3->ListMessages("",1);
					if(GetType($result)=="array")
					{
						for(Reset($result),$message=0;$message<count($result);Next($result),$message++)
							echo "<PRE>Message ",Key($result),", Unique ID - \"",$result[Key($result)],"\"</PRE>\n";
						$message_nr=0;
						while($messages>$message_nr)
						{
							$message_nr++;	if(($error=$pop3->RetrieveMessage($message_nr,$headers,$body,32))=="")
							{
								echo "<PRE>Message $message_nr:\n---Message headers starts below---</PRE>\n";
								for($line=0;$line<count($headers);$line++)
									echo "<PRE>",HtmlSpecialChars($headers[$line]),"</PRE>\n";
								echo "<PRE>---Message headers ends above---\n---Message body debug starts below---</PRE>\n";
								echo "<pre>";print_r($body);echo "</pre>";
								echo "<PRE>---Message body debug ends above---\n---Message body starts below---</PRE>\n";
								for($line=0;$line<count($body);$line++)
									echo "<PRE>",HtmlSpecialChars($body[$line]),"</PRE>\n";
								echo "<PRE>---Message body ends above---</PRE>\n";
								if(($error=$pop3->DeleteMessage($message_nr))=="")
								{
									echo "<PRE>Marked message 1 for deletion.</PRE>\n";
									if(($error=$pop3->ResetDeletedMessages())=="")
									{
										echo "<PRE>Resetted the list of messages to be deleted.</PRE>\n";
									}
								}
							}
						}
						if($error==""
						&& ($error=$pop3->Close())=="")
							echo "<PRE>Disconnected from the POP3 server &quot;".$pop3->hostname."&quot;.</PRE>\n";
						
					}
					else
						$error=$result;
				}
				else
					$error=$result;
			}
		}
	}
	if($error!="")
		echo "<H2>Error: ",HtmlSpecialChars($error),"</H2>";
?>

</BODY>
</HTML>
