	var drag=false;
	var dragContainer;
	var dragXPos=0,dragXStart=0;
	var dragYPos=0,dragYStart=0;
	var resize=false;
	var resizeContainer; 
	var resizeXPos=0, resizeXStart=0;
	var resizeYPos=0, resizeYStart=0;
	var allTimer, chatTimer= new Array();
	var onlineTimer;
	var blinkTimer;
	var channelAllFrequency = 15000; // Alle X Sekunden wird nach neuen
									// Post von allen Benutzern gefragt.
	var channelChatFrequency = 4000;		// Wenn ein Chat-Fenster geöffnet 
									// wird alle X Sekunden nach neuen 
									// Posts gefragt.
var limitFrom=0;
var limitCount=20;
var postCount=0;
var muteSound = false;

var ajaxHandler;
function getPosts(limit,order,parent)
{	
	
	$("a.loading").remove();
	var limitString="", orderString="";
	
	if (limit)
	{
		limitString = "&limit=" + limit;
	}
	if (order)
	{
		orderString = "&order=" + order;
	}
	parent = parseInt(parent);	// if not set then zero
	
	var dataString = "parent=" + parent + limitString + orderString;
	
	//if (ajaxHandler) ajaxHandler.abort();
	ajaxHandler = $.ajax({
		url:"index.php?task=getPosts",
		type:"post",
		data:dataString,
		success:function(data) {
			if (!data)
			{
				scrolledToEnd = true;
			}
			$(".contentWrapperContainer").append(data).removeClass("loading");
			setStyleColor(styleColor);
			initializeFancybox($(".contentWrapperContainer"));
			initializeTooltips();
		}
	});
}
var scrolledToEnd = false;
window.onscroll = function() {
	$(window).scrollTop(1);
};
$().ready(function() {
	setTimeout(function(){
		$(window).scrollTop(0);
		window.onscroll=undefined;
	},1000);
	$(".contentWrapperContainer").click(function() {
		var obj = $(".chatContainer.active");
		if (obj.length)
		{
			//var zIndex = obj.css("zIndex");
			obj.css("zIndex",210).removeClass("active");
		}
		var obj = $("#contextMenuContainer").find(".contextMenu");
		if (obj.length)
		{
			obj.remove();
		}
	});
	
	var getPostHandler;
	if ($(".contentWrapperContainer").find(".postContainer").length==0)
	{
		getPosts(limitFrom+","+limitCount);
		$(window).scroll(function() {
			var scrollTop = $(this).scrollTop();
			var scrollHeight = $(document).height()-$(window).height();
			if (scrollTop > scrollHeight-2)	//scrolled to Bottom
			{
				if (!scrolledToEnd)
				{
					limitFrom += limitCount;
					$(".contentWrapperContainer").addClass("loading");
					clearTimeout(getPostHandler);
					getPosts(limitFrom+","+limitCount);
				}
			}
		});
	}
	else
	{
		initializeFancybox($(".contentWrapperContainer"));
	}
	blinkTimer = setInterval("chatBlinkNew()",1000);
	var cookieArr = document.cookie.split(";");
	onlineTimer = setInterval("stateActiveTimer()", 10000);
	// for resetting document title (chatNewBlink())
	titleStoreCaption = document.title;
	$(window).resize(function() { 
		adjustWindowSize();
	});
	muteSound = $.cookie("muteSound");
	if (muteSound==="true")
	{
		$(".soundButton").removeClass("on").addClass("off").attr("title", lang["unmuteSound"]);
		muteSound=true;
	}
	else
	{
		//$(this).removeClass("on").addClass("off").attr("title", lang["muteSound"]);
		muteSound = false;
	}
	
	if ($("#chatUserList").length)
	{
		allTimer = setTimeout("channelAll()",100);
		for (var i in cookieArr)
		{
			if (cookieArr[i].split("=")[1]=="open")
			{
				var id = cookieArr[i].trim().split("=")[0].substring(5);
				if (parseInt(id)>0)
				{
					openChat(id);
				}
				else
				{
					$.cookie(cookieArr[i].split("=")[0], null);
				}
			}
		}
	}
	
	$("#soundPlayer").jPlayer({
		ready: function () {
		$(this).jPlayer("setMedia", {
			mp3: "static/sound/post.mp3"
		});//.jPlayer("play"); // Attempts to Auto-Play the media
		},
		swfPath:"static/script/JPlayer.swf",
		solution:"flash, html"
	});
	
	$(".soundButton").click(function() {
		if ($(this).hasClass("on"))
		{
			$(this).removeClass("on").addClass("off").attr("title", lang["unmuteSound"]);
			muteSound = true;
			$.cookie("muteSound","true");
			
		}
		else
		{
			$(this).removeClass("off").addClass("on").attr("title", lang["muteSound"]);
			muteSound = false;
			$.cookie("muteSound","false");
		}
	});
	$(window).keydown(function(e){
		if (e.keyCode==16)
		{
		holdShift=true;
		}
		if (e.keyCode==17)
		{
		holdStrg=true;
		}
	});
	$(window).keyup(function(e){
		if (e.keyCode==16)
		{
		holdShift=false;
		}
		if (e.keyCode==17)
		{
		holdStrg=false;
		}
	});
	$(window).mouseup(function(e){
		if (drag || resize)
		{
			$("*")	.removeAttr("unselectable");
		}
	drag=false;
	resize=false;
	});
	$(window).mousemove(function(e){
		moving=true;
		if (drag) 
		{
			var newX = e.clientX + dragXPos - dragXStart;
			var newY = e.clientY + dragYPos - dragYStart;
			if (newX+$(dragContainer).width()>$(window).width()-6)
			{
				newX = $(window).width() - $(dragContainer).width()-6;
			}
			if (newX<6){
				newX = 6;
			}
			if (newY+$(dragContainer).height()>$(window).height()-6)
			{
				newY = window.innerHeight - $(dragContainer).height()-6;
			}
			if (newY < 6){
				newY = 6;
			} 
			$.cookie($(dragContainer).attr("id") + "_X", newX, {expires:1000});
			$.cookie($(dragContainer).attr("id") + "_Y", newY, {expires:1000});

			$(dragContainer).css("left", newX+"px");
			$(dragContainer).css("top", newY+"px");
		}
		if (resize)
		{
			//für resizeHandleVertical:
			var newY = e.clientY + resizeYPos - resizeYStart;
			if (newY < 6)
			{
				newY = 6;
			}
			
			// für Chat:
			if ($(resizeContainer).hasClass("chatContainer"))
			{
				if (newY+$(resizeContainer).position().top-$(document).scrollTop()>$(window).height()-6)
				{
					//newY = $(document).height() - ($(dragContainer).height()+10);
					newY = window.innerHeight - $(resizeContainer).position().top+$(document).scrollTop()-6;
					//console.log(newY);
				}
				var newX = e.clientX + resizeXPos - resizeXStart;
				//console.log ("new: " + newX);
				if (newX < 6)
				{
					newX = 6;
				}
				if (newX + $(resizeContainer).position().left > $(window).width()-6)
				{
					newX = $(window).width()-$(resizeContainer).position().left-6;
					//console.log("limit: " + newX);
				}
				//console.log("width:" + newX);
				$(resizeContainer).width(newX);
				$.cookie($(resizeContainer).attr("id") + "_width", newX, {expires:1000});
				$.cookie($(resizeContainer).attr("id") + "_height", newY, {expires:1000});
				$(resizeContainer).find(".messageContainer").height(newY-75);
			}
			$(resizeContainer).height(newY);
		}
	});
	$("a.postUser, .chatLink, .profileImage").live("mouseover", function(e) {
		var obj = $(this).find(".profileImage");
		var width=48,height=48,marginTop=-17,marginLeft=0,right=0;
		if ($(this).hasClass("profileImage post") || obj.hasClass("post"))
		{
			width=48;height=48;marginTop=-22;marginLeft=0;right=-20;
			if (!obj.hasClass("post"))
			{
				obj = $(this);
			}
		}
		if (!obj.hasClass("zooming"))
		{	  
			obj
			.addClass("zooming")
			.css("zIndex",2)
			.stop()
			.animate({"width":width,"height":height,"marginTop":marginTop,"marginLeft":marginLeft,"right":right}, 300)
			.removeClass("zooming");
		}
	});
	$("a.like,a.dislike").live("click", function() {
		
		if (!$(this).hasClass("fix"))
		{
			var that = this;
			var id = parseInt($(this).parent().parent().attr("rel").substr(5));
			var type = $(this).parent().parent().attr("rel").substr(0,4);
			
			var rating="like";
			var unrate = false,unrateStr ="";
			if ($(this).hasClass("dislike"))
			{
				rating="dislike";
			}
			if ($(this).hasClass("active"))
			{
				unrate = true;
				unrateStr = "&unrate=true";
			}
			var dataString = "id=" + id + "&rating=" + rating + unrateStr;
			if (type=="poll")
			{
				dataString += "&type=poll";
			}
			$.ajax({
				url:'index.php?task=rating',
				type:'post',
				data:dataString,
				success:function(data) {
					var like=0,dislike=0;
					if (rating == 'like')
					{
						var dislikeBtn = $(that).parent().find("a.dislike");
						if ($(dislikeBtn).hasClass("active"))
						{
							$(dislikeBtn).attr("ref", parseInt($(dislikeBtn).attr("ref"))-1).removeClass("active");
						}	
						if (unrate)
						{
							$(that).attr("ref", parseInt($(that).attr("ref"))-1).removeClass("active");
						}
						else
						{
							$(that).attr("ref", parseInt($(that).attr("ref"))+1).addClass("active");
						}
						like = $(that).attr("ref");
						dislike = $(dislikeBtn).attr("ref");
					}
					else
					{
						var likeBtn = $(that).parent().find("a.like");
						if ($(likeBtn).hasClass("active"))
						{
							$(likeBtn).attr("ref", parseInt($(likeBtn).attr("ref"))-1).removeClass("active");
						}
						if (unrate)
						{
							$(that).attr("ref", parseInt($(that).attr("ref"))-1).removeClass("active");
						}
						else
						{
							$(that).attr("ref", parseInt($(that).attr("ref"))+1).addClass("active");
						}

						like = $(likeBtn).attr("ref");
						dislike = $(that).attr("ref");
					}
					calcLikeStats($(that).parent().find(".votingContainer"), like, dislike);
				}
			});
		}
	});

	$("a.postUser, .chatLink,.profileImage").live("mouseout",function(e) {
		var obj = $(this).find(".profileImage");
		var width=27,height=27,marginTop=-6,marginLeft=0,right=3;
		if ($(this).hasClass("profileImage post") || obj.hasClass("post"))
		{
			width=16;height=16;marginTop=-2;marginLeft=0,right=5;
			if (!obj.hasClass("post"))
			{
				obj = $(this);
			}
		}
		if (!obj.hasClass("unzooming"))
		{
			obj
			.addClass("unzooming")
			.stop()
			.animate({"width":width,"height":height,"marginTop":marginTop,"marginLeft":marginLeft,"right":right}, 300)
			.css("zIndex",1)
			.removeClass("unzooming");
		} 
	});
	
	$("input.tag").live("focus", function() {
		if ($(this).val()=="")
		{
			$(this).removeClass("empty");
		}
	});
	$("input.tag").live("blur", function() {
		if ($(this).val()=="")
		{
			$(this).addClass("empty");
		}
	});
	$("input.tag").live("keydown", function(e) {
		if (e.keyCode == 13)
		{
			var that = this;
			var value = $(this).val(), id = $(this).parent().parent().parent().parent().attr("rel");
			var type = id.substr(0,4);
			id = id.substr(5);
			
			//console.log(type);
			if (value != "" && id && type)
			{
				$.ajax({
					url:'tag',
					type:'post',
					data:'id=' + id + "&type=" + type + "&name=" + value,
					success:function(data)
					{
						if (data=="200")
						{
							var obj = $(that).parent().parent().parent().find("div.tag");
							//console.log(obj);
							var comma = "";
							if (obj.children().length>0)
							{
								comma = ", ";
							}
							$(obj).append(comma + "<a href='tag/" + value + "'>"+value+"</a><span class='tagRemove' rel='"+value+"'></span>");
							$(that).val("").addClass("empty").blur();
						}
						if (data=="500")
						{
							//alert("500");
							$(that).val("").addClass("empty").blur();
						}
					}
				});
			}
		}
		if (e.keyCode == 27)
		{
			$(this).val("").addClass("empty").blur();
		}
	});
	$("span.tagRemove").live("mouseover",function() {
		$(this).addClass("hover");
	});
	$("span.tagRemove").live("mouseout",function() {
		$(this).removeClass("hover");
	});
	$("span.tagRemove").live("click",function() {
		var that = this;
		var name = $(this).attr("rel");
		var id = $(this).parent().parent().parent().attr("rel");
		var type=id.substr(0,4);
		id=id.substr(5)
		if (confirm(lang['tagRemoveConfirm']))
		{
			$.ajax({
				url:"tag",
				type:"post",
				data:"action=remove&name=" + name + "&type=" + type + "&id=" + id,
				success:function(data)
				{
					if (data=="200")
					{
						$(that).prev().remove();
						$(that).remove();
					}
				}
			})
			
		}
	});
	var footerButtonWaiting =false;

	$("a.footerButton.comment").live("click", function () {
		if (!footerButtonWaiting)
		{
			if ($(this).hasClass("active"))
			{
				var obj = this;
				$(this).parent().parent().find(".commentContainerWrapper:eq(0)").slideUp(300, function() {
					$(obj).removeClass("active styleColorBackground").addClass("styleColor");
				});
			}
			else
			{
				var that = this;
				var id = parseInt($(this).parent().parent().attr("rel").substr(5));
				var type = $(this).parent().parent().attr("rel").substr(0,4);
				var dataString = "id=" + id;
				if (type=="poll")
				{
					dataString += "&type=poll";
				}
				$.ajax({
					url:"comments",
					type:"post",
					data:dataString,
					success:function(data) {

						$(that).addClass("active styleColorBackground").removeClass("styleColor");
						var wrapper = $(that).parent().parent().find(".commentContainerWrapper");
						$(wrapper).html(data).slideDown(500,"linear", function() {
							//animation complete (height final)
							initializeFancybox(wrapper);
						});
						var top = $(wrapper).offset().top;
						scrollToPos(top);
					}
				});
			}
		}
		return false;
	});

	$("textarea.messageComment").live("keydown", function(e) {
		//console.log(e.keyCode);
		if (e.keyCode==27){
			if ($(this).hasClass("edit"))
			{
				$(this).parent().html(stripslashes(unescape($(this).parent().attr("store"))));
			}
		}
		if (e.keyCode==13 && !(holdShift || holdStrg))
		{

			if ($(this).hasClass("edit") || $(this).val()!="")
			{
				$(this).parent().addClass("edit");
				pressedEnter=true;
				sendComment(this);
			}
			return false;
		}
	});
	var pressedEnter = false;
	$("textarea.messageComment.edit").live("blur", function(e) {
		if (!pressedEnter)
		{
			$(this).parent().addClass("edit");
			if(escape($(this).val()) != escape(unescape($(this).attr("ref")).split('<br>').join('\n')))
			{
				if (confirm("Sollen Ihre Änderungen gespeichert werden?\n(OK=Speichern/Abbrechen=Verwerfen)"))
				{
					sendComment(this);
				}
				else
				{
					$(this).parent().html(unescape($(this).parent().attr("store")));
				}
			}
			else
			{
				$(this).parent().html(unescape($(this).parent().attr("store")));
			}
			var parent = $(this).parent();
			initializeFancybox(parent);
			if ($(parent).hasClass("commentContainer"))
			{
				var top = $(parent).position().top;
				scrollToPos(top);
			}
		}
		
	});
	
	$("textarea.messageComment").live("focus",function() {
			if ($(this).val()==lang['commentBlank'])
			{
				$(this).val("");
				$(this).removeClass("empty");
				$(this).parent().removeClass("edit");
			}
		}).live("blur", function() {
			if ($(this).val()=="")
			{
				$(this).val(lang['commentBlank']);
				$(this).addClass("empty");
				$(this).parent().addClass("edit");
			}
		});
	
	
	
	function sendComment(that)
	{
		var parentID=0;
		var isNew = false;
		var id = 0;
		var type="", typeDataString="";
		var obj = $(that).parent();
		if ($(that).hasClass("edit"))
		{
			//edit
			id = $(that).parent().attr("rel").substr(5);
			if ($(that).parent().parent().parent().hasClass("postContainer"))
			{
				parentID = parseInt($(that).parents(".postContainer").attr("rel").substr(5));
				obj = $("div.message[rel=post_"+parentID+"]").parent();
			}
			else
			{
				parentID=id;
				obj = $(that).parent().parent();
			}
		}
		else
		{
			//new 
			isNew = true;
			parentID = parseInt($(that).parent().parent().attr("rel").substr(5));
			type = $(that).parent().parent().attr("rel").substr(0,4);
			if (type=="poll")
			{
				typeDataString = "&type=" + type;
			}
			
		}
		if (parentID>0)
		{
			var message = escape(encodeURIComponent($(that).val()));
			$.ajax({
				url:"sendComment",
				type:"post",
				data:"message="+message + "&parentID=" + parentID + "&id=" + id + typeDataString,
				success:function(data){
					if (isNew)
					{
						// increase count by one
						var commentCountContainer = $(that).parent().parent().children(".postFooter").children(".footerButton.comment").children("span.count");
						var commentCount = parseInt($(commentCountContainer).attr("rel"))+1;
						$(commentCountContainer).attr("rel",commentCount).html(commentCount);
					}
					else if (message==="")
					{
						// if edit to empty string decrease commentcount by one
						var commentCountContainer = $(that).parent().parent().parent().parent().children(".postFooter").children(".footerButton.comment").children("span.count");
						var commentCount = parseInt($(commentCountContainer).attr("rel"))-1;
						$(commentCountContainer).attr("rel",commentCount).html(commentCount);
					}
					
					var scrollStore = $(document).scrollTop();
					if ($(obj).hasClass("commentWrapper"))
					{
						$(obj).replaceWith(data);
					}
					else if($(obj).hasClass("postContainer"))
					{
						$(obj).html(data);
					}
					else if ($(obj).hasClass("postMessageContainer"))
					{
						$(obj).parent().replaceWith(data);
					}
					else
					{
						$(obj).parent().find(".commentContainerWrapper").html(data);
					}
					
					if ($(obj).find(".postDeleted").length>0)
					{
						$(obj).slideUp(2000, function() {
							$(obj).remove();
						});
					}
					initializeFancybox(data);
					scrollToPos(scrollStore);
				}
			});
			return false;
		}
	}
	//on Click on a comment, show EDIT menu
	$(".commentContainer.edit .chatButtonContainer button.edit").live("click", function(e) {
		var that = $(this).parent().parent();
		if ($(that).find("textarea").length==0)
		{
			$(that).removeClass("edit");
			$(this).parent().remove();
			var storeHeight = $(that).height() + 20;
			var value=unescape(stripslashes(decodeURIComponent($(that).attr("ref").replace(/\+/g, " ")).trim()));
		
			$(that).attr("store",escape($(that).html()));
			$(that).html('<textarea class="messageComment edit" ref="'+escape(value.replace("\n", "<br>"))+'">' + value + '</textarea><div class="notice">' + lang["holdShiftToBreak"] + "</div>");
			$(that).find("textarea.messageComment.edit").height(storeHeight).focus().select();
		}
	});
	var contextMenuVisible=false;
	$(".contextMenu a").live("click", function () {
		hC();
	});
	
	$(".chatLink").live("contextmenu", function(e) {
		
		var id = parseInt(this.id.substr(9));
		// todo create DOM elements
		var contextMenu = "<div class='contextMenu styleColorBorder' style='border-color:"+styleColor+"'><a href='javascript:openChat("+id+");' style='color:"+styleColor+"'><strong>"+lang["openChat"]+"</strong></a><a href='u"+id+"' style='color:"+styleColor+"'>"+lang["showProfile"]+"</a></div>";
		$("#contextMenuContainer").html(contextMenu);
		if ($(".contextMenu").length>0)
		{
			$(".contextMenu").css({"left":e.clientX+"px", "top":e.clientY+"px"});
			$(".contextMenu").fadeIn();
			contextMenuVisible=true;
		}
		else
			{
				alert("not found");
			}
		e.preventDefault();
	});
	
	$(".chatLink").live("click",function(e){
		$(this).removeClass("new");
		var id = $(this).attr("href").substring(1);
		//alert($("#chatWindows").find("#chat_" + id).length);
		openChat(id);
	return false;
	});

		// für "Status aktualisieren"
	 $("#pollQuestion").val(lang['pollQuestion']);
	 $("#pollQuestion").focus(function() {
		 if ($("#pollQuestion").val()==lang['pollQuestion'])
		 {
			$("#pollQuestion").val("");
			$("#pollQuestion").removeClass("empty");
		 } 
	 });
	  $("#pollQuestion").blur(function() {
		if ($(this).val()=="")
		{
			$("#pollQuestion").val(lang["pollQuestion"]);
			$("#pollQuestion").addClass("empty");
		}
	});
	
	$("#pollDescription").val(lang['pollDescription']);
	 $("#pollDescription").focus(function() {
		 if ($("#pollDescription").val()==lang['pollDescription'])
		 {
			$("#pollDescription").val("");
			$("#pollDescription").removeClass("empty");
		 } 
	 });
	  $("#pollDescription").blur(function() {
		if ($(this).val()=="")
		{
			$("#pollDescription").val(lang["pollDescription"]);
			$("#pollDescription").addClass("empty");
		}
	});
	
	 var storeStatusHeight = 0;
	 $("#statusMessage").keydown(function(e) {
		isStatusFocused=false;
		if (e.keyCode == 27)
		{
			storeStatusHeight = $("#statusMessage").height();
			$("#statusMessage").animate({height:28},800, function() {
				//$(".newPostForm.status").removeClass("show");
				if ($(this).val()=="")
				{
					$(this).addClass("empty").blur().val(lang['statusBlank']).parent().find(".submit").removeClass("show").parent().find(".resizeHandleVertical").removeClass("show");
				}
				else
				{
					$(".newPostForm.status").removeClass("show");
				}
			});
		}
		
	 });
	 // für Meldungen:
	 $("#postCaption").val(lang["postCaptionBlank"]);
	 $("#postCaption").hide();
	 
	 $("#postCaption").focus(function() {
		 
		 if ($(this).val()==lang["postCaptionBlank"])
		 {
				$(this).val("") ;
				$(this).removeClass("empty");
		 } 
	 });
	 $("#postCaption").blur(function() {
		if ($(this).val()=="")
		{
			$("#postCaption").val(lang["postCaptionBlank"]);
			$("#postCaption").addClass("empty");
		}
	});
	$("#postMessage").val(lang["postBlank"]);
	$("#postMessage").focus(function(){
		if ($(this).val()==lang["postBlank"])
		{
			$("#postCaption").show();
			$(this).val("");
			$(this).removeClass("empty");
			
		}
	});
	$("#postMessage,#postCaption,#statusMessage").keydown(function(){
		$(this).removeClass("error")
	});
	$(".resizeHandleVertical").mousedown(function(e) {
			resize = true;
			resizeContainer = $(this).parent().find(".message");
			resizeXStart = e.pageX;
			resizeYStart=e.pageY;
			resizeXPos=$(resizeContainer).width();
			resizeYPos=$(resizeContainer).height();
		});
		$("#newPostForm").submit(function() {
			if ($("#postMessage").val()!="" && $("#postMessage").val()!=lang['postBlank'])
			{
				var dataString = "message=" + escape(encodeURIComponent($("#postMessage").val()));
				
				if ($("#postCaption").val()!="" && $("#postCaption").val()!=lang['postCaptionBlank'])
				{
					dataString += "&caption=" + escape(encodeURIComponent($("#postCaption").val()));
				}
				$.ajax({
					url:"index.php?task=ajaxPost",
					type:"post",
					data:dataString,
					success:function(data){
						$("#postMessage").val(lang['postBlank']);
						$("#newPostForm").removeClass("show");
						$(".addPost").removeClass("active");
						var newDiv = $("<div>");
						newDiv.html(data);
						newDiv.css({"opacity":0.0})
						$(newDiv).insertAfter(".listFilterContainer")
										.animate({"opacity":1.0}, 2000);
						initializeFancybox(newDiv);
					}
				});
				return false;
			}
			else
			{
				$("#postMessage").addClass("error");
			}
		 return false; 
		});
		$("#newPollForm").submit(function() {
			if ($("#pollQuestion").val() != "" && $("#pollQuestion").val() != lang["pollQuestion"])
			{
				var pollQuestion = escape(encodeURIComponent($("#pollQuestion").val()));
				var pollDescription="";
				if ($("#pollDescription").val() != "" && $("#pollDescription").val() != lang["pollDescription"])
				{
					pollDescription = escape(encodeURIComponent($("#pollDescription").val()));
				}
				if ($("#answer1").val()!="")
				{
					var answers = "&answer1=" + escape(encodeURIComponent($("#answer1").val()));
					if ($("#answer2").val()!="")
					{
						answers += "&answer2=" + escape(encodeURIComponent($("#answer2").val()));
						$("#pollAnswers").find("li").each(function(index) {
							if (index>1 && $("#answer" + parseInt(index+1)).val() != "")
							{
								answers += "&answer"+parseInt(index+1)+"="+escape(encodeURIComponent($("#answer" + parseInt(index+1)).val()));
							}
						});
						
						var dataString = "question=" + pollQuestion + "&description="+ pollDescription + answers;
						$.ajax({
							url:"index.php?task=ajaxPost",
							type:"post",
							data:dataString,
							success:function(data){
								$("#pollQuestion").val(lang['pollQuestion']);
								$("#newPollForm").removeClass("show");
								$(".addPost").removeClass("active");

								var newDiv = $("<div>");
								newDiv.html(data);
								newDiv.css({"opacity":0.0})
								$(newDiv).insertAfter(".listFilterContainer")
												.animate({"opacity":1.0}, 2000);

								initializeFancybox(newDiv);
							}
						});
						return false;
					}
					else
					{
						$("#answer2").addClass("error");
					}
				}
				else
				{
					$("#answer1").addClass("error");
				}
			}
			else
			{
				$("#pollQuestion").addClass("error");
			}
			return false;
		});
		$("input.text").live("keyup click change",function() {
			$(this).removeClass("error");
		});
		$("#pollQuestion").focus(function() {
			$("#pollDescription").slideDown()
		});
		$("#newDiscussForm").submit(function() {
			//alert("Kein Text eingebeben!");
			if ($("#postMessage").val()!="" && $("#postMessage").val()!=lang["discussBlank"] && $("#postCaption").val()!="" && $("#postCaption").val()!=lang["discussCaptionBlank"])
			{
				var dataString = "caption=" + escape(encodeURIComponent($("#postCaption").val())) + "&message=" + escape(encodeURIComponent($("#postMessage").val()));
				$.ajax({
					url:"index.php?task=ajaxPost",
					type:"post",
					data:dataString,
					success:function(data){
						$("#postCaption").val(lang["discussCaptionBlank"]);
						$("#postMessage").val(lang["discussBlank"]);
						$("#newDiscussForm").removeClass("show");
						$(".addPost").removeClass("active");
						
						var newDiv = $("<div>");
						newDiv.html(data);
						newDiv.css({"opacity":0.0})
						$(newDiv).insertAfter(".listFilterContainer")
										.animate({"opacity":1.0}, 2000);
					}
				});
				return false;
			}
			else
			{
				if ($("#postMessage").val()=="" || $("#postMessage").val()==lang["discussBlank"])
				{
					$("#postMessage").addClass("error");
				}
				if ($("#postCaption").val()=="" || $("#postCaption").val()==lang["discussCaptionBlank"])
				{
					$("#postCaption").addClass("error");
				}
			}
		 return false; 
		});
	$("a.addPost.poll").live("click", function(e) {
		if (!$(this).hasClass("disabled"))
		{
			if ($(".newPostForm.poll").is(":visible"))
			{
				$(".newPostForm.poll").removeClass("show");
				$(this).removeClass("active");
			}
			else
			{
				$(".newPostForm").removeClass("show");
				$(".newPostForm.poll").addClass("show");
				$(".addPost").removeClass("active");
				$(this).addClass("active");
			}
		}
		return false;
	});
	$("a.addPost.post").live("click", function(e) {
		if ($(".newPostForm.post").is(":visible"))
		{
			if ($(this).hasClass("active"))
			{
				$(".newPostForm.post").removeClass("show");
				$(this).removeClass("active");
			}
			else
			{
				$(this).addClass("active");
				$("#statusMessage").focus();
			}
		}
		else
		{
			$(".newPostForm").removeClass("show");
			$(".newPostForm.post").addClass("show");
			$(".addPost").removeClass("active");
			$(this).addClass("active");
			$("#statusMessage").focus();
		}
		return false;
	});
	$(".commentContainer.message.edit").live("mouseenter" , function() {
		var html = '<div class="chatButtonContainer"><button class="chat edit styleColorBackground" style="background-color:'+styleColor+';border-color:'+styleColor+'"></button><button class="chat delete"></button></div>'
		$(this).append(html);
	}).live("mouseleave",function(e) {
		$(this).find(".chatButtonContainer").remove();
	}).live('dblclick',function() {
		if ($(this).find(".chatButtonContainer").find("button.chat.edit").length)
		{
			$(this).find(".chatButtonContainer").find("button.chat.edit").click();
		}
		else
		{
			alert ("error chat button not found!");
		}
	});
	$(".commentContainerWrapper").each(function() {
		if ($(this).html()!="")
		{
			$(this).show();
		}
	});
	
	$("#chatUserList").mousewheel(function(e) {
		scrollDir = -1 * e.originalEvent.wheelDeltaY / 5;	//webkit
		if (!scrollDir) scrollDir = e.originalEvent.detail * 5;	// firefox
		$("#chatUserList").scrollTop(scrollDir + $("#chatUserList")[0].scrollTop);
		return false;
	});
	
	
	$(".chatContainer").live('mousewheel',function(e) {
		scrollDir = -1 * e.originalEvent.wheelDeltaY / 10;	//webkit
		if (!scrollDir) scrollDir = e.originalEvent.detail * 10;	// firefox
		$(this).find(".messageContainer").scrollTop(scrollDir + $(this).find(".messageContainer")[0].scrollTop);
		return false;
	});
	
	var answers=3;
	$("button.addPollAnswer").click(function() {
		answers += 1;
		$("button.removePollAnswer").removeClass("disabled").removeAttr("disabled");
		var html = '<li id="liAnswer'+answers+'">'+
			'<label class="inset" for="answer'+answers+'">&bull; ' /*+String.fromCharCode(answers+96)*/+ '</label>' +
		'<input class="inset text" type="text" name="answer'+answers+'" id="answer'+answers+'" value=""/>' + 
		'</li>';

		$("#pollAnswers").append(html);
		return false;
	});
	$("button.removePollAnswer").click(function() {
		if (answers>2)
		{
			$("#liAnswer" + answers).remove();
			answers -= 1;
			
		}
		if (answers==2)
		{
			$("button.removePollAnswer").addClass("disabled").attr("disabled","disabled");
		}
		return false;
	});
	$("#answers").live("change click keyup", function() {
		var answers = parseInt($(this).val());
		var loops = answers;
		if (prevAnswers>answers) 
		{
			loops = prevAnswers; 
		}
		//console.log (prevAnswers + " > " + answers);
		
		for (var i = 1; i<=loops; i++)
		{
			if (i > prevAnswers)
			{
				var html = '<div id="divAnswer'+i+'">'+
				'<label class="inset" for="answer'+i+'">Antwort '+i+':</label>' + 
				'<input class="inset text" type="text" name="answer'+i+'" id="answer'+i+'" value=""/>' + 
				'</div>';
				
				$("#pollAnswers").append(html)
			}
			else if (i>answers)
			{
				$("#divAnswer" + i).remove();
			}
		}
		prevAnswers = answers;
	});
	adjustWindowSize();
	
	
	$("a.showPollButton").live("click", function() {
		var id = parseInt(this.id.substring(14));
		if ($("#graph" + id).is(":visible"))
		{
			$("#graph" + id).find("svg").remove();
			$("#graph" + id).hide();
			$(this).html(lang["showPollResults"]);
		}
		else
		{
			showPollResult(id);
			$(this).html(lang["hidePollResults"]);
		}
	});
	$("a.nextSlogan").live("click",function() {
		$.ajax({
			url:"slogan",
			success:function(data){
					$("div.slogan").replaceWith(data);
				}
			});
	});
});
function openChat(id)
{
	if ($("#chatWindows").find("#chat_" + id).length==0)
		{
			$.ajax({
					url:"chat.php?task=openChat",
					type:"post",
					data:"user=" + id,
					success:function(data){
						$("#chatWindows").append(data);
						var chatWindowObj = $("#chatWindows").find("#chat_" + id);	
						initializeChat(chatWindowObj);
						//var obj=document.getElementById("messageContainer" + id);
						scrollDownChatContainerWhenImageLoaded(id);
						//initializeFancybox(chatWindowObj);
						//makeSmallImagesUnclickable(chatWindowObj);
						//$("#messageContainer"+id).scrollTop(obj.scrollHeight);
						//alert($("#messageContainer"+id).scrollTop() + " -> "+ obj.scrollTop);
					}
			});
	}
	else
	{
		$(".chatContainer").removeClass("active");
		$("#chatWindows").find("#chat_"+id).addClass("active");
	}

}
var holdShift=false, holdStrg=false;
var moving=false;
var prevMoving = true;
var onlineState = true;
function stateActiveTimer()
{
	if (!moving)
	{
		//onlineState=false;
		if (!prevMoving)
		{
			onlineState=false;
		}
	}
	else
	{
		onlineState = true;
	}
	prevMoving=moving;
	moving=false;
	//console.log(onlineState);
}

