<?php
class Language
{
	var $languageArray=array();
	var $monthArray=array();
	var $dayOfWeekArray=array();
	function Language($lang)
	{
		$filename = "lib/lang/" . $lang . ".php";
		if (file_exists($filename))
		{
			include_once $filename;
			
			
			$lang = new Lang();
			$this->languageArray = $lang->languageArray;
			$this->monthArray = $lang->monthArray;
			$this->dayOfWeekArray = $lang->dayOfWeekArray;
		}
		else
		{
			die ("Language file:".$filename. " not found.");
		}
	}
	
	function get($index)
	{
		if (array_key_exists($index, $this->languageArray))
		{
			return $this->languageArray[$index];
		}
		else
		{
			return false;
		}
	}
	function getMonthArray()
	{
		return $this->monthArray;
	}
	function getDayOfWeekArray()
	{
		return $this->dayOfWeekArray;
	}
	
	function toJSON()
	{
		return json_encode($this->languageArray);
	}
}
?>
