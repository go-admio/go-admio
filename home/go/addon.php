<?php
include "navigator.php";
$Buttons="";
include "title.php";

$AddonID=$_REQUEST['AddonID'];
$Password=ValidatePassword($_REQUEST['Password']);
$Directory=ValidateDirectory($_REQUEST['Directory']);
$Domain=ValidateDomain($_REQUEST['Domain']);
$AddonDomain=ValidateDomain($_REQUEST['AddonDomain']);
$Primary=intval($_REQUEST['Primary']);

If ($Delete==1 and $Step==1)
{

	echo Error("Delete \"$AddonDomain\" ? <a href=\"javascript:Load('$CurrentFileName?Delete=1&AddonDomain=$AddonDomain&Domain=$Domain&Step=2')\" class=Action>Yes</a> <a href=\"javascript:Load('$CurrentFileName?ControlID=$ControlID')\" class=Action>No</a>");
	exit;
	
}
elseif ($Delete==1 and $Step==2)
{
	include "access.php";
	
	$Error=SSH ("sudo screen -d -m bash -c '/go/delete $AddonDomain > /var/log/go/delete/$AddonDomain.log'",$_SESSION['SessionSSHUsername'],$_SESSION['SessionSSHPassword']);
	echo Error("Account $AddonDomain  has been terminated.");
	echo Error("<a href=\"javascript:Load('$CurrentFileName')\"><img src='theme/{$_SESSION['SessionTheme']}/image/back.svg' height=24 style='padding-right:8px;vertical-align:middle'>Go Back</a>");
	exit;	
}

if ($Primary==1 and $AddonDomain!="")
{
	include "access.php";
	
	$Error=SSH ("/go/primary $AddonDomain",$_SESSION['SessionSSHUsername'],$_SESSION['SessionSSHPassword']);
	echo Error($Error);
	
}
elseif ($AddonDomain!="")
{
	$Result = SQL("select AddonNo from Site where Domain=?", [$Domain]);
	foreach ($Result as $Row)
	{
		if ($Row['AddonNo']<=0)
		{
		echo "<div class='UnderTitle'>This feature has been disabled by your administrator. Max Addon AddonDomains for $Domain: 0</div>";
		exit;
		}
	
	}

	include "access.php";

	if ($Password=="") {$Password=time();}

	$Owner=$Domain;
	$Error=SSH ("/usr/bin/sudo /go/create --domain '$AddonDomain' --username '$AddonDomain' --password '$Password' --documentroot '$DocumentRoot' --fullname '$FullName' --mobile '$Mobile' --email '$Email' --packageid '$PackageID' --diskspace '$DiskSpace' --bandwidth '$Bandwidth' --inode '$Inode' --ftpno '$FTPNo' --emailno '$EmailNo' --databaseno '$DatabaseNo' --subdomainno '$SubDomainNo' --aliasno '$AliasNo' --addonno '$AddonNo' --shell '$Shell' --jailedshell '$JailedShell' --timezone '$Timezone' --permission '$Permission' --description '$Description' --phpversion '$PHPVersion' --smtphost '$SMTPHost' --smtpuser '$SMTPUser' --smtppass '$SMTPPass' --smtpport '$SMTPPort' --sslcertificate '$SSLCertificate' --sslredirect '$SSLRedirect' --bot '$Bot' --proxy '$Proxy' --ftpstatus '$FTPStatus' --ftpupload '$FTPUpload' --ftpdownload '$FTPDownload' --ftpdelete '$FTPDelete' --backup '$Backup' --backupstamp '$BackupStamp' --expireson '$ExpiresOn' --domainexpireson '$DomainExpiresOn' --mailttl '$MailTTL' --browsercachettl '$BrowserCacheTTL' --apiignore '$APIIgnore' --userid '$UserID' --owner '$Owner' --timestamp '$TimeStamp' --skeleton '$Skeleton'",$_SESSION['SessionSSHUsername'],$_SESSION['SessionSSHPassword']);
	echo Error("$Error");

}






	Echo "
	<form name=\"Form\" method=\"POST\" onsubmit=\"return Submit('$CurrentFileName');\" autocomplete=\"off\" action=\"$CurrentFileName\">
	<input type=hidden name=Edit value='$Edit'><input type=hidden name=ServiceControl value='$ServiceControl'>
	<input type=hidden name=AddonID value='$AddonID'>

	<div class='DivInput {$Dir}DivInput'>{$LNG['AddonDomain']}<br>
	<input type='text' name='AddonDomain' maxlength=255 class=InputText size=40>
	</div>

	<div class='DivInput {$Dir}DivInput'>{$LNG['PrimaryAddonDomain']}<br>
	<select name='Domain' class=Select>
	";
	
	$Result = SQL("select * from Site where Trash=0 and Owner='' $SearchSql order by Domain");
	foreach ($Result as $Row)
	{
	echo "
	<option value='{$Row['Domain']}'>{$Row['Domain']}</option>
	";
	}
	
	echo "
	</select>
	</div>
	
	<div class='DivInput {$Dir}DivInput'>{$LNG['Password']}<br>
	<input type='password' name='Password' id='Password' pattern='^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$' title='Must contain at least one number, one uppercase letter, one lowercase letter, and be at least 8 characters long' autocomplete='new-password' required maxlength=100 class=InputText> <span onclick=\"GeneratePassword('Password')\" style='color: var(--blue);cursor:pointer'>Generate Password</span>
	</div>
	
	<div id=DivSubmit class=DivSubmit>
	";

	if ($Edit==1)
	{
		echo "<input type=submit value='{$LNG['SaveChanges']}' Class=InputButton>";
	}
	else
	{
		echo "<input type=submit value='{$LNG['Create']}' Class=InputButton>";
	}

	Echo "
	</div>