function initializeChat(that)
{
	/* @todo: automatisch anpassen
	 *	an fenstergröße - SICHTBAR!
	 * */
	//console.log(that);
	if (parseInt($(that).attr("id").substring(5))>0)
	{
		$.cookie($(that).attr("id"), "open");
	}
	if ($.cookie($(that).attr("id")+"_width"))
	{
		var newX = parseInt($.cookie($(that).attr("id")+"_width"));
		if (newX > $(window).width()) newX = $(window).width()/2;
		if (newX < 100)
		{
			newX = 100;
		}
		$(that).width(newX);
	}
	if ($.cookie($(that).attr("id")+"_height"))
	{
		var newY= parseInt($.cookie($(that).attr("id")+"_height"));
		if (newY > $(window).height()) newY = $(window).height()/2;
		if (newY < 100)
		{
			newY = 100;
		}
		$(that).height(newY);
		$(that).find(".messageContainer").height(newY-75); 
	}
	if ($.cookie($(that).attr("id")+"_X"))
	{
		var newX = parseInt($.cookie($(that).attr("id")+"_X"));
		
		if (newX>($(window).width()-$(that).width()))
		{
			newX = $(window).width()-$(that).width()-10;
		}
		if (newX<0) newX = 0;
		$(that).css("left", newX+"px");
	}
	if ($.cookie($(that).attr("id")+"_Y"))
	{
		var newY = parseInt($.cookie($(that).attr("id")+"_Y"));
		if (newY>($(window).height()-$(that).height()))
		{
			newY = $(window).height()-$(that).height()-10;
		}
		if (newY<0) newY = 0;
		$(that).css("top", newY+"px");
	}
		$(".resizeHandle").mousedown(function(e){
			$("*").attr("unselectable", "unselectable");// styled for modern browsers
			if (!$(this).parent().hasClass("active"))
			{
				 $(".chatContainer").removeClass("active");
				$(this).parent().addClass("active");
			}
			resize=true;
			resizeContainer = $(this).parent();
			resizeXStart=e.clientX;
			resizeYStart=e.clientY;
			resizeXPos = $(resizeContainer).width();
			resizeYPos = $(resizeContainer).height();
		});
		
 $(".messageContainer").click(function(){
	 if (!$(this).parent().hasClass("active"))
	 {
		 $(".chatContainer").removeClass("active");
		$(this).parent().addClass("active");
	 } 
 });
 $(".chatAnswer").focus(function() {
	 if (!$(this).parent().hasClass("active"))
	 {
		 $(".chatContainer").removeClass("active");
		$(this).parent().addClass("active");
	 } 
 });
 
 $(".chatTitleContainer").mousedown(function(e){
		$("*")	.attr("unselectable", "unselectable"); // styled for modern browsers
		if (!$(this).parent().hasClass("active"))
		{
			 $(".chatContainer").removeClass("active");
			$(this).parent().addClass("active");
		}
		
		drag=true;
		dragContainer = $(this).parent();
		dragXStart=e.clientX;
		dragYStart=e.clientY;
		dragXPos = $(dragContainer).position().left;
		dragYPos = $(dragContainer).position().top-$(document).scrollTop();
		//console.log("drag activated " + dragXStart + " " + dragYStart + " | " + dragXPos + " " + dragYPos);
	});
	$(".chatButton.close").click(function(){
		//console.log("shutting up chattimer");
		var user = $(this).parent().parent().attr("id").substring(5);
		clearTimeout(chatTimer[user]);
		$(this).parent().parent().remove();
		
		if (parseInt(user)>0)
		{
			//messageCountChat[user]=0;
			prevData[user]="";
			$.cookie("chat_" + user, null);
		}
		setTimeout('$(".qtip").hide();', 200);
	});
	
	$(".chatAnswer").keydown(function(e){
		if (e.keyCode == 13)
		{
			if (!(holdShift || holdStrg)){
					var message = escape(encodeURIComponent($(this).val()));
					var receiver = $(this).parent().attr("id").substring(5);
					//console.log("sending into nirvana: " + message);
					$.ajax({
						url:"chat/message",
						type:"post",
						data:"message=" + message + "&receiver=" +  receiver,
						success:function(data) {
							var obj = $("#chat_" + receiver).find(".messageContainer");
							obj.html(data);
							scrollDownChatContainerWhenImageLoaded(receiver);
							initializeFancybox(obj);
							prevData[receiver]=escape(data);
						}
					});
					$(this).val("");
					return false;
			}
		}
		if (e.keyCode == 27) // Esc
		{
			if ($(this).val()!="")
			{
				$(this).val("");
			}
			else
			{
				$(this).parent().find(".chatTitleContainer").find(".chatButton.close").click();
			}
		}
	});

	$(".chatAnswer").keyup(function(e){
		if(e.keyCode==16) holdShift=false;
		if (e.keyCode==17) holdStrg = false;
	});
	
	$(".chatContainer").css("right",0);
	$(".chatContainer").css("bottom",0);
	
	var chatUser =$(".chatContainer").last().attr("id").substring(5);  
	channel(chatUser);
	//initializeFancybox();
	initializeTooltips();


}
function initializeFancybox(obj)
{
	if ($(obj).find(".commentContainer.message.edit").length){
		$(obj).find(".chatButtonContainer").remove();
		
	}
	$(obj).find("a.fancybox").each(function() {
		$(this).find("img:not(.loaded)").each(function() {
			$(this).load(function() {
				if ($(this).height()>=99)
				{
					$("a.fancybox:not(.small)").fancybox({
						type:'image'
					});
				}
				else
				{
					$(this).parent().click(function(e) {
						e.preventDefault();
						return false;
					}).addClass("small");
				}
				$(this).addClass("loaded");
			}).error(function() {
				$(this).parent().remove();
			});
			$(this).attr("src", $(this).attr("ref"));
		});
	}); 

}

