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
		<meta property="og:url" content="http://www.onlinepartei.eu/" /> 
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
		<script type="text/javascript" src="static/script/fancybox/jquery.fancybox.pack.js"></script>
		<script type="text/javascript" src="static/script/functions.js" ></script>
		
		<title>{$pageTitle}</title>
	</head>
	<body>
		<div id="contextMenuContainer"></div>
		<div id="soundPlayer"></div>
		<div class="menuContainer">
			<a href="./" class="logo qtipTitle tooltipLeft" title="onlinepartei.eu"></a>
			<span class="headerText">
				<h2>onlinepartei.eu</h2>
				SOCIAL DEMOCRATING PLATFORM<br>
				(under development)
			</span>   
			{if $slogan}
			{include file='slogan.tpl'}
			{/if}
					<a href="http://www.github.com/grilly86/onlinepartei/" style="margin-left:15px;color:#fff;font-weight:bold;font-size:8pt;display:inline-block;text-align:center"><img src="static/img/github.png" alt="Github" target="_blank" title="{$lang.sourcecode}"/><br>Quellcode herunterladen</a>
				{if $loggedIn}
					<div class="menuUserItem">
						{if $user.hasImage}
						<img class="profileImage" src="uploads/p/{$user.id}.jpg" alt="" height="36"/>
						{/if}
						<a class="username" href="u{$user.id}">{$user.name}</a>
						<a href="logout" class="titleButton logout qtipTitle tooltipRight" title="{$lang.logout}"></a>
					</div>
				<a href="settings" class="menuButton settings qtipTitle {$settingsActive}" title="{$lang.settings}"></a>
				{/if}
				<a href="polls" class="menuButton polls qtipTitle {$pollsActive}" title="{$lang.polls}"></a>
				<a href="./" class="menuButton posts qtipTitle {$postsActive}" title="{$lang.posts}"></a>
				
		</div>
		<div class="sidebarContainer">
		{if $loggedIn}
			<div class="loginFormContainer">
			<div class="titleContainer">
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
		</div>
		<button class="soundButton on" title="{$lang.muteSound}"></button>
		
		{else}
		<div style="float:right">
			<div class="loginFormContainer">
				<div class="titleContainer"><span class="loginIcon"></span>{$lang.login}</div>
				<form id="loginForm" class="loginForm" method="post" action="index.php?task=login">
					<div id="loginErrorContainer" class="errorContainer">
					{if $loginError}
						{$loginError}
					{/if}
					</div>
					<label for="username">{$lang.email}:</label><input class="text" type="text" name="username" id="loginUsername"><br clear="all" />
					<label for="password">{$lang.password}:</label><input class="text" type="password" name="password" id="loginPassword">
					<input type="submit" class="submit" value="{$lang.login}">
				</form>  
				<br clear="right"/>
				<br clear="right"/>
			</div>
			<div class="loginFormContainer">
				<div class="titleContainer"><span class="registerIcon"></span>{$lang.register}</div>
				<form id="registerForm" class="loginForm" method="post" action="index.php?task=register">
					<div id="registerErrorContainer" class="errorContainer">
					{if $registerError}
						{$registerError}
					{/if}
					</div>
					<label for="email">{$lang.email}:</label><input class="text" type="text" name="email" id="email"><br clear="all" />
					<label for="password">{$lang.password}:</label><input class="text" type="password" name="password" id="registerPassword"><br clear="all" />
					<label for="password" title="{$lang.repeatPassword}">{$lang.repeatPassword}:</label><input class="text" type="password" name="passwordRepeat" id="registerPasswordRepeat" />
					<label for="username" title="{$lang.nickname}">{$lang.nickname}:</label><input class="text" type="text" name="username" id="registerUsername" />
					<input type="submit" class="submit" value="{$lang.register}" />
				</form>
				{literal}
				<script type="text/javascript">
					$("#loginForm").validate({
						errorLabelContainer: "#loginErrorContainer",
						rules:{
							username:{
								required:true,
								email:true
							},
							password:{
								required:true,
								minlength:4
							}
						},
						messages:{
							username:"Bitte geben Sie die E-Mail-Adresse an mit der Sie sich registriert haben!",
							password:{
								required:"Bitte geben Sie ein Passwort ein!",
								minlength:"Das Passwort muss mindestens 5 Zeichen lang sein."
							}
						}
					});
					$("#registerForm").validate({
						errorLabelContainer: "#registerErrorContainer",
						rules:{
							email:{
								required:true,
								email:true
							},
							password:{
								required:true,
								minlength:5
							},
							passwordRepeat:{
								equalTo:"#registerPassword"
							},
							username: {
								required:true
							}
						},
						messages:{
							email:"Bitte geben Sie eine g&uuml;ltige E-Mail-Adresse an!",
							password:{
								required:"Bitte geben Sie ein Passwort ein!",
								minlength:"Das Passwort muss mindestens 5 Zeichen lang sein."
							},
							passwordRepeat:{
								equalTo:"Die Passw&ouml;rter stimmen nicht &uuml;berein."
							},
							username:"Bitte geben Sie einen Benutzernamen ein"
						}
					})

				</script>
				{/literal}
				<br clear="right" />
				
				<br clear="right" />
			</div>
		</div>
		{/if}
		<br />
		<a href="impressum.html">{$lang.imprint}</a>
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