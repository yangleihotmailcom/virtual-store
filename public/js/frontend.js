$(document).ready( function() {
	$("#header-nav > ul").on("mouseenter", 'li', function () { 
	                    	          $(this).css({border:"1px solid #D3D3D3", "background-color":"#AAA"});
	                    	       })
                         .on("mouseleave", 'li', function() { $(this).css({border:"", "background-color":""});});

	$("#footer-nav > ul >li:even")
	                      .on("mouseenter", function () { 
   	                                  $(this).css({border:"1px solid #D3D3D3", "background-color":"#AAA",cursor:"pointer"});
   	                               })
                          .on("mouseleave", function() { $(this).css({border:"", "background-color":"",cursor:""});});   
}); 

var cart = {
		 checkout  : false ,    // Checkout if login successfully.
		 purchaseURL : null ,   //  amazon purchase URL.
		 amazonCheckout : function () { 
             if ( ! this.checkout ) { $.fancybox.close(); return; }
        //     $('authenticationFormContainer').empty().html('<div><img src="/images/ajax-loader.gif" /></div>');
             $.ajax({
            	 url  : '/index/cartitemlist',
                 type : 'GET',
                 success : function (data) {
                	 var cartList = $('#shoppingcartContent > table');
                	 $( 'tbody,tfoot' , cartList).remove();
                	 cartList.append(data);
                	 $.fancybox.close();                	 
                     setTimeout( function () { 
                    	 $("#CartCheckout").trigger('click');
                     } , 400);                	 
                 },
                 error : function () { $.fancybox.close(); }
             });
		 },
		 gotoAmazon: function (data ) {
			 
			 var purchaseURL = data.cart.purchaseURL;
			 var errors = data.cart.errors;
			 
			 if ( errors.length == 0 && purchaseURL )
			 {
            	 window.open(purchaseURL, 'Amazon', 'fullscreen=yes, scrollbars=auto');
            	 $.fancybox.close();
            	 return;
			 }
			 
			 if ( errors.length > 0  )
			 {
         		var html = $('<ol style="margin-bottom:30px;text-align:left;"></ol>');
         		for ( i = 0 ; i < errors.length; i++ ) html.append('<li>'+ errors[i].Message+'</li>');
        		var buttonOK = $('<span class="checkoutButton">Ok</span>');
        		var buttonCancel = $('<span class="checkoutButton" style="margin-left:30px;">Cancel</span>');
        		$('#CheckoutProcessContent').empty().append(html).append(buttonOK);
        		if ( purchaseURL && purchaseURL.length > 0 ) 
        		{
        			$('#CheckoutProcessContent').append(buttonCancel);
        			buttonOK.text('Continue');
        		}
        		$.fancybox.resize();
        		
        		buttonOK.click(function() {
        			if ( purchaseURL ) window.open(purchaseURL, 'Amazon', 'fullscreen=yes, scrollbars=auto');
        			$.fancybox.close();
        		});
        		
        		buttonCancel.click(function() {
        			$.fancybox.close();
        		});
        		
        		$(".checkoutButton").hover(
        			function () { $(this).css({border:"1px solid #333"});},
        			function () { $(this).css({border:""});}
   				);
			 }

		 } 
};

