$(document).ready(function(){

    // Show or hide placeholder inside Search form
    // ============================================
    $('.s-search').focus(function(){
      $(this).attr('placeholder','e.g. Library and Information');
    })
    $('.s-search').blur(function(){
      $(this).removeAttr('placeholder');
    })

    // Show hide menu
    // ============================================
    $('#show-menu').on('click', function(){
      $('.s-menu-content').removeClass('slideOutRight').addClass('active slideInRight');
      $('.s-menu-content ul a').addClass('animated fadeInRight delay4');
    });

    $('#hide-menu').on('click', function(){
      $('.s-menu-content').removeClass('active slideInRight').addClass('slideOutRight');
      $('.s-menu-content ul a').removeClass('animated fadeInRight delay4');
    });

    // Hide only an empty tag inside biblio detail
    // ============================================
    $('.controls:empty').parent().hide();

    // Show or hide chat
    // ============================================
    $('#show-pchat').on('click', function(){
        $('.s-chat').toggleClass('s-maximize ');
        $('.s-pchat-toggle i').toggleClass('fa-comment-o fa-times ');
    });

});


// Select makeover
// ============================================
(function(){
	var customSelects = document.querySelectorAll(".custom-dropdown__select");
	for(var i=0; i<customSelects.length; i++){
		if (customSelects[i].hasAttribute("disabled")){
			customSelects[i].parentNode.className += " custom-dropdown--disabled";
		}
	}
})()

$(document).ready(function(){
  $(window).load(function () {
    $('#keyword').focus();
  });

  $('.s-menu-toggle').click(function(e){
    e.preventDefault();
    $(this).toggleClass('is-active');
    $('.s-menu-content').toggleClass('active');
  })

  $('#content').css("min-height", (screen.height - 333));

});