var messageCountChat = new Array();
var prevData = new Array();

function channel(user)
{
	$.ajax({
		url:"chat",
		type:"post",
		data:"receiver=" + user,
		success:function(data)
		{
			var escapedData = escape(data);
			if (prevData[user] != escapedData)
			{
				//if ((messageCountChat[user] < parseInt($(tempDiv).find(".message").length))&& $("#chat_" + user).find(".messageContainer").find(".loader").length==0 && messageCountChat[user]>0)
				if (prevData[user] && escapedData > prevData[user] && !muteSound)
				{
					$("#soundPlayer").jPlayer("play");
				}
				var obj = $("#chat_" + user).find(".messageContainer");
				obj.html(data);
				scrollDownChatContainerWhenImageLoaded(user);
				initializeFancybox(obj);
			}
			prevData[user] = escapedData;
			chatTimer[user] = setTimeout("channel(" + user+ ")", channelChatFrequency);
		}
	});
}

var messageCount = new Array();
var userListStore = "";
function channelAll()
{
	//console.log("shooting ALL");
	$.ajax({
		url:"chat",
		type:"post",
		success:function(data)
		{
			if (parseInt(data)!==-1)
			{
				var obj = eval("(" + data + ")");
				
				if (obj["userList"]!=userListStore)
				{
					userListStore = obj["userList"];
					$("#chatUserList").html(userListStore);
				}
				for (newUser in obj["obj"])
				{
					var x = $("#chatLink_" + parseInt(obj["obj"][newUser]["senderid"]));
					if (($("#chat_" + obj["obj"][newUser]["senderid"]).length == 0) && messageCount[obj["obj"][newUser]["senderid"]]!=obj["obj"][newUser]["messageCount"])
					{
						x.addClass("new");
						clearInterval(blinkTimer);
						blinkTimer = setInterval("chatBlinkNew()",1000);
						if(!muteSound)
						{
							$("#soundPlayer").jPlayer("play");
						}
					}
					messageCount[obj["obj"][newUser]["senderid"]]=obj["obj"][newUser]["messageCount"];
				}
			}
			allTimer = setTimeout("channelAll()",channelAllFrequency);
		}
	});
}


