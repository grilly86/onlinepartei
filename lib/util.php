<?php
	include_once("static/jsonwrapper/jsonwrapper.php");
	include_once("static/markdown.php");
	
	define("ALLOWED_TAGS", "<a><b><p><strong><em><h1><h2><h3><h4><h5><h6><br><img><div><span><code><blockquote><pre><table><tr><td><th>");
	
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
		
		$htmlText = $this->convertPost($htmlText);	
		$htmlText = Markdown($htmlText);
		$htmlText = strip_tags($htmlText,ALLOWED_TAGS);
		return $htmlText;
	}

	function convertPost($post)
	{ // Disclaimer: This "URL plucking" regex is far from ideal.
		$pattern = '`(?<!\(|\<)((?:https?|ftp)://\S+[[:alnum:]]/?)(?!\)|\>)`si';
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
	} 
	// Otherwise handle as NOT an image.
	if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $matches[0], $xMatches))
	{	
		$type= 'youtube';
		$videoID = $xMatches[1];
		$oembed = getOembed("http://www.youtube.com/oembed?url=http%3A//www.youtube.com/watch%3Fv%3D".$videoID."&format=json");
		$title = "";
		if ($oembed) $title = $oembed->title;
		$html = '<div class="videoLink" style="background:url(http://img.youtube.com/vi/'.$xMatches[1].'/default.jpg) no-repeat left;"><a target="_blank" href="'.$matches[0].'"><span class="videoIcon"></span><strong>'.$title . "</strong><br>".$matches[0].'</a></div>';
		return $html; //"<a class='youtube' href='http://www.youtube.com/watch/?v=" . $xMatches[0] . "'>" . $xMatches[0] . "</a>";
	}
	if (preg_match_all('#(http://vimeo.com)/([0-9]+)#i',$matches[0],$output))
	{
		$type='vimeo';
		$videoID=$output[2][0];
		
		$oembed = getOembed("vimeo.com/api/v2/video/" . $videoID . ".json");
		$thumb = $oembed->thumbnail_small;
		$title = $oembed->title;
		if (!$title) $title = $this->langArr["noTitle"];
		$html = '<div class="videoLink" style="background:url('.$thumb.') no-repeat left;"><a target="_blank" href="'.$matches[0].'"><span class="videoIcon"></span><strong>'.$title . "</strong><br>".$matches[0].'</a></div>';
		return $html;
	}
	
	// otherwise handle as normal LINK
	return '<a target="_blank" class="styleColor" href="'. $matches[0] .'">'. $matches[0] .'</a>';
}

function getOembed($url)
{
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$data = curl_exec($ch);
	curl_close($ch);
	return(json_decode($data));
}