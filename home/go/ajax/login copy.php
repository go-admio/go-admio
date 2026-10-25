<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR|E_PARSE);

$SessionDir=session_save_path();
if (!is_dir("$SessionDir"))
{
//	echo "Session Directory \"$SessionDir\" not Exists.";
//	exit;
}

$UserID=$_REQUEST['UserID'];

$Admin=$_REQUEST['Admin'];
$Logout=$_REQUEST['Logout'];
$Action=$_REQUEST['Action'];
$SessionLng=$_REQUEST['SessionLng'];
$SessionTheme=$_REQUEST['SessionTheme'];
$SessionAdminView=$_REQUEST['SessionAdminView'];

setcookie ("CookiesLng",$SessionLng,time()+31104000,"/");
setcookie ("CookiesTheme",$SessionTheme,time()+31104000,"/");
setcookie ("CookiesAdminView",$SessionAdminView,time()+31104000,"/");

if ($CookiesPassword=="deleted") {$CookiesPassword="";}

include("/usr/local/gopanel/home/go/include/function/function.php");
include("/usr/local/gopanel/home/go/include/function/sendmail.php");
include("/usr/local/gopanel/home/go/include/function/advertise.php");
include("/usr/local/gopanel/include/config/config.php");

if (file_exists("vendor/autoload.php"))
{
require "vendor/autoload.php";
}

