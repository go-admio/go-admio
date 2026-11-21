<?php
include "navigator.php";
include "no.php";
$Buttons="";
include "title.php";

$SenderID=intval($_REQUEST['SenderID']);
$SenderName=trim($_REQUEST['SenderName']);
$SenderEmail=trim($_REQUEST['SenderEmail']);
$Domain=ValidateDomain($_REQUEST['Domain']);

if ($Action=="Delete")
{
	include "access.php";
	
	SQL("DELETE FROM Sender WHERE SenderEmail=? and Domain=?", [$SenderEmail,$Domain]);

	echo Error("Sender Email $SenderEmail has been deleted.");

	$Action="";

}
elseif ($Action=="Edit" and $SenderEmail!="")
{
	include "access.php";
	if (filter_var($SenderEmail, FILTER_VALIDATE_EMAIL)) 
	{
		SQL("UPDATE OR IGNORE Sender SET Domain=?,SenderName=?,SenderEmail=? WHERE SenderID=?",[$Domain,$SenderName,$SenderEmail,$SenderID]);
		echo Error("Sender Email $SenderEmail has been edited.");
	
	}

	$Action="";

}
elseif ($SenderEmail!="")
{
	include "access.php";
	
	if (filter_var($SenderEmail, FILTER_VALIDATE_EMAIL)) 
	{
	$TimeStamp=time();
	SQL("INSERT OR IGNORE INTO Sender (Domain,SenderName,SenderEmail,TimeStamp) VALUES (?,?,?,?)",[$Domain,$SenderName,$SenderEmail,$TimeStamp]);
	echo Error("Sender Email $SenderEmail has been added.");
	}

	$Action="";

}

if ($Action=="Edit")
{
		$Result = SQL("select * from Sender where SenderID=?",[$SenderID]);
		foreach ($Result as $Row)
		{
			$Domain=$Row['Domain'];
			$SenderName=$Row['SenderName'];
			$SenderEmail=$Row['SenderEmail'];
		}

}
else
{
	$SenderID="";
	$SenderName="";
	$SenderEmail="";
}

	echo "

	<form name=\"Form\" method=\"POST\" onsubmit=\"return Submit('$CurrentFileName');\" autocomplete=\"off\" action=\"$CurrentFileName\">
	<input type=hidden name=Action value='$Action'>
	<input type=hidden name=SenderID value='$SenderID'>
	
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

	<div class='DivInput {$Dir}DivInput'>Sender Name<br>	
	<input type='text' name='SenderName' value='$SenderName' required maxlength=100 class=InputText>
	</div>
	
	<div class='DivInput {$Dir}DivInput'>Sender Email<br>	
	<input type='email' name='SenderEmail' value='$SenderEmail' required maxlength=255 class=InputText>
	</div>
	
	<div id=DivSubmit class=DivSubmit>
	";
	
	if ($Action=="Edit")
	{
		Echo "<input type=submit value='{$LNG['SaveChanges']}' Class=InputButton>";
	}
	else
	{
		Echo "<input type=submit value='Add Sender' Class=InputButton>";
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
		
	<th align='$DAlign' width='20%'>
	{$LNG['Domain']}
	</th>
	
	<th align='$DAlign' width='20%'>
	Sender Name
	</th>


	<th align='$DAlign' width='20%'>
	Sender Email
	</th>

	<th align='$DAlign' width='20%'>
	{$LNG['CreatedDate']}
	</th>

	<th align='$OAlign' width='20%'>

	</th>

	</tr>
	
	</THEAD>
	
	";



	$Table="Sender";$Field="SenderID>=1";
	$DefaultOrderBy="SenderName";
	$DefaultDirection="ASC";
	include "include/sql.php";

	$X=0;
	$Result = SQL($SQL);
	foreach ($Result as $Row)
	{
		$Domain=$Row['Domain'];
		$SenderID=$Row['SenderID'];
		$SenderName=$Row['SenderName'];
		$SenderEmail=$Row['SenderEmail'];

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
		<tr id='TR{$ID}' class='$TDColor' divid=Find find='$Domain-$SenderName-$SenderEmail'>

		<TD><a href='http://$Domain'>$Domain</a></TD>

		<TD>$SenderName</TD>
		<TD>$SenderEmail</TD>
		
		<TD>$CreatedDate</TD>

		<TD align='$OAlign'>
		
		<a href=\"javascript:Load('$CurrentFileName?Action=Edit&SenderID=$SenderID&Domain=$Domain')\" class=Action>Edit</a>
		<a href=\"javascript:Load('$CurrentFileName?Action=Delete&SenderID=$SenderID&SenderEmail=$SenderEmail&Domain=$Domain')\" class=Action>Delete</a>

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