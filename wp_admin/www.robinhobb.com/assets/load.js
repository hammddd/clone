 
  
    
 
  $('body').imagesLoaded().always( function( instance ) {
    if ($(window).width() > 767) {
      right = $('.column-sidebar-02__content-wrap').height();
      left = $('section.main-body').height();
      console.log("Left-> " + left + " Right-> " + right);
      if (left > right) {
        $('.column-sidebar-02__content-wrap').height(left);
      } else {
        $('section.main-body').height(right);
      }
    }
  });
  
  function widthResizer(){
    var width = window.innerWidth
    if (width <= 767) {
      $(".column-sidebar-01__content-wrap").prependTo(".column-last");
    } else {
      $(".column-sidebar-01__content-wrap").prependTo(".column-sidebar-01");
    }
  }
  
  
  $( document ).ready(function() {
    widthResizer();
    window.addEventListener('resize', widthResizer);
  });
