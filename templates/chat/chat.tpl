<div class="chatContainer styleColorBorder" id="chat_{$user.id}">
	<div class="chatTitleContainer styleColorBackground">
		{if $user.name}{$user.name}{else}{$lang.noUsername}{/if}
		<a href="javascript:void(0);" class="chatButton close tooltipRight" title="{$lang.closeChat}"></a>
		{*<a href="javascript:void(0);" class="chatButton minify"></a>*}
	</div>
	<div class="messageContainer" id="messageContainer{$user.id}">
		<img class="loader" src="static/img/loader.gif" />
	</div>
	<textarea class="chatAnswer" name="message" title="{$lang.holdShiftToBreak_nolink}" noresize></textarea>
	<span class="resizeHandle"></span>
</div>