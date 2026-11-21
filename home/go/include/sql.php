<?php

use function PHPSTORM_META\elementType;

if ($OrderBy=="") {$OrderBy=$DefaultOrderBy;}
if ($Direction=="")	{$Direction=$DefaultDirection;}
if ($Page=="") {$Page=1;}
$FilterSql=str_replace("\'","'",$FilterSql);
$FromRecord=($Page-1)*$PageNo;

if ($Table=="Script")
{
$SearchSql="";
}

if ($FilterSql!="") 
{
	if ($GroupBy=="")
	{
	$SQL="select * from $Table where $Field and $FilterSql $SearchSql order by $OrderBy $Direction LIMIT $FromRecord,$PageNo";
	}
	else
	{
	$SQL="select * from $Table where $Field and $FilterSql $SearchSql GROUP BY $GroupBy order by $OrderBy $Direction LIMIT $FromRecord,$PageNo";
	}
}
elseif ($SearchFor=="")
{
	if ($GroupBy=="")
	{
	$SQL="select * from $Table where $Field $SearchSql order by $OrderBy $Direction  LIMIT $FromRecord,$PageNo";
	}
	else
	{
	$SQL="select * from $Table where $Field $SearchSql GROUP BY $GroupBy order by $OrderBy $Direction  LIMIT $FromRecord,$PageNo";
	}
}
else
{
	include "include/search.php";
}

$RowsNo=RowCount($SQL,$Table);

?>