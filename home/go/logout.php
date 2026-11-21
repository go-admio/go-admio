<?php
session_start();

session_unset();
session_destroy();
unset($_SESSION['SessionUsername'],$_SESSION['SessionPassword'],$_SESSION['SessionType'],$_SESSION['SessionSwitchUsername']);

?>