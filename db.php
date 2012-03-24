<?php

include "lib/config.php";

$config = new Config();
$config->connect();

//$sql = "ALTER TABLE  `message` CHANGE  `timestamp`  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
//$sql = "ALTER TABLE  `message` CHANGE  `timestamp`  `timestamp` DATETIME NOT NULL";
//$rs = mysql_query($sql) or die(mysql_error());

$sql = "SELECT * FROM user WHERE id=9";
$rs = mysql_query($sql) or die(mysql_error());
while ($row = mysql_fetch_assoc($rs))
{
    print_r($row);
}
//while ($row = mysql_fetch_assoc($rs))
///{
  //  print_r($row);
//}


?>
