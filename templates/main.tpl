<!DOCTYPE html>
<html>
	<head>	
		<base href="{$basePath}">
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		{if $loggedIn}
		<meta http-equiv="content-language" content="{$user.language}">
		{else}
		<meta http-equiv="content-language" content="de">
		{/if}
		<meta property="og:title" content="{$pageTitle}" />  
		<meta property="og:description" content="{$pageDescription}" />  
		<meta property="og:url" content="{$pageUrl}" /> 
		<meta property="og:image" content="http://www.onlinepartei.eu/static/img/logo.png" /> 
		<link rel="image_src" href="http://www.onlinepartei.eu/static/img/logo.png" />  
		<link rel="icon" href="http://www.onlinepartei.eu/favicon.ico" type="image/x-icon">
		<link rel="shortcut" href="http://www.onlinepartei.eu/favicon.ico" type="image/x-icon">
		<link rel="stylesheet" href="static/script/fancybox/jquery.fancybox.css" type="text/css" media="screen" />
		<link rel="stylesheet" type="text/css" href="style.css"></link>
		<script type="text/javascript" src="static/script/raphael.js" ></script>
		<script type="text/javascript" src="static/script/jquery-1.7.1.min.js" ></script>
		<script type="text/javascript" src="static/script/jquery.cookie.js" ></script>
		<script type="text/javascript" src="static/script/jquery.easing-1.3.pack.js" ></script>
		<script type="text/javascript" src="static/script/jquery.qtip-1.0.0-rc3.min.js" ></script>
		<script type="text/javascript" src="static/script/jquery.jplayer.min.js" ></script>
		<script type="text/javascript" src="static/script/jquery.validate.min.js" ></script>
		<script type="text/javascript" src="static/script/socialshareprivacy/jquery.socialshareprivacy.min.js"></script>
		<script type="text/javascript" src="static/script/fancybox/jquery.fancybox.pack.js"></script>
		<script type="text/javascript" src="static/script/functions.js"></script>
		
		<title>{$pageTitle}</title>
	</head>
	<body>
		<div id="contextMenuContainer"></div>
		<div id="soundPlayer"></div>
		<div class="menuContainer styleColorBackground">
			<a href="./" class="logo qtipTitle tooltipLeft" title="onlinepartei.eu"></a>
			<span class="headerText">
				<h2>onlinepartei.eu</h2>
				Beta<br>
				{$lang.pageSubTitle}
			</span>
			{if $slogan}
			{include file='slogan.tpl'}
			{/if}
				{if $loggedIn}
					<div class="menuUserItem">
						{if $user.hasImage}
						<img class="profileImage" src="uploads/p/{$user.id}.jpg" alt="" height="36"/>
						{/if}
						<a class="username" href="u{$user.id}">{$user.name}</a>
						<a href="logout" class="titleButton logout qtipTitle tooltipRight styleColorBackground" title="{$lang.logout}"></a>
					</div>
				<a href="settings" class="menuButton settings qtipTitle {$settingsActive}" title="{$lang.settings}"></a>
				{/if}
				
				{if !$loggedIn}
				<div style="float:right;position:relative;margin-right:24px;">
					{$styleColor}
					<input id="colorPickerInput" value="{$styleColor}" style="background:{$styleColor}" /><span class="colorPickerDice"></span>
					<div id="colorPicker" style="margin-top:30px;margin-left:-50px;"></div>
				</div>
				{literal}
					<script type="text/javascript" src="static/script/farbtastic/farbtastic.js"></script>
					<script type="text/javascript">
						$().ready(function() {
							styleColor = colorToHex($(".menuContainer").css("background-color"));
							if ($.cookie("styleColor"))
							{
								styleColor = $.cookie("styleColor");
							}
							$("#colorPickerInput").keyup(function(e) {
								if (e.keyCode == 13 || e.keyCode == 27)
								{
									e.preventDefault();
									$(this).blur();
								}
								var x = /^#[0-9a-f]{6}$/i.exec(this.value);
								if (x)
								{
									if (x[0] != styleColor)
									{
										styleColor = x[0];
										colorPicker.setColor(styleColor);
									}
								}
							});
							$("#colorPickerInput").val(styleColor).css("background-color",styleColor);
							var colorPicker = $.farbtastic('#colorPicker');
								colorPicker.linkTo(pickerUpdate);
								colorPicker.setColor(styleColor);
									function pickerUpdate(color)
									{
										$('#colorPickerInput').css({'background-color':color}).val(color);
										setStyleColor(color);
									}
								$("#colorPickerInput").focus(function() {
									$("#colorPicker").fadeIn();
								}).blur(function() {
									$("#colorPicker").fadeOut();
									var x = /^#[0-9a-f]{6}$/i.exec(this.value);
									if (!x)	{
										this.value = styleColor;
									}
									$.cookie("styleColor", this.value);
								});
								$(".colorPickerDice").click(function() {
									color = generateRandomColor();
									$("#colorPickerInput").css({'background-color':color}).val(color);
									setStyleColor(color);
									$.cookie('styleColor', '', { expires: -1 })
									e.preventDefault();
								});
						});
					</script>
				{/literal}
				{/if}
				<a href="polls" class="menuButton polls qtipTitle {$pollsActive}" title="{$lang.polls}"></a>
				<a href="./" class="menuButton posts qtipTitle {$postsActive}" title="{$lang.posts}"></a>
		</div>
		<div class="sidebarContainer">
		{if $loggedIn}
			<div class="loginFormContainer styleColorBorder">
			<div class="titleContainer styleColorBackground">
				<span class="mailIcon"></span>
				{*<select name="userList" class="selectUserList">
					<option value="0" selected="selected">Alle</option>
				</select>*}
				{$lang.all}
			</div>
			<div id="chatUserList">
				{foreach from=$userList item=item}
				<a class="chatLink" id="chatLink_{$item.id}" href="u{$item.id}" title="{$lang.online} {$item.readableOnline}">
						<span class="onlineState {if ($item.online>$smarty.now-30)}online{elseif ($item.online>$smarty.now-60*2)}idle{/if}"></span>
						<span class="name">{if $item.name}{$item.name}{else}{$lang.noUsername}{/if}</span>
						{if $item.hasImage}
							<img class="profileImage" src="uploads/p/{$item.id}.jpg" width="27" height="27">
						{/if}
						
					</a>
				{/foreach}
			</div>
			<div id="connectionError">
				{$lang.connectionError}
			</div>
		</div>
		<button class="soundButton on styleColorBackground" title="{$lang.muteSound}"></button>
		
		{else}
			{include file='login.tpl' }
		{/if}
		<br />
		<a href="impressum.html" class="styleColor" style="float:right">{$lang.imprint}</a>
		<a href="http://www.github.com/grilly86/onlinepartei/" class="styleColor"  target="_blank" style="float:right;margin-right:10px;">{$lang.getSourceCode}</a>
				
		</div>
		<div id="sessionError">
				<div class="sessionErrorWrapper"><a class="chatButton close" href="./"></a>
					{$lang.sessionError}
					<br clear="all" />
					<div class="loginFormContainer styleColorBorder" style="float:left;margin-top:10px;">
					<div class="titleContainer styleColorBackground"><span class="loginIcon"></span>{$lang.login}</div>
					<form id="loginForm" class="loginForm" method="post" action="index.php?task=login">
						<div id="loginErrorContainer" class="errorContainer">
						{if $loginError}
							{$loginError}
						{/if}
						</div>
						<label for="username">{$lang.email}:</label><input class="text" type="text" name="username" id="loginUsername"><br clear="all" />
						<label for="password">{$lang.password}:</label><input class="text" type="password" name="password" id="loginPassword">
						<input type="submit" class="submit opButton" value="{$lang.login}">
					</form>
					
				</div>
			</div>
		</div>
		{$contents}
		<div id="chatWindows">
			{$chats}
		</div>
		{* LANGUAGE ARRAY FOR JAVASCRIPT *}
		<script type="text/javascript">
				var lang=new Array();
{foreach from=$lang item=l key=k}
				lang['{$k}']='{$l}';
{/foreach}
		var styleColor = '{$styleColor}';
		</script>
	</body>
</html>