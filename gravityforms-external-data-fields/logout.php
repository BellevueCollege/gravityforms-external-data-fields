
<?php

require_once("requireAuthentication.php");
$auth_ob = new requireAuthentication(array());
$auth_ob->logout();

?>