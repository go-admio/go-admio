<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR|E_PARSE);

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
	echo "Invalid Go Panel Path";
	exit;
}

// Input ID
$ID=$_REQUEST['ID'];

$SiteID=$_REQUEST['SiteID'];
$Value=$_REQUEST['Value'];
$Value=str_replace('"','',$Value);
$Value=str_replace("'","",$Value);

if ($ID=="AccountID" and $Value!="")
{
SQL("UPDATE Config SET CloudflareAccountID='$Value'");
}
elseif ($ID=="APIToken" and $Value!="")
{
SQL("UPDATE Config SET CloudflareAPIToken='$Value'");
}

echo $Value;

?>