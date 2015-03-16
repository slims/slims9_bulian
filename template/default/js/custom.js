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
      $('.s-menu-content').removeClass('slideOutRight animated-fast').toggleClass('active slideInRight');
    });

    $('#hide-menu').on('click', function(){
      $('.s-menu-content').removeClass('slideInRight animated-fast').toggleClass('active animated slideOutRight');
    });

    // Animate background color
    // ============================================
    var bg = $('.gradients');
    function fade() {
      var divs = bg.children();
      $(".current").transition({opacity: 1}, 5000, 'linear', function() {
        $('.current').removeClass('current');
        firstDiv = divs.first();
        firstDiv.addClass('current').css({opacity: 0});
        firstDiv.appendTo(bg);
        fade();
      });
    }
    fade();
});