</form>

	
";

	if($Edit!=1)
	{
		include "search.php";

		Echo "
	
		<div class=DivXTable>
		<table cellPadding='8' cellSpacing='0' width='100%' class='Table'>

		<THEAD>
		
		<tr>
		
		<th align='$DAlign' width='25%'>
		{$LNG['AddonDomain']}
		</th>
		
		<th align='$DAlign' width='25%'>
		{$LNG['Owner']}
		</TD>
		
		<th align='$DAlign' width='25%'>
		{$LNG['CreatedDate']}
		</th>

		<th width='23%'>
	
		</TD>

		</THEAD>
		";

		$Table="Site";$Field="Owner!=''";
		$DefaultOrderBy="Domain";
		$DefaultDirection="ASC";
		include "include/sql.php";

		$X=0;
		$Result = SQL($SQL);
		foreach ($Result as $Row)
		{
		$ID=$Row['SiteID'];
		
			if ($X==0)
			{
			echo "<TBODY>";
			}

			if ($X%2==0)
			{
			$TDColor="Td";
			}
			else
			{
			$TDColor="TdB";
			}
			
			$CreatedDate=date("D j M Y g:i a", $Row['TimeStamp']);

			echo "
			<tr id='TR{$ID}' class='$TDColor' divid=Find find='{$Row['Domain']}-{$Row['Owner']}'>

			<TD>{$Row['Domain']}</td>

			<TD>{$Row['Owner']}</td>
			
			<TD>$CreatedDate</td>
			
			<TD align='$OAlign'>
			
				<button onclick=\"DropdownButton('AddonDomain{$ID}', event)\" class='DropButton'>Action</button>	
			  
				<div id='AddonDomain{$ID}' class='DropdownButtonContent'>
				";
				
					echo "	
					<a href=\"javascript:Load('$CurrentFileName?Delete=1&Step=1&AddonDomain={$Row['Domain']}&Domain={$Row['Owner']}')\">Dlete</a>
					";

					if ($_SESSION['SessionType']=="Root")
					{
					echo "
					<a href=\"javascript:Load('$CurrentFileName?Primary=1&AddonDomain={$Row['Domain']}&Domain={$Row['Owner']}')\">{$LNG['MakePrimary']}</a>
					";
					}
				
				echo "
				</div>
			


			</TD>
			";
			
		$X++;
		}
		
		if ($X!=0)
		{
		echo "</TBODY>";
		}

		echo "
		<TFOOT>

		<tr>

		<th align='$DAlign' colspan=2>
		Showing $X of $RowsNo records.
		</th>
		
		<th align='$OAlign' colspan=3>
		";
				
		include "pages.php";

		echo "
		</th>

		</tr>

		</TFOOT>

		</TABLE>
		</div>
		";
		
	}
?>