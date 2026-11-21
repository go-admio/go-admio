<?php
include "navigator.php";
include "no.php";
$Buttons="";
include "title.php";

$AudienceName=trim($_REQUEST['AudienceName']);
$SenderName=trim($_REQUEST['SenderName']);
$SenderEmail=trim($_REQUEST['SenderEmail']);
$RecipientID=intval($_REQUEST['RecipientID']);
$Subject=$_REQUEST['Subject'];
$Body=$_REQUEST['Body'];
$SenderID=intval($_REQUEST['SenderID']);
$OutboxID=intval($_REQUEST['OutboxID']);
$AudienceID=$_REQUEST['AudienceID'];
if (!is_array($AudienceID)) { $AudienceID=array($AudienceID); }
$AudienceID=array_filter(array_map('intval',$AudienceID));
$CampaignID=trim($_REQUEST['CampaignID']);
$Domain=ValidateDomain($_REQUEST['Domain']);

if ($Action=="Delete")
{
	include "access.php";
	
	SQL("DELETE FROM Outbox WHERE CampaignID=?",[$CampaignID]);

	echo Error("Campaign $CampaignID has been deleted.");

	$Action="";

}
elseif ($Action=="Edit" and $Subject!="")
{
	include "access.php";

	SQL("UPDATE OR IGNORE Outbox SET Subject=?,Body=? WHERE CampaignID=?",[$Subject,$Body,$CampaignID]);

	echo Error("Campaign $CampaignID has been edited.");

	$Action="";

}
elseif ($Subject!="")
{
	include "access.php";
	$CampaignID = 'CMP-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

	$Result = SQL("select * from Sender where SenderID=?",[$SenderID]);
	foreach ($Result as $Row)
	{
		$SenderName=$Row['SenderName'];
		$SenderEmail=$Row['SenderEmail'];
	}

	$CreatedAt=time();
	$ScheduledAt=time();
	$Status="Pending";

	$Attempts=0;

	foreach ($AudienceID as $Audience)
	{
		$Result = SQL("select * from Audience where AudienceID=?",[$Audience]);
		foreach ($Result as $Row)
		{
			$Domain=$Row['Domain'];
			$AudienceName=$Row['AudienceName'];

			if ($AudienceString=="")
			{
			$AudienceString=$AudienceName;
			}
			else
			{
			$AudienceString.=", $AudienceName";
			}
		}

		$Result = SQL("select * from Recipient where AudienceName=? and Domain=?",[$AudienceName,$Domain]);
		foreach ($Result as $Row)
		{
				$RecipientName=$Row['RecipientName'];
				$RecipientEmail=$Row['RecipientEmail'];

				SQL("INSERT OR IGNORE INTO Outbox (Domain,AudienceName,SenderName,SenderEmail,RecipientName,RecipientEmail,Subject,Body,Status,CampaignID,Attempts,ScheduledAt,CreatedAt) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)",[$Domain,$AudienceName,$SenderName,$SenderEmail,$RecipientName,$RecipientEmail,$Subject,$Body,$Status,$CampaignID,$Attempts,$ScheduledAt,$CreatedAt]);
		}
	}

	$TotalRecipients=SQL("SELECT COUNT(*) FROM Outbox WHERE CampaignID=?",[$CampaignID]);
	$AudienceName=$AudienceString;
	SQL("UPDATE Outbox SET TotalRecipients=?,AudienceName=? WHERE CampaignID=?",[$TotalRecipients,$AudienceName,$CampaignID]);

	echo Error("Campaign $CampaignID has been created.");

	$Action="";

}

