
var _fj = 0;

$(document).ready(function(){
	
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

function togglefj(){
	
	//$(".vocablist dt").show();
	
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