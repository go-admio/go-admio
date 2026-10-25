<?php
if (substr(getcwd(),0,18) == "/usr/local/goadmio")
{
$DBHost="localhost";
$DBName="go";
$DBUser="root";
$DBPass="T5Bksrurp6jRrgve";
$SSHHost="localhost";
$SSHUsername="go4adac51b42b530";

$SSHPort="22222";
}
else
{
echo "Invalid GoAdmio Path (Run: /go/config --force)";
exit;
}
?>
