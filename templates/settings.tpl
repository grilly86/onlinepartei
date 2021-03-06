		<div class="contentWrapperContainer">
			<div class="contentTitleContainer styleColorBackground">
				{$lang.settingsFor} {$user.name}
			</div>
			<div class="contentContainer postContainer styleColorBorder">
				<h1 class="styleColor">{$lang.profile}</h1>
				<form name="profile" method="post" action="index.php?task=settings" enctype="multipart/form-data">
					<label for="nickname">
						{$lang.email}:
					</label>
					<label style="color:#999;width:300px;">{$user.email} (wird nicht angezeigt)</label>
					<br />
					<label for="nickname">
						{$lang.nickname}:
					</label>
					<input class="text" type="text" name="nickname" id="nickname" value="{$user.name}" />
					<br />
					<label for="language">
						{$lang.language}
					</label>
					<select id="language" name="language">
						<option value="de" {if $user.language=="de"}selected="selected"{/if}>{$lang.languageGerman}</option>
						<option value="en" {if $user.language=="en"}selected="selected"{/if}>{$lang.languageEnglish}</option>
					</select>
					<br />
					<label for="colorPickerInput">{$lang.styleColor}:</label>
					<div style="position:relative;float:left;clear:none">
						<input type="text" class="text" id="colorPickerInput" name="color" value="{$user.color}" /><span class="colorPickerDice"></span>
						<div id="colorPicker"></div>
					</div>
					{*<span><strong>Hinweis: </strong>F&uuml;r optimale Lesbarkeit wird ein <strong>dunkler Farbwert</strong> empfohlen!</span></div>*}
					<br />
					<label for="profileImage" >
						{$lang.profileImage}:
					</label>
					{if $user.hasImage}
						<img src="uploads/p/{$user.id}.jpg?time={$smarty.now}">
						<br clear="all" />
					{/if}
					<p class="labelChooseImage small">{$lang.chooseImage}</p>
					<p class="labelCutImage small" style="display:none">{$lang.cutImage}</p>
					<label class="labelCutImage" style="display:none">{$lang.changeImage}:</label>
					<input class="text" type="file" name="profileImage" size="20" id="profileImage" />
					<br />
					<div class="preview styleColorBorder" unselectable>
						<div class="drawAvatarSize">
							<div class="drawAvatarResize"></div>
						</div>
						<div id="thumbDiv"></div><img id="thumb" />
						
							<img id="preview" />
							<input type="submit" class="opButton" value="{$lang.done}" style="position:absolute;left:50%;top:50%;" />
						
						<br clear="all" />
						
					</div>
					<input type="hidden" name="uploadProfileImage" id="uploadProfileImage" value="0" />
					<br clear="left" />
					<br clear="left" />
					<fieldset >
						<legend>Privatsph&auml;re</legend>
						(noch nicht implementiert)</p>
						<label for="checkChatAll" class="auto"><input disabled class="check" type="checkbox" name="chatAll" id="checkChatAll" value="1" checked="checked">Im Chat f&uuml;r alle sichtbar</label>
						<br clear="left" />
						<label for="checkOnlineState" class="auto"><input disabled class="check" type="checkbox" name="onlineState" id="checkOnlineState" value="1" checked="checked">Online-Status anzeigen</label>
						<br clear="left" />
						<label for="checkOnlineTime" class="auto"><input disabled class="check" type="checkbox" name="onlineTime" id="checkOnlineTime" value="1" checked="checked">Zeitpunkt der letzten Aktivit&auml;t anzeigen</label>
						<p>
					</fieldset>
					<br clear="left" />
					<input class="submit opButton styleColorBackground" type="submit" value="{$lang.save}" />
					<br clear="left" />
					<br clear="left" />
				</form>
			</div>
			<br clear="all" />
		</div>
	</div>
{literal}
<script type="text/javascript" src="static/script/ajaxupload.js"></script>
<script type="text/javascript">
	$().ready(function() {
							var thumb = $('img#thumb');	

							new AjaxUpload('profileImage', {
											action: "index.php?task=fileUpload",
											name: 'profileImage',
											onSubmit: function(file, extension) {
												console.log("zack!");
															$('div.preview').addClass('loading');
											},
											onComplete: function(file, response) {
												console.log("bum!");
															thumb.load(function(){
																$('div.preview').removeClass('loading');
																thumb.unbind();
																initializeAvatarSize(response);
																$(".labelCutImage").show();
																$(".labelChooseImage").hide();
															});
															var date = new Date();
															thumb.attr('src', response+ "?time=" + date.getTime());
											}
							});
	});

	function initializeAvatarSize(response)
	{

								$("div.preview").show();
								//alert(response);
								var date = new Date();
								$("#thumbDiv").css("background", "url(" + response + "?time=" + date.getTime() + ") no-repeat");
								$("#thumbDiv").width($("#thumb").width());
								$("#thumbDiv").height($("#thumb").height());
								$("#thumb").hide();
		$(".drawAvatarSize").show();

		var left=0;var top=0;
		var height = $("#thumb").height();
		var width = $("#thumb").width();
		if (height > width)
		{
			top = (height-width)/2;
			height=width;
		}
		if (height < width)
		{
			left = (width-height)/2;
			width=height;
		}
		$(".drawAvatarSize").width(width);
		$(".drawAvatarSize").height(height);
		$(".drawAvatarSize").css("left", left + "px");
		$(".drawAvatarSize").css("top", top + "px");

		refreshAvatarPreview();

		var dragAvatarStartX = 0;
		var dragAvatarStartY = 0;
		var dragMouseStartX = 0;
		var dragMouseStartY = 0;

		var dragAvatar = false;

		$(".drawAvatarSize").mousedown(function(e) {
			dragMouseStartX = e.pageX;
			dragMouseStartY = e.pageY;
			dragAvatarStartX = $(".drawAvatarSize").position().left;
			dragAvatarStartY = $(".drawAvatarSize").position().top;
			//console.log(e);
			dragAvatar = true;
		});
		$(".drawAvatarSize").mouseup(function(e) {

		});
		$(".drawAvatarSize").mousemove(function(e) {
			if (dragAvatar && !resizeAvatar)
			{
				var newX = e.pageX - dragMouseStartX + dragAvatarStartX;
				var newY = e.pageY - dragMouseStartY + dragAvatarStartY;
				if (newX + $(".drawAvatarSize").width()>$("#thumbDiv").width())
				{
					newX = $("#thumbDiv").width()-$(".drawAvatarSize").width();
				}
				if (newY + $(".drawAvatarSize").height()>$("#thumbDiv").height()) 
				{
					newY = $("#thumbDiv").height()-$(".drawAvatarSize").height();
				}
				if (newX < 0) newX = 0;
				if (newY < 0) newY = 0;
				$(".drawAvatarSize").css("left",newX+"px");
				$(".drawAvatarSize").css("top",newY+"px");

				clearTimeout(refreshAvatarTimer);
				refreshAvatarTimer = setTimeout("refreshAvatarPreview()",300);
			}
		});
		var resizeAvatarStartWidth = 0;
		var resizeAvatarStartHeight = 0;
		var resizeAvatarMouseX = 0;
		var resizeAvatarMouseY = 0;
		var resizeAvatar = false;
		$(".drawAvatarResize").mousedown(function(e) {
			resizeAvatar = true;
			resizeAvatarStartWidth = $(".drawAvatarSize").width();
			resizeAvatarStartHeight = $(".drawAvatarSize").height();
			resizeAvatarMouseX = e.pageX;
			resizeAvatarMouseY = e.pageY;
		});
		$(window).mouseup(function(e) {
			resizeAvatar=false;
			dragAvatar=false;
		});
		$(window).mousemove(function(e) {
			if (resizeAvatar)
			{
				var newWidth = e.pageX-resizeAvatarMouseX+resizeAvatarStartWidth;
				var newHeight = e.pageY-resizeAvatarMouseY+resizeAvatarStartHeight;
				if (newHeight >= 48 || newWidth >= 48)
				{
					if (($(".drawAvatarSize").position().left+newWidth <= $("#thumbDiv").width())&&($(".drawAvatarSize").position().top+newHeight <= $("#thumbDiv").height()))
					{ 
					if (newHeight > newWidth)
					{
						$(".drawAvatarSize").width(newHeight);
						$(".drawAvatarSize").height(newHeight);
					}
					else
					{
						$(".drawAvatarSize").width(newWidth);
						$(".drawAvatarSize").height(newWidth);
					}
					clearTimeout(refreshAvatarTimer);
					refreshAvatarTimer = setTimeout("refreshAvatarPreview()",300);
					}
				}
			}
		});
	}
	function refreshAvatarPreview()
	{
		var dataString = "left=" + $(".drawAvatarSize").position().left + "&top=" + $(".drawAvatarSize").position().top + "&width=" + $(".drawAvatarSize").width() + "&height=" + $(".drawAvatarSize").height();
		$.ajax({
			url:"index.php?task=generateAvatarPreview",
			type:"get",
			data:dataString,
			success:function(data){
				var date = new Date();
				//console.log(date.getTime());
				$("#preview").attr("src",data + "?time=" + date.getTime());

				var marginBottom = ($("#thumbDiv").height()-48)/2;
				if (marginBottom < 0) marginBottom = 0;

				var marginLeft = ($("div.preview").width()-$("#thumbDiv").width()-48)/2;
				$("#preview").css("marginTop",marginBottom + "px");
				$("#preview").css("marginLeft",marginLeft + "px");
				$("#uploadProfileImage").val(1);
			}
		});
	}
	var refreshAvatarTimer;
