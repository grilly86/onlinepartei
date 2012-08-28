{if $list}
{foreach from=$list item=item}
<div class="commentWrapper" rel="post_{$item.id}">
	{include file='post/postStamp.tpl'}
	{if $item.caption}<h2><span class="discussIcon"></span>{$item.caption}</h2>{/if}
	<div class="commentContainer message {if ($item.userid==0||$item.userid==$user.id)}edit{/if}" rel="post_{$item.id}" ref="{$item.message}">
		{$item.messageReadable}
	</div>
	{include file='post/postFooter.tpl'}	
</div>
{/foreach}
{/if}
<textarea name="comment" class="messageComment empty">{$lang.commentBlank}</textarea>
<div class="notice">{if !$loggedIn}{$lang.anonymousComment}<br>{$lang.holdShiftToBreak}{else}{$lang.holdShiftToBreak}{/if}</div>