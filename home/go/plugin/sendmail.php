<?php
header('Content-Type: text/html; charset=UTF-8');

include("/usr/local/goadmio/home/go/include/function/function.php");
include("/usr/local/goadmio/home/go/include/function/sendmail.php");
include("/usr/local/goadmio/include/config/config.php");

$CampaignID = $_REQUEST['CampaignID'] ?? '';

if ($CampaignID == "")
{
	echo "Invalid CampaignID";
	exit;
}

$Domain = '';

$Result = SQL("select * from Outbox where CampaignID=?", [$CampaignID]);
foreach ($Result as $Row)
{
	$Domain = $Row['Domain'];
}

if ($Domain == "")
{
	echo "Campaign $CampaignID not exits";
	exit;
}

$Result = SQL("select * from Site where Domain=?", [$Domain]);
foreach ($Result as $Row)
{
	$SMTPHost = $Row['SMTPHost'];
	$SMTPUser = $Row['SMTPUser'];
	$SMTPPass = $Row['SMTPPass'];
	$SMTPPort = $Row['SMTPPort'];
}

if ($SMTPHost == "")
{
	echo "There is no Custom SMTP Relay for Domain $Domain.";
	exit;
}

$Result = SQL("select * from Outbox where CampaignID=? LIMIT 10", [$CampaignID]);
foreach ($Result as $Row)
{
	$OutboxID      = $Row['OutboxID'];
	$SenderName    = $Row['SenderName'];
	$SenderEmail   = $Row['SenderEmail'];
	$RecipientName = $Row['RecipientName'];

	$To = $Row['RecipientEmail'];

	$Subject = $Row['Subject'];
	$Subject = str_ireplace("[Name]", $RecipientName, $Subject);

	$Body = $Row['Body'];
	$Body = str_ireplace("[Name]", $RecipientName, $Body);

	$TotalRecipients = $Row['TotalRecipients'];

	try
	{
		// Separate variable so we don't overwrite the $Result we are iterating.
		$MailResult = SendMail($To, $Subject, $Body, $SenderName, $RecipientName, $SenderEmail);

		if (!stristr($MailResult, "successfully"))
		{
			echo "$MailResult<br>";
		}

		SQL("DELETE FROM Outbox where OutboxID=?", [$OutboxID]);
	}
	catch (Throwable $e)
	{
		echo "Outbox $OutboxID ($To) exception: " . $e->getMessage() . "<br>\n";
		
		$RefreshURL='sendmail.php?CampaignID=' . urlencode($CampaignID) . '&T=' . time();
		echo "<meta http-equiv='refresh' content='1;URL=$RefreshURL'>";

	}
}

$RemainRecipients = SQL("SELECT COUNT(*) FROM Outbox WHERE CampaignID=?", [$CampaignID]);
$N = $TotalRecipients - $RemainRecipients;

include "include/sendmail.php";

?>