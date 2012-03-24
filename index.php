<?php
	session_start();
	require 'static/smarty/Smarty.class.php';
	include_once 'lib/config.php';
	$workspace = new Workspace();
	$workspace->process();
	error_reporting(0);
	class Workspace
	{
			var $task="";
			var $user = array();
			var $util;
			var $config;
			var $lang,$langArr=array();
			function process()
			{
				$loggedIn=false;
				$smarty = new Smarty();
				$this->config = new Config();
				$pageTitle = "";
				if ($this->config->connect())
				{
					//connection established 
					if (isset($_GET["task"]))
					{
						$this->task=$_GET["task"];
					}
					if (isset($_POST["language"]))
					{
						$_SESSION["user"]["language"]=$_POST["language"];
					}
					if (isset($_SESSION["user"]))
					{
						$this->user=$_SESSION["user"];
						$loggedIn=true;
					}
					$smarty->assign("loggedIn", (int)$loggedIn);
					$l = "de";
					if (isset($this->user["language"]))
					{
						if ($this->user["language"]=="en")
						{
							$l = "en";
						}
						//print_r($_POST);
					}
					define("LANGUAGE",$l);
					include_once "lib/lang/lang.php";
					$lang = new Language($l);
					
					// pass whole languageArray to smarty: $lang
					$this->langArr = $lang->languageArray;
					$smarty->assign("lang", $this->langArr);
					
					switch($this->task)
					{
						case "showPost":
							include_once "lib/util.php";
							$this->util = new Util($lang);
							$id = (int)$_GET["id"];
							if ($id)
							{

								
								$obj = $this->getPostList("post.postID=". $id, "post.timestamp ASC");
								$smarty->assign("list", $obj);
								$smarty->assign("user",$this->user);
								$commentsHtml = $smarty->fetch("post/comment.html");
								
								$obj = $this->getPostList("post.id=". $id);
								if ($obj[0]["caption"]!="")
								{
									$pageTitle = $obj[0]["caption"];
								}
								else
								{
									$pageTitle = $obj[0]["username"] ." ". $this->util->makeDateReadable($obj[0]["timestamp"],true);
								}
								if ($parentID = $obj[0]["postid"]>0)
								{
									$parent = $this->getPostList("post.id=".$parentID);
									$obj[0]["parent"]=$parent;
								}#
								if ($obj[0]["postid"]>0)
								{
									$parent = $this->getPostList("post.id=".$obj[0]["postid"]);
									$obj[0]["parent"]=$parent[0];
								}
								
								$obj[0]["commentsHtml"]=$commentsHtml;
								
								$smarty->assign("withFrame",true);
								$smarty->assign("list", $obj);
								$smarty->assign("user", $this->user);
								
								$smarty->assign("TPL_POSTS", $smarty->fetch("post/list.html"));
								$smarty->assign("contents", $smarty->fetch("post/form.html"));
							}
							break;
						case "rating":
							if ($this->user)
							{
								$postID = (int)$_POST["id"];
								$userID = (int)$this->user["id"];
								$rating = $_POST["rating"];
								$unrate = isset($_POST["unrate"]);
								$timestamp = date("Y-m-d H:i:s");
								$sql = "SELECT rating FROM rating WHERE userID=" . $userID . " AND postID=" . $postID;
								$rs = mysql_query($sql) or die(mysql_error());
								if (mysql_num_rows($rs))
								{
									// UPDATE
									if ($unrate)
									{
										$sql = "DELETE FROM rating WHERE postID=" . $postID . " AND userID=" . $userID . " AND rating=" . $rating;
									}
									else
									{
										$sql = "UPDATE rating SET rating='".$rating."', timestamp='".$timestamp."' WHERE postID=" . $postID . " AND userID=" . $userID;
									}

								}
								else
								{
									// INSERT
									$sql = "INSERT rating (postID,userID,rating,timestamp) VALUES (".$postID.",".$userID.",'".$rating."','".$timestamp."')";
								}
								$rs = mysql_query($sql) or die(mysql_error());
							}
							die();
							break;
						
						case "profile":
							include_once "lib/util.php";
							$this->util = new Util($lang);
							// Handles profile view
							$id = (int)$_GET["id"];
							$profileUser = array();
							if ($id)
							{
								$sql = "SELECT id,name as username, hasImage FROM user WHERE id=" . $id;
								$rs = mysql_query($sql);
								
								if ($rs = mysql_fetch_array($rs))
								{
									$profileUser = $rs;
								}
							}
							else
							{
								$profileUser["userid"]=0;
								$profileUser["username"]=$this->langArr["guest"];
								$profileUser["hasImage"]=1;
							}
							$obj = $this->getPostList("post.userID=".$id);
							foreach ($obj as $k => $o)
							{
								if ($o["postid"]>0)
								{
									$parent = $this->getPostList("post.id=".$o["postid"]);
									$obj[$k]["parent"]=$parent[0];
								}
							}
							$smarty->assign("user", $this->user);
							$smarty->assign("list",$obj);
							$smarty->assign("profileUser", $profileUser);
							$smarty->assign("contents", $smarty->fetch("profile.html"));

							break;
						case "login":
							include_once "lib/util.php";
							$this->util = new Util($lang);
							if (isset($_POST["username"]) && isset($_POST["password"]))
							{
								//$sql = "SELECT id,name FROM user WHERE email='".mysql_escape_string($_POST["username"])."' AND password='".md5($_POST["password"])."'";
								$sql = "SELECT id,name,color,hasImage,language FROM user WHERE email='".mysql_escape_string($_POST["username"])."' AND `password`=MD5(CONCAT(id,'".SALT."','".$_POST["password"]."'))";
								$rs = mysql_query($sql) or die(mysql_error());
								if ($this->user=mysql_fetch_assoc($rs))
								{
									$_SESSION["user"] = $this->user;
									$loggedIn=true;
									
									if (isset($_SERVER["HTTP_REFERER"]))
									{
										header("Location:" . $_SERVER["HTTP_REFERER"]);
									}
									else
									{
										header("Location:./");
									}
									die();
								}
								else
								{
									$smarty->assign("loginError","Anmeldung fehlgeschlagen!");
								}
							}
							else
							{
								header("Location:./");
							}
							break;
						case "register":
							if (isset($_POST["email"]) && isset($_POST["password"]) && isset($_POST["passwordRepeat"]) && isset($_POST["username"]))
							{
								include_once "lib/util.php";
								$this->util = new Util($lang);
								if ($_POST["password"])
								{
									if ($_POST["password"] != $_POST["passwordRepeat"])
									{
										$smarty->assign("registerError", "Die Passw&ouml;rter stimmen nicht &uuml;berein.");
									}
									else
									{
										$sql = "SELECT COUNT(id) FROM user WHERE email='".$_POST["email"]."'";
										$rs = mysql_query($sql) or die(mysql_error());
										$rs = mysql_fetch_array($rs);
										if ($rs[0]>0)
										{
											//e-mail bereits registrier.
											$smarty->assign("registerError", "Diese E-Mail-Adresse wurde bereits registriert.");
										}
										else
										{
											$nickname = addslashes(strip_tags($_POST["username"]));
											
											$sql = "INSERT INTO user (email,active,color,name) VALUES ('".$_POST["email"]."',1,'#666666','".$nickname."')";
											$rs = mysql_query($sql) or die(mysql_error());
											$userID = mysql_insert_id();
											$md5 = md5($userID . SALT . $_POST["password"]);
											$sql = "UPDATE user SET password='".$md5."' WHERE id=" . $userID;
											$rs = mysql_query($sql) or die(mysql_error());
											$sql = "SELECT id,name,color,hasImage,language FROM user WHERE id=" . $userID;
											$rs = mysql_query($sql) or die(mysql_error());
											if ($this->user = mysql_fetch_array($rs))
											{
												$_SESSION["user"] = $this->user;
												$loggedIn=true;  
												$smarty->assign("user", $_SESSION["user"]);
												$smarty->assign("contents", $smarty->fetch("register/success.html"));
											}
										}
									}
								}
							}
							break;
						case "logout":
							if (isset($_SESSION["user"]))
							{
								//online timestamp zurücksetzen
								$sql = "UPDATE user SET online='".date('Y-m-d H:i:s') ."' WHERE id=" . $this->user["id"];
								$rs = mysql_query($sql) or die(mysql_error());

								unset($_SESSION["user"]);
								if (isset($_SERVER["HTTP_REFERER"]))
								{
									header("Location:" . $_SERVER["HTTP_REFERER"]);
								}
								else
								{
									header("Location:./");
								}
								$loggedIn=false;
								die();
							}
							break;
						case "generateAvatarPreview":
								$image_src = "uploads/t_" . $this->user["id"] . ".jpg";

								$image = imagecreatefromjpeg($image_src);
								$thumbPath = "uploads/temp_" . $this->user["id"] . ".jpg";
								$new_width=48;$new_height=48;
								$image_p = imagecreatetruecolor($new_width, $new_height);
								$width = (int)$_GET["width"];
								$height = (int)$_GET["height"];// = getimagesize($image_src);
								$left = (int)$_GET["left"];
								$top = (int)$_GET["top"];
								imagecopyresampled($image_p, $image, 0, 0, $left,$top , $new_width, $new_height, $width, $height) or die("error_resample");
								if (!imagejpeg($image_p,$thumbPath,100)) die ("error imagejpg");
								die ($thumbPath);
								break;
						case "fileUpload":
							$target_path = "uploads/";
							//die (print_r($_FILES));
							$target_path = $target_path . "temp_" . basename($_FILES["profileImage"]["name"]);
							if (move_uploaded_file($_FILES["profileImage"]["tmp_name"], $target_path)) 
							{
								$type = explode(".", strtolower(basename($_FILES["profileImage"]["name"])));
								$type = $type[count($type)-1];
								$image=array();
								if ($type == "gif")
								{
									$image = imagecreatefromgif($target_path);
								}
								if ($type == "jpg" || $type == "jpeg")
								{
									$image = imagecreatefromjpeg($target_path);
								}
								if ($type == "png")
								{
									$image = imagecreatefrompng($target_path);
								}
								list($width, $height) = getimagesize($target_path);
								if ($width>$height && $width>400)
								{
									$new_width = 400;
									$new_height = 400/$width*$height;
								}
								elseif ($height>400)
								{
									$new_height = 400;
									$new_width = 400/$height*$width;
								}
								else
								{
									$new_height = $height;
									$new_width = $width;
								}
								$image_p = imagecreatetruecolor($new_width, $new_height);
								$thumbPath = "uploads/t_" . $this->user["id"] . ".jpg";
								imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
								imagejpeg($image_p,$thumbPath,100);
								unlink($target_path);
								//$html = "<div id='thumb-wrap' style='width:" . $new_width . "px;height:" . $new_height . "px;background:url(" . $thumbPath . ") no-repeat'></div>";											die ();
								//die($html);
								die ($thumbPath);
							}
							else
							{
								die (0);
							}
							break;
						case "settings":
							include_once "lib/util.php";
							$this->util = new Util($lang);
							if ($this->user)
							{
								$sqlSet = "";
								if (isset($_POST["color"]))
								{
									$sqlSet = "color='".$_POST["color"]."'";
									
									//$sql = "UPDATE user SET color='".$_POST["color"]."' WHERE id=" . $this->user["id"];
									//$rs = mysql_query($sql) or die(mysql_error());	
								}
								if (isset ($_POST["nickname"]))
								{
									if ($sqlSet) $sqlSet .=",";
									$sqlSet .= "name='".$_POST["nickname"]."'";
									//$sql = "UPDATE user SET name='".$_POST["nickname"]."' WHERE id=" . $this->user["id"];
									//$rs = mysql_query($sql) or die(mysql_error());

								}
								if (isset ($_POST["language"]))
								{
									if ($sqlSet) $sqlSet .=",";
									$sqlSet .= "language='".$_POST["language"]."'";
									
									//$sql = "UPDATE user SET language='".$_POST["language"]."' WHERE id=" . $this->user["id"];
									//$rs = mysql_query($sql) or die(mysql_error());
								}
								if (isset($_POST["uploadProfileImage"]))
								{
									if ((int)$_POST["uploadProfileImage"]==1)
									{
										$src = "uploads/temp_" . (int)$this->user["id"] . ".jpg";
										$new = "uploads/p/" . (int)$this->user["id"] . ".jpg";												
										if (file_exists($src))
										{
											rename ($src, $new);
											
											if ($sqlSet) $sqlSet .=",";
											$sqlSet .= "hasImage=1";
											//$sql = "UPDATE user SET hasImage=1 WHERE id=" . (int)$this->user["id"];
											//$rs = mysql_query($sql) or die(mysql_error());
										}
									}
								}
								if ($sqlSet)
								{
									$sql = "UPDATE user SET " . $sqlSet . " WHERE id=" . (int)$this->user["id"];
									$rs = mysql_query($sql) or die(mysql_error());
								}
								
								$sql = "SELECT id,name,email,color,hasImage,language FROM user WHERE id=" . $this->user["id"];
								$rs = mysql_query($sql) or die(mysql_error());
								$this->user = mysql_fetch_array($rs);
								$_SESSION["user"]=$this->user;
								//header("Location:");
								$smarty->assign("user", $_SESSION["user"]);
								$smarty->assign("contents", $smarty->fetch("settings.html"));
							}

							break;
						case "ajaxPost":
							if (isset($_POST["status"]))
							{
								$status = $this->config->convertToDatabase($_POST["status"]);
								$sql = 'INSERT INTO post (postID,userID,caption,message,timestamp) VALUES (0,'. $this->user["id"] . ',"","' . $status . '","'.date("Y-m-d H:i:s").'")';
								 mysql_query($sql);
								$id =mysql_insert_id();
								$getPost = true;
								$newPost = true;
								//die ("".$id);
							}
							if (isset($_POST["caption"]) && isset($_POST["message"]))
							{
								$caption = $this->config->convertToDatabase($_POST["caption"]);
								$message = $this->config->convertToDatabase($_POST["message"]);
								$sql = 'INSERT INTO post (postID,userID,caption,message,timestamp) VALUES (0,'.$this->user["id"].',"'.$caption.'","'.$message.'","'.date("Y-m-d H:i:s").'")';
								mysql_query($sql);
								$id = mysql_insert_id();
								$getPost = true;
								$newPost = true;
								//die ("".$id);
							}
							// no break : get POST
						case "sendComment":
							if (!isset($getPost)) // skip when ajaxPost (to return post)
							{
								if (isset($_POST["parentID"]) && isset($_POST["message"]))
								{
									$parentID=(int)$_POST["parentID"];
									//$message = str_replace("||plus||", "+", $_POST["message"]);
									$message = $this->config->convertToDatabase($_POST["message"]);
									//echo "<pre>" . $message . "</pre>";
									$id=0;
									if (isset($_POST["id"]) && $_POST["id"]>0)
									{
										//@todo: WRITE HISTORY
										$id = (int)$_POST["id"];

										$userID=0;
										$userWhere = "";
										if ($this->user)
										{
											$userID = (int)$this->user["id"];
										}
										$sqlSet = "";
										$sql = "";
										if ($message == "")
										{
											$sqlSet = ",deleted=1";
										}
										if ($id == $parentID)
										{
											$getPost = true;
											$sql = "UPDATE post SET message='".$message."' ".$sqlSet." WHERE id=" . $id . " AND (userID=" . $userID . " OR userID=0)";
										}
										else
										{
											$sql = "UPDATE post SET message='".$message."' ".$sqlSet." WHERE id=" . $id . " AND (userID=" . $userID . " OR userID=0)" . " AND postID=" . $parentID;
										}

										$rs = mysql_query($sql) or die(mysql_error());
									}
									else
									{
										$userID=0;
										if ($this->user)
										{
											$userID = (int)$this->user["id"];
										}
										$sql = "INSERT INTO post (postID, message, userID, timestamp) VALUES (" . $parentID . ",'".$message."',".$userID.",'".date("Y-m-d H:i:s")."')";
										$rs = mysql_query($sql) or die(mysql_error());
										$id = mysql_insert_id();
									}
									$id=$parentID;
								}
							}
						
							//break;
							//no BREAK here RETURN comments!
						case "getComments":
							// skip for ajax post
							if (!isset($getPost))
							{
								include_once "lib/util.php";
								$this->util = new Util($lang);
								if (!isset($id))
								{
									$id=(int)$_POST["id"];
								}
								if (isset($id))
								{
									$obj = $this->getPostList("post.postID=". $id, "post.timestamp ASC");
									$smarty->assign("list", $obj);
									$smarty->assign("user",$this->user);
									die($smarty->fetch("post/comment.html"));
								}
								else
								{
									die ("");
								}	
								break;
							}
						case "getPosts":
							if (!isset($getPost))
							{
								include_once "lib/util.php";
								$this->util=new Util($lang);
								if (isset($_REQUEST["parent"]))
								{
									$parent = (int)$_REQUEST["parent"];
								}
								if (isset($parent))
								{
									$limit = "0,30";
									if (isset($_REQUEST["limit"]))
									{
										$limit = $_REQUEST["limit"];
									}

									$obj = $this->getPostList("post.postID=" . (int)$parent,'post.timestamp DESC' , $limit);
									$smarty->assign("user",$this->user);
									$smarty->assign("list",$obj);
									die($smarty->fetch("post/ajax.html"));
								}
								else
								{
									$obj = $this->getPostList("post.postID=0",'post.timestamp DESC');
									$smarty->assign("user",$this->user);
									$smarty->assign("list",$obj);
									$smarty->assign("TPL_POSTS", $smarty->fetch("post/ajax.html"));
									$smarty->assign("contents", $smarty->fetch("post/form.html"));
								}
								
								break;
							}
						case "post":
							
							include_once "lib/util.php";
							$this->util=new Util($lang);
							if (!isset ($id) && isset($_GET["id"]))
							{
								$id = (int)$_GET["id"];
							}
							
							if ((isset($id) && $postID = (int)$id) )
							{
								$withCaption =""; // standard without caption
								$obj = array();
								if (isset($newPost))	
								{
									$smarty->assign("withFrame", true);
								}
								
								$obj = $this->getPostList("post.id=" . $id);
								if (isset($obj["caption"]) || isset($parentID))
								{
									$smarty->assign("withCaption", true);
								}
								if (count($obj)>1)
								{
									//$smarty->assign("item")
								}	
								if (isset($obj[0]))
								{
									$smarty->assign("item", $obj[0]);
									$smarty->assign("user", $this->user);
									die($smarty->fetch("post/post.html"));
								}
								else
								{
									die ($smarty->fetch("post/postDeleted.html"));
								}
								
							}
							else
							{
								die();
							}
							break;
						default:
							include_once "lib/util.php";
							$this->util=new Util($lang);
							//$obj = array();
							//$obj = $this->getPostList("post.postID=0");
							$smarty->assign("user",$this->user);
							$sql = "SELECT count(id) FROM post WHERE postID=0 AND deleted=0";
							$rs = mysql_query($sql) or die(mysql_error());
							$postCount = mysql_fetch_array($rs);
							$postCount = $postCount[0];
							$smarty->assign("postCount",$postCount);
							$smarty->assign("contents", $smarty->fetch("post/form.html"));
							break;
						}
						if (isset($_SESSION["user"]))
						{
							$this->user=$_SESSION["user"];
							$loggedIn=true;
						}
						if ($this->user)
						{
							$sql =	"SELECT user.id as id,user.name as name,user.online, SUM(message.`read`=0) as new, user.hasImage " . 
									"FROM user LEFT JOIN message ON message.senderID=user.id AND message.receiverID=".$this->user["id"]." " . 
									"WHERE user.id!=".$this->user["id"]." AND user.active=1 GROUP BY user.id " .
									"ORDER BY online DESC, user.name ASC";
							//$sql = "SELECT user.id as id,user.name as name,MAX(UNIX_TIMESTAMP(online)) as online, (COUNT(message.id)-SUM(message.`read`)) as new, user.hasImage FROM user LEFT JOIN message ON message.senderid=user.id WHERE user.id!=".$this->user["id"]." AND user.active=1 GROUP BY user.id ORDER BY new DESC, online DESC, user.name ASC";
							
							$rs = mysql_query($sql) or die (mysql_error());
							if ($rs)
							{
								while ($row=mysql_fetch_assoc($rs)) {
									
									//echo $row["online"];
									$row["readableOnline"]=$this->util->makeDateReadable($row["online"], true);
									$row["online"]=strtotime($row["online"]);
									$userList[]=$row;
								}
								//print_r ($userList);	
								$smarty->assign("userList", $userList);
							}
						}
					if ($pageTitle)
					{
						$pageTitle = $pageTitle . " - onlinepartei.eu";
					}
					else
					{
						$pageTitle = "onlinepartei.eu";
					}
					$smarty->assign("loggedIn", (int)$loggedIn);
					$smarty->assign("user", $this->user);
					$smarty->assign("pageTitle", $pageTitle);
					$base = "http://" . $_SERVER["HTTP_HOST"];
					$self= explode("/", $_SERVER["PHP_SELF"]);
					unset($self[count($self)-1]);
					$self = implode("/", $self);
					$base = $base . $self . "/";
					$smarty->assign("basePath", $base);
					$smarty->display("main.html");
		}
	}
	function getPostList($sqlWhere="", $sqlOrder="post.timestamp DESC", $limit="")
	{
		$sql = "";
		if ($sqlOrder)
		{
			$sqlOrder = " ORDER BY " . $sqlOrder;
		}
		$sqlLimit = "";
		if ($limit)
		{
			$sqlLimit = " LIMIT " . $limit;
		}
		if ($sqlWhere)
		{
			$sql = "SELECT post.id as id,post.postID as postid, post.caption as caption,post.timestamp as timestamp,user.name as username,user.id as userid,user.hasImage as hasImage,post.message as message, COUNT(sub.id) as comments, SUM(sub.deleted) as deletedComments FROM post LEFT JOIN user ON user.id = post.userID LEFT JOIN post as sub ON post.id=sub.postID WHERE post.deleted=0 AND ".$sqlWhere." GROUP BY sub.postID,post.id " . $sqlOrder . $sqlLimit;
		}
		else
		{
			$sql = "SELECT post.id as id,post.postID as postid, post.caption as caption,post.timestamp as timestamp,user.name as username,user.id as userid,user.hasImage as hasImage,post.message as message, COUNT(sub.id) as comments, SUM(sub.deleted) as deletedComments FROM post LEFT JOIN user ON user.id = post.userID LEFT JOIN post as sub ON post.id=sub.postID WHERE post.postID=0 AND post.deleted=0 GROUP BY sub.postID,post.id " . $sqlOrder . $sqlLimit;
		}
		$obj = array();
		$rs = mysql_query($sql) or die(mysql_error());
		while($row = mysql_fetch_assoc($rs))
		{
			if ($this->user)
			{	// with myRating
				$sql = "SELECT SUM(r.rating='like') as `like`, SUM(r.rating='dislike') as `dislike`, myRating.rating as myRating FROM rating r LEFT JOIN rating myRating ON myRating.postID=".$row["id"]. " AND myRating.userID=".$this->user["id"]." WHERE r.postID=" . $row["id"] . " GROUP BY r.postID";
			}
			else
			{
				//without myRating
				$sql = "SELECT SUM(r.rating='like') as `like`, SUM(r.rating='dislike') as `dislike` FROM rating r WHERE r.postID=" . $row["id"] . " GROUP BY r.postID";
			}
			$rsRating = mysql_query($sql) or die(mysql_error());
			if ($rowRating = mysql_fetch_assoc($rsRating))
			{
				if (isset($rowRating["like"]))
				{	$row["like"] = (int)$rowRating["like"]; }
				else
				{	$row["like"]=0;	}
				if (isset($rowRating["dislike"]))
				{	$row["dislike"] = (int)$rowRating["dislike"]; }
				else
				{	$row["dislike"]=0;	}
				if (isset($rowRating["myRating"]))
				{
					$row["myRating"] = $rowRating["myRating"];
				}
			}
			else
			{ $row["like"]=0;$row["dislike"]=0; }
			$sum = $row["like"] + $row["dislike"];
			if ($sum)
			{
				$percent = $row["like"]/$sum*100;
				$votingBarWidth = $percent/2;
			}
			else
			{
				$votingBarWidth = 25;
				$percent = "-";
			}
			if (!$row["userid"]>0)
			{
				$row["userid"]=0;
				$row["hasImage"]=1;
				$row["username"] = $this->langArr["guest"];
			}
			$row["caption"]=$this->config->convertFromDatabase($row["caption"]);
			$row["comments"]=$row["comments"]-$row["deletedComments"];
			$row["percent"]=$percent;
			$row["votingBarWidth"]=$votingBarWidth;
			$row["date"]=$this->util->makeDateReadable($row["timestamp"],true);
			$row["message"]=$this->config->convertFromDatabase($row["message"]);
			$row["messageReadable"] = $this->util->makeLinks($row["message"]);
			$row["message"]=urlencode($row["message"]);
			$obj[]=$row;
		}
		return $obj;
	}
	
	

}

?>