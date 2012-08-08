<?php
if (isset($_GET["action"]))
{
	$action = $_GET["action"];

	include "lib/lang/lang.php";
	$lang = new Language("de");
	include "lib/util.php";
	$util = new Util($lang);	
	include "lib/config.php";
	$config = new Config();
	$config->connect();
	
	switch($action)
	{
		case "renderHtml":
			// prerender posts
			$sql = "SELECT id,message FROM post";
			$rs = mysql_query($sql);
			$postCount = 0;
			while($row = mysql_fetch_assoc($rs))
			{
				$messageHtml = $config->convertToDatabase(urlencode($util->makeLinks(urldecode($config->convertFromDatabase($row["message"])))));
				$sql="UPDATE post SET messageHtml='".$messageHtml."' WHERE id=" . $row["id"];
				mysql_query($sql);
				$postCount++;
			}
			
			// prerender polls
			$sql = "SELECT id,text FROM poll";
			$rs = mysql_query($sql);
			$pollCount = 0;
			while($row = mysql_fetch_assoc($rs))
			{
				$messageHtml = $config->convertToDatabase(urlencode($util->makeLinks(urldecode($config->convertFromDatabase($row["text"])))));
				$sql="UPDATE poll SET messageHtml='".$messageHtml."' WHERE id=" . $row["id"];
				mysql_query($sql);
				$pollCount++;
			}
			
			// prerender chat messages
			$sql = "SELECT id,message FROM message";
			$rs = mysql_query($sql);
			$messageCount = 0;
			while($row = mysql_fetch_assoc($rs))
			{
				$messageHtml = $config->convertToDatabase(urlencode($util->makeLinks(urldecode($config->convertFromDatabase($row["message"])))));
				$sql = "UPDATE message SET messageHtml='" . $messageHtml . "' WHERE id=" . $row["id"];
				mysql_query($sql);
				$messageCount++;
			}
			echo "SUCCESS: updated " . $postCount . " posts, ".$pollCount." polls and " . $messageCount . " messages.";
			break;
	}
}
else
{
	echo "ERROR: no action definded";
}