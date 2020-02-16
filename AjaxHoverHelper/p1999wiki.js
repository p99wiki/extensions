var requestStorage=function(){var a={},b=[];return{length:0,key:function(a){return typeof a=="number"&&b.length>=a&&a>=0?b[a]:null},getItem:function(b){return a.hasOwnProperty(b)?a[b]:null},setItem:function(c,d){a.hasOwnProperty(c)||(this.length++,b.push(c)),a[c]=d},removeItem:function(c){if(a.hasOwnProperty(c)){this.length--;for(var d=0;d<b.length;d++)b[d]==c&&b.splice(d,1)}delete a[c]},clear:function(){a={},b=[],this.length=0}}}();(function(a){var b={timeStamp:"__timeStamp__",responseText:"__responseText__",hasResponseXML:"__hasResponseXML__",responseHeaders:"__responseHeaders__"};a.ajaxCacheResponse={storage:window.requestStorage};var c=function(b){var d="";for(var e in b){var f=typeof b[e];f==="string"||f==="number"||f==="boolean"?d+=e+"="+b[e]+",":f==="object"?d+=e+"="+a.param(b[e])+",":f==="array"&&a.each(b[e],function(a,b){d+=c(b)})}return d},d=function(c,d){if(a.ajaxCacheResponse.storage.getItem(c+b.timeStamp)===null)return!1;var e=!0;if(typeof d.cacheResponseTimer=="number"){var f=parseInt(a.ajaxCacheResponse.storage.getItem(c+b.timeStamp));if(typeof f=="number"){var g=(new Date).getTime();e=f+d.cacheResponseTimer>g,e===!0&&(d.cacheTimeRemaining=d.cacheResponseTimer-(g-f))}}a.isFunction(d.cacheResponseValid)&&(e=d.cacheResponseValid.call(this,d));return e};a.ajaxPrefilter(function(e,f,g){if(e.cacheResponse===!0){if(a.ajaxCacheResponse.storage===undefined)throw"No valid storage defined for the Ajax Cache Response plugin";var h=c(f);e.cacheResponseId=h;if(d(h,e)===!0){var i=a.extend({},b);for(var j in i)i[j]=a.ajaxCacheResponse.storage.getItem(h+i[j]);e.xhr=function(){return{open:a.noop,setRequestHeader:a.noop,send:a.noop,abort:a.noop,onreadystatechange:a.noop,getResponseHeader:a.noop,getAllResponseHeaders:function(){return i.responseHeaders},readyState:4,status:200,statusText:"success",responseText:i.responseText,responseXML:i.hasResponseXML===!0?a.parseXML(i.responseText):undefined}},g.responseFromCache=!0,g.cacheTimeRemaining=e.cacheTimeRemaining}else g.responseFromCache=!1}}),a(document).ajaxSuccess(function(c,e,f,g){if(f.cacheResponse===!0){var h=f.cacheResponseId;if(d(h,f)===!1){var i=a.ajaxCacheResponse.storage;i.setItem(h+b.responseText,e.responseText),i.setItem(h+b.responseHeaders,e.getAllResponseHeaders()),i.setItem(h+b.timeStamp,(new Date).getTime()),i.setItem(h+b.hasResponseXML,e.responseXML!==undefined)}}})})(jQuery)

