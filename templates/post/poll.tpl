{if $item.postid}
		<div class="postMessageContainer" rel="post_{$item.id}">
			<div class="message commentContainer {if ($item.userid==0||$item.userid==$user.id)}edit{/if}" rel="post_{$item.id}" ref="{$item.message}">
			{$item.messageReadable}
			</div>
			{include file='post/postFooter.tpl'}
		</div>
{else}
<h2><a href="poll{$item.id}">{$item.caption|@urldecode|@stripslashes}</a></h2>
	<p style="margin-left:10px">{$item.message|@urldecode|@stripslashes}</p>
	{if $loggedIn}
		<form class="pollVoteForm" id="pollForm{$item.id}"name="pollForm{$item.id}" method="post">
			{assign var=x value=0}
			{foreach from=$item.answer item=answer}
			<label {if $item.isVoted}class="disabled"{/if}>
				<input type="radio" name="vote{$item.id}" {if $item.isVoted && $item.uservote==$x}checked="checked"{/if} value="{$x}" {if $item.isVoted}disabled="disabled"{/if} />
				{$answer.text|@urldecode|@stripslashes}
			</label>
			{assign var=x value=$x+1}
			{/foreach}
			<input type="hidden" name="id" value="{$item.id}" />
			<input class="submit {if $item.isVoted}revert{/if}" type="submit" value="{if $item.isVoted}Stimme zur&uuml;ckziehen{else}Abstimmen{/if}" />
			<label class="error">{$lang.errorNoVote}</label>
			{if $item.isVoted}
				<script type="text/javascript">
				{literal}
				$().ready(function() {
					showPollResult({/literal}{$item.id}{literal});
				});
				{/literal}
				</script>
			{/if}
		</form>
		<br clear="left" />
		<a id="showPollButton{$item.id}" class="showPollButton" href="javascript:void(0)">
			{$lang.showPollResults}
		</a>
		<script type="text/javascript">
			{literal}
				$("#pollForm{/literal}{$item.id}{literal}").submit(function() {
					var that = this;
					var vote = -1;
					if ($(this).find("input[name=vote{/literal}{$item.id}{literal}]:checked").length>0)
					{
						vote = $(this).find("input[name=vote{/literal}{$item.id}{literal}]:checked").val();
					}
					var dataString = "";
					var id=$(this).find("[name=id]").val();
					if ($(this).find("input[type=submit]").hasClass("revert")) {
						dataString = "pollID=" + id + "&revert=true";
					}
					else
					{
						dataString = "pollID={/literal}{$item.id}{literal}&vote=" + vote;
					}
					if (vote>-1)
					{
						$(this).find(".error").hide();
						var i = {/literal}{$item.id}{literal};
						$.ajax({
							url:"index.php?task=polls",
							data:dataString,
							type:"post",
							success:function(data){
								if (parseInt(data)>0)
								{
									//voted
									$(that).find("label")
												.addClass("disabled")
												.find("input[type=radio]")
													.attr("disabled","disabled");
									$(that).find("input.submit").addClass("revert").val("Stimme zurückziehen");
									
									var obj = $("#poll{/literal}{$item.id}{literal}").find("tr").eq(vote);
									$(obj).addClass("uservote").find("td").html(parseInt($(obj).find("td").html())+1);
									showPollResult(i);
								}
								else if (data == "true")
								{
									// unvoted
									$(that).find("label")
												.removeClass("disabled")
												.find("input[type=radio]")
													.removeAttr("disabled")
													.removeAttr("checked");
									$(that).find("input.submit").removeClass("revert").val("Abstimmen");
									
									var obj = $("#poll{/literal}{$item.id}{literal}").find("tr").eq(vote);
									$(obj).removeClass("uservote").find("td").html(parseInt($(obj).find("td").html())-1);
									showPollResult(i);
								}
								else
								{
									console.log(data);
								}
							}
						});
					}
					else
					{
						$(this).find(".error").show();
					}
					return false;
				});
			{/literal}
		</script>
	{else}
		<script type="text/javascript">
			{literal}
			$().ready(function() {
				showPollResult({/literal}{$item.id}{literal});
			});
			{/literal}
	</script>
	{/if}
	<table id="poll{$item.id}" class="pollData">
		<tbody>
			{foreach from=$item.answer item=answer}
			<tr class="{if $answer.uservote}uservote{/if}">
				<th scope="row">{$answer.text|@urldecode|@stripslashes}</th>
				<td>{$answer.vote}</td>
			</tr>
			{/foreach}
		</tbody>
	</table>
	<div id="graph{$item.id}" class="graphContainer"></div>
	{if !$loggedIn}
		<p class="small indent">{$lang.loginToVote}</p>
	{/if}
	{include file='post/postFooter.tpl'}
<script type="text/javascript">
{literal}

{/literal}
</script>
{/if}