//======================================================================
var soundEmbed = null;
//======================================================================
function soundPlay(which)
{
	/*if (soundEmbed)	
	{
		document.body.removeChild(soundEmbed);
		soundEmbed.removed = true;
		soundEmbed = null;		
	}
	soundEmbed = document.createElement("embed");
	soundEmbed.setAttribute("src", "static/sound/"+which);
	soundEmbed.setAttribute("hidden", true);
	soundEmbed.setAttribute("autostart", true);
				
	soundEmbed.removed = false;
	document.body.appendChild(soundEmbed);*/
}
var titleBlink=false;
var titleBlinkID=0;
var titleStoreCaption = "";
function chatBlinkNew()
{	
	var titleCaption = document.title;
	if ($(".chatLink.new").length==0)
	{
		document.title = titleStoreCaption;
		clearInterval(blinkTimer);
	}
	else
	{
		if (titleBlink)
		{
			titleBlinkID=titleBlinkID+1;
			if (titleBlinkID>$(".chatLink.new").length)
			{
				titleBlinkID=1;
			}
			titleCaption = ". " + $(".chatLink.new").eq(titleBlinkID-1).find("span.name").html() + " sagt ...";
		} 
		else
		{
			titleCaption = "! " + $(".chatLink.new").eq(titleBlinkID-1).find("span.name").html() + " sagt ...";
		}
		titleBlink = !titleBlink;
		document.title = titleCaption;
		$(".chatLink.new").each(function(){
			$(this).find(".onlineState").toggleClass("blink");
		});
	}
}
function initializeTooltips()
{
	$('.qtipTitle:not(.doneQtip):not(.tooltipRight):not(.tooltipLeft)').qtip({
			position:{
				corner:{
					target: 'bottomMiddle',
					tooltip:'topMiddle'
				}
			},
			style: { 
				name: 'dark', 
				tip:'topMiddle',
				border:{
					width:3,
					radius:3
				}
			}
		}).addClass("doneQtip");
	$(".qtipTitle.tooltipRight:not(.doneQtip)").qtip({
			position:{
				corner:{
					target: 'bottomMiddle',
					tooltip:'topRight'
				}
			},
			style: {
				name: 'dark', 
				tip:'topRight',
				border:{
					width:3,
					radius:3
				}
			}
		}).addClass("doneQtip");
	$(".qtipTitle.tooltipLeft:not(.doneQtip)").qtip({
				position:{
					corner:{
						target: 'bottomMiddle',
						tooltip:'topLeft'
					}
				},
				style: {
					name: 'dark',
					tip:'topLeft',
					border:{
						width:3,
						radius:3
					}
				}
			}).addClass("doneQtip");
}

