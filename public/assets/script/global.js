const flashcard_length = 100; //in em
var current_flashcard_i = 0;

var confirm_exit = false;
window.onbeforeunload = function() {
  if(confirm_exit) return "";
}

/**
 * Navigate flash cars
 * @param  {integer} increments A signed integer indicating number of positions to navigate
 * -1 Navigate one card back (to the left)
 * 1 Navigate one card forward
 * 0 Navigate to the beginning
 */
function fcnav(increments) {
	
	if( $("a.fcnav").hasClass("disable") ) return;
	$("a.fcnav").addClass("disable");
	
	var x_position = 0,
		x_position_str = 0;

	if (increments == 0) {
		current_flashcard_i = 0;
	} else {
		current_flashcard_i += increments;
		x_position = (increments * flashcard_length * -1);
		x_position_str = "+="+x_position+"em";
	}

	$("#fcards-container").animate({left:x_position_str}, 400, function(){
		$("a.fcnav").removeClass("disable");
		// Return if we went too far back or forward
		if (current_flashcard_i < 0) fcnav(0);
		if (current_flashcard_i >= $("#fcards-container .fcard").length) fcnav(0);
		console.log(current_flashcard_i);
	});
	
}

function toggleVocab(key) {
	console.log("toggle vocab key:", key);
	switch (key) {
		case "h":
			$(".vocablist dt").toggleClass("toggle-vis");
			$("#toggcontr .sw-hz").toggleClass("sw-on");
			break;
		case "p":
			$(".vocablist dd.pinyin").toggleClass("toggle-vis");
			$("#toggcontr .sw-py").toggleClass("sw-on");
			break;
		case "d":
			$(".vocablist dd.definitions").toggleClass("toggle-vis");
			$("#toggcontr .sw-df").toggleClass("sw-on");
			break;
		case "f":
			$(".vocablist dt .hz").toggle();
			$("#toggcontr .fjsw").toggleClass("sw-on");
			break;
	}
}

$(document).ready(function(){
	
	$(".preventdefault").click(function(Ev) {
		Ev.preventDefault();
	});

	$("#search-input").focus(function(){
		$('#search-info').fadeIn();
	}).blur(function(){
		$('#search-info').animate({opacity:1}, 200, function(){ $(this).fadeOut(); });
	});

	$("#toggcontr .controller").click(function(){
		toggleVocab( $(this).attr("accesskey") );
	});

	$(document).keydown(function(Ev) {
		
		if( !$(".vocablist").length ) return;
		
		if( $("#box").is(":visible") ) return; //if editing an item, for example
		
		var k = Ev.keyCode;
		if(k == 104 || k == 72) {
			toggleVocab("h");
		}
		if(k == 80 || k == 112) {
			toggleVocab("p");
		}
		if(k == 100 || k == 68) {
			toggleVocab("d");
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
			toggleVocab("f");
		}
		if(k == 37) {
			//left
			fcnav(-1);
		}
		if(k == 39) {
			//right || .> || /?
			fcnav(1);
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
	
	fcnav(1);
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