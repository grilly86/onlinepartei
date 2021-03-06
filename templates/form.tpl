<div class="contentWrapperContainer">
	{if $loggedIn}
	<div class="contentToolbarContainer">
	<a class="addPost post {$postActive} qtipTitle tooltipLeft styleColor" href="javascript:void(0);" title="{$lang.postTooltip}">{$lang.newPost}</a>
	<a class="addPost poll {$pollsActive} qtipTitle styleColor" href="javascript:void(0);" title="{$lang.pollTooltip}">{$lang.newPoll}</a>
	<br clear="left" />
		<form class="newPostForm post {if $postActive}show{/if} styleColorBorder" name="newStatusForm" id="newPostForm" method="post">
			<span class="addPostArrow status styleColorBackground"></span>
			<input type="text" class="text empty bold styleColorBorder" name="caption" id="postCaption" placeholder="{$lang.postCaptionBlank}" />
			<textarea id="postMessage" class="text message empty styleColorBorder" name="status"  placeholder="{$lang.postBlank}"></textarea>
			<br clear="left" />
			<input type="submit" class="submit opButton" value="{$lang.send}" />
			<span class="resizeHandleVertical"></span>
			<input type="hidden" name="isUserReal" />
		</form>
		<form class="newPostForm poll {if $pollsActive}show{/if} styleColorBorder" name="newPollForm" id="newPollForm" method="post">
			<span class="addPostArrow poll styleColorBackground"></span>
			<input type="text" class="text empty bold" name="question" id="pollQuestion" placeholder="{$lang.pollQuestion}" />
			<textarea id="pollDescription" class="text message empty" name="description" placeholder="{$lang.pollDescription}" style="display:none"></textarea>
			<br clear="left" />
			<ol id="pollAnswers">
				<li id="liAnswer1">
					<label class="inset" for="answer1">&bull;</label>
					<input class="inset text" type="text" name="answer1" id="answer1" value="" placeholder="{$lang.answer} 1" />
				</li>
				<li id="liAnswer2">
					<label class="inset" for="answer2">&bull;</label>
					<input class="inset text" type="text" name="answer2" id="answer2" value="" placeholder="{$lang.answer} 2"/>
				</li>
				<li id="liAnswer3">
					<label class="inset" for="answer3">&bull;</label>
					<input class="inset text" type="text" name="answer3" id="answer3" value="" placeholder="{$lang.answer} 3"/>
				</li>
				
			</ol>
			<button class="removePollAnswer opButton" unselectable>&minus; Antwortmöglichkeit entfernen</button>
			<button class="addPollAnswer opButton" unselectable>&plus; Antwortmöglichkeit hinzufügen</button>
			<input type="submit" class="submit show opButton" value="{$lang.send}" />
			<input type="hidden" name="isUserReal" />
		</form>
	</div>
	{/if}
	
	<!--<input type="hidden" id="postCount" value="{$postCount}" />-->
	<div class="listFilterContainer">
		<!--input class="searchField styleColorBorder" type="text" name="search" id="searchInput" placeholder="{$lang.search}" /-->
		<a class="sortLink desc styleColor styleColorBorder" rel="{if $sort}{$sort}{else}newest{/if}">
			{if $sort=="mostliked"}{$lang.mostliked}{else}{$lang.newest}{/if}
			<span class="arrow desc"></span>
		</a>
		<div class="sortOptions">
			<a class="" href="./"rel="newest">{$lang.newest}</a>
			<a class="" href="liked" rel="mostliked">{$lang.mostliked}</a>
		</div>
		{literal}
		<script type="text/javascript">
			$(".sortLink.desc").click(function() {
				$("div.sortOptions").show();
			});
			$(".sortOptions").find("a").click(function(e) {
				$(".sortLink").html($(this).html() + '<span class="arrow desc"></span>').attr("rel", $(this).attr("rel"));
				$(".sortOptions").fadeOut();
					
				if (history.pushState)
				{
					var url = ".";
					if ($(this).attr("rel") == "mostliked")
					{
						url = "liked";
					}
					history.pushState(null, "onlinepartei.eu", url);
					getList();
					e.preventDefault();
					return false;
				}
			});
			function getList()
			{
				$(".postContainer").remove();
				var sort = $(".sortLink").attr("rel");
				$.ajax({
					url:"sort",
					type:"get",
					data:"sort=" + sort,
					success:function(data) {
						$(".contentWrapperContainer").append(data);
					}
				});
			}
		</script>
		{/literal}
	</div>
	<!-- posts are listed here: -->
	{if $TPL_POSTS}
	{$TPL_POSTS}
	{else}
	<a href="all" class="loading"/>Lade Posts</a>
	{/if}
</div>