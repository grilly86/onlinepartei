<?php

	class Util
	{
	var $langArr = array();
	var $dayOfWeekArray=array();
	var $monthArray=array();

	function Util($lang)
	{
		if (!$lang) die("Util without language!");
		$this->langArr = $lang->languageArray;
		$this->dayOfWeekArray=$lang->getDayOfWeekArray();
		$this->monthArray = $lang->getMonthArray();
	}
	function makeDateReadable($timestamp,$withTime=false, $withTimeCaption=true)
	{
		// @todo: get from language files!
		$date=strtotime($timestamp);
		$day =date("Y-m-d",$date);
		if (date("Y-m-d",$date) == date("Y-m-d"))
		{
			$dayCaption = $this->langArr["today"];
		}
		elseif (date("Y-m-d",$date) == date('Y-m-d', time() - 86400))
		{
			$dayCaption = $this->langArr["yesterday"];
		}
		else
		{
			if ($withTime)
			{
				$dayCaption = $this->langArr["dateOn"]." " . $this->getDayCaption(date("w", $date)) . ", " . date("j",$date) . ". ". $this->getMonthCaption(date("n",$date)) ." " . date("Y",$date);
			}
			else
			{
				$dayCaption = $this->getDayCaption(date("w", $date)) . ", " . date("j",$date) . ". ". $this->getMonthCaption(date("n",$date)) ." " . date("Y",$date);
			}
		}
		if ($withTime)
		{
			$timeCaption="";
			if ($withTimeCaption)
			{
				$timeCaption = " " .  $this->langArr["dateOclock"];
			}
			return $dayCaption . " ".$this->langArr["dateAt"]." " . date("H:i",$date) . $timeCaption;
		}
		else
		{
			return $dayCaption;
		}
	}

	function makeLinks ($htmlText) {

		$htmlText = str_replace("http://www.","www.",$htmlText);
		$htmlText = str_replace("www.","http://www.",$htmlText);
		$htmlText = $this->convertPost($htmlText);
		$htmlText = nl2br($htmlText);
		return $htmlText;
	}

	function convertPost($post)
	{ // Disclaimer: This "URL plucking" regex is far from ideal.
		$pattern = '`((?:https?|ftp)://\S+[[:alnum:]]/?)`si';
		$pattern = '`((?:https?|ftp)://\S+[[:alnum:]]/?)`si';
		$replace='_handle_URL_callback';
		return preg_replace_callback($pattern,$replace, $post);
	}

	function getDayCaption($dayOfWeek)
	{
		return $this->dayOfWeekArray[$dayOfWeek];
	}
	function getMonthCaption($m)
	{
		return $this->monthArray[$m-1];		 
	}
}
function _handle_URL_callback($matches)
{ // preg_replace_callback() is passed one parameter: $matches.
	if (preg_match('/\.(?:jpe?g|png|gif)(?:$|[?#])/', $matches[0]))
	{ // This is an image if path ends in .GIF, .PNG, .JPG or .JPEG.
		
		return '<div class="imageWrapperContainer"><a class="fancybox" target="_blank" href="' . $matches[0] . '"><img class="thumbnail" ref="'. $matches[0] .'"></a></div>';
	} // Otherwise handle as NOT an image.
	if(preg_match('/http:\/\/www\.youtube\.com\/watch\?v=[^&]+/', $matches[0], $xMatches)) {
	$type= 'youtube';
	if (preg_match("#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $matches[0], $xMatches))
		{
			$videoID = $xMatches[0];
			$html = '<div class="videoLink" style="background:url(http://img.youtube.com/vi/'.$xMatches[0].'/default.jpg) no-repeat left;"><a target="_blank" href="'.$matches[0].'"><span class="videoIcon"></span>'.$matches[0].'</a></div>';
			return $html; //"<a class='youtube' href='http://www.youtube.com/watch/?v=" . $xMatches[0] . "'>" . $xMatches[0] . "</a>";
		}
	}
	return '<a target="_blank" href="'. $matches[0] .'">'. $matches[0] .'</a>';
		
	}	
	?>
