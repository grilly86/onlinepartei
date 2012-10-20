<div class="contentWrapperContainer">
{if $tag}
	<br clear="all" />
	<h1 class="tagline"><span class="tagicon"></span>{$lang.taggedWith} &raquo;<span class="tagged">{$tag}</span>&laquo;</h1>
{/if}
{if $list}
{foreach from=$list item=item}
	{include file='post/post.tpl'}
{/foreach}
{else}
	<div class="postContainer" style="padding:10px;">
	{$lang.noPosts}
	</div>
{/if}

</div>