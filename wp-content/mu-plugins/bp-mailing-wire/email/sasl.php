<?php
/*
 * sasl.php
 *
 * @(#) $Id: sasl.php,v 1.8 2004/11/17 07:59:54 mlemos Exp $
 *
 */

define("SASL_INTERACT", 2);
define("SASL_CONTINUE", 1);
define("SASL_OK",       0);
define("SASL_FAIL",    -1);
define("SASL_NOMECH",  -4);

class sasl_interact_class
{
	var $id;
	var $challenge;
	var $prompt;
	var $default_result;
	var $result;
};

/*
{metadocument}<?xml version="1.0" encoding="ISO-8859-1" ?>
<class>

	<package>net.manuellemos.sasl</package>

	<version>@(#) $Id: sasl.php,v 1.8 2004/11/17 07:59:54 mlemos Exp $</version>
	<copyright>Copyright © (C) Manuel Lemos 2004</copyright>
	<title>Simple Authentication and Security Layer client</title>
	<author>Manuel Lemos</author>
	<authoraddress>mlemos-at-acm.org</authoraddress>

	<documentation>
		<idiom>en</idiom>
		<purpose>Provide a common interface to plug-in driver classes that
			implement different mechanisms for authentication used by clients of
			standard protocols like SMTP, POP3, IMAP, HTTP, etc.. Currently the
			supported authentication mechanisms are: <tt>PLAIN</tt>,
			<tt>LOGIN</tt>, <tt>CRAM-MD5</tt> and <tt>NTML</tt> (Windows or
			Samba).</purpose>
		<usage>.</usage>
	</documentation>

{/metadocument}
*/

class sasl_client_class
{
	/* Public variables */

/*
{metadocument}
	<variable>
		<name>error</name>
		<type>STRING</type>
		<value></value>
		<documentation>
			<purpose>Store the message that is returned when an error
				occurs.</purpose>
			<usage>Check this variable to understand what happened when a call to
				any of the class functions has failed.<paragraphbreak />
				This class uses cumulative error handling. This means that if one
				class functions that may fail is called and this variable was
				already set to an error message due to a failure in a previous call
				to the same or other function, the function will also fail and does
				not do anything.<paragraphbreak />
				This allows programs using this class to safely call several
				functions that may fail and only check the failure condition after
				the last function call.<paragraphbreak />
				Just set this variable to an empty string to clear the error
				condition.</usage>
		</documentation>
	</variable>
{/metadocument}
*/
	var $error='';

/*
{metadocument}
	<variable>
		<name>mechanism</name>
		<type>STRING</type>
		<value></value>
		<documentation>
			<purpose>Store the name of the mechanism that was selected during the
				call to the <functionlink>Start</functionlink> function.</purpose>
			<usage>.</usage>
		</documentation>
	</variable>
{/metadocument}
*/
	var $mechanism;

	/* Private variables */

	var $driver;
	var $drivers=array(
		"CRAM-MD5" => array("cram_md5_sasl_client_class", "cram_md5_sasl_client.php" ),
		"LOGIN"    => array("login_sasl_client_class",    "login_sasl_client.php"    ),
		"NTLM"     => array("ntlm_sasl_client_class",     "ntlm_sasl_client.php"     ),
		"PLAIN"    => array("plain_sasl_client_class",    "plain_sasl_client.php"    ),
		"Basic"    => array("basic_sasl_client_class",    "basic_sasl_client.php"    )
	);
	var $credentials=array();