var Authentication = {
		 
         login : function ( result ) {
                     var login = $('#header-login').parents('li').hide();
                     var logout = $('#header-logout').parents('li').show();
                     var welcome = $('<a id="welcomeUser"></a>').attr({"href":"#"})
                                                                .html('<span>Hello '+ result.username +'</span');
                     $('<li></li>').append( welcome).insertBefore(logout);
                     if ( cart.checkout ) cart.amazonCheckout();
                     else $.fancybox.close();
                 }, 
                 
         logout : function () {
                     $('#welcomeUser').parents('li').remove();
                     $('#header-logout').parents('li').hide();
                     $('#header-login').parents('li').show();    
                 },
                 
         refreshAuthenticationForm : function ( authenticationFormContainer )
                 {
       	          var height = authenticationFormContainer.height();
       	          var parentOfForm = authenticationFormContainer.parent();
       	          var fancyContainer = parentOfForm.parent();

       	          parentOfForm.height(height+10);
       	          fancyContainer.height(height+15); 

       	          // refresh captcha
       	          $("#captcha-element img", authenticationFormContainer)
       	                            .attr({title: "Hard to read? click me!"})
       	                            .css({ cursor: "pointer"})
       	                            .click ( function () {
       		                                       $.ajax({
       			                                       url : "/ajax/authentication/refresh",
       			                                       type: "POST",
       			                                       data: { format : "json" }, 
       			                                       success : function ( data ) {
       				                                       var result = data.result;
       				                                       $('#captcha-element img').attr('src', result.src); 
       				                                       $('#captcha-id').attr('value', result.id); 
       			                                       }
       		                                       });
       		                                 } );
                   // submit 
       	          $("input[type=submit]", authenticationFormContainer).click( function () {
       		          var form = $(this.form);
       		          var formContainer = form.parent();
       		          $.ajax({
       			          url : form.attr("action"),
       		              type: "POST",
       	                  data: form.serialize(),
                             success : function ( data ) { 
                                         var pattern = /^\s*{/;
                                         if ( pattern.test( data ) )
                                 		  {
                                     		  var result = eval( "(" + data + ")" );
                                     		  Authentication.login(result);
                                     		  return;
                                 		  } 
                                         formContainer.empty().html(data); 
                                         var authenticationFormContainer = $("#authenticationFormContainer"); 
                                         Authentication.refreshAuthenticationForm(authenticationFormContainer);  
                                       }
       		          });
       		          return false ;
       	          });              
                 }
	      } ;
 
      $(document).ready( function () {

               
          $("#languages").change( function() {
              var lang = $(this).val();
              var dest = "<?php echo $this->url( array('controller'=>'Lang','action'=>'switch')) ;?>" ;
              location.href = dest + "/lang/" + lang; 
          });

          
          $("#header-login").parent('a').fancybox({
                   onComplete : function () {
                	                  var authenticationFormContainer = $("#authenticationFormContainer");  
                	                  $("#authenticationFormContainer").tabs({ 
                            	           load : function ( event , ui ) { Authentication.refreshAuthenticationForm(authenticationFormContainer); }
                            	      }); 
		                        },
		           onClosed : function () {
                             	 cart.checkout = false;
                            	 cart.purchaseURL = null;
		                      },             
                   autoDimensions : false ,
                   width : 350,
                   height : 240,
                   padding: 20,
                   scrolling : false,
                   speedIn : 0
              });

          $("#header-logout").parent('a').click( function () {
                  $.ajax({
                           url: $(this).attr("href"),
                           type:"POST",
                           data: { format : "json" }, 
                           success : function ( data ) {
                                    if ( ! data.result ) return;
                                    Authentication.logout();
                               }
                      });
                  return false;
              });
       });

var fancyboxItem = {
	onComplete : function () {
		var addToCart = $("span.addToCart");
		
		addToCart.css({cursor:"pointer"})
		         .click( function () {
						$.ajax({
							 url : "/ajax/cart/additem",
						     type: "POST",
						     dataType : "json",
						     data : { format: "json", asin: addToCart.attr("asin")},
						     success: function ( data ) { 
						    	     if ( data.result && data.qty ) $('#numOfItemsInCart').text( data.qty);
						    	 }
			        	});
					});
		$("span.addToCart,span.gotoDetailsPage > a > span,span.gotoAmazon > a > img")
				 .hover( function(){ $(this).css({border:"1px solid #333"}) ;},
                         function () { $(this).css({border:""}); } )	;	
	    }	
}      
var quickView = null ;
var quickViewFlag = false ;
function addQuickView( item, asin , title )
{
	if ( quickView ) return ; 
	
	var parentPos = item.parent().position();
	var pos = item.position();
	var centerX = item.width()/2;
	var centerY = item.height()/2;
	
	quickView = $("<div id='quickViewPane'></div>").hide();
	if ( title.length > 80 ) title = title.substring(0,80)+'...'
	var title = $('<span></span>').text(title).css({color:"black"});
	var aLink = $('<a></a>').text('Quick View')
                             .addClass('quickViewLink')
                             .attr({href: "/ajax/banner/quickview/asin/"+asin})
                             .fancybox({ onComplete : fancyboxItem.onComplete });
	
	var qvButton = $('<div></div>').append(aLink).css({"text-align": "center",
		                                               "padding": "0px"});
	
//	quickView.append(title).append(qvButton).appendTo(item);
//	quickView.append(qvButton).appendTo(item);	
	quickView.append(aLink).appendTo(item);	
	var left = centerX - quickView.width()/2;
	var top = centerY - quickView.height()/2;
	
	if ( pos.left + left + parentPos.left < 0 ) left = -5 ; 
	var limit = item.parent().parent().width();
	var diff = pos.left + parentPos.left + centerX + quickView.width()/2  - limit ; 
	if ( diff > 0  ) left -= diff ; 
	quickView.css({left:left, top:top});
//	quickView.css({left:-10 , top:10});	
	quickView.hover( function () { quickViewFlag = true ;}, 
			         function () {  
	                	  quickViewFlag = false ;	  
		                  removeQuickView() ;} );
	quickView.fadeIn('slow');
	
}

function removeQuickView()
{
	if ( ! quickView  || quickViewFlag ) return ; 
	quickView.remove();
	quickView = null ;
}


$(document).ready( function () {
// home banner
	var homeBanner = $("#home-banner");
	var numOfImages = 10 ;
	var imageHeight = 200 ;
	//var first = 2 ;
	var animateFlag = false ;
	if ( homeBanner.length > 0 )
	{
		
		function initBanner()
		{
			animateFlag = false ;
			container.fadeOut(2000, showBanner);
		}

		tempCategory = '';  // for category landing page.
		function showBanner()
		{
			container.html("");
			container.css({"left": 0 });
			$('#home-banner-waiting').fadeIn('fast');			
  		    if ( tempCategory == '' && typeof(categoryName) == 'string') tempCategory = categoryName;
			$.ajax({
				url: '/ajax/banner/home',
				type: 'POST',
				dataType : 'json',
				data : { num : numOfImages, format:"json", category: tempCategory },
				success : function ( results ) {
					var data = results.results;
					var num = data.length;
					$.each( data, function ( i, img ) {
					   var item = $('<img />').attr({ 
									   "id" : "banner-"+img.asin,
									   "src": img.LargeImage,
									   "alt": img.Title,
									   "title" : img.Title, 
									   //"height" : imageHeight,
									   "asin"  : img.asin
								   }).css({"max-height":imageHeight, "vertical-align":"middle"})
								     .load( function () {
									    num--;
									    if ( num ) return ;
									    $('#home-banner-waiting').fadeOut('fast');
										container.fadeIn(3000, moveHomeBanner);							    
										//var firstBanner = $("#banner-container img:first");
										function moveHomeBanner()
										{  // return;
											var lastBanner = $("#banner-container img:last");
											var last  = lastBanner.position().left + lastBanner.width() - homeBanner.width();

											animateFlag = true ;
											if ( last < 0 ) last = 0 ;
											container.animate({"left":-last},numOfImages*1000,'linear',initBanner );
											//container.animate({"left":-last},numOfImages*1000 );
										}					   
								   }).hover(
										      function() { 
										    	  if ( animateFlag ) container.pause() ;
		//								    	  $(this).addClass('hover');
										    	  $(this).css({"border": "1px #333 solid"});
										      },
										      function() { 
										    	  if ( animateFlag ) container.resume();
		//								    	  $(this).removeClass('hover');
										    	  $(this).css({"border": "1px white solid"});
									    	  }
									);
					         var aLink = $('<a></a>').append(item)
					                                  .appendTo(container)
					                                  .addClass('quickViewLink')
                                                      .attr({href: "/ajax/banner/quickview/asin/"+img.asin})
                                                      .fancybox({ onComplete : fancyboxItem.onComplete });
	 			       })
				}
			});
		}
		var container = $("#banner-container");
		homeBanner.css({height:imageHeight+3});
		initBanner();
	}
});


$(document).ready(function () {
	
	var numOfItems = 10 ;
	var imageHeight = 100;
	
	$("#content > div.section > span").click(function () {
		var name = $(this).attr("name");
		var container  =$(this).siblings('div');
		var Height = container.parent().height();
		if ( Height  > 165 ) container.parent().css({"height": Height});
		container.hide();
		container.html('');
		container.css({left:5});
		$(this).siblings('span').removeClass().addClass('initStatus');
		$(this).removeClass().addClass('selectedMenu');
		$.ajax({
			url: '/ajax/banner/category',
			type: 'POST',
			dataType : 'json',
			data : { num : numOfItems, categoryName : name, format:"json" },
			success : function ( results ) { 
				 var data = results.results
			     var imageLoaded = numOfItems ;
				 $.each( data, function ( i, item) {
				     var itemDiv = $("<div></div>").hover(function() {
														$(this).css({"border": "1px #333333 solid"});
														 var asin = item.asin;
											    	     var title = item.Title;
												    	 addQuickView($(this),asin,title);
												    }, function(){
													     $(this).css({"border": "1px white solid"});
											    	     removeQuickView();
												    });
					 var itemImage = $('<img />').attr({ 
								   "src": item.MediumImage,
								   "alt": item.Title,
								   "title": item.Title,
								   "asin" : item.asin }).css({"max-height" : imageHeight });
					 itemDiv.append(itemImage);
					 if ( item.Price || item.Amount )
					 {
						 var price = 0 ; 
						 if ( ! item.Price ) price = item.Amount;
						 if ( ! item.Amount ) price = item.Price;
						 if ( item.Price && item.Amount ) price = Math.min(item.Price, item.Amount);
						 
						 price /= 100 ;
						 itemPrice = $('<span></span>').text( '$' + price.toFixed(2) );
						 itemDiv.append('<br />').append(itemPrice);
					 }
					 else 
						 itemDiv.append('<br /><span>&nbsp;</span>');
					 itemDiv.appendTo(container);
				 });
				 container.fadeIn(2000);
			}
		});	     	
	}).hover( function () { $(this).addClass('hover') } , function () { $(this).removeClass('hover') } );
	
	$("#content > div.section > span:nth-child(2)").each( function (i) {
		 $(this).trigger('click');
	});
	
	function getPositions( itemsContainer )
	{
		var positions = [];
		var items = itemsContainer.children('div');
		$.each( items,function (i, item) {
			var position = { left : $(item).position().left, width : $(item).width() };
			positions.push( position );
		});
		return positions;
	}
	
	$("#content > div.section > img.leftPointer").hover(
           function() { $(this).attr({'src':'/ajax/banner/cursor/direction/left/width/10/height/20/color/333333'})},
           function() { $(this).attr({'src':'/ajax/banner/cursor/direction/left/width/10/height/20/color/cccccc'})}
	).click( function () {
		 var container = $(this).siblings('div')
		 var positions = getPositions( container );
		 var totalLen = positions[positions.length-1].left + positions[positions.length-1].width;		 
		 var current = container.position().left;
		 var limit = $(this).parent().width();
	//	 var available = totalLen + current;

		 if ( totalLen <= limit ) return ;
		 for ( i = 0 ; i < positions.length-1 ; i++) positions[i].left += current;
		 if ( current + limit < 5 )
		 {
			 for ( i = 0 ; i < positions.length-1 ; i++) if( positions[i].left >= 5 ) break;
			 container.animate({left: current+limit-positions[i].left } , 'slow');
		 }
		 else
			 container.animate({left: 5}, 'slow');
	});
	
	$("#content > div.section > img.rightPointer").hover(
	           function() { $(this).attr({'src':'/ajax/banner/cursor/direction/right/width/10/height/20/color/333333'})},
	           function() { $(this).attr({'src':'/ajax/banner/cursor/direction/right/width/10/height/20/color/cccccc'})}
	).click( function () {
		 var container = $(this).siblings('div')
		 var positions = getPositions( container );
		 var totalLen = positions[positions.length-1].left + positions[positions.length-1].width;		 
		 var current = container.position().left;
		 var limit = $(this).parent().width();
		 var available = totalLen + current;

		 if ( totalLen <= limit ) return ;
		 for ( i = 0 ; i < positions.length-1 ; i++) positions[i].left += current;
		 if ( available >= 2*limit)
		 {
			 for ( i = 0 ; i < positions.length-1 ; i++) if( positions[i].left > limit ) break;
			 container.animate({left: current-positions[i-1].left } , 'slow');
		 }
		 else
			 container.animate({left: -totalLen+limit}, 'slow');
	});

});

// navigator
$(document).ready( function() {
	$.ajax({
		url: '/ajax/banner/navigator',
		type: 'GET',
		success : function ( result ) { 
			   $("#navigator").empty().html(result);
			   if ( typeof(searchParam) == 'object' ) {
				   $('#category').val( searchParam.category );
				   $('#keyword').val( searchParam.keyword );
			   }

			   var tempCategory = '';
			   if ( typeof(categoryName) == 'string')
			   {
				   tempCategory = categoryName;
				   $('span.categoryName').each( function () {
					   var name = $(this).text();
					 //  console.log(name);
					   if ( name == categoryName )
					   {
						   $(this).css({"font-weight":"bolder", color:"white" });
						   $(this).parent().parent().css({"background-color":"#AAA"});
						   return false;
					   }

				   });
			   }
			   
			   $('div.navigatorCategories > ul > li').hover(
				   function () {
					    if ( $('a span' , this).text() == tempCategory ) return;
					    $(this).css({border:"1px solid #D3D3D3", "background-color":"#AAA"});
					    $("a span" , this).css({color:"white"});
				    } ,
				   function () { 
					    if ( $('a span' , this).text() == tempCategory ) return;				    	
				    	$(this).css({border:"", "background-color":"" });
				    	$("a span" , this).css({color:""});
			    	}).click( function () { document.location.href = $('a', this).attr("href");	});
			}
	})
});

// search result 
$(document).ready( function() {
    var aLink = $("<a></a>").attr({"href":"#"}).hide().appendTo('body').fancybox({ onComplete : fancyboxItem.onComplete });
	$('#searchResult ul li.itemFound')
	     .hover( function(){  $(this).css({"background-color":"#eee",cursor:"pointer"}); }, 
	    		 function(){ $(this).css({"background-color":"",cursor:""}); } )
	     .click( function () {
	    	  var asin = $(this).attr('asin'); 
	    	  aLink.attr({"href":"/ajax/banner/quickview/asin/"+asin});
	    	  aLink.trigger('click');})
	     .each( function (i, item) {
	          var image = $('div.itemFoundImage' , item);
	          var content = $('div.itemFoundContent' , item);
	          var addToCart = $('div.itemFoundAddToCartButton' , item);
	          
	          var minHeight = content.height();
	          if ( minHeight > 50 ) image.css({"min-height": minHeight+'px'});
	          
	          $('dd', item).css({ "margin-left":225 });
	          
	          var asin = $(item).attr("asin");
	          addToCart.hover(function () { $(this).css({border:"1px solid #333"})} , 
	        		          function() { $(this).css({ border: ""})})
			           .click( function ( event ) {
			        	    event.stopPropagation();
			        	    var $this = $(this);
			        	    var width = $this.width();
			        	    var height = $this.height();
			        	    $this.width( width ).height(height);
			        	    var oldText = $this.text();
			        	    $this.text( 'Adding...');
			        	    
							$.ajax({
								 url : "/ajax/cart/additem",
							     type: "POST",
							     dataType : "json",
							     data : { format: "json", asin: asin },
							     success: function ( data ) { 
							    	     if ( data.result && data.qty ) 
						    	    	 {
							    	    	addToCart.text(oldText);
						    	    	 }
							    	 }
				        	});
							return false;
						});
	     });
	
	// pagination 
    if ( $('#searchResult').length > 0 )
   	{
		var currentPage = 1 ;
		var current = null ;
		var searchURL = null ;
		
		if ( searchParam && searchParam.page && !isNaN( searchParam.page ) )
		{
			var page = parseInt(searchParam.page);
			currentPage = page <= 1 ? 1 : page ;
			page--;
			current = $("div.pageNum[page='"+currentPage+"']").css({"background-color":"#ccc",color:"white"});
			
			if ( searchParam.category && searchParam.keyword )
			searchURL = '/search/index/category/'+ searchParam.category+'/keyword/'+ searchParam.keyword+'/page/';
			
		}
		
		$("div.pageNum,div.selectGroup").not("[page='"+currentPage+"']")
		    .hover( function(){ $(this).css({"background-color":"#ccc",color:"white",cursor:"pointer"}); }, 
	                function(){ $(this).css({"background-color":"", color:"",cursor:""}); } )
	        .click( function () {
	        	var $this = $(this);
	        	if ( searchURL && $this.hasClass('pageNum') ) {
	        		document.location.href = searchURL + $this.text();
	        		return;
	        	}
	        });
		
		var lastPageNum = $("div.pageNum:last");
		$("#paginationView").width( lastPageNum.position().left+20);
   	}
	
});

//  Shopping cart

$(document).ready( function () {
    
    var overlay = $('<div id="myOverlay"></div>').fadeTo( 'fast' , 0.7);

    var cartTitle = $('span#header-shoppingCart');
    var offset = cartTitle.offset();
    var newCartTitle = null ;
    var cart = null;
    var maxWidth = 550;
    var timerID = null;
    
    var aLink = $("<a></a>").attr({"href":"#"}).hide().appendTo('body').fancybox({ onComplete : fancyboxItem.onComplete });    
    
    function closeShoppingcart()
    {
    	  cart.slideUp(500, function () {
	                      overlay.remove();
	                      cart.remove();
	                      newCartTitle.remove();
	                      cartTitle.css({visibility:"visible"});
	                    });                        	      
    }

	function delayCloseShoppingcart ()
	{
	      timerID = setTimeout( closeShoppingcart ,  150);
	}

    function popupShoppingcart( data )
    {
        timerID = null;
  	    newCartTitle = cartTitle.clone(false);
        newCartTitle.css({ position:"absolute", left: offset.left-3, top: offset.top, 
            "background-color": "white", "z-index":10,
            "padding-left" : "4px", "padding-right": "4px" });
        cartTitle.css({visibility:"hidden"});
        newCartTitle.appendTo('body');
        overlay.appendTo('body');
        cart = $('<div id="popupShoppingcart"></div>').css({ "min-width": (newCartTitle.width()+8)+'px' });
        cart.empty().html(data).appendTo('body').hide();
        $("#popupCartCheckout").click( function () { document.location.href = '/index/cart' ;})
                               .hover( function () { $(this).css({border:"1px solid #333"}) ; },
                            		   function () { $(this).css({border:""});});

        cart.css({left : offset.left + cartTitle.width() - cart.width()+5,
                  top :  offset.top + cartTitle.height()+3 });
        cart.slideDown(500);
        newCartTitle.hover(clearTimer, delayCloseShoppingcart );
        cart.hover(clearTimer, delayCloseShoppingcart );
    }

    function clearTimer ()
    {
        if ( timerID ) clearTimeout( timerID );
        timerID = null;
    }
    
     cartTitle.mouseenter( function () {
					         $.ajax({
					               url : '/ajax/cart/popup',
					               type: 'POST',
					               success : popupShoppingcart });
                            })
	          .mouseleave( function () { delayCloseShoppingcart });
     
   // Shopping cart update/remove
     $('#shoppingcartContent')
          .on('click','form.qtyForm input[name=submit]', function () {
          	// submit
    		  var qtyInput = $(this.form.qty);
    		  var asin = $(this.form.asin).val();
    		  var cartID = $(this.form.cartID).val();
    		  var qty = $.trim(qtyInput.val());
    		  qtyInput.val(qty);
    		  var dialog = null ;
    		  var pattern = /^\d+$/;
    		  if ( ! pattern.test(qty) || qty == 0 ) 
   			  {
    			  $('<div></div>').text('Please enter a valid quantity').dialog({ title:'Error'});
    			  return false;
   			  }
    		  
    		  $msgDialog = $('<div></div>').text('Updating...')
    		                               .dialog({ title:'Message', autoOpen: false });
    		  $msgDialog.dialog('open');
    		  
    		  $.ajax({
    			  url : "/ajax/cart/updateitem",
    			  type: "POST",
    		      dataType : "json",
    		      data : {format:"json", asin: asin, qty : qty, cartID: cartID },
    		      success : function ( data ) { 
    		    	        //  console.log(data);
    		    	          if ( data.result )
   		    	        	  {
    		    	        	// update total price;
    		    	        	 var total = 0 ; 
   		    	        	     $('form.qtyForm').each( function( i , item ) {
   		    	        	    	  var itemQty = $(item.qty).val();
   		    	        	    	  var itemPrice = $(item.price).val();
   		    	        	    	  total += itemQty*itemPrice;
   		    	        	     });
   		    	        	     total = total/100;
   		    	        	     $('#cartTotal').text( '$'+ total.toFixed(2) );
   		    	        	  }
    		    	          $msgDialog.dialog('close'); 
    		    	        },
    		      error : function () { 
    		    	          $msgDialog.dialog('close');
       		       			  $('<div></div>').text('Internal error, please try again.')
       		       			                  .dialog({ title:'Error'});
    		                } 	        
    		  });
    		  return false;
         })
        .on('click','form.qtyForm div.qtyUpdate', function () {
     	      // Update button
        	   var form = $(this).parents('form');
        	   $( 'input[name=submit]', form).trigger('click');
        	   return false;
         })
        .on('click','form.qtyForm div.qtyRemove', function () {
        	  // Remove button
     	   var form = $(this).parents('form');        	
   		   var asin = $('input[name=asin]', form).val();
		   var cartID = $('input[name=cartID]', form).val();
		   var currentRow = $(this).parents('tr');
   		   var removeConfirmDialog = $('<div></div>')
               .text('The item will be removed, are you sure?')
               .dialog({ title:'Message', 
            	        autoOpen: false,
            	        buttons : { 
            	        	          "Ok" : function () {
            	        	        	  $.ajax({
            	        	    			  url : "/ajax/cart/removeitem",
            	        	    			  type: "POST",
            	        	    		      dataType : "json",
            	        	    		      data : {format:"json", asin: asin, cartID: cartID },
            	        	    		      success : function ( data ) { 
            	        	    		    	       //   console.log(data);
            	        	    		    	          if ( data.result )
            	        	   		    	        	  {
            	        	    		    	        	  currentRow.remove();
            	        	    		    	        	// update total price;
            	        	    		    	        	 var total = 0 ; 
            	        	    		    	        	 var hasItem = false ;
            	        	   		    	        	     $('form.qtyForm').each( function( i , item ) {
            	        	   		    	        	    	  var itemQty = $(item.qty).val();
            	        	   		    	        	    	  var itemPrice = $(item.price).val();
            	        	   		    	        	    	  total += itemQty*itemPrice;
            	        	   		    	        	    	  hasItem = true ;
            	        	   		    	        	     });
            	        	   		    	        	     total = total/100;
            	        	   		    	        	     $('#cartTotal').text( '$'+ total.toFixed(2) );
            	        	   		    	        	     if ( ! hasItem ) document.location.reload();
            	        	   		    	        	  }
            	        	    		    	          removeConfirmDialog.dialog('close'); 
            	        	    		    	        },
            	        	    		      error : function () { 
            	        	    		    	          removeConfirmDialog.dialog('close');
            	        	       		       			  $('<div></div>').text('Internal error, please try again.')
            	        	       		       			                  .dialog({ title:'Error'});
            	        	    		                } 	        
            	        	    		  });                	        	        	  
            	        	          },
            	        	          "Cancel" : function () { removeConfirmDialog.dialog('close');}
            	        	      } 
                      });
   		  
 		      removeConfirmDialog.dialog('open');
 		      return false;
        	
         })
        .on('click','#emptyCart', function () {
        	// empty cart
    		  var emptyConfirmDialog = $('<div></div>')
		      .text('All items will be removed, are you sure?')
	          .dialog({ title:'Message', 
	        	        autoOpen: false,
	        	        buttons : { 
        	        	          "Ok" : function () {
        	        	        	  $.ajax({
        	        	    			  url : "/ajax/cart/emptycart",
        	        	    			  type: "POST",
        	        	    		      dataType : "json",
        	        	    		      data : {format:"json" },
        	        	    		      success : function ( data ) {
        	        	    		    	          emptyConfirmDialog.dialog('close');
        	        	    		    	          if ( data.result )
        	        	   		    	        	  {
        	        	    		    	        	 document.location.href = "/";  
        	        	   		    	        	  }
        	        	    		    	           
        	        	    		    	        },
        	        	    		      error : function () { 
        	        	    		    	          emptyConfirmDialog.dialog('close');
        	        	       		       			  $('<div></div>').text('Internal error, please try again.')
        	        	       		       			                  .dialog({ title:'Error'});
        	        	    		                } 	        
        	        	    		  });                	        	        	  
        	        	          },
        	        	          "Cancel" : function () { emptyConfirmDialog.dialog('close');}
	        	        	    } 
	                  });
		  
     		emptyConfirmDialog.dialog('open'); 
         })
      .on({ 'mouseenter': function() { var parentTr = $(this).parents('tr'); parentTr.css({cursor:"pointer","background-color":"#eee"});},
    	    'mouseleave': function() { var parentTr = $(this).parents('tr'); parentTr.css({cursor:"pointer","background-color":""});},
    	    'click': function () {
    	    	
    	    	          var parentTr = $(this).parents('tr');
		    	    	  var asin = $('input[name=asin]',parentTr).val(); 
		    	    	  aLink.attr({"href":"/ajax/banner/quickview/asin/"+asin});
		    	    	  aLink.trigger('click'); }
          }, '.cartItemImage,.cartItemTitle')   
      .on({ 'mouseenter' : function(){ $(this).css({border:"1px solid #333"}) ;} ,
    		'mouseleave' : function () { $(this).css({border:""}); }  
          }
    	  ,"div.qtyUpdate,div.qtyRemove,span#CartCheckout,span#emptyCart");
 });


//  Details page
$(document).ready( function () { 
    $("#editorialReviewTabs").tabs();
    
	$("#itemDetailsPrimaryInfo span#itemDetailsAddToCart")
	         .css({cursor:"pointer"})
	         .click( function () {
					$.ajax({
						 url : "/ajax/cart/additem",
					     type: "POST",
					     dataType : "json",
					     data : { format: "json", asin: $(this).attr("asin")},
					     success: function ( data ) { 
					    	     if ( data.result && data.qty ) $('#itemDetailsNumOfItemsInCart').text( data.qty);
					    	 }
		        	});
				})
			 .hover( function(){ $(this).css({border:"1px solid #333"}) ;},
                     function () { $(this).css({border:""}); } );
	
    var sections = [];
    var all = [];
	$("div.itemDetailsList").each( function ( i ,list ) { sections.push( $(list).children() ); });
	$('div.itemDetailsItemContainer').each ( function ( i, asin) { all.push( $(asin).attr('asin')); });

    if ( all.length > 0 )
    {
        var asins = all.join();
        $.ajax({
            url : "/ajax/banner/getitem",
            type: "POST",
            dataType :"json",
            data : { format: "json", asin : asins },
            success : function (data ) {
//                         console.log(data); return;
                         var results = data.result;
                         var num = results.length;
                         if ( num <= 0 ) return;
                         $.each( results , function ( i, resultDB ) {
                             var result = resultDB[0];
                             var src = result.MediumImage;
                             var title = result.Title;
                             var formatedTitle = title;
                             var asin = result.ASIN;
                             var img = null ;

                             if ( formatedTitle.length > 100 ) formatedTitle = formatedTitle.substring(0, 97)+'...';
                             if ( src.length > 0 )
                             {
                                 img = $('<img />').attr({src:src,title:formatedTitle,alt:formatedTitle});
                                 img.load( function () {
                                        num--;
                                        if ( num > 0 ) return ;
                                        for ( i = 0 ; i < sections.length ; i++ )
                                        { 
                       					   var height = 0 ;
                     					   sections[i].each( function (index, item) { height = Math.max(height, $(item).height()); });
                     					   sections[i].each( function (index, item) { 
                     						      $(item).height(height); //console.log(height); return;
                     						      var img = $('img', item);
                     						      var lineHeight = height - img.height(); //console.log(lineHeight);
                     	                          var divTitle = $('.itemDetailsItemTitle' ,item).height( lineHeight )
                     	                                                                          .css({ "line-height": lineHeight + "px" });
                     	                          spanTitle = $('span' , divTitle).css({ "line-height": "15px" });
                     						 });				
                                        }
                                   });
                             }    
                             else
                             	img = $('<div class="itemDetailsNoImage"><span>No Image</span></div>');

                         	titleObj = $('<div class="itemDetailsItemTitle"><span>'+formatedTitle+'</span></div>');
                         	$('div.itemDetailsItemContainer[asin='+asin+']').empty().append(img).append(titleObj);
                         }); 
                     }
        });
    }			

    var aLink = $("<a></a>").attr({"href":"#"}).hide().appendTo('body').fancybox({ onComplete : fancyboxItem.onComplete });
		$(".itemDetailsItemContainer").css({cursor:"pointer"})
		                              .hover( function () { $(this).css({border : "1px solid #D3D3D3"});} , 
	     		                              function () { $(this).css({border : ""});} )
                   			      .click( function () {
							    	  var asin = $(this).attr('asin'); 
							    	  aLink.attr({"href":"/ajax/banner/quickview/asin/"+asin});
							    	  aLink.trigger('click');});

		var largeImageObj = null ;
		$("div#itemDetailsMediumImageContainer")
		         .hover( function() {
 		                 $(this).css({border:"1px solid #D3D3D3"});
 		                 var src = $('.itemDetailsImage',this).attr("largeimage");
 		                 if ( ! src ) return ;
 		                 if ( largeImageObj == null ) 
	                	 {
 		                    largeImageObj = $("<img id='itemDetailLargeImage' />").attr({ src: src })
 		                                                                          .css({border:"1px solid #D3D3D3"})
 		                                                                          .hide();
 		                    $(this).append(largeImageObj);
	                	 }   
 		                 $("div#itemDetails").css({overflow:"visible"});
 		                 largeImageObj.slideDown('slow');
 		               
		              } , 
	     		         function() {
		            	     $(this).css({border:""});
		            	     if ( largeImageObj ) {
		            	    	largeImageObj.slideUp('slow', function () {
		            	    		                           // largeImageObj.remove();
	         		                                            $("div#itemDetails").css({overflow:"hidden"});      		            	    	
	            	    	                                });
		            	     }
		            	     else    
		            	    	$("div#itemDetails").css({overflow:"hidden"});
	     		         });  
});