if ($Action=="Edit")
{
		$Result = SQL("select * from Outbox where OutboxID=?",[$OutboxID]);
		foreach ($Result as $Row)
		{
			$Subject=$Row['Subject'];
			$Body=$Row['Body'];
			$CampaignID=$Row['CampaignID'];
		}

}
else
{
			$Subject="";
			$Body="Dear [Name]";
			$CampaignID="";
}

	$BodyEsc=htmlspecialchars($Body,ENT_QUOTES,'UTF-8');

	echo "
	

	<form name=\"Form\" method=\"POST\" onsubmit=\"return Submit('$CurrentFileName');\" autocomplete=\"off\" action=\"$CurrentFileName\">
	<input type=hidden name=Action value='$Action'>
	<input type=hidden name=CampaignID value='$CampaignID'>
	";

	if ($_SESSION['SessionType']=="Website")
	{
		echo "<input type=hidden name=Domain value='{$_SESSION['SessionDomain']}'>";
	}

	echo "
	<div class='DivInput {$Dir}DivInput'>{$LNG['Audience']}<br>
	";


	if ($Action!="Edit")
	{
		$CheckAudience="";

		$Result = SQL("select * from Audience where AudienceID>=1 $SearchSql ORDER BY AudienceName");
		foreach ($Result as $Row)
		{	
			$CheckAudience.="
			<div class=DivCheckbox>
			
				<label class=LabelCheckbox>{$Row['AudienceName']} ({$Row['Domain']})
					<input type='checkbox' name='AudienceID[]' value='{$Row['AudienceID']}'>
					<span class='Checkbox'></span>
				</label>
			
			</div>
			";

		}

		echo $CheckAudience;
	}

	echo "
	</div>

	<div class='DivInput {$Dir}DivInput'>Sender<br>
	";

	if ($Action=="Edit")
	{
		echo "<input type='text' value='{$SenderName} &lt;{$SenderEmail}&gt;' readonly maxlength=255 class=InputText>";
	}
	else
	{

		$SelectSender="<select name='SenderID' id='SenderID' required class=Select>";
		$SelectSender.="<option value=''>Select Sender</option>";

		$Result = SQL("select * from Sender where SenderID>=1 $SearchSql ORDER BY SenderName");
		foreach ($Result as $Row)
		{	
			if ($Row['SenderID']==$SenderID)
			{
			$SelectSender.="<option value='{$Row['SenderID']}' selected>{$Row['SenderName']} &lt;{$Row['SenderEmail']}&gt;</option>";
			}
			else
			{
			$SelectSender.="<option value='{$Row['SenderID']}'>{$Row['SenderName']} &lt;{$Row['SenderEmail']}&gt;</option>";
			}
		}
		$SelectSender.="</select>";

		echo $SelectSender;
	}

	echo "
	</div>

	<div class='DivInput {$Dir}DivInput'>Subject<br>	
	<input type='text' name='Subject' value='$Subject' required maxlength=255 class=InputText>
	</div>

	<div class=DivTextarea>

	<textarea name=\"Body\" id=\"RichText\" style=\"width:100%;\" rows=\"15\"
          spellcheck=\"true\" autocapitalize=\"off\" autocorrect=\"off\"
          placeholder=\"Message\">$BodyEsc</textarea>
	</div>

	<div id=DivSubmit class=DivSubmit>
	";
	
	if ($Action=="Edit")
	{
		Echo "<input type=submit value='{$LNG['SaveChanges']}' Class=InputButton>";
	}
	else
	{
		Echo "<input type=submit value='Add Campaign' Class=InputButton>";
	}

	Echo "
	</div>

</form>

	";
	

	include "search.php";
	
	echo "
	<div class=DivXTable>
	<table cellPadding='8' cellSpacing='0' width='100%' class='Table'>

	<THEAD>
	
	<tr>
		
	<th align='$DAlign' width='15%'>
	{$LNG['Domain']}
	</th>

	<th align='$DAlign' width='15%'>
	Subject
	</th>

	<th align='$DAlign' width='15%'>
	Audience Name
	</th>

	<th align='$DAlign' width='15%'>
	Sent
	</th>

	<th align='$OAlign' width='25%'>

	</th>

	</tr>
	
	</THEAD>
	
	";



	$Table="Outbox";
	$Field="OutboxID>=1";
	$GroupBy="CampaignID";
	$DefaultOrderBy="OutboxID";
	$DefaultDirection="DESC";
	include "include/sql.php";

	$X=0;
	$Result = SQL($SQL);
	foreach ($Result as $Row)
	{
		$Domain=$Row['Domain'];
		$OutboxID=$Row['OutboxID'];
		$CampaignID=$Row['CampaignID'];
		$Subject=$Row['Subject'];
		$AudienceName=$Row['AudienceName'];
		$SenderName=$Row['SenderName'];
		$SenderEmail=$Row['SenderEmail'];
		$TotalRecipients=$Row['TotalRecipients'];

		$RemainRecipients=SQL("SELECT COUNT(*) FROM Outbox WHERE CampaignID=?",[$CampaignID]);

		$N=$TotalRecipients-$RemainRecipients;

		if ($X%2==0)
		{
		$TDColor="Td";
		}
		else
		{
		$TDColor="TdB";
		}

		Echo "
		<tr id='TR{$ID}' class='$TDColor' divid=Find find='$Domain-$RecipientName-$RecipientEmail'>

		<TD><a href='http://$Domain'>$Domain</a></TD>
		<TD>$Subject</TD>
		<TD>$AudienceName</TD>
		<TD>$N/$TotalRecipients</TD>
		
		<TD align='$OAlign'>
		
		<a href=\"plugin/sendmail.php?CampaignID=$CampaignID\" target='_blank' class=Action>Start</a>
		<a href=\"javascript:Load('$CurrentFileName?Action=Edit&OutboxID=$OutboxID&CampaignID=$CampaignID&Domain=$Domain&AudienceName=$AudienceName&SenderName=$SenderName&SenderEmail=$SenderEmail')\" class=Action>Edit</a>
		<a href=\"javascript:Load('$CurrentFileName?Action=Delete&OutboxID=$OutboxID&CampaignID=$CampaignID&Domain=$Domain')\" class=Action>Delete</a>

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

	<th align='$DAlign' colspan=3>
	Showing $X of $RowsNo records.
	</th>
	
	<th align='$OAlign' colspan=4>
	";
			
	include "pages.php";

	echo "
	</th>

    </tr>

	</TFOOT>

    </TABLE>
	</form>
	";
?>