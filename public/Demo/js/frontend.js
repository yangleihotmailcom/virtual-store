$(document).ready( function () {
  
  var setupYoutube = function ( id ) { 
     var href = "http://www.youtube.com/v/" + id + '?fs=1&autoplay=1' ;
     return href;
  }
  
   $('a.videoLink').css({ "background-image": "url('images/demopage.png')"})
                   .click( function (){
                        var href = setupYoutube( $(this).attr('youtube') );
                        var height = 315*2;
                        var width = 420*2;

                        $.fancybox({
                            'padding'      : 0,
                            'autoScale'    : true,
                        	  'transitionIn' : 'fade',
                      			'transitionOut': 'fade',
                      			'title'        : $(this).attr('caption'),
                  			    'width'        : width,
                  			    'height'       : height,
                  			    'href'         : href,
                  			    'type'         : 'swf',
                  			    'swf'          :{ 'wmode': 'transparent' , 'allowfullscreen': 'true'}     
                            });
                    })
                   .each( function (i, link ) {
                       var caption = $(this).attr('caption');
                	     $(this).html('<div class="caption">'+caption+'</div><img class="play" src="images/playIcon.png" />');
                       var href = setupYoutube( $(this).attr('youtube') );
                	   $( '#tabs-'+(i+2)+' > p > a').attr({href:href});
                    });
   
   $('#intro').tabs();
});
