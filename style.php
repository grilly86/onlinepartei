<?php
	session_start();
	require 'static/smarty/Smarty.class.php';
	include_once 'lib/config.php';

	$workspace = new Workspace();	
	$workspace->process();

	class Workspace
	{
		function process()
		{
			$config = new Config();
			$config->connect();
			$smarty = new Smarty();
			header("Content-Type: text/css");
			$userID=0;
			//$smarty->assign("styleColor", " rgb(" . rand(0,255) ."," . rand(0,255) .",". rand(0,255). ")");
			if (isset($_SESSION["user"]))
			{
				$user = $_SESSION["user"];
				$userID = (int)$user["id"];
				$sql = "SELECT color FROM user WHERE id=" . $userID;
				$rs = mysql_query($sql) or die(mysql_error());
				$row =  mysql_fetch_array($rs);
				$color = $row["color"];
				
			}
			else
			{
				//zufällige farbe generieren (nicht zu hell!)
				$r = rand(0,150);
				if ($r > 100)
				{
					$g = rand(0,50);
				}
				else
				{
					$g = rand(0,150);
				}
				if (($r+$g) > 100)
				{
					$b = rand(0,50);
				}
				else
				{
					$b = rand(0,150);
				}
				
				$r = str_pad(dechex($r), 2, "0", STR_PAD_LEFT);
				$g = str_pad(dechex($g), 2, "0", STR_PAD_LEFT);
				$b = str_pad(dechex($b), 2, "0", STR_PAD_LEFT);
				
				$color = "#" . $r . $g . $b;
			}
			$smarty->assign("userID", $userID);
			$smarty->assign("styleColor", $color);
			$smarty->display("style.css");
		}
	}
?>
