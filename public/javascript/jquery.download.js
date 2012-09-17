/*
 * jQuery Download
 */
/*global $, jQuery, unescape */
(function() {

  "use strict";

  $.fn.download = function(url, data, method) {

    var clean_string = function(str) {
      return unescape(str.replace(/\+/g, ' ')).replace(/\"/g, '&quot;');
    };

    return this.each(function() {
      var form = '', id = '', pair = [];

      if(url && data){
        data = (typeof data === 'string') ? data : $.param(data);
        form = $('<form id="jquery-download-extension" action="' + url + '" method="' + (method||'post') + '"></form>');
        $.each(data.split('&'), function(){
          pair = this.split('=');
          id = 'jquery-download-' + unescape(pair[0]);
          form.append('<input type="hidden" name="' + unescape(pair[0]) + '" id="' + id + '" value="' + clean_string(pair[1]) + '" />');
        });
        form.appendTo($('body'));
        $('#jquery-download-extension').submit().remove();
      }
    });

  };
}(jQuery));