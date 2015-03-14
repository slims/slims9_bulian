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

});
