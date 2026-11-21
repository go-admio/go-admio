<?php
include "navigator.php";
include "no.php";
$Buttons="";
include "title.php";

$AudienceID=intval($_REQUEST['AudienceID']);
$AudienceName=trim($_REQUEST['AudienceName']);
$Domain=ValidateDomain($_REQUEST['Domain']);

if ($Action=="Delete")
{
	include "access.php";
	
	SQL("DELETE FROM Audience WHERE AudienceName=? and Domain=?", [$AudienceName,$Domain]);
	SQL("DELETE FROM Recipient WHERE AudienceName=? and Domain=?", [$AudienceName,$Domain]);

	echo Error("Audience $AudienceName has been deleted.");

	$Action="";

}
elseif ($Action=="Empty")
{
	include "access.php";
	
	SQL("DELETE FROM Recipient WHERE AudienceName=? and Domain=?", [$AudienceName,$Domain]);

	echo Error("All Audiences has been deleted.");

	$Action="";

}
elseif ($Action=="Edit" and $AudienceName!="")
{
	include "access.php";

	SQL("UPDATE OR IGNORE Audience SET Domain=?,AudienceName=? WHERE AudienceID=?",[$Domain,$AudienceName,$AudienceID]);
	echo Error("Audience $AudienceName has been edited.");

	$Action="";

}
elseif ($AudienceName!="")
{
	include "access.php";
	
	$TimeStamp=time();
	SQL("INSERT OR IGNORE INTO Audience (Domain,AudienceName,TimeStamp) VALUES (?,?,?)",[$Domain,$AudienceName,$TimeStamp]);
	echo Error("Audience $AudienceName has been added.");
	
	$Action="";

}

if ($Action=="Edit")
{
		$Result = SQL("select * from Audience where AudienceID=?",[$AudienceID]);
		foreach ($Result as $Row)
		{
			$Domain=$Row['Domain'];
			$AudienceName=$Row['AudienceName'];
		}

}
else
{
	$AudienceID="";
	$AudienceName="";
}

	echo "

	<form name=\"Form\" method=\"POST\" onsubmit=\"return Submit('$CurrentFileName');\" autocomplete=\"off\" action=\"$CurrentFileName\">
	<input type=hidden name=Action value='$Action'>
	<input type=hidden name=AudienceID value='$AudienceID'>
	
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

	<div class='DivInput {$Dir}DivInput'>Audience Name<br>	
	<input type='text' name='AudienceName' value='$AudienceName' required maxlength=100 class=InputText>
	</div>
	
	<div id=DivSubmit class=DivSubmit>
	";
	
	if ($Action=="Edit")
	{
		Echo "<input type=submit value='{$LNG['SaveChanges']}' Class=InputButton>";
	}
	else
	{
		Echo "<input type=submit value='Add Audience' Class=InputButton>";
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
		
	<th align='$DAlign' width='30%'>
	{$LNG['Domain']}
	</th>
	
	<th align='$DAlign' width='30%'>
	Audience Name
	</th>

	<th align='$OAlign' width='40%'>

	</th>

	</tr>
	
	</THEAD>
	
	";



	$Table="Audience";$Field="AudienceID>=1";
	$DefaultOrderBy="AudienceName";
	$DefaultDirection="ASC";
	include "include/sql.php";

	$X=0;
	$Result = SQL($SQL);
	foreach ($Result as $Row)
	{
		$Domain=$Row['Domain'];
		$AudienceID=$Row['AudienceID'];
		$AudienceName=$Row['AudienceName'];

		$RecipientNo=SQL("SELECT COUNT(*) FROM Recipient WHERE Domain=? and AudienceName=?",[$Domain,$AudienceName]);

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
		<tr id='TR{$ID}' class='$TDColor' divid=Find find='$Domain-$AudienceName'>

		<TD><a href='http://$Domain'>$Domain</a></TD>

		<TD>$AudienceName ($RecipientNo)</TD>
		
		<TD align='$OAlign'>
		
		<a href=\"javascript:Load('$CurrentFileName?Action=Empty&AudienceID=$AudienceID&AudienceName=$AudienceName&Domain=$Domain')\" class=Action>Delete All Recipients</a>
		<a href=\"javascript:Load('$CurrentFileName?Action=Delete&AudienceID=$AudienceID&AudienceName=$AudienceName&Domain=$Domain')\" class=Action>Delete</a>

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