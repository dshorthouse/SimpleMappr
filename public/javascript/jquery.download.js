/*global jQuery, unescape */

jQuery.download = function(url, data, method){

  "use strict";

  var form = '', id = '', pair = [];

  if(url && data){
    data = (typeof data === 'string') ? data : jQuery.param(data);
    form = jQuery('<form action="' + url + '" method="' + (method||'post') + '"></form>');
    jQuery.each(data.split('&'), function(){
      pair = this.split('=');
      id = 'jquery-download-' + unescape(pair[0]);
      form.append('<input type="hidden" name="' + unescape(pair[0]) + '" id="' + id + '" value="' + unescape(pair[1].replace(/\+/g,' ')) + '" />');
    });
    form.appendTo('body').submit().remove();
  }
}; 