{foreach from=$chat item=item}
	{if $item.username != $prevUsername}
		{if $notFirst}</div>{/if}
		{assign var='notFirst' value=true}

	<div class="message {if $item.senderid==$user.id}me {else}you {/if}" id="message_{$item.id}">
		<div class="chatUsername styleColor {if $item.senderid==$user.id}me {else}you {/if}" style="float:{if $item.senderid==$user.id}left{else}right{/if}">
			{if $item.username}{$item.username}{else}{$lang.noUsername}{/if}<br>
			{if $item.senderHasImage}<img src="uploads/p/{$item.senderid}.jpg" />{/if}
		</div>
	{/if}	
			{*if $item.timestamp|date_format:"%Y-%m-%d"==$prevDate}
				<span class="chatTimestamp">{$item.timestamp|date_format:"%H:%M"}</span>
			{else}
				<span class="chatTimestamp">{$item.readableDate}</span>
			{/if*}
			{assign var ='prevDate' value=$item.timestamp|date_format:"%Y-%m-%d" }
			<span class="chatMessage" title="{$item.readableDate}">{$item.message}</span>
	{assign var='prevUsername' value=$item.username}
	{if $item.username != $prevUsername}
	</div>
	{/if}
{/foreach}
{if $notFirst}</div>{/if}
<br clear="all" />