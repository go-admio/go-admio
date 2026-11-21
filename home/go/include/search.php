<?php

$SearchFor=trim($SearchFor);

$FieldNameString=Column($Table,"2",$SearchFor,"like");

if ($SearchSql=="")
{
	if ($GroupBy=="")
	{
	$SQL="select * from $Table where $FieldNameString order by $OrderBy $Direction LIMIT $FromRecord,$PageNo";
	}
	else
	{
	$SQL="select * from $Table where $FieldNameString GROUP BY $GroupBy order by $OrderBy $Direction LIMIT $FromRecord,$PageNo";
	}
}
else
{
	if ($GroupBy=="")
	{
	$SQL="select * from $Table where ($FieldNameString) $SearchSql order by $OrderBy $Direction LIMIT $FromRecord,$PageNo";
	}
	else
	{
	$SQL="select * from $Table where ($FieldNameString) $SearchSql GROUP BY $GroupBy order by $OrderBy $Direction LIMIT $FromRecord,$PageNo";
	}

}

?>