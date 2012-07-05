<div class="postFooter">
	<a class="like {if $item.myRating=='like'}active{/if} {if !$loggedIn}fix{/if} styleColorBackground styleColorBorder" title="{if !$loggedIn}{$lang.logInToRate}{else}{$lang.like}{/if}" ref="{$item.like}"></a>
	<div class="votingContainer" title="{$item.dislike} {$lang.NrDislike} : {$item.like} {$lang.NrLike}">
		<span class="percent">{$item.percent} %</span>

		<span class="votingBar right" style="width:50px"></span>
		<span class="votingBar left {if $item.percent == '-' }deac{/if} styleColorBackground" style="width:{$item.votingBarWidth}px"></span>
	</div>
	<a class="dislike {if $item.myRating=='dislike'}active{/if} {if !$loggedIn}fix{/if} styleColorBackground styleColorBorder" title="{if !$loggedIn}{$lang.logInToRate}{else}{$lang.dislike}{/if}" ref="{$item.dislike}"></a>
	<a class="footerButton comment {if $item.commentsHtml}active styleColorBackground{else}styleColor{/if} styleColorBorder" href="p{$item.id}" title="{if ($item.comments>0)}{$lang.clickToComments}{else}{$lang.clickToComment}{/if}">
		<span class="icon comment styleColorBackground"></span>
		<span class="count " rel="{$item.comments}">
		{if !$item.comments}
			{$lang.none}
		{else}
			{$item.comments}
		{/if}
		</span>
		{if $item.comments==1}
			{$lang.comment}
		{else}
			{$lang.comments}
		{/if}
		<span class="arrow styleColorBorderTop"></span>
	</a>	
	{if $loggedIn || $item.userid==0}
		{*if $user.id ==$item.userid || $item.userid==0*}
			{assign var="authorized" value="true"}
		{*/if*}
	{/if}
	<div class="tag {if $authorized}authorized{/if}">
		{foreach from=$item.tags item=tag name=tagList}
			<a href="tag/{$tag|@strtolower}">{$tag}</a>{if $authorized}<span class="tagRemove" rel="{$tag}"></span>{/if}{if not $smarty.foreach.tagList.last}, {/if}
		{/foreach}
	</div>
	{if $authorized}
		<div class="newtag">
			<label class="qtipTitle" title="{$lang.newTag}">
			<span class="tagicon styleColorBackground"></span>
			<input class="tag empty " name="tagname" />
			</label>
		</div>
	{/if}
</div>
<div class="commentContainerWrapper">{$item.commentsHtml}</div>