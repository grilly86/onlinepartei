{foreach from=$userList item=item}
	<a class="chatLink {if $item.new>0}new{/if}" id="chatLink_{$item.id}" href="u{$item.id}" title="{$lang.online} {$item.readableOnline}">
		<span class="onlineState {if ($item.online>$smarty.now-30)}online{elseif ($item.online>$smarty.now-60*2)}idle{/if}"></span>
		<span class="name">{if $item.name}{$item.name}{else}{$lang.noUsername}{/if}</span>
		{if $item.hasImage}
			<img class="profileImage" src="uploads/p/{$item.id}.jpg" width="27" height="27">
		{/if}
	</a>
{/foreach}