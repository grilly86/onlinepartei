<?php
	session_start();
	require 'static/smarty/Smarty.class.php';
	include_once 'lib/config.php';
	$workspace = new Workspace();
	$workspace->process();
	//error_reporting(E_ALL);
	class Workspace
	{
			var $task="";
			var $user = array();
			var $util;
			var $config;
			var $smarty;
			var $lang,$langArr=array();
			function process()
			{
				$loggedIn=false;
				$this->smarty = new Smarty();
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
					$this->smarty->assign("loggedIn", (int)$loggedIn);
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
					$this->smarty->assign("lang", $this->langArr);
					switch($this->task)
					{
						case "slogan":
							include_once "lib/util.php";
							$this->util = new Util($lang);

							die ($this->getRandomSlogan());
							
							break;
						case "tag":
							if (isset($_POST['id']) && isset($_POST['name']) && isset($_POST['type']))
							{
								$id = (int)$_POST['id'];
								$name = $this->config->convertToDatabase(strtolower($_POST['name']));
								$type = $this->config->convertToDatabase($_POST['type']);

								$sql = "SELECT userid FROM " . $type . " WHERE id=" . $id;
								$rs = mysql_query($sql) or die(mysql_error());
								if ($row = mysql_fetch_assoc($rs))
								{
									if ($row['userid']==0 || (isset($this->user)))// && $row['userid']==$this->user['id']))
									{
										if ($type=='post') $type='';
										
										if (isset($_POST['action']) && $_POST['action']=='remove')
										{
											//finde tag id:
											$sql = "SELECT id FROM tag WHERE name='".$name."'";
											$rs = mysql_query($sql) or die(mysql_error());
											$tagID = 0;
											if (mysql_num_rows($rs))
											{
												$tag = mysql_fetch_assoc($rs);
												$tagID=(int)$tag['id'];
											}
											// lösche Tag
											$sql = "DELETE FROM post_tag WHERE tagID=" . $tagID . " AND parentID=" . $id . " AND `type`='".$type."'";
											$rs = mysql_query($sql) or die(mysql_error());

											// wenn tag nicht mehr zugewiesen: tag entfernen
											$sql = "SELECT tagID FROM post_tag WHERE tagID=" . $tagID;
											$rs = mysql_query($sql) or die(mysql_error());
											if  (!mysql_num_rows($rs))
											{
												//tag entfernen
												$sql = "DELETE FROM tag WHERE id=" . $tagID;
												$rs = mysql_query($sql) or die (mysql_error());
											}
											die ("200");
										}
										else
										{
											// tag bereits vorhanden?
											$sql = "SELECT id FROM tag WHERE name='".$name."'";
											$rs = mysql_query($sql) or die(mysql_error());
											$tagID = 0;
											if (mysql_num_rows($rs))
											{
												$tag = mysql_fetch_assoc($rs);
												$tagID=(int)$tag['id'];

												$sql = "SELECT * FROM post_tag WHERE tagID=" . $tagID . " AND parentID=" . $id . " AND type='".$type."'";
												$rs = mysql_query($sql) or die(mysql_error());
												if (mysql_num_rows($rs))
												{
													die("500");
												}
											}
											else
											{
												$sql = "INSERT INTO tag (name) VALUES ('" . $name . "')";
												$rs = mysql_query($sql) or die(mysql_error());
												$tagID = mysql_insert_id();
											}
											$sql = "INSERT INTO post_tag (parentID,type,tagID) VALUES(".$id.",'".$type."',".$tagID.")";
											$rs = mysql_query($sql) or die(mysql_error());
											die ("200");
										}
									}
								}
								die("500");
							}
							if (isset($_GET['name']))
							{
								include_once "lib/util.php";
								$this->util = new Util($lang);
								$name = $this->config->convertToDatabase($_GET['name']);
								$obj = $this->getPostList("tag.name='".$name."'","timestamp DESC","","both");
								
								$this->smarty->assign("tag",$this->config->convertFromDatabase($name));
								
								$this->smarty->assign("list", $obj);
								$this->smarty->assign("user", $this->user);
								$this->smarty->assign("postActive","active");

								$this->smarty->assign("contents", $this->smarty->fetch("tag.tpl"));
								/*$this->smarty->assign("contents", $this->smarty->fetch("form.tpl"));*/
								$this->smarty->assign("postsActive", "active");
								
							}
							break;
						case "showPost":
							include_once "lib/util.php";
							$this->util = new Util($lang);
							$id = (int)$_GET["id"];
							if ($id)
							{
								//load comments
								$obj = $this->getPostList($id, "post.timestamp ASC",'','post');
								$this->smarty->assign("list", $obj);
								$this->smarty->assign("user",$this->user);
								$commentsHtml = $this->smarty->fetch("comment.tpl");

								//load specified post
								$obj = $this->getPostList("post.id=" . $id,'timestamp DESC','','post');
								if($obj)
								{
									if ($obj[0]["caption"]!="")
									{
										$pageTitle = $obj[0]["caption"];
									}
									else
									{
										$pageTitle = $obj[0]["username"] ." ". $this->util->makeDateReadable($obj[0]["timestamp"],true);
									}
									$pType =$obj[0]['pType'];
									if (!$pType) $pType = 'post';
									
									if ($parentID = $obj[0]["postid"]>0)
									{
										$parent = $this->getPostList($pType.".id=".$parentID,'timestamp DESC','',$pType);
										$obj[0]["parent"]=$parent;
									}#
									if ($obj[0]["postid"]>0)
									{
										$parent = $this->getPostList($pType.".id=".$obj[0]["postid"],'timestamp DESC','',$pType);
										$obj[0]["parent"]=$parent[0];
									}
									
									$obj[0]["commentsHtml"]=$commentsHtml;

									$this->smarty->assign("withFrame",true);
									$this->smarty->assign("list", $obj);
									$this->smarty->assign("user", $this->user);
									$this->smarty->assign("postActive","active");

									$this->smarty->assign("TPL_POSTS", $this->smarty->fetch("post/list.tpl"));

								}
								else
								{
									header("Status: 404 Not Found");
									$this->smarty->assign("TPL_POSTS", $this->smarty->fetch("error/404.tpl"));
								}

								$this->smarty->assign("contents", $this->smarty->fetch("form.tpl"));
								$this->smarty->assign("postsActive", "active");



							}
							break;
						case "polls":
							// show polls
							include_once "lib/util.php";
							$this->util = new Util($lang);
							if (isset($_POST['pollID']) && isset($_POST['vote']) && $this->user && (int)$_POST['vote']>-1)
							{
								//die ($_POST['vote']);
								$sql = "SELECT * FROM poll_vote WHERE userID=" . $this->user['id'] . " AND pollID=".(int)$_POST['pollID'];
								$rs = mysql_query ($sql) or die (mysql_error());
								if (mysql_num_rows($rs)==0)
								{
									$sql = "INSERT INTO poll_vote(userID,pollID,vote,timestamp) VALUES (" . (int)$this->user['id'] . "," . (int)$_POST['pollID'] .",". $_POST['vote']. ",'" . date("Y-m-d H:i:s") . "')";
									mysql_query($sql) or die(mysql_error());
									die ($_POST['pollID']);
								}
								else
								{
									die("error eintrag schon vorhanden");
								}
							}
							elseif (isset($_POST["pollID"]) && isset($_POST["revert"]) && $this->user )
							{
								$sql = "DELETE FROM poll_vote WHERE userID=" . $this->user['id'] . " AND pollID=" . (int)$_POST['pollID'];
								if (mysql_query($sql))
								{
									die("true");
								}
								else
								{
									die(mysql_error());
								}
							}
							$where="";$id=0;
							if (isset($_GET["id"]))
							{
								$id = (int)$_GET["id"];
								$where = " WHERE poll.id=" . $id;
							}
							$sql = "SELECT poll.id,poll.question as caption, poll.text as message,poll.answers as answers, poll.timestamp as timestamp, user.name as username,user.id as userid,user.hasImage as hasImage, COUNT(sub.id) as comments, SUM(sub.deleted) as deletedComments  FROM poll " . 
									"LEFT JOIN user ON user.id = poll.userID " .
									"LEFT JOIN post as sub ON poll.id=sub.postID AND sub.type='poll' " . 
									$where .
									" GROUP BY sub.postID,poll.id ORDER BY poll.timestamp DESC";
							$rs = mysql_query($sql) or die(mysql_error());
							$obj = array();
							while ($row = mysql_fetch_assoc($rs))
							{
								//vote of current user:
								$userVote=-1;
								if ($this->user)
								{
									$sql = "SELECT vote FROM poll_vote WHERE userID=". $this->user['id'] . " AND pollID=" . (int)$row['id'];
									$rsU = mysql_query($sql);
									if (mysql_num_rows($rsU) > 0)
									{
										$userVote = mysql_fetch_array($rsU);
										$userVote = $userVote[0];
									}
								}
								//all votes
								$sql = "SELECT COUNT(*) as count, vote as vote FROM poll_vote WHERE pollID=" . (int)$row['id'] . " GROUP BY vote ORDER BY vote";
								$rsV = mysql_query($sql) or die(mysql_error());
								$votes = array();
								$totalVotes = 0;
								while ($rowV = mysql_fetch_assoc($rsV))
								{
									$totalVotes += $rowV['count'];
									$votes[$rowV['vote']] = $rowV['count'];
								}
								$answers = explode(";",$row['answers']);
								$isVoted=false;
								foreach ($answers as $i => $a)
								{

									$row['answer'][$i]["text"] = $a;
									if (isset($votes[$i]))
									{
										$row['answer'][$i]["vote"] = $votes[$i];
										$row['answer'][$i]["percent"] = (int)($votes[$i]/$totalVotes*100) . " %";
									}
									else
									{
										$row['answer'][$i]["vote"] = 0;
										$row['answer'][$i]["percent"] = "0 %";
									}
									if ($i == $userVote)
									{
										$isVoted = true;
										$row['answer'][$i]["uservote"] = true;
										$row['uservote']=$i;
									}
									else
									{
										$row['answer'][$i]["userVote"] = false;
									}
								}

								// LIKES 
								if ($this->user)
								{	// with myRating
									$sql = "SELECT SUM(r.rating='like') as `like`, SUM(r.rating='dislike') as `dislike`, myRating.rating as myRating FROM rating r LEFT JOIN rating myRating ON myRating.postID=".$row["id"]. " AND myRating.userID=".$this->user["id"]." WHERE r.postID=" . $row["id"] . " AND r.type='poll' AND myRating.type='poll' GROUP BY r.postID";
								}
								else
								{
									//without myRating
									$sql = "SELECT SUM(r.rating='like') as `like`, SUM(r.rating='dislike') as `dislike` FROM rating r WHERE r.postID=" . $row["id"] . " AND r.type='poll' GROUP BY r.postID";
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
									$percent = (int)($row["like"]/$sum*100);
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

								$row['isVoted']=$isVoted;
								//$row['question'] = $this->util->makeLinks($row['question']);
								
								// tags 
								$sql = 'SELECT tag.name as name FROM post_tag LEFT JOIN tag ON post_tag.tagID=tag.id WHERE parentID=' . $row['id'] . " AND `type`='poll' ORDER BY tag.name";
								$rsTag = mysql_query($sql) or die(mysql_error());
								while ($rowTag = mysql_fetch_assoc($rsTag))
								{
									$row["tags"][]=$this->config->convertFromDatabase($rowTag['name']);
								}
								$row['message'] = $this->util->makeLinks($row['message']);
								$row["comments"]=$row["comments"]-$row["deletedComments"];
								$row["percent"]=$percent;
								$row["votingBarWidth"]=$votingBarWidth;
								$row["date"]=$this->util->makeDateReadable($row["timestamp"],true);
								$row["type"]='poll';
								$obj[] = $row;
							}
							$this->smarty->assign("user",$this->user);
							$this->smarty->assign("list",$obj);
							
							$this->smarty->assign("pollsActive", "active");
							
							$this->smarty->assign("TPL_POSTS", $this->smarty->fetch("post/list.tpl"));
							$this->smarty->assign("contents", $this->smarty->fetch("form.tpl"));
							break;
						case "rating":
							if ($this->user)
							{
								$postID = (int)$_POST["id"];
								$userID = (int)$this->user["id"];
								$rating = $_POST["rating"];
								$unrate = isset($_POST["unrate"]);
								$timestamp = date("Y-m-d H:i:s");
								$type = "";
								$typeWhere ="";
								if (isset($_POST["type"]))
								{
									$type = $_POST["type"];
									$typeWhere = " AND type='".$type."' ";
								}
								$sql = "SELECT rating FROM rating WHERE userID=" . $userID . " AND postID=" . $postID . $typeWhere;
								$rs = mysql_query($sql) or die(mysql_error());
								if (mysql_num_rows($rs))
								{
									// UPDATE
									if ($unrate)
									{
										$sql = "DELETE FROM rating WHERE postID=" . $postID . " AND userID=" . $userID . " AND rating='" . $rating . "'" . $typeWhere;
									}
									else
									{
										$sql = "UPDATE rating SET rating='".$rating."', timestamp='".$timestamp."' WHERE postID=" . $postID . " AND userID=" . $userID . $typeWhere;
									}
								}
								else
								{
									// INSERT
									$sql = "INSERT rating (postID,userID,rating,timestamp,type) VALUES (".$postID.",".$userID.",'".$rating."','".$timestamp."','".$type."')";
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
							$obj = $this->getPostList("user.id=".$id);
							foreach ($obj as $k => $o)
							{
								if ($o["postid"]>0 && $o["type"]!="poll")
								{
									$parent = $this->getPostList('post.id=' . $o["postid"],'timestamp DESC','','post');
									if ($parent)
									{
										$obj[$k]["parent"]=$parent[0];
									}
								}
							}
							$this->smarty->assign("user", $this->user);
							$this->smarty->assign("list",$obj);
							$this->smarty->assign("profileUser", $profileUser);
							$this->smarty->assign("contents", $this->smarty->fetch("profile.tpl"));

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
									$this->smarty->assign("loginError","Anmeldung fehlgeschlagen!");
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
										$this->smarty->assign("registerError", "Die Passw&ouml;rter stimmen nicht &uuml;berein.");
									}
									else
									{
										$sql = "SELECT COUNT(id) FROM user WHERE email='".$_POST["email"]."'";
										$rs = mysql_query($sql) or die(mysql_error());
										$rs = mysql_fetch_array($rs);
										if ($rs[0]>0)
										{
											//e-mail bereits registrier.
											$this->smarty->assign("registerError", "Diese E-Mail-Adresse wurde bereits registriert.");
										}
										else
										{
											$nickname = addslashes(strip_tags($_POST["username"]));

											$color = "#666666";
											if (isset($_COOKIE["styleColor"]) && preg_match('/^#[a-f0-9]{6}$/i', $_COOKIE["styleColor"]))
											{
												$color = $_COOKIE["styleColor"];
											}
											
											$sql = "INSERT INTO user (email,active,color,name) VALUES ('".$_POST["email"]."',1,'".$color."','".$nickname."')";
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
												$this->smarty->assign("user", $_SESSION["user"]);
												$this->smarty->assign("contents", $this->smarty->fetch("register/success.tpl"));
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
								$this->smarty->assign("user", $_SESSION["user"]);
								$this->smarty->assign("contents", $this->smarty->fetch("settings.tpl"));
								$this->smarty->assign("settingsActive", "active");
							}

							break;
						case "ajaxPost":
							if (isset($_POST["message"]))
							{
								$this->saveStat();
								$caption ="";
								$message = $this->config->convertToDatabase($_POST["message"]);
								if (isset($_POST["caption"]))
								{
									$caption = $this->config->convertToDatabase($_POST["caption"]);
								}

								$sql = 'INSERT INTO post (postID,userID,caption,message,timestamp) VALUES (0,'.$this->user["id"].',"'.$caption.'","'.$message.'","'.date("Y-m-d H:i:s").'")';
								mysql_query($sql);
								$id = mysql_insert_id();
								$getPost = true;
								$newPost = true;
								//die ("".$id);
							}
							if (isset($_POST['question']))
							{
								$question=$this->config->convertToDatabase($_POST['question']);
								$description = $this->config->convertToDatabase($_POST['description']);
								$answers = $this->config->convertToDatabase($_POST['answer1']);
								$i=2;
								while (isset($_POST['answer' . $i]))
								{
									$answers .= ';'.$this->config->convertToDatabase($_POST['answer' . $i]);
									$i++;
								}
								$sql = 'INSERT INTO poll(userID,question,text,answers,timestamp) VALUES ('. $this->user['id'].',"' . $question . '","' . $description . '","' . $answers.'","'.date("Y-m-d H:i:s") . '")';

								mysql_query($sql) or die(mysql_error());
								$id = mysql_insert_id();
								$getPost=true;
								$newPost=true;
								$getPoll=true;
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
										$type="";
										if (isset($_POST["type"]) && $_POST["type"] == "poll")
										{
											$type="poll";
										}
										$userID=0;
										if ($this->user)
										{
											$userID = (int)$this->user["id"];
										}
										$sql = "INSERT INTO post (postID, message, userID, timestamp,type) VALUES (" . $parentID . ",'".$message."',".$userID.",'".date("Y-m-d H:i:s")."','".$type."')";
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
									$type = "";
									if (isset($_POST['type']) && $_POST['type']=="poll")
									{
										$type = $_POST['type'];
									}
									else
									{
										$type="post";
									}
									$obj = $this->getPostList($id, "timestamp ASC",'',$type);
									$this->smarty->assign("list", $obj);
									$this->smarty->assign("user",$this->user);
									die($this->smarty->fetch("comment.tpl"));
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
									$obj = $this->getPostList((int)$parent,'timestamp DESC' , $limit);
									$this->smarty->assign("user",$this->user);
									$this->smarty->assign("list",$obj);
									die($this->smarty->fetch("post/list.tpl"));
								}
								else
								{
									
									$obj = $this->getPostList(0,'timestamp DESC');
									$this->smarty->assign("user",$this->user);
									$this->smarty->assign("list",$obj);
									$this->smarty->assign("postActive","active");
									$this->smarty->assign("TPL_POSTS", $this->smarty->fetch("post/list.tpl"));
									$this->smarty->assign("contents", $this->smarty->fetch("form.tpl"));
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
									$this->smarty->assign("withFrame", true);
								}
								$obj = $this->getPostList("post.id=" . $id,'timestamp DESC ', '','post');
								if (isset($obj["caption"]) || isset($parentID))
								{
									$this->smarty->assign("withCaption", true);
								}
								if (count($obj)>1)
								{
									//$this->smarty->assign("item")
								}	
								if (isset($obj[0]))
								{
									$this->smarty->assign("item", $obj[0]);
									$this->smarty->assign("user", $this->user);
									die($this->smarty->fetch("post/post.tpl"));
								}
								else
								{
									die ($this->smarty->fetch("post/postDeleted.tpl"));
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
							$this->smarty->assign("user",$this->user);
							$sql = "SELECT count(id) FROM post WHERE postID=0 AND deleted=0";
							$rs = mysql_query($sql) or die(mysql_error());
							$postCount = mysql_fetch_array($rs);
							$postCount = $postCount[0];
							$this->smarty->assign("postActive","active");

							$this->smarty->assign("contents", $this->smarty->fetch("form.tpl"));
							$this->smarty->assign("postsActive", "active");
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
								$userList = array();
								while ($row=mysql_fetch_assoc($rs)) {

									//echo $row["online"];
									$row["readableOnline"]=$this->util->makeDateReadable($row["online"], true);
									$row["online"]=strtotime($row["online"]);
									$userList[]=$row;
								}
								$this->smarty->assign("userList", $userList);
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
					if (isset($this->user['color']))
					{
						$this->smarty->assign("styleColor", $this->user['color']);
					}
					else
					{
						$this->smarty->assign("styleColor", "");
					}
					$this->smarty->assign("loggedIn", (int)$loggedIn);
					$this->smarty->assign("user", $this->user);
					$this->smarty->assign("pageTitle", $pageTitle);
					$base = "http://" . $_SERVER["HTTP_HOST"];
					$self= explode("/", $_SERVER["PHP_SELF"]);
					unset($self[count($self)-1]);
					$self = implode("/", $self);
					$base = $base . $self . "/";
					$this->smarty->assign("basePath", $base);
					$this->getRandomSlogan();
					$this->smarty->display("main.tpl");
					$this->saveStat();
		}
	}
	function getRandomSlogan()
	{						
		$obj = $this->getPostList("tag.name='slogan'","RAND()","1","both");
		if ($obj)
		{
			$this->smarty->assign("slogan", $obj[0]);
			return $this->smarty->fetch("slogan.tpl");
		}
	}
	
	function getPostList($sqlWhere='',$order="timestamp DESC", $limit='', $type='')
	{
			$sqlLimit = "";
			if ($limit)
			{
				$sqlLimit = " LIMIT " . $limit;
			}
			$postWhere="";
			$pollWhere = "";
			$withPoll=true;
			$withPost=true;
			if ($type=='post')
			{
				$withPoll=false;
			}
			if ($type=='poll')
			{
				$withPost=false;
			}
			//echo $sqlWhere;
			if (is_int($sqlWhere))
			{
				$postWhere = " AND post.postID=" . $sqlWhere;
				
				if ($type=='poll')
				{
					$postWhere .= " AND post.type='poll' ";
					$withPost=true;
				}
				if ($type=='post')
				{
					$postWhere .= " AND post.type='' ";
				}
				$pollWhere = "";
				$withPoll=!((int)$sqlWhere);
			}
			else
			{
				if ($sqlWhere)
				{
					$postWhere = " AND " . $sqlWhere . " ";
					$pollWhere = " AND " . $sqlWhere . " ";
				}
			}
			$sql="";
			if ($withPost)
			{
				$sql =	"SELECT post.id as id, post.postID as postid, post.caption as caption, post.message as message, post.timestamp as `timestamp`,post.message as answers, '0' as poll,user.name as username, user.id as userid, user.hasImage as hasImage,post.type as pType " . 
						"FROM post " . 
							"LEFT JOIN user ON user.id=post.userid " . 
							"LEFT JOIN post_tag as pt ON post.id=pt.parentID AND pt.type='' " . 
							"LEFT JOIN tag ON pt.tagID=tag.id " . 
							"WHERE post.deleted=0 " . $postWhere ;
			}
			if ($withPoll)
			{
				if ($withPost)	$sql .= " UNION ";
				
				$sql .="SELECT poll.id as id, '0' as postid, poll.question as caption, poll.text as message, poll.timestamp as `timestamp`, poll.answers as answers, '1' as poll, user.name as username, user.id as userid, user.hasImage as hasImage, '' as pType " . 
							"FROM poll " . 
							"LEFT JOIN user ON user.id=poll.userid " . 
							"LEFT JOIN post_tag as pt ON poll.id=pt.parentID AND pt.type='poll' " . 
							"LEFT JOIN tag ON pt.tagID=tag.id " . 
							"WHERE 1=1 " . $pollWhere;
						
			}
			$sql .= " GROUP BY id,poll ORDER BY " . $order . $sqlLimit;
			//echo "<br><br>" . $sql . "<br><br>";
			$rs = mysql_query($sql) or die(mysql_error());
			$obj = array();
			while ($row = mysql_fetch_assoc($rs))
			{
				if ($row['poll']==1)
				{
					$row['type']='poll';
				}
				else
				{
					$row['type']='';
				}
				if ($row['type']=='poll')
				{
					$userVote=-1;
					if ($this->user)
					{
						$sql = "SELECT vote FROM poll_vote WHERE userID=". $this->user['id'] . " AND pollID=" . (int)$row['id'];
						$rsU = mysql_query($sql);
						if (mysql_num_rows($rsU) > 0)
						{
							$userVote = mysql_fetch_array($rsU);
							$userVote = $userVote[0];
							$row['isVoted'] = 1;
						}
						
					}
					//all votes
					$sql = "SELECT COUNT(*) as count, vote as vote FROM poll_vote WHERE pollID=" . (int)$row['id'] . " GROUP BY vote ORDER BY vote";
					$rsV = mysql_query($sql) or die(mysql_error());
					$votes = array();
					$totalVotes = 0;
					while ($rowV = mysql_fetch_assoc($rsV))
					{
						$totalVotes += $rowV['count'];
						$votes[$rowV['vote']] = $rowV['count'];
					}
					$answers = explode(";",$row['answers']);
					//print_r($answers);
					
					$isVoted=false;
					foreach ($answers as $i => $a)
					{
						$row['answer'][$i]["text"] = $a;
						if (isset($votes[$i]))
						{
							$row['answer'][$i]["vote"] = $votes[$i];
							$row['answer'][$i]["percent"] = (int)($votes[$i]/$totalVotes*100) . " %";
						}
						else
						{
							$row['answer'][$i]["vote"] = 0;
							$row['answer'][$i]["percent"] = "0 %";
						}
						if ($i == $userVote)
						{
							$isVoted = true;
							$row['answer'][$i]["uservote"] = true;
							$row['uservote']=$i;
						}
						else
						{
							$row['answer'][$i]["userVote"] = false;
						}
					}
					
					
					//print_r($row['answer']);
				}
				
				if ($this->user)
				{	// with myRating
					$sql = "SELECT SUM(r.rating='like') as `like`, SUM(r.rating='dislike') as `dislike`, myRating.rating as myRating FROM rating r LEFT JOIN rating myRating ON myRating.postID=".$row["id"]. " AND myRating.`type`='".$row['type']."' AND myRating.userID=".$this->user["id"]." WHERE r.postID=" . $row["id"] . " AND r.`type`='".$row['type']. "' GROUP BY r.postID";
				}
				else
				{
					//without myRating
					$sql = "SELECT SUM(r.rating='like') as `like`, SUM(r.rating='dislike') as `dislike` FROM rating r WHERE r.postID=" . $row["id"] . " AND r.`type`='".$row['type']."' GROUP BY r.postID";
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
					$percent = (int)($row["like"]/$sum*100);
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
				// tags 
				$sql = 'SELECT tag.name as name FROM post_tag LEFT JOIN tag ON post_tag.tagID=tag.id WHERE parentID=' . $row['id'] . " AND `type`='".$row['type']."' ORDER BY name";
				$rsTag = mysql_query($sql) or die(mysql_error());
				while ($rowTag = mysql_fetch_assoc($rsTag))
				{
					$row["tags"][]=$this->config->convertFromDatabase($rowTag['name']);
				}
				$row["caption"]=$this->config->convertFromDatabase($row["caption"]);

				$sql = "SELECT count(*) FROM post WHERE postID=" . $row['id'] . " AND type='".$row["type"]."' AND deleted=0";
				$rsComments = mysql_query($sql) or die(mysql_error());
				$rowComments = mysql_fetch_array($rsComments);

				$row["comments"]=$rowComments[0];
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
	/*function getPostList($sqlWhere="", $sqlOrder="timestamp DESC", $limit="",$type="")
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
		$typeWhere = " AND post.type='' ";
		//$tagTypeWhere = " AND pt.type='' ";
		if ($type=="poll")
		{
			$typeWhere = " AND post.type='poll' ";
			//$tagTypeWhere = " AND pt.type='poll' ";
		}
		if ($type == "both")
		{
			$typeWhere = " ";
		}
		if ($sqlWhere)
		{
			$sql =	"SELECT post.id as id,post.postID as postid,post.caption as caption,post.timestamp as timestamp,post.type as `type`, user.name as username,user.id as userid,user.hasImage as hasImage,post.message as message " . 
					"FROM post " .
							"LEFT JOIN user ON user.id = post.userID ".
							"LEFT JOIN post_tag as pt ON post.id=pt.parentID " . 
							"LEFT JOIN tag ON pt.tagID=tag.id " . 
					"WHERE post.deleted=0 AND ".$sqlWhere." " . $typeWhere .
					"GROUP BY post.id " . $sqlOrder . $sqlLimit;
		}
		else
		{
			$sql = "SELECT post.id as id,post.postID as postid, post.caption as caption,post.timestamp as timestamp,post.type as `type`,user.name as username,user.id as userid,user.hasImage as hasImage,post.message as message " . 
					"FROM post ". 
							"LEFT JOIN user ON user.id = post.userID " . 
							"LEFT JOIN post_tag as pt ON post.id=pt.parentID " . 
							"LEFT JOIN tag as tag ON pt.tagID=tag.id " . 
					"WHERE post.postID=0 AND post.deleted=0 ".$typeWhere." GROUP BY post.id " . $sqlOrder . $sqlLimit;
		}
		$obj = array();
		$rs = mysql_query($sql) or die(mysql_error());
		
		while($row = mysql_fetch_assoc($rs))
		{
			$typeWhere = " AND r.type='' ";
			if ($type=="poll")
			{
				$typeWhere = " AND r.type='poll' ";
			}
			if ($type == "both")
			{
				$typeWhere = " ";
			}
			if ($this->user)
			{	// with myRating
				$sql = "SELECT SUM(r.rating='like') as `like`, SUM(r.rating='dislike') as `dislike`, myRating.rating as myRating FROM rating r LEFT JOIN rating myRating ON myRating.postID=".$row["id"]. " AND myRating.userID=".$this->user["id"]." WHERE r.postID=" . $row["id"] . $typeWhere. " GROUP BY r.postID";
			}
			else
			{
				//without myRating
				$sql = "SELECT SUM(r.rating='like') as `like`, SUM(r.rating='dislike') as `dislike` FROM rating r WHERE r.postID=" . $row["id"] . $typeWhere . " GROUP BY r.postID";
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
				$percent = (int)($row["like"]/$sum*100);
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
			// tags 
			$sql = 'SELECT tag.name as name FROM post_tag LEFT JOIN tag ON post_tag.tagID=tag.id WHERE parentID=' . $row['id'] . " AND `type`='' ORDER BY name";
			$rsTag = mysql_query($sql) or die(mysql_error());
			while ($rowTag = mysql_fetch_assoc($rsTag))
			{
				$row["tags"][]=$this->config->convertFromDatabase($rowTag['name']);
			}
			$row["caption"]=$this->config->convertFromDatabase($row["caption"]);
			
			$sql = "SELECT count(*) FROM post WHERE postID=" . $row['id'] . " AND type='".$type."' AND deleted=0";
			$rsComments = mysql_query($sql) or die(mysql_error());
			$rowComments = mysql_fetch_array($rsComments);
			
			$row["comments"]=$rowComments[0];
			$row["percent"]=$percent;
			$row["votingBarWidth"]=$votingBarWidth;
			$row["date"]=$this->util->makeDateReadable($row["timestamp"],true);
			$row["message"]=$this->config->convertFromDatabase($row["message"]);
			$row["messageReadable"] = $this->util->makeLinks($row["message"]);
			$row["message"]=urlencode($row["message"]);
			$obj[]=$row;
		}
		return $obj;
	}*/
	
	function saveStat()
	{
		$userId = 0;
		if (isset($this->user['id']))
		{
			$userId = (int)$this->user['id'];
		}
		$request = "";
		if (isset($_GET))
		{
			foreach ($_GET as $g => $i)
			{
				$request.= $g ." => " . $i . ";";
			}

		}
		if (isset($_POST))
		{
			foreach ($_POST as $g => $i)
			{
				$request.= $g ." => " . $i . ";";
			}
		}
		$ip = $_SERVER['REMOTE_ADDR'];
		$referrer = "";
		if (isset($_SERVER['HTTP_REFERER']))
		{
			$referrer = $_SERVER['HTTP_REFERER'];
		}
		$ua = "";
		if (isset($_SERVER['HTTP_USER_AGENT']))
		{
			$ua = mysql_real_escape_string($_SERVER['HTTP_USER_AGENT']);
		}
		$sql = "INSERT INTO stat (userid,timestamp,ip,ua,referrer,request) VALUES(" .$userId.",'".date("Y-m-d H:i:s")."','".$ip."','". $ua . "','" .$referrer."','".$request."')";
		mysql_query($sql);
	}
}
?>