</script>

<script type="text/javascript" src="static/script/farbtastic/farbtastic.js"></script>
<script type="text/javascript">
	$().ready(function() {
		$('#colorPicker').farbtastic("#colorPickerInput");
			
		$("#colorPickerInput").keyup(function(e) {
			
			if (e.keyCode == 13 || e.keyCode == 27)
			{
				e.preventDefault();
				$(this).blur();
			}
			var x = /^#[0-9a-f]{6}$/i.exec(this.value);
			if (x)
			{
				if (x[0] != styleColor) {
					styleColor = x[0];
					colorPicker.setColor(styleColor);
				}
			}
		});
		styleColor = $('#colorPickerInput').val();
		var colorPicker = $.farbtastic('#colorPicker');
		colorPicker.linkTo(pickerUpdate);
		colorPicker.setColor(styleColor);
		function pickerUpdate(color)
		{
			$('#colorPickerInput').css({'background-color':color}).val(color);
			setStyleColor(color);
		}
		$("#colorPickerInput").focus(function() {
			$("#colorPicker").fadeIn();
		}).blur(function() {
			$("#colorPicker").fadeOut();
			var x = /^#[0-9a-f]{6}$/i.exec(this.value);
			if (!x)	{
				this.value = styleColor;
			}
			$.cookie("styleColor", this.value);
		});
		$(".colorPickerDice").click(function() {
			color = generateRandomColor();
			$("#colorPickerInput").css({'background-color':color}).val(color);

			setStyleColor(color);
			$.cookie('styleColor', '', { expires: -1 })
		});
	});
</script>
{/literal}