use Sonata\GoogleAuthenticator\GoogleAuthenticator;
use Sonata\GoogleAuthenticator\GoogleQrUrl;
if (class_exists(GoogleAuthenticator::class)) 
{
    $ga = new GoogleAuthenticator();
} 

	$Username=ValidateUsername($_REQUEST['Username']);
	$Password=$_REQUEST['Password'];
	$Code=ValidateUsername($_REQUEST['Code']);

	if ($Username=="root" and $DemoPassword!="")
	{
	$Password=$DemoPassword;
	}
	
	if (stristr($Username,"'") or stristr($Username," and ")  or stristr($Username," or ") or stristr($Password,"'") or stristr($Password," and ")  or stristr($Password," or "))
	{
		echo Error("Access denied");
		exit;
	}
	
	$Result = SQL("select Username from Site where Username='$Username'");
	foreach ($Result as $Row)
	{
	$SiteExists=1;
	}
	
	$Result = SQL("select * from Auth where AuthUser='$Username'");
	foreach ($Result as $Row)
	{
	$AuthUser=$Row['AuthUser'];
	$AuthSecret=$Row['AuthSecret'];
	}
	
	if ($AuthUser!="" and $AuthSecret!="")
	{
	
		if (trim($Code)=="")
		{
			echo "Please enter Google Authenticator code!";
			exit;
		}
		
		

		if (!$ga->checkCode($AuthSecret,$Code))
		{
			echo "Invalid or expired Google Authenticator code!";
			exit;
		}
	}
	
	if ($Username=="root" or $Username==$SSHUsername)
	{
		if (StartsWith(getcwd(),"/usr/local/gopanel"))
		{

			// SSH Access
			$Connect = null;
			$Authenticated = false;

			// Try using ssh2 extension first if available
			if (function_exists('ssh2_connect')) 
			{
				$Connect = ssh2_connect("localhost", $SSHPort);
				if (@ssh2_auth_password($Connect, $Username, $Password)) {
					$Authenticated = true;
				}
			}

			// Fallback to phpseclib if ssh2 failed or not available
			if (!$Authenticated && class_exists('phpseclib3\\Net\\SSH2')) 
			{
				include_once "vendor/autoload.php";

				try {
					$Connect = new \phpseclib3\Net\SSH2("localhost", $SSHPort);
					if ($Connect->login($Username, $Password)) {
						$Authenticated = true;
					}
				} catch (Exception $e) {
					// Log error if needed
					$Authenticated = false;
				}
			}

			if ($Authenticated) {
				SQL("UPDATE User SET Username='$Username',Password='".md5($Password)."' where UserID=1");
				
				$Result = SQL("select * from User where UserID=1");
				foreach ($Result as $Row) {
					$Success = 1;
					
					$_SESSION['SessionType'] = "Root";
					$_SESSION['SkipContact'] = 1;
					
					$SessionAdminDomain = $_SERVER['HTTP_HOST'];
					$SessionAdminDomain = str_replace("www.","",$SessionAdminDomain);
					
					$_SESSION['SessionUserID'] = $Row['UserID'];
					$_SESSION['SessionUsername'] = $Row['Username'];
					$_SESSION['SessionSwitchUsername'] = $Row['Username'];
					$_SESSION['SessionFullName'] = $Row['FullName'];
					
					$_SESSION['SessionPassword'] = $Password;
					$_SESSION['SessionSwitchPassword'] = $Password;
					$_SESSION['SessionSSHPassword'] = $Password;
					
					$_SESSION['SessionLng'] = $_REQUEST['SessionLng'];
					
					if ($_REQUEST['SessionTheme'] == "light") {
						$_SESSION['SessionTheme'] = "light";
					} else {
						$_SESSION['SessionTheme'] = "dark";
					}
					
					$_SESSION['SessionAdminView'] = $_REQUEST['SessionAdminView'];
					$_SESSION['SessionUserGroup'] = $Row['UserGroup'];
					
					setcookie("CookiesUsername", $Row['Username'], time()+31104000, "/");
					
					SQL("UPDATE Config SET LoginNo=LoginNo+1 where ConfigID=1");
					
					$IP = ClientIP();
					if (filter_var($IP, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
						$Error = SSH("/usr/bin/sudo screen -d -m bash -c '/go/admin $IP'", $Username, $Password, 3600);
					}
				}
			}
		}
		else
		{
		echo "Invalid GoPanel Path";
		exit;
		}
	}
	elseif ($SiteExists==1)
	{
		if (StartsWith(getcwd(),"/usr/local/gopanel"))
		{
			// FTP Access
			$Connect=@ftp_connect("localhost");
			$Login=@ftp_login($Connect,$Username,$Password);
			if ($Login)
			{
			
				$Password=md5($Password);
				SQL("UPDATE Site SET Password='$Password' where Username='$Username'");
			
				$Result=SQL("select * from Site where Username='$Username'");
				foreach ($Result as $Row)
				{
					$Success=1;
					
					$SessionAdminDomain=$_SERVER['HTTP_HOST'];
					$SessionAdminDomain=str_replace("www.","",$SessionAdminDomain);
					
					$_SESSION['SessionType']="Website";
					$_SESSION['SessionUsername']=$Row['Username'];
					$_SESSION['SessionDomain']=$Row['Domain'];
					$_SESSION['SessionFullName']=$Row['FullName'];
					
					$_SESSION['SessionPassword']=$Row['Password'];
					$_SESSION['SessionSalt']=$Row['Salt'];
					
					$_SESSION['SessionUserGroup']=5;
					
					$_SESSION['SessionLng']=$_REQUEST['SessionLng'];
					$_SESSION['SessionTheme']=$_REQUEST['SessionTheme'];
					$_SESSION['SessionAdminView']=$_REQUEST['SessionAdminView'];
					
					setcookie ("CookiesUsername",$Row['Username'],time()+31104000,"/");
					

					
				}
			}
			
			// Login to User Using root Password
			if ($Success!=1)
			{
				
				$Connect = ssh2_connect("localhost", $SSHPort);
				if (@ssh2_auth_password($Connect,"root",$Password))
				{
				
					$Result = SQL("select * from Site where Username='$Username'");
					foreach ($Result as $Row)
					{
						$Success=1;
						
						$SessionAdminDomain=$_SERVER['HTTP_HOST'];
						$SessionAdminDomain=str_replace("www.","",$SessionAdminDomain);
						
						$_SESSION['SessionType']="Website";
						$_SESSION['SessionUsername']=$Row['Username'];
						$_SESSION['SessionDomain']=$Row['Domain'];
						$_SESSION['SessionFullName']=$Row['FullName'];
						
						$_SESSION['SessionPassword']=$Row['Password'];
						$_SESSION['SessionSalt']=$Row['Salt'];
						
						
						$_SESSION['SessionUserGroup']=5;
						
						$_SESSION['SessionLng']=$_REQUEST['SessionLng'];
						$_SESSION['SessionTheme']=$_REQUEST['SessionTheme'];
						$_SESSION['SessionAdminView']=$_REQUEST['SessionAdminView'];
							
					}
				
				}
				
			}
			
			// MD5 Access
			if ($Success!=1)
			{
			
				// MD5 Access
				$Result = SQL("select * from Site where Username='$Username' and Password='".md5($Password)."'");
				foreach ($Result as $Row)
				{
					$Success=1;

					$_SESSION['ResetPassword']=$Password;
					
					$SessionAdminDomain=$_SERVER['HTTP_HOST'];
					$SessionAdminDomain=str_replace("www.","",$SessionAdminDomain);
					
					$_SESSION['SessionType']="Website";
					$_SESSION['SessionUsername']=$Row['Username'];
					$_SESSION['SessionDomain']=$Row['Domain'];
					$_SESSION['SessionFullName']=$Row['FullName'];
					
					$_SESSION['SessionPassword']=$Row['Password'];
					$_SESSION['SessionSalt']=$Row['Salt'];
					
					$_SESSION['SessionUserGroup']=5;
					
					$_SESSION['SessionLng']=$_REQUEST['SessionLng'];
					$_SESSION['SessionTheme']=$_REQUEST['SessionTheme'];
					$_SESSION['SessionAdminView']=$_REQUEST['SessionAdminView'];
					
					
					
					setcookie ("CookiesUsername",$Row['Domain'],time()+31104000,"/");
					
				}

			}
					
		}
		else
		{
		echo "Invalid GoPanel Path";
		exit;
		}
	}
	else
	{
	
		if (StartsWith(getcwd(),"/usr/local/gopanel"))
		{
		// Reseller
	
			$Password=md5($Password);
			$Result = SQL("select * from User where Username='$Username' and Password='$Password'");
			foreach ($Result as $Row)
			{

				$Success=1;
				
				$_SESSION['SessionType']="Reseller";
				$_SESSION['SkipContact']=1;
				
				$SessionAdminDomain=$_SERVER['HTTP_HOST'];
				$SessionAdminDomain=str_replace("www.","",$SessionAdminDomain);
				
				$_SESSION['SessionUserID']=$Row['UserID'];
				$_SESSION['SessionUsername']=$Row['Username'];
				$_SESSION['SessionSwitchUsername']=$Row['Username'];
				$_SESSION['SessionFullName']=$Row['FullName'];
				$_SESSION['SessionSubReseller']=$Row['SubReseller'];
				
				$_SESSION['SessionUserGroup']=$Row['UserGroup'];
				
				$_SESSION['SessionResellerUsername']=$Row['Username'];

				$_SESSION['SessionPassword']=$Password;
				$_SESSION['SessionResellerPassword']=$Password;
				
				$_SESSION['SessionLng']=$_REQUEST['SessionLng'];
				$_SESSION['SessionTheme']=$_REQUEST['SessionTheme'];
				$_SESSION['SessionAdminView']=$_REQUEST['SessionAdminView'];
				
				setcookie ("CookiesUsername",$Row['Username'],time()+31104000,"/");
				

			
				
			}
			

		}
		
	}
	
	
	If ($Success!=1)
	{
		echo "The login is invalid.";
		
		$IP = getenv ("REMOTE_ADDR");
		//$X=@SendMail("info@mls.eg","Access Admin Area / Failed","Someone try to access $Domain admin area using this information:<br>Username: {$_REQUEST['SessionUsername']}<br>Password: {$_REQUEST['Password']}<br>IP: $IP","Blackhost CMS","noreply@mlseg.com",1,"UTF-8");
			
		exit;
	}
	
	echo "Success";
	
?>