<?php
include "navigator.php";
include "no.php";
$Buttons="";
include "title.php";

$RecipientID=intval($_REQUEST['RecipientID']);
$Recipients=$_REQUEST['Recipients'];
$RecipientName=trim($_REQUEST['RecipientName']);
$RecipientEmail=trim($_REQUEST['RecipientEmail']);
$AudienceName=trim($_REQUEST['AudienceName']);
$Domain=ValidateDomain($_REQUEST['Domain']);

if ($Action=="Delete")
{
	include "access.php";
	
	SQL("DELETE FROM Recipient WHERE RecipientEmail=? and Domain=?", [$RecipientEmail,$Domain]);

	echo Error("Recipient Email $RecipientEmail has been deleted.");

	$Action="";

}
elseif ($Recipients!="")
{
	include "access.php";

	$Raw=$Recipients;
	$Raw=str_replace(["\r\n","\r"],"\n",$Raw);

	foreach (explode("\n", $Raw) as $Line)
	{
		$RecipientArray=explode(",",$Line);
		$RecipientName=trim($RecipientArray[0]);
		$RecipientEmail=trim($RecipientArray[1]);
	
		if ($RecipientName!="" and $RecipientEmail!="")
		{
			if (filter_var($RecipientEmail, FILTER_VALIDATE_EMAIL)) 
			{
			$I++;
			$TimeStamp=time();
			SQL("INSERT OR IGNORE INTO Recipient (Domain,RecipientName,RecipientEmail,AudienceName,TimeStamp) VALUES (?,?,?,?,?)",[$Domain,$RecipientName,$RecipientEmail,$AudienceName,$TimeStamp]);
			}
		}
	}

	echo Error("$I Emails has been inserted.");

	$Action="";

}

if ($Action=="Edit")
{
		$Result = SQL("select * from Recipient where RecipientID=?",[$RecipientID]);
		foreach ($Result as $Row)
		{
			$Domain=$Row['Domain'];
			$RecipientName=$Row['RecipientName'];
			$RecipientEmail=$Row['RecipientEmail'];
			$AudienceName=$Row['AudienceName'];
		}

}
else
{
	$RecipientID="";
	$RecipientName="";
	$RecipientEmail="";
	$AudienceName="";
}

	echo "

	<form name=\"Form\" method=\"POST\" onsubmit=\"return Submit('$CurrentFileName');\" autocomplete=\"off\" action=\"$CurrentFileName\">
	<input type=hidden name=Action value='$Action'>
	<input type=hidden name=RecipientID value='$RecipientID'>
	
	<div class='DivInput {$Dir}DivInput'>{$LNG['Domain']}<br>
	";

	$Result = SQL("select * from Site where Trash=0 $SearchSql");
	foreach ($Result as $Row)
	{
	$Account['Domain'][]=$Row['Domain'];
	$Account['Username'][]=$Row['Username'];
	}
	
	array_multisort($Account['Domain'], SORT_ASC, SORT_STRING,$Account['Username'], SORT_NUMERIC, SORT_DESC);

	echo "<select name='Domain' id='Username' class=Select>";
	
	for ($E=0;$E<count($Account['Domain']);$E++)
	{
		if ($Account['Domain'][$E]==$_REQUEST['Domain'])
		{
		echo "<option value='{$Account['Domain'][$E]}' selected>{$Account['Domain'][$E]}</option>";
		}
		else
		{
		echo "<option value='{$Account['Domain'][$E]}'>{$Account['Domain'][$E]}</option>";
		}
	}
	echo "</select>";

	echo "
	</div>

	<div class='DivInput {$Dir}DivInput'>{$LNG['Audience']}<br>
	";

	$SelectAudience="<select name='AudienceName' id='AudienceName' required class=Select>";
	$SelectAudience.="<option value=''>Select Audience</option>";

	$Result = SQL("select * from Audience where AudienceID>=1 $SearchSql order by AudienceName");
	foreach ($Result as $Row)
	{	
		if ($Row['AudienceName']==$AudienceName)
		{
		$SelectAudience.="<option value='{$Row['AudienceNAme']}' selected>{$Row['AudienceName']}</option>";
		}
		else
		{
		$SelectAudience.="<option value='{$Row['AudienceName']}'>{$Row['AudienceName']}</option>";
		}
	}
	$SelectAudience.="</select>";

	echo $SelectAudience;

	echo "
	</div>

	<div class=DivTextarea>

	<label for=\"Recipients\">Recipients</label>
	<textarea name=\"Recipients\" id=\"Recipients\" style=\"width:100%;\" rows=\"8\"
          spellcheck=\"false\" autocapitalize=\"off\" autocorrect=\"off\"
          placeholder=\"Name,Email&#10;Ahmed Ali,ahmed@example.com&#10;Sara Mohamed,sara@example.com\"></textarea>
	<small>One recipient per line — format: Name,Email</small>

	</div>

	<div id=DivSubmit class=DivSubmit>
	";
	
	if ($Action=="Edit")
	{
		Echo "<input type=submit value='{$LNG['SaveChanges']}' Class=InputButton>";
	}
	else
	{
		Echo "<input type=submit value='Add Recipient' Class=InputButton>";
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
	Audience Name
	</th>

	<th align='$DAlign' width='15%'>
	Recipient Name
	</th>


	<th align='$DAlign' width='15%'>
	Recipient Email
	</th>

	<th align='$DAlign' width='15%'>
	{$LNG['CreatedDate']}
	</th>

	<th align='$OAlign' width='25%'>

	</th>

	</tr>
	
	</THEAD>
	
	";



	$Table="Recipient";$Field="RecipientID>=1";
	$DefaultOrderBy="RecipientName";
	$DefaultDirection="ASC";
	include "include/sql.php";

	$X=0;
	$Result = SQL($SQL);
	foreach ($Result as $Row)
	{
		$Domain=$Row['Domain'];
		$RecipientID=$Row['RecipientID'];
		$RecipientName=$Row['RecipientName'];
		$RecipientEmail=$Row['RecipientEmail'];
		$AudienceName=$Row['AudienceName'];

		if ($X%2==0)
		{
		$TDColor="Td";
		}
		else
		{
		$TDColor="TdB";
		}

		$CreatedDate=date("D j M Y g:i a", $Row['TimeStamp']);

		Echo "
		<tr id='TR{$ID}' class='$TDColor' divid=Find find='$Domain-$RecipientName-$RecipientEmail'>

		<TD><a href='http://$Domain'>$Domain</a></TD>

		<TD>$AudienceName</TD>

		<TD>$RecipientName</TD>
		<TD>$RecipientEmail</TD>
		
		<TD>$CreatedDate</TD>

		<TD align='$OAlign'>
		
		<a href=\"javascript:Load('$CurrentFileName?Action=Delete&RecipientID=$RecipientID&RecipientEmail=$RecipientEmail&Domain=$Domain')\" class=Action>Delete</a>

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