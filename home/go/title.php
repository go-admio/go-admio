<?php

$Help=trim($Control);
$Help=str_ireplace("(s)","",$Help);
$Help=str_replace("(","",$Help);
$Help=str_replace(")","",$Help);
$Help=str_replace("&","",$Help);
$Help=str_replace("/"," ",$Help);
$Help=str_ireplace(" To "," ",$Help);
$Help=str_ireplace(" And "," ",$Help);
$Help=str_ireplace(" For "," ",$Help);
$Help=preg_replace("/\s+/"," ",$Help);
$Help=str_replace(" ","-",$Help);
$HelpURL="https://www.goadmio.com/docs/$Help";

echo "
<div class='Title {$Dir}Title' style='position:relative; background-color: var(--background-quaternary); background-image:url(theme/{$_SESSION['SessionTheme']}/svg/" . htmlspecialchars($ControlImage, ENT_QUOTES, 'UTF-8') . "); background-repeat:no-repeat; background-position:{$DAlign} 16px center; background-size:32px;'>$Control
";

    echo "<a href='$HelpURL' target='_blank' rel='noopener noreferrer' title='Help' aria-label='Help' style='position:absolute; right:16px; top:50%; transform:translateY(-50%); display:inline-flex; align-items:center; justify-content:center; width:24px; height:24px; border-radius:50%; border:1px solid currentColor; text-decoration:none; font-weight:700; color:inherit; opacity:.7; user-select:none;'>?</a>";

echo "
</div>
";

if (trim($Buttons)!="")
{
echo "
<div class='DivButton'>
$Buttons
</div>
";
}

if (trim($MainMenu)!="")
{
echo "
<div style='display: flex;justify-content: flex-end;align-items: center;border-bottom: 1px solid var(--border-tertiary);height:60px;padding-left: 20px;padding-right: 20px;'>
$MainMenu
</div>
";
}


?>