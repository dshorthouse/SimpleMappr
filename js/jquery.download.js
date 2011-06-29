/*global jQuery, unescape */

jQuery.download = function(url, data, method) {

  "use strict";

  var inputs = '', pair = [], value = "";

  if( url && data ) { 
    data = (typeof data === 'string') ? data : jQuery.param(data);
    jQuery.each(data.split('&'), function() { 
      pair = this.split('=');
      value = pair[1].replace(/\+/g,' ');
      inputs+='<input type="hidden" name="'+ unescape(pair[0]) +'" value="'+ unescape(value) +'" />';
    });
    //send request
    jQuery('<form action="'+ url +'" method="'+ (method||'post') +'">'+inputs+'</form>').appendTo('body').submit().remove();
  }
};