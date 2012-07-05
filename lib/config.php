<?php

define("SALT", "s4lt");

class Config
{
	
	var $mysqlServer="";
	var $mysqlDatabase="";
	var $mysqlUser="";
	var $mysqlPassword="";

	function Config()
	{
		$this->mysqlServer="localhost";
		$this->mysqlDatabase="op";
		$this->mysqlUser="root";
		$this->mysqlPassword="";
	}

	function connect()
	{
		$error=false;
		$conn = mysql_connect($this->mysqlServer, $this->mysqlUser, $this->mysqlPassword);
		mysql_select_db($this->mysqlDatabase) or $error=true;
		if ($error)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	function getServer()
	{
		return $this->mysqlServer;
	}
	function getDatabase()
	{
		return $this->mysqlDatabase;
	}
	function getUser()
	{
		return $this->mysqlUser;
	}
	function getPassword()
	{
		return $this->mysqlPassword;
	}
	function convertToDatabase($str)
	{
		$str = str_replace(array("<",">"),array("&lt;","&gt;"),$str);
		$str = utf8_decode(addslashes($str));
		return $str;
	}
	function convertFromDatabase($str)
	{
		$str = urldecode(utf8_encode($str));
		$str = str_replace(array("<",">"),array("&lt;","&gt;"),$str);
		return stripslashes($str);
	}

}
?>
