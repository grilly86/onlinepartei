<div class="postStamp">
	<a class="anchor" href="p{if $item.type=='poll'}oll{/if}{$item.id}">{$item.date}</a>
	{$lang.by}
	<a class="postUser" href="u{$item.userid}">
		<img src="uploads/p/{if $item.hasImage}{$item.userid}{else}0{/if}.jpg" class="profileImage post">
		{if $item.username}
			{$item.username}
		{else}
			{$lang.noUsername}
		{/if}
	</a>
	{if $item.parent}
	{if $item.parent.caption}
		{$lang.in} <a href="p{if $item.parent.type=='poll'}oll{/if}{$item.parent.id}">{$item.parent.caption}</a> {$lang.by} <a href="u{$item.parent.userid}">{$item.parent.username}</a>
	{else}
		{$lang.ina} <a href="p{if $item.parent.type=='poll'}oll{/if}{$item.parent.id}">{$lang.comment}</a> {$lang.by} <a href="u{$item.parent.userid}">{$item.parent.username}</a>
	{/if}
	{/if}
</div>