function calcLikeStats(votingContainer, like, dislike)
{
	if (like>0 || dislike>0)
	{
		//console.log (like + ":" + dislike);
		var percent = parseInt(like/(parseInt(like)+parseInt(dislike))*100);

		var width = percent/2;
		$(votingContainer).find(".votingBar.left").width(width).removeClass("deac").end()
						   .find("span.percent").text(percent + " %").end()
						   .attr("title", dislike + " "+lang['NrDislike']+" : " + like + " " + lang['NrLike']);
	}
	else
	{
		var percent = "-";
		var width = 25;
		$(votingContainer).find(".votingBar.left").width(width).addClass("deac").end()
						   .find("span.percent").text(percent + " %").end()
						   .attr("title", dislike + " "+lang['NrDislike']+" : " + like + " " + lang['NrLike']);
		
	}
}

function adjustWindowSize()
{
	$(".contentWrapperContainer").width(window.innerWidth - $(".loginFormContainer").width()-64);
	$("#chatUserList").height(window.innerHeight - 150);
}
function scrollToPos(i)
{	
	/*
	if (i>-1)
	{
		var x= $(document).scrollTop();
		$('body').scrollTop(x);
		//console.log(x);
		var elem = 'body';
		if ($.browser.firefox)
		{
			elem = document;
		}
		$(elem).animate({
			scrollTop:i-80
		},{
			duration:'slow'
		});
	}*/
}
function hC()	// hide Contextmenu
{
	$(".contextMenu").fadeOut(500, function() {
		$(this).remove();
	})
}
function stripslashes(str) {
	str=str.replace(/\\/g,'\'');
	str=str.replace(/\\"/g,'"');
	str=str.replace(/\\0/g,'\0');
	str=str.replace(/\\\\/g,'\\');
	return str;
}
function scrollDownChatContainerWhenImageLoaded(id)
{
	var obj = $("#chat_" + id);
	var scrollObj = $(obj).find(".messageContainer");
	if (scrollObj.length)
	{
		$(scrollObj).find("img").load(function() {
			setTimeout(function() {
				scrollDownContainer(scrollObj);
			},50);	// ugly bugfix
			initializeFancybox(scrollObj);
		}).error(function(e) {
			$(this).parent().remove();
		});
	}
	scrollDownContainer(scrollObj);
	
}
function scrollDownContainer(obj)
{
	if(obj[0] != undefined)
	{
		var scrollHeight = obj[0].scrollHeight;
		if (scrollHeight)
		{
			obj.scrollTop(scrollHeight+20);
		}
	}
	
	
}

	
Raphael.fn.pieChart = function (cx, cy, r, values, labels, stroke,colorHue,colorSaturation,colorBrightness,colorInterval) {
	//if (paper) paper.clear();
	var paper = this,
		rad = Math.PI / 180,
		chart = this.set();
	function sector(cx, cy, r, startAngle, endAngle, params) {
		var x1 = cx + r * Math.cos(-startAngle * rad),
			x2 = cx + r * Math.cos(-endAngle * rad),
			y1 = cy + r * Math.sin(-startAngle * rad),
			y2 = cy + r * Math.sin(-endAngle * rad);
		return paper.path(["M", cx, cy, "L", x1, y1, "A", r, r, 0, +(endAngle - startAngle > 180), 0, x2, y2, "z"]).attr(params);
	}

	//var styleColor = Raphael.rgb2hsb(100,153,153);
	var angle = 0,
		total = 0,
		start = colorHue,
		brightness=colorBrightness+0.2;//styleColor.b,
		if (brightness > 1.0)
		{
			brightness = 1.0;
		}
		var saturation=colorSaturation+0.2;//styleColor.s,
		if (saturation > 1.0)
		{
			saturation = 1.0;
		}
		var prevValue = 0;
		process = function (j) {
			var value = values[j];
			if (value >0)
			{
				var angleplus;
				if (total > values[j])
				{
					
					angleplus = (360 * value / total );
				}
				else
				{
					angleplus = (359 * value / total );
				}
				var popangle = angle + (angleplus / 2),
				color = Raphael.hsb(start, saturation,brightness),
				ms = 500,
				delta = 0;
				if (prevValue == value && value == 0)
				{
					popangle += 12;
				}
				if (angleplus == 0) angleplus = 1;
				bcolor = Raphael.hsb(start, saturation, brightness);
				var fontWeightBold="";
				var txt,p;
				if (j==uservote)
				{
					var xcolor =Raphael.hsb(start,1.0,0.2);
					p = sector(cx, cy, r+1, angle, angle + angleplus, {fill:color,stroke:xcolor, "stroke-width": 2});
					txt = paper.text(cx + (r + delta + 150) * Math.cos(-popangle * rad), cy + (r + delta + 25) * Math.sin(-popangle * rad), labels[j]).attr({fill: "#333333", stroke: "none", opacity: 1,"font-size": 12,"font-weight":"bold","font-family":"Georgia,serif","width":"150px"}); 
					if (value>0)
					{
						label = paper.text(cx + (r + delta - 40) * Math.cos(-popangle * rad), cy + (r + delta - 40) * Math.sin(-popangle * rad), value).attr({fill:"#ffffff" ,opacity: 1,"font-weight":"bold","font-size": 13,"font-family":"Arial,Helvetica,sans-serif"});
					}
				}
				else
				{
					p = sector(cx, cy, r, angle, angle + angleplus, {fill: color,stroke: stroke, "stroke-width": 1});
					txt = paper.text(cx + (r + delta + 150) * Math.cos(-popangle * rad), cy + (r + delta + 25) * Math.sin(-popangle * rad), labels[j]).attr({fill: "#333333", stroke: "none", opacity: 1,"font-size": 12,"font-family":"Georgia,serif","width":"150px"}); 
					if (value>0)
					{
						label = paper.text(cx + (r + delta - 40) * Math.cos(-popangle * rad), cy + (r + delta - 40) * Math.sin(-popangle * rad), value).attr({fill: "#ffffff", stroke: "none", opacity: 1,"font-size": 13,"font-family":"Arial,Helvetica,sans-serif"});
					}
				}
				//brightness += 0.05;
				//saturation += 0.05;
				angle += angleplus;
				chart.push(p);
				chart.push(txt);
				//saturation-=.1;
				prevValue = value;
			}
			start+=colorInterval;
			if (start>1) start = start-1;
			if (start<0) start = 1+start;
		};
	for (var i = 0, ii = values.length; i < ii; i++) {
		total += values[i];
	}
	if (total > 0)
	{
		for (i = 0; i < ii; i++) {
			process(i);
		}
	}
	else
	{
		var txt = paper.text(cx, cy, lang["noPollVotes"]).attr({fill: "#999999", stroke: "none", opacity: 1,"font-size": 10,"font-family":"Georgia,serif"}); 
	}
	
	return chart;
};


var uservote = -1;
var values = [],
	labels = [];

function showPollResult(id)
{
	uservote=-1;
	values[id] = [];
	labels[id] = [];
	$("#poll"+id+" tr").each(function (e,i) {
			values[id].push(parseInt($("td", this).text()));
			if ($(this).hasClass("uservote"))
			{
				uservote = e;
			}
			labels[id].push($("th", this).text());
		});
	if ($("#graph" + id).find("svg").length > 0)
	{
		$("#graph" + id).find("svg").remove();

	}

	var width = $(".contentWrapperContainer").width();


	$("#graph" + id).show();

	var color = Raphael.color(styleColor),
	hue = color.h,
	sat = color.s,
	bri = color.l;
	Raphael("graph"+id,width,250).pieChart(width/2, 120, 80, values[id], labels[id], "#fff", hue, sat,bri, -0.22);
	$("#showPollButton" + id).html(lang['hidePollResults']);
}
var styleColor;
function setStyleColor(color)
{ 
	if (color)
	{
		styleColor = color;
		$(".styleColorBorder").css("border-color", color);
		$(".styleColorBorderTop").css("border-color-top", color);
		$(".styleColorBorderBottom").css("border-color-bottom", color);
		$(".styleColorBackground").css("background-color", color);
		$(".styleColor").css("color", color);
	}
}
function colorToHex( c ) {
	var m = /rgba?\((\d+), (\d+), (\d+)/.exec( c );

	return m
	? '#' + ( toHex(m[1])+toHex(m[2])+toHex(m[3]))
	: c;
};

function toHex(n) {
	n = parseInt(n,10);
	if (isNaN(n)) return "00";
	n = Math.max(0,Math.min(n,255));
	return "0123456789ABCDEF".charAt((n-n%16)/16)
		+ "0123456789ABCDEF".charAt(n%16);
}
function generateRandomColor()
{
	var $r = rand(0,150);
	var $b;var $g;
	if ($r > 100)
	{
		$g = rand(0,50);
	}
	else
	{
		$g = rand(0,150);
	}
	if (($r+$g) > 100)
	{
		$b = rand(0,50);
	}
	else
	{
		$b = rand(0,150);
	}

	$r = toHex($r);
	$g = toHex($g);
	$b = toHex($b);

	return "#" + $r + $g + $b;
}
function rand(min,max) {
	if(min > max) {
		return -1;
	}

	if(min == max) {
		return min;
	}

	var r;

	do {
		r = Math.random();
	}
	while(r == 1.0);

	return min + parseInt(r * (max-min+1));
}