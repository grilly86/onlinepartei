<div class="postContainer active" rel="{if $item.type=='poll'}poll{else}post{/if}_{$item.id}">
	{include file='post/postStamp.tpl'}
	{if $item.type=='poll'}
		{include file='post/poll.tpl'}
	{else}
		{if $item.caption}<h2><a href='p{$item.id}'>{$item.caption}</a></h2>{/if}
		<div class="postMessageContainer" rel="post_{$item.id}">
			<div class="message commentContainer {if ($item.userid==0||$item.userid==$user.id)}edit{/if}" rel="post_{$item.id}" ref="{$item.message}">
			{$item.messageReadable}
			</div>
			{include file='post/postFooter.tpl'}
		</div>
	{/if}
	</div>