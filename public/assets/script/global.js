var confirm_exit = false;
window.onbeforeunload = function() {
  if(confirm_exit) return "";
}

$(document).ready(function(){
	
	$("input[type='button'], input[type='submit'], input[type='reset']").hover(
		function(){
			$(this).addClass("over");
		}, function(){
			$(this).removeClass("over");
		}
	).mousedown(function() {
		$(this).addClass("down");
	}).mouseup(function() {
		$(this).removeClass("down");
	}).mouseout(function() {
		$(this).removeClass("down");
	});
	
	$(".preventdefault").click(function(Ev) {
		Ev.preventDefault();
	});
	
	$("table.results tr").hover(
		function(){
			$(this).addClass('over');
		}, function(){
			$(this).removeClass('over');
		}
	);
	
	$("#nav > ul > li").hover(
		function(){
			$(this).children("ul").slideDown(200);
		}, function(){
			$(this).children("ul").slideUp(200);
		}
	);
	
	/*
	$(".vocablist dl").hover(
		function(){
			
		}, function(){
			
		}
	);*/
	
	$(".vocab .mark").live("click", function(){
		
		var vid = $(this).closest("dl").attr("id").replace("vocab-", "");
		var act = $(this).attr("rel");
		
		$(this).addClass("marked").find("img").attr("src", "/assets/img/loading_arrows_white.gif");
		$(this).animate({opacity:1}, 1000, function(){
			$(this).removeClass("marked").find("img").attr("src", "/assets/img/smile.png").animate({opacity:1}, 1000, function(){
				$(this).attr("src", "/assets/img/mark_"+act+".png");
			});
		});
		
		$.post(
			"/vocab.php",
			{ _action: "mark",
				_vocabid: vid,
				_act: act
			}, function(res){
				if(res != '') alert(res);
			}
		);
		
		fcnav("next");
		
	});

	$(".editvocab").live("click", function(){
		
		var vid = $(this).attr("rel");
		
		$(this).closest("dl").before('<div id="box"><a href="#close" onclick="$(\'#box\').fadeOut(function(){$(\'#box\').remove()});">close</a><div class="container"></div></div>');
		
		$("#box").fadeIn().find(".container").html('<h2>Edit Vocab</h2>Loading...');
		
		$.post(
			"/vocab.php",
			{ edit:vid },
			function(res){
				$("#box .container").html(res);
			}
		);
	
	});

	$("#search-input").focus(function(){
		$('#search-info').fadeIn();
	}).blur(function(){
		$('#search-info').animate({opacity:1}, 200, function(){ $(this).fadeOut(); });
	});
	
});

function resetUnfocused() {
	$(".resetonfocus").val("");
}

function submitEditVocab(vid, submButton){
	
	$(submButton).attr("disabled", "disabled").next().show();
	
	var formfields = $(submButton).closest("form").serialize();
	
	$.post(
		"/vocab.php",
		{ submit_edit:'1',
			_input:formfields
		}, function(res){
			if(res.substr(0,5) == "ERROR") {
				alert(res);
				$(submButton).removeAttr("disabled");
			} else {
				$(submButton).next().css("background-image","none").html("Saved!").animate(
					{opacity:1}, 500, function(){
						$("#box").fadeOut(function(){
							$("#box").remove();
						});
					}
				);
				$("#vocab-"+vid).after(res);
				$("#vocab-"+vid+":eq(0)").remove();
			}
		}
	);
	
}
	

function removeVocab(vid) {
	
	if(!confirm("Really remove this entry?")) return;
	
	var $obj = $("#vocab-"+vid);
	if(!$($obj).length) return;
	
	$($obj).fadeOut(1000);
	$("#box").fadeOut();
	
	$.post(
		"/vocab.php",
		{ _action: "delete",
			_vocabid: vid
		}, function(t) {
			if(t) {
				alert("Error: "+t);
				$(obj).show();
			} else {
				$($obj).remove();
			}
		}
	);

}

function reposBox(el){
	
	//reposition #box to the position of el
	
	var pos = $(el).offset();
	var cssObj = {
		"top": pos.top+"px"
	}
	$("#box").css(cssObj);
	
}

//	Device-scale user interface elements in iOS Mobile Safari
//	http://37signals.com/svn/posts/2407-device-scale-user-interface-elements-in-ios-mobile-safari
/*(function() {
  var hasTouchSupport = "createTouch" in document;
  if (!hasTouchSupport) return;

  var headElement  = document.getElementsByTagName("head")[0];
  var styleElement = document.createElement("style");

  styleElement.setAttribute("type", "text/css");
  headElement.appendChild(styleElement);

  var stylesheet = styleElement.sheet;

  window.addEventListener("scroll", updateDeviceScaleStyle, false);
  window.addEventListener("resize", updateDeviceScaleStyle, false);
  window.addEventListener("load",   updateDeviceScaleStyle, false);
  updateDeviceScaleStyle();

  function updateDeviceScaleStyle() {
    if (stylesheet.rules.length) {
      stylesheet.deleteRule(0);
    }

    stylesheet.insertRule(
      ".device_scale {-webkit-transform:scale(" + getDeviceScale() + ")}", 0
    );
  }

  // Adapted from code by Mislav Marohnic: http://gist.github.com/355625
  function getDeviceScale() {
    var deviceWidth, landscape = Math.abs(window.orientation) == 90;

    if (landscape) {
      // iPhone OS < 3.2 reports a screen height of 396px
      deviceWidth = Math.max(480, screen.height);
    } else {
      deviceWidth = screen.width;
    }

    return window.innerWidth / deviceWidth;
  }
})();*/