$(document).ready(function() {

    $(function()  
    {  
      var hideDelay = 0;
      var trigDelay = 250;
      var hideTimer = null;  
      var ajax = null;  

      var currentPosition = { left: '0px', top: '0px' };  

      // One instance that's reused to show info for the current person  
      var container = $('<div id="itemHoverContainer">'  
          + '<div id="itemHoverContent"></div>'
          + '</div>');  
      
      $('body').append(container);  

      //$('span.ih a')

      $('span.ih a').live('mouseover', function()  
      {  
          var itemname = $(this).attr('title');  

          if (itemname == '' || itemname == 'undefined')  
              return;  
      
          if (hideTimer)  
              clearTimeout(hideTimer);  
      
          //var pos = $(this).offset();  
          //var width = $(this).width(); 
 
          //container.css({  
          //    left: (pos.left + width) + 'px',  
          //    top: pos.top - 5 + 'px'  
          //});

          $(this).trigger('mousemove');
      
          $('#itemHoverContent').html('&nbsp;');

          //$('#itemHoverContent').html('<div class="itemtopbg"><div class="itemtitle">Loading...</div></div>'
          //                          + '<div class="itembg" style="min-height:50px;"><div class="itemdata">'
          //                          + '<div class="itemicon" style="float:right;"><img alt="" src="/images/Ajax_loader.gif" border="0"></div>'
          //                          + '<p></p></div></div><div class="itembotbg"></div>'); 

          if (ajax)  
          {  
              ajax.abort();  
              ajax = null;  
          }  
  
          ajax = $.ajax({  
              url: 'http://wiki.project1999.org/index.php/Special:AjaxHoverHelper/'+itemname,  
              cacheResponse: true,
              success: function(html)  
              {  
                $('#itemHoverContent').html(html);  
              }  
          });    

          container.css('display', 'block');
          //container.fadeIn('fast');

      }); //live mouseover
      
      $('span.ih a').live('mouseout', function()  
      {  
          if (hideTimer)  
              clearTimeout(hideTimer);  
          hideTimer = setTimeout(function()  
          {  
              container.css('display', 'none');  
              //container.fadeOut('fast');
          }, hideDelay);  
      });  

      $('span.ih a').mousemove(function(e){
        var mousex = e.pageX + 20; //Get X coodrinates
        var mousey = e.pageY + 20; //Get Y coordinates
        var tipWidth = container.width(); //Find width of tooltip
        var tipHeight = container.height(); //Find height of tooltip

        //Distance of element from the right edge of viewport
        var tipVisX = $(window).width() - (mousex + tipWidth);
        //Distance of element from the bottom of viewport
        var tipVisY = $(window).height() - (mousey + tipHeight);

        if ( tipVisX < 20 ) { //If tooltip exceeds the X coordinate of viewport
            
            if( tipWidth > e.pageX - 20 ){
                mousex = 0;
            } else {
                mousex = e.pageX - tipWidth - 20;
            }
            
        } if ( tipVisY < 20 ) { //If tooltip exceeds the Y coordinate of viewport
            mousey = e.pageY - tipHeight - 20;
        }

        container.css({  top: mousey, left: mousex });
      });
      
      // Allow mouse over of details without hiding details  
      $('#itemHoverContainer').mouseover(function()  
      {  
          if (hideTimer)  
              clearTimeout(hideTimer);  
      });  
      
      // Hide after mouseout  
      $('#itemHoverContainer').mouseout(function()  
      {  
          if (hideTimer)  
              clearTimeout(hideTimer);  
          hideTimer = setTimeout(function()  
          {  
              container.css('display', 'none');  
              //container.fadeOut('fast');
          }, hideDelay);  
      });  
			
			// magelo non-ajax item hover, but move box with mouse
      $('.magelohb').mousemove(function(e){
				var childContainer = $(this).children('span.hb');

        var tipWidth = childContainer.width(); //Find width of tooltip
        var tipHeight = childContainer.height(); //Find height of tooltip
				
        var mousex = e.pageX + 20; //Get X coodrinates
        var mousey = e.pageY + 20; //Get Y coordinates
				
				console.log(e.pageX,e.pageY);
				
        //Distance of element from the right edge of viewport
        var tipVisX = $(window).width() - (mousex + tipWidth - 20);
        //Distance of element from the bottom of viewport
        var tipVisY = $(window).height() - (mousey + tipHeight - 20);

        if ( tipVisX < 20 ) { //If tooltip exceeds the X coordinate of viewport
            
            if( tipWidth > e.pageX - 20){
                mousex = 0;
            } else {
                mousex = e.pageX - tipWidth - 20;
            }
            
        } if ( tipVisY < 20 ) { //If tooltip exceeds the Y coordinate of viewport
            mousey = e.pageY - tipHeight - 20;
        }
				
        childContainer.css({ top: mousey, left: mousex, 'z-index':'999' });
      });
			
			// change to position:fixed on all hover divs if we have JS active
			// otherwise leave as position:absolute so the stationary hovers are near their items
			$('.magelohb span.hb').each(function(i) {
				$(this).css({'position':'fixed'});
			});
			
    });  


});