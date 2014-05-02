$(document).ready(function() {
       				
		$('#top-menu li a').click(function () {
			$('.item').animate({left: '-1000px'}, 'fast', loadContent);			
			var target = $(this).attr('href');
			
			function loadContent(){
				$('.item').load(target, function(){
					$('#item_informasi').animate({left: 0}, 'fast');
					
				});
			}
			return false;
		});

		$('#top-menu li').hover(
		    function() {
		        $(this).animate({backgroundColor:'#336600'}, 300);
		    }, 
		    function() {
		        $(this).animate({backgroundColor:'#99CC00'}, 100);
			return false;
	    	}
	    );
		
		$('.menu a').click(function () {
			$('.subcontent').slideOut('slow', loadContent);			
			var target = $(this).attr('href');
			
			function loadContent(){
				$('#sidebar').load(target, function(){
					$('.subcontent').slideIn('slow');
					
				});
			}
			return false;
		});
						
		$('#clock').jclock();
});