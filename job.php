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
		case "updateSort":
			
			$l=0;$d=0;
			$sql = "SELECT post.id as id, COUNT(rating.`timestamp`) as count FROM `post` LEFT JOIN rating ON rating.postID=post.id WHERE rating.`type`='' AND post.deleted = 0 AND rating.rating = 'like' GROUP BY post.id";
			$rs = mysql_query($sql) or die(mysql_error());
			while ($row = mysql_fetch_assoc($rs))
			{
				$l += (int)$row['count'];
				$sql = "UPDATE post SET sortLike=" . $row['count'] . " WHERE id=" . $row['id'];
				mysql_query($sql) or die(mysql_error());
			}
			$sql = "SELECT post.id as id, COUNT(rating.`timestamp`) as count FROM `post` LEFT JOIN rating ON rating.postID=post.id WHERE rating.`type`='' AND post.deleted = 0 AND rating.rating = 'dislike' GROUP BY post.id";
			$rs = mysql_query($sql) or die(mysql_error());
			while ($row = mysql_fetch_assoc($rs))
			{
				$d += (int)$row['count'];
				$sql = "UPDATE post SET sortDislike=" . $row['count'] . " WHERE id=" . $row['id'];
				mysql_query($sql) or die(mysql_error());
			}
			$sql = "UPDATE post SET sortRatio = sortLike/(sortLike+sortDislike)*sortLike*2 - sortDislike/(sortDislike+sortLike) *sortDislike";
			mysql_query($sql) or die(mysql_error());
			$sql = "SELECT poll.id as id, COUNT(rating.`timestamp`) as count FROM `poll` LEFT JOIN rating ON rating.postID=poll.id WHERE rating.`type`='poll' AND rating.rating = 'like' GROUP BY poll.id";
			$rs = mysql_query($sql) or die(mysql_error());
			while ($row = mysql_fetch_assoc($rs))
			{
				$l += (int)$row['count'];
				$sql = "UPDATE poll SET sortLike=" . $row['count'] . " WHERE id=" . $row['id'];
				mysql_query($sql) or die(mysql_error());
			}
			$sql = "SELECT poll.id as id, COUNT(rating.`timestamp`) as count FROM `poll` LEFT JOIN rating ON rating.postID=poll.id WHERE rating.`type`='poll' AND rating.rating = 'dislike' GROUP BY poll.id";
			$rs = mysql_query($sql) or die(mysql_error());
			while ($row = mysql_fetch_assoc($rs))
			{
				$d += (int)$row['count'];
				$sql = "UPDATE poll SET sortDislike=" . $row['count'] . " WHERE id=" . $row['id'];
				mysql_query($sql) or die(mysql_error());
			}
			
			$sql = "UPDATE poll SET sortRatio = sortLike/(sortLike+sortDislike)*sortLike*2 - sortDislike/(sortDislike+sortLike) *sortDislike";
			mysql_query($sql) or die(mysql_error());
			
			echo "likes:" . $l . " | dislikes:" . $d;
			break;
			
	}
}
else
{
	echo "ERROR: no action definded";
}