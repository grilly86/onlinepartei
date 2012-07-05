<div class="contentWrapperContainer">
	<br clear="all" />
{if ($profileUser.hasImage && $profileUser.id>0)}
	<img class="profileImage profile" src="uploads/p/{$profileUser.id}.jpg" />
{else}
	<img class="profileImage profile" src="uploads/p/0.jpg" />
{/if}
{if $profileUser.id>0}
	<h1 class="profileHeadline">{$lang.profileOf} {$profileUser.username}</h1>
{else}
	<h1 class="profileHeadline">{$lang.postsOfGuests}</h1>
{/if}
	<br clear="all" />
{if $list}
{foreach from=$list item=item}
	{include file='post/post.tpl'}
{/foreach}
{else}
	{$lang.noPosts}
{/if}

</div>