	/* Public functions */

/*
{metadocument}
	<function>
		<name>SetCredential</name>
		<type>VOID</type>
		<documentation>
			<purpose>Store the value of a credential that may be used by any of
			 the supported mechanisms to process the authentication messages and
			 responses.</purpose>
			<usage>.</usage>
			<returnvalue>.</returnvalue>
		</documentation>
		<argument>
			<name>key</name>
			<type>STRING</type>
			<documentation>
				<purpose>.</purpose>
			</documentation>
		</argument>
		<argument>
			<name>value</name>
			<type>STRING</type>
			<documentation>
				<purpose>.</purpose>
			</documentation>
		</argument>
		<do>
{/metadocument}
*/
	Function SetCredential($key,$value)
	{
		$this->credentials[$key]=$value;
	}
/*
{metadocument}
		</do>
	</function>
{/metadocument}
*/

/*
{metadocument}
	<function>
		<name>GetCredentials</name>
		<type>INTEGER</type>
		<documentation>
			<purpose>Retrieve the values of one or more credentials to be used by
				the authentication mechanism classes.</purpose>
			<usage>.</usage>
			<returnvalue>.</returnvalue>
		</documentation>
		<argument>
			<name>credentials</name>
			<type>STRING</type>
			<documentation>
				<purpose>.</purpose>
			</documentation>
		</argument>
		<argument>
			<name>defaults</name>
			<type>HASH</type>
			<documentation>
				<purpose>.</purpose>
			</documentation>
		</argument>
		<argument>
			<name>interactions</name>
			<type>ARRAY</type>
			<documentation>
				<purpose>.</purpose>
			</documentation>
		</argument>
		<do>
{/metadocument}
*/
	Function GetCredentials(&$credentials,$defaults,&$interactions)
	{
		Reset($credentials);
		$end=(GetType($key=Key($credentials))!="string");
		for(;!$end;)
		{
			if(!IsSet($this->credentials[$key]))
			{
				if(IsSet($defaults[$key]))
					$credentials[$key]=$defaults[$key];
				else
				{
					$this->error="the requested credential ".$key." is not defined";
					return(SASL_NOMECH);
				}
			}
			else
				$credentials[$key]=$this->credentials[$key];
			Next($credentials);
			$end=(GetType($key=Key($credentials))!="string");
		}
		return(SASL_CONTINUE);
	}
/*
{metadocument}
		</do>
	</function>
{/metadocument}
*/

/*
{metadocument}
	<function>
		<name>Start</name>
		<type>INTEGER</type>
		<documentation>
			<purpose>Process the initial authentication step initializing the
				driver class that implements the first of the list of requested
				mechanisms that is supported by this SASL client library
				implementation.</purpose>
			<usage>.</usage>
			<returnvalue>.</returnvalue>
		</documentation>
		<argument>
			<name>mechanisms</name>
			<type>ARRAY</type>
			<documentation>
				<purpose>.</purpose>
			</documentation>
		</argument>
		<argument>
			<name>message</name>
			<type>STRING</type>
			<documentation>
				<purpose>.</purpose>
			</documentation>
		</argument>
		<argument>
			<name>interactions</name>
			<type>ARRAY</type>
			<documentation>
				<purpose>.</purpose>
			</documentation>
		</argument>
		<do>
{/metadocument}
*/
	Function Start($mechanisms, &$message, &$interactions)
	{
		if(strlen($this->error))
			return(SASL_FAIL);
		if(IsSet($this->driver))
			return($this->driver->Start($this,$message,$interactions));
		$no_mechanism_error="";
		for($m=0;$m<count($mechanisms);$m++)
		{
			$mechanism=$mechanisms[$m];
			if(IsSet($this->drivers[$mechanism]))
			{
				if(!class_exists($this->drivers[$mechanism][0]))
					require(dirname(__FILE__)."/".$this->drivers[$mechanism][1]);
				$this->driver=new $this->drivers[$mechanism][0];
				if($this->driver->Initialize($this))
				{
					$status=$this->driver->Start($this,$message,$interactions);
					switch($status)
					{
						case SASL_NOMECH:
							Unset($this->driver);
							if(strlen($no_mechanism_error)==0)
								$no_mechanism_error=$this->error;
							$this->error="";
							break;
						case SASL_CONTINUE:
							$this->mechanism=$mechanism;
							return($status);
						default:
							Unset($this->driver);
							$this->error="";
							return($status);
					}
				}
				else
				{
					Unset($this->driver);
					if(strlen($no_mechanism_error)==0)
						$no_mechanism_error=$this->error;
					$this->error="";
				}
			}
		}
		$this->error=(strlen($no_mechanism_error) ? $no_mechanism_error : "it was not requested any of the authentication mechanisms that are supported");
		return(SASL_NOMECH);
	}
/*
{metadocument}
		</do>
	</function>
{/metadocument}
*/

/*
{metadocument}
	<function>
		<name>Step</name>
		<type>INTEGER</type>
		<documentation>
			<purpose>Process the authentication steps after the initial until the
				authetication iteration dialog is complete.</purpose>
			<usage>.</usage>
			<returnvalue>.</returnvalue>
		</documentation>
		<argument>
			<name>response</name>
			<type>STRING</type>
			<documentation>
				<purpose>.</purpose>
			</documentation>
		</argument>
		<argument>
			<name>message</name>
			<type>STRING</type>
			<documentation>
				<purpose>.</purpose>
			</documentation>
		</argument>
		<argument>
			<name>interactions</name>
			<type>ARRAY</type>
			<documentation>
				<purpose>.</purpose>
			</documentation>
		</argument>
		<do>
{/metadocument}
*/
	Function Step($response, &$message, &$interactions)
	{
		if(strlen($this->error))
			return(SASL_FAIL);
		return($this->driver->Step($this,$response,$message,$interactions));
	}
/*
{metadocument}
		</do>
	</function>
{/metadocument}
*/

};

/*

{metadocument}
</class>
{/metadocument}

*/

?>