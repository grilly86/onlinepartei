<?php
		session_start();
		require "static/jsonwrapper/jsonwrapper.php";
		$task="";
		if (isset($_REQUEST["task"]))
		{
			$task = $_REQUEST["task"];
		}
		require 'static/smarty/Smarty.class.php';
		$smarty = new Smarty();
		include "lib/config.php";
		$config = new Config();
		$config->connect();

		$user = array();
		if (isset($_SESSION["user"]))
		{
			$user = $_SESSION["user"];
		}
		if ($user)
		{
			switch($task)
			{
				case "openChat":
					include_once "lib/lang/lang.php";
					$l = "de";
					if (isset($user["language"]) && $user["language"]=="en")
					{
						$l = "en";
					}
					$lang = new Language($l);
					$smarty->assign("lang", $lang->languageArray);
					if (isset($_REQUEST["user"]))
					{
						$config->connect();
						$sql = "SELECT id,name FROM user WHERE id=". (int)$_REQUEST["user"];
						$rs = mysql_query($sql) or die (mysql_error());
	
						$user = mysql_fetch_array($rs);
						$smarty->assign("user",$user); 
					}
					
					$smarty->display("chat.html");
					break;
				case "message":
						if (isset($_REQUEST["message"]) && isset($_REQUEST["receiver"]))
						{
							$message = $config->convertToDatabase($_REQUEST["message"]);
							if ($message)
							{
								$receiver = (int)$_REQUEST["receiver"];
								$sql = "INSERT INTO message (senderID,receiverID,message,timestamp) VALUES(".(int)$user["id"].",".$receiver.",'" . $message . "','".date("Y-m-d H:i:s")."')";
								$rs = mysql_query($sql) or die(mysql_error());
							}
						}
					//break;			// NO BREAK HERE -> RETURN CONVERSATION !
				default:
					if(isset($_REQUEST["receiver"]))
					{
						include_once "lib/lang/lang.php";
						$l = "de";
						if (isset($user["language"]) && $user["language"]=="en")
						{
							$l = "en";
						}
						$lang = new Language($l);
						$receiver = (int)$_REQUEST["receiver"];
						$sql = "SELECT message.id, message.message,message.timestamp,receiver.name as receivername, sender.name as username, message.senderID as senderid,message.receiverID as receiverid FROM message, user as sender, user as receiver WHERE senderID=sender.id AND receiverID=receiver.id AND (senderID=" . $user["id"] . " OR receiverID=" . $user["id"] . ") AND (" . 
													 " senderID=" . $receiver . " OR receiverID=" . $receiver . ") AND message.timestamp>'".date('Y-m-d', time() - 2592000)." 00:00:00' ORDER BY timestamp"; 
						$rs = mysql_query($sql) or die(mysql_error());
						$obj=array();
						//echo makelink("Dieser Link sollte geparst werden: http://www.onlinepartei.eu/ - wieso geht das?nicht?");
						include_once "lib/util.php";
						$util = new Util($lang);
						while($row=mysql_fetch_assoc($rs))
						{
							$row["readableDate"]=$util->makeDateReadable($row["timestamp"], true,false);
							$row["message"]=$util->makeLinks($config->convertFromDatabase($row["message"]));
							$obj[]=$row;
						}
						$sql = "UPDATE message SET `read`=1 WHERE `read`=0 AND receiverID=" . $user["id"];
						$rs = mysql_query($sql) or die(mysql_error());
						$smarty->assign("user",$user);
						$smarty->assign("chat", $obj);
						die(str_replace("<br />", "<br>", $smarty->fetch("chat/message.html")));
					}
					else
					{
						include_once "lib/lang/lang.php";
						$l = "de";
						if (isset($user["language"]) && $user["language"]=="en")
						{
							$l = "en";
						}
						$lang = new Language($l);
						include_once "lib/util.php";
						$util = new Util($lang);
						$smarty->assign("lang", $lang->languageArray);
						// set user online
						$sql = "UPDATE user SET online='".date("Y-m-d H:i:s")."' WHERE id=" . (int)$user["id"];
						$rs = mysql_query($sql) or die(mysql_error());
						//current user online list

						$sql =	"SELECT user.id as id,user.name as name,user.online, SUM(message.`read`=0) as new, user.hasImage " . 
									"FROM user LEFT JOIN message ON message.senderID=user.id AND message.receiverID=".$user["id"]." " . 
									"WHERE user.id!=".$user["id"]." AND user.active=1 GROUP BY user.id " .
									"ORDER BY online DESC, user.name ASC";
						//echo $sql . "<br>";
						$rs=mysql_query($sql); //or die mysql_error(s);
						while ($row=mysql_fetch_assoc($rs)) 
						{							
							$row["readableOnline"] = $util->makeDateReadable($row["online"],true);
							$row["online"]=strtotime($row["online"]);
							$userList[]=$row;
						}
						//print_r ($userList);	
						$smarty->assign("userList", $userList);
						$userList = $smarty->fetch("chat/userlist.html");
						
						//query
						$sql = "SELECT COUNT(message.message) as messageCount, sender.id as senderid,receiver.name as receivername, sender.name as username FROM message,user as sender, user as receiver WHERE senderID=sender.id AND receiverID=receiver.id AND (receiverID=" . $user["id"] . ") AND message.read=0 GROUP BY sender.id ORDER BY timestamp"; 
						$rs = mysql_query($sql) or die(mysql_error());
						$obj = array();
						while($row = mysql_fetch_assoc($rs))
						{
							$obj[] = array("senderid"=>$row["senderid"],"messageCount"=>$row["messageCount"]);
						}
						
						$obj = array("obj"=>$obj, "userList"=>$userList);
						if ($obj)
						{
							die(json_encode($obj));
						}
					}
					break;
			}
}
?>
