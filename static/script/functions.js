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
	var channelAllFrequency = 10000; // Alle X Sekunden wird nach neuen
									// Post von allen Benutzern gefragt.
	var channelChatFrequency = 4000;		// Wenn ein Chat-Fenster geöffnet 
									// wird alle X Sekunden nach neuen 
									// Posts gefragt.
var limitFrom=0;
var limitCount=30;
var postCount=0;
var muteSound = false;

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
	$.ajax({
		url:"index.php?task=getPosts",
		type:"post",
		data:dataString,
		success:function(data) {
			//console.log(data);
			$(".contentWrapperContainer").append(data);
			initializeFancybox($(".contentWrapperContainer"));
			initializeTooltips();
		}
	});

}

$().ready(function() {
	
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
	postCount = parseInt($("#postCount").val());
	if (postCount)
	{
		getPosts(limitFrom+","+limitCount);
		scrollToPos(0);
		$(window).scroll(function() {

			var scrollTop = $(this).scrollTop();
			var scrollHeight = $(document).height()-$(window).height();
			if (scrollTop == scrollHeight)	//scrolled to Bottom
			{
				limitFrom += limitCount;
				if (limitFrom<postCount)
				{
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
		$(".soundButton").removeClass("on").addClass("off");
		muteSound=true;
	}
	else
	{
		muteSound = false;
	}
	adjustWindowSize();
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
			$(this).removeClass("on").addClass("off");
			muteSound = true;
			$.cookie("muteSound","true");
		}
		else
		{
			$(this).removeClass("off").addClass("on");
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
			width=48;height=48;marginTop=-22;marginLeft=-20;right=0;
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
			width=16;height=16;marginTop=-2;marginLeft=0,right=16;
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

	$("a.footerButton.comment").live("click", function () {
		if ($(this).hasClass("active"))
		{
			$(this).parent().parent().find(".commentContainerWrapper").slideUp(500,function() { $(this).empty() });
			$(this).removeClass("active");
		}
		else
		{
			var that = this;
			var id = parseInt($(this).parent().parent().attr("rel").substr(5));
			var dataString = "id=" + id;
			$.ajax({
				url:"comments",
				type:"post",
				data:dataString,
				success:function(data) {
					$(that).addClass("active");
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
		var obj = $(that).parent();
		if ($(that).hasClass("edit"))
		{
			//edit
			id = $(that).parent().attr("rel").substr(5);
			if ($(that).parent().parent().parent().hasClass("postContainer"))
			{
				parentID = parseInt($(that).parents(".postContainer").attr("rel").substr(5));
				obj = $(that).parents(".postContainer");
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
		}
		
		if (parentID>0)
		{
			var message = escape(encodeURIComponent($(that).val()));
			$.ajax({
				url:"sendComment",
				type:"post",
				data:"message="+message + "&parentID=" + parentID + "&id=" + id,
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
					//console.log(obj);
					if ($(obj).hasClass("commentWrapper"))
					{
						$(obj).replaceWith(data);
					}
					else
					{
						//console.log(obj);
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
		var contextMenu = "<div class='contextMenu'><a href='javascript:openChat("+id+");'><strong>"+lang["openChat"]+"</strong></a><a href='u"+id+"'>"+lang["showProfile"]+"</a></div>";
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
		var id = $(this).attr("href").substring(5);
		//alert($("#chatWindows").find("#chat_" + id).length);
		openChat(id);
	return false;
	});

		// Default Input Values 
		var isStatusFocused=false;
		// für "Status aktualisieren"
	 $("#statusMessage").val(lang['statusBlank']);
	 $(".newPostForm.status").focusin(function() {
	 
		isStatusFocused=true;
		 if ($("#statusMessage").val()==lang['statusBlank'])
		 {
			$("#statusMessage").animate({height:100},500).removeClass("empty").parent().find(".submit").addClass("show").parent().find(".resizeHandleVertical").addClass("show"); 
			$("#statusMessage").val("");
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
	 // für Diskussion:
	 $("#postCaption").val(lang["discussCaptionBlank"]);
	 $("#postCaption").focus(function() {
		 if ($(this).val()==lang["discussCaptionBlank"])
		 {
				$(this).val("") ;
				$(this).removeClass("empty");
		 } 
	 });
	 $("#postCaption").blur(function() {
		if ($(this).val()=="")
		{
			$("#postCaption").val(lang["discussCaptionBlank"]);
			$("#postCaption").addClass("empty");
		}
	});
	$("#postMessage").val(lang["discussBlank"]);
	$("#postMessage").focus(function(){
		if ($(this).val()==lang["discussBlank"])
		{
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
		$("#newStatusForm").submit(function() {
			//alert("Kein Text eingebeben!");
			if ($("#statusMessage").val()!="" && $("#statusMessage").val()!=lang['statusBlank'])
			{
				var dataString = "status=" + escape(encodeURIComponent($("#statusMessage").val()));
				$.ajax({
					url:"index.php?task=ajaxPost",
					type:"post",
					data:dataString,
					success:function(data){
						$("#statusMessage").val(lang['statusBlank']);
						$("#newStatusForm").removeClass("show");
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
				$("#statusMessage").addClass("error");
			}
		 return false; 
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
	$("a.addPost.discuss").live("click", function(e) {
		if ($(".newPostForm.discuss").is(":visible"))
		{
			$(".newPostForm.discuss").removeClass("show");
			$(this).removeClass("active");
		}
		else
		{
			$(".newPostForm").removeClass("show");
			$(".newPostForm.discuss").addClass("show");
			$(".addPost").removeClass("active");
			$(this).addClass("active");
		}
		
		return false;
	});
	$("a.addPost.status").live("click", function(e) {
		if ($(".newPostForm.status").is(":visible"))
		{
			if ($(this).hasClass("active"))
			{
				$(".newPostForm.status").removeClass("show");
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
			$(".newPostForm.status").addClass("show");
			$(".addPost").removeClass("active");
			$(this).addClass("active");
			$("#statusMessage").focus();
		}
		return false;
	});
	$(".commentContainer.message.edit").live("mouseenter" , function() {
		var html = '<div class="chatButtonContainer"><button class="chat edit"></button><button class="chat delete"></button></div>'
		$(this).append(html);
	}).live("mouseleave",function(e) {
		$(this).find(".chatButtonContainer").remove();
	}).live('dblclick',function() {
		$(this).find(".chatButtonContainer").find("button.chat.edit").click();
	});
	$(".commentContainerWrapper").each(function() {
		if ($(this).html()!="")
		{
			$(this).show();
		}
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
						var chatWindowObj = ($("#chatWindows").find("#chat_" + id))
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
	//console.log("shooting CHAT");
	
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
		var percent = like/(parseInt(like)+parseInt(dislike))*100;

		var width = percent/2;
		$(votingContainer).find(".votingBar.left").width(width).removeClass("deac").end()
						   .find("span.percent").text(percent + " %").end()
						   .attr("title", like + " stimmen zu : " + dislike + " lehnen ab");
	}
	else
	{
		var percent = "-";
		var width = 25;
		$(votingContainer).find(".votingBar.left").width(width).addClass("deac").end()
						   .find("span.percent").text(percent + " %").end()
						   .attr("title", like + " stimmen zu : " + dislike + " lehnen ab");
		
	}
}

function adjustWindowSize()
{
	$(".contentWrapperContainer").width(window.innerWidth - $(".loginFormContainer").width()-64);
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
	var scrollHeight = obj[0].scrollHeight;
	obj.scrollTop(scrollHeight);
}