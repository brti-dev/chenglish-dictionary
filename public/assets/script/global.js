var confirm_exit = false;
window.onbeforeunload = function() {
  if(confirm_exit) return "";
}

function togglefj(){
	
	$(".vocablist dt .hz").toggle();
	
	$("#toggcontr .fjsw").toggleClass("fjsw-on");
	
}

function fcnav(dir) {
	
	if( $("a.fcnav").hasClass("disable") ) return;
	$("a.fcnav").addClass("disable").animate({opacity:1}, 700, function(){ $("a.fcnav").removeClass("disable"); });
	
	var cur = $("dl.fcnav-curr");
	var prv = $("dl.fcnav-curr").prev();
	if(!$(prv).length) {
		prv = $(".vocablist .fcards dl:last");
	}
	$(prv).css("left", "-740px");
	var nxt = $("dl.fcnav-curr").next();
	if(!$(nxt).length) {
		nxt = $(".vocablist .fcards dl:first");
	}
	$(nxt).css("left", "740px");
	
	if(dir == "next") {
		$(cur).animate({left:"-740px"}, 400, function(){ $(this).removeClass("fcnav-curr"); });
		$(nxt).animate({left:"0px"}, 400).addClass("fcnav-curr");
	}
	if(dir == "prev") {
		$(cur).animate({left:"740px"}, 400, function(){ $(this).removeClass("fcnav-curr"); });
		$(prv).animate({left:"0px"}, 400).addClass("fcnav-curr");
	}
	
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

	$("#search-input").focus(function(){
		$('#search-info').fadeIn();
	}).blur(function(){
		$('#search-info').animate({opacity:1}, 200, function(){ $(this).fadeOut(); });
	});

	$(document).keydown(function(Ev) {
		
		if( !$(".vocablist").length ) return;
		
		if( $("#box").is(":visible") ) return; //if editing an item, for example
		
		var k = Ev.keyCode;
		if(k == 104 || k == 72) {
			//h
			$(".vocablist dt").toggleClass("toggle-vis");
			$("#toggcontr .sw-hz").toggleClass("sw-on");
		}
		if(k == 80 || k == 112) {
			//p
			$(".vocablist dd.pinyin").toggleClass("toggle-vis");
			$("#toggcontr .sw-py").toggleClass("sw-on");
		}
		if(k == 100 || k == 68) {
			//d
			$(".vocablist dd.definitions").toggleClass("toggle-vis");
			$("#toggcontr .sw-df").toggleClass("sw-on");
		}
		if(k == 120 || k == 88) {
			//x
			//$(".vocablist dd.extras").toggleClass("toggle-vis");
		}
		if(k == 109 || k == 77) {
			//m
			//toggleMemorized();
		}
		if(k == 102 || k == 70) {
			//f
			togglefj();
		}
		if(k == 37) {
			//left
			fcnav("prev");
		}
		if(k == 39) {
			//right || .> || /?
			fcnav("next");
		}
		
	});
	
	$("#toggcontr a").click(function(){
		$(this).nextUntil("a").toggleClass("sw-on");
	});
	
});

function resetUnfocused() {
	$(".resetonfocus").val("");
}

function editVocab(vid){
	$("#vocab-"+vid).before('<div id="box"><a href="#close" onclick="$(\'#box\').fadeOut(function(){$(\'#box\').remove()});">close</a><div class="container"></div></div>');
	
	$("#box").fadeIn().find(".container").html('<h2>Edit Vocab</h2>Loading...');
	
	$.post(
		"/vocab.php",
		{ edit:vid },
		function(res){
			$("#box .container").html(res);
		}
	);
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
				$("#vocab-"+vid).replaceWith(res);
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
			_vocab_id: vid
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

function markVocab(el, act){
	var vid = $(el).closest("dl").data("vocab_id");
	
	$(el).parent().addClass("marked");
	
	$.post(
		"/vocab.php",
		{ _action: "mark",
			_vocab_id: vid,
			_act: act
		}, function(res){
			if(res != '') alert(res);
			$(el).parent().removeClass("marked");
		}
	);
	
	fcnav("next");
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