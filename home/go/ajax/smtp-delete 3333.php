<?php
session_start();
header('Content-Type: text/html; charset=UTF-8'); 

include "/usr/local/gopanel/include/config/config.php";
include "/usr/local/gopanel/home/go/include/function/function.php";

if ($_SESSION['SessionSSHUsername']!="root" and $_SESSION['SessionSSHUsername']!=$SSHUsername)
{
	echo "Sorry, You Are Not Allowed to Access This Page";
	exit;
}

if (!StartsWith(getcwd(),"/usr/local/gopanel"))
{
	echo "Invalid GoPanel Path";
	exit;
}

$SMTPID=$_REQUEST['SMTPID'];
$Value=$_REQUEST['Value'];

if ($SMTPID=="SMTPHostname")
{

	$Value=trim($Value);


	if( preg_match( "/^(?:[-A-Za-z0-9]+\.)+[A-Za-z]{2,12}$/", $Value))
	{
	
	}
	elseif (filter_var($Value, FILTER_VALIDATE_IP)) 
	{
	
	}
	else
	{
	$Value="localhost";
	}
		
	SQL("update Config set SMTPHostname='$Value' where ConfigID=1");

	echo $Value;

	SSH2 ("/usr/bin/sudo screen -d -m bash -c '/go/postconf && /go/mail restart'",$_SESSION['SessionSSHUsername'],$_SESSION['SessionSSHPassword']);

}

if ($SMTPID=="SMTPAuth")
{
	$Value=trim($Value);

	if($Value=="1" or strtolower($Value)=="on" or strtolower($Value)=="yes")
	{
	$Value="yes";
	}
	else
	{
	$Value="no";
	}
		
	SQL("update Config set SMTPAuth='$Value' where ConfigID=1");

	echo $Value;
	SSH2 ("/usr/bin/sudo screen -d -m bash -c '/go/postconf && /go/mail restart'",$_SESSION['SessionSSHUsername'],$_SESSION['SessionSSHPassword']);

}

if ($SMTPID=="SMTPPort")
{

	$Value=intval($Value);

	if ($Value==0 or $Value>65535 or $Value<0)
	{
	$Value="587";
	}
	
	SQL("update Config set SMTPPort='$Value' where ConfigID=1");

	if ($Value=="993")
	{
	SQL("update Config set SMTPSecure='SSL' where ConfigID=1");
	}

	echo $Value;
	SSH2 ("/usr/bin/sudo screen -d -m bash -c '/go/postconf && /go/mail restart'",$_SESSION['SessionSSHUsername'],$_SESSION['SessionSSHPassword']);

}

if ($SMTPID=="SMTPUsername")
{

	$Value=trim($Value);

	SQL("update Config set SMTPUsername='$Value' where ConfigID=1");

	echo $Value;
	SSH2 ("/usr/bin/sudo screen -d -m bash -c '/go/postconf && /go/mail restart'",$_SESSION['SessionSSHUsername'],$_SESSION['SessionSSHPassword']);

}

if ($SMTPID=="SMTPPassword")
{

	$Value=trim($Value);

	SQL("update Config set SMTPPassword='$Value' where ConfigID=1");

	echo $Value;
	SSH2 ("/usr/bin/sudo screen -d -m bash -c '/go/postconf && /go/mail restart'",$_SESSION['SessionSSHUsername'],$_SESSION['SessionSSHPassword']);

}

// Failover



if ($SMTPID=="FailoverSMTPHostname")
{

	$Value=trim($Value);

	if( preg_match( "/^(?:[-A-Za-z0-9]+\.)+[A-Za-z]{2,12}$/", $Value))
	{
	
	}
	elseif (filter_var($Value, FILTER_VALIDATE_IP)) 
	{
	
	}
	else
	{
	$Value="localhost";
	}
	
	
	SQL("update Config set FailoverSMTPHostname='$Value' where ConfigID=1");

	echo $Value;

	SSH2 ("/usr/bin/sudo screen -d -m bash -c '/go/vmail'",$_SESSION['SessionSSHUsername'],$_SESSION['SessionSSHPassword']);

}

if ($SMTPID=="FailoverSMTPPort")
{
	$Value=intval($Value);

	if ($Value==0 or $Value>65535 or $Value<0)
	{
	$Value="587";
	}
	
	SQL("update Config set FailoverSMTPPort='$Value' where ConfigID=1");

	if ($Value=="993")
	{
	SQL("update Config set FailoverSMTPSecure='SSL' where ConfigID=1");
	}

	echo $Value;
	SSH2 ("/usr/bin/sudo screen -d -m bash -c '/go/vmail'",$_SESSION['SessionSSHUsername'],$_SESSION['SessionSSHPassword']);

}

if ($SMTPID=="FailoverSMTPUsername")
{

	$Value=trim($Value);

	SQL("update Config set FailoverSMTPUsername='$Value' where ConfigID=1");

	echo $Value;
	SSH2 ("/usr/bin/sudo screen -d -m bash -c '/go/vmail'",$_SESSION['SessionSSHUsername'],$_SESSION['SessionSSHPassword']);

}

if ($SMTPID=="FailoverSMTPPassword")
{

	$Value=trim($Value);

	SQL("update Config set FailoverSMTPPassword='$Value' where ConfigID=1");

	echo $Value;
	SSH2 ("/usr/bin/sudo screen -d -m bash -c '/go/vmail'",$_SESSION['SessionSSHUsername'],$_SESSION['SessionSSHPassword']);

}


// Brevo 


if ($SMTPID=="BrevoAPIKey")
{
	$Value=trim($Value);

	SQL("update Config set BrevoAPIKey='$Value' where ConfigID=1");

	echo $Value;
	SSH2 ("/usr/bin/sudo screen -d -m bash -c '/go/brevo config'",$_SESSION['SessionSSHUsername'],$_SESSION['SessionSSHPassword']);

}


if ($SMTPID=="RoundcubeSMTPHost")
{

	$Value=trim($Value);
	
	SQL("update Config set RoundcubeSMTPHost='$Value' where ConfigID=1");
	echo $Value;
	
	SSH2 ("/usr/bin/sudo screen -d -m bash -c '/go/brevo config'",$_SESSION['SessionSSHUsername'],$_SESSION['SessionSSHPassword']);

}

if ($SMTPID=="RoundcubeSMTPPort")
{

	$Value=intval($Value);

	if ($Value==0 or $Value>65535 or $Value<0)
	{
	$Value="587";
	}
	
	SQL("update Config set RoundcubeSMTPPort='$Value' where ConfigID=1");
	echo $Value;
	
	SSH2 ("/usr/bin/sudo screen -d -m bash -c '/go/brevo config'",$_SESSION['SessionSSHUsername'],$_SESSION['SessionSSHPassword']);

}

if ($SMTPID=="RoundcubeSMTPUser")
{

	$Value=trim($Value);

	SQL("update Config set RoundcubeSMTPUser='$Value' where ConfigID=1");

	echo $Value;
	
	SSH2 ("/usr/bin/sudo screen -d -m bash -c '/go/brevo config'",$_SESSION['SessionSSHUsername'],$_SESSION['SessionSSHPassword']);

}

if ($SMTPID=="RoundcubeSMTPPass")
{

	$Value=trim($Value);

	SQL("update Config set RoundcubeSMTPPass='$Value' where ConfigID=1");

	echo $Value;
	
	SSH2 ("/usr/bin/sudo screen -d -m bash -c '/go/brevo config'",$_SESSION['SessionSSHUsername'],$_SESSION['SessionSSHPassword']);

}


?>