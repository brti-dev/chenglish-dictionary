
this.tooltip = function(){
	
	/* CONFIG */		
		yOffset = 5;
		xOffset = -11;		
		// these 2 variable determine popup's distance from the cursor
		// you might want to adjust to get the right result		
	/* END CONFIG */		
	$(".tooltip").hover(function(e){
		
		if( $(this).hasClass("tooltip-offset") ) yOffset = -150;
		else yOffset = 5;
		
		this.t = this.title;
		this.title = "";
		$("body").append("<p id='tooltip'>"+ this.t +"</p>");
		$("#tooltip")
			.css("top",(e.pageY - xOffset) + "px")
			.css("left",(e.pageX + yOffset) + "px")
			.fadeIn("fast");
    },
	function(){
		this.title = this.t;		
		$("#tooltip").remove();
  });
  
  //move the tooltip with the cursor
	$(".tooltip").mousemove(function(e){
		$("#tooltip")
			.css("top",(e.pageY - xOffset) + "px")
			.css("left",(e.pageX + yOffset) + "px");
	});
};

// starting the script on page load
$(document).ready(function(){
	tooltip();
});