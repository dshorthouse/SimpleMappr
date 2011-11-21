/*global $, jQuery, unescape */

(function() {

  "use strict";

  $.fn.download = function(url, data, method) {

    return this.each(function() {
      var form = '', id = '', pair = [];

      if(url && data){
        data = (typeof data === 'string') ? data : $.param(data);
        form = $('<form action="' + url + '" method="' + (method||'post') + '"></form>');
        $.each(data.split('&'), function(){
          pair = this.split('=');
          id = 'jquery-download-' + unescape(pair[0]);
          form.append('<input type="hidden" name="' + unescape(pair[0]) + '" id="' + id + '" value="' + unescape(pair[1].replace(/\+/g,' ')) + '" />');
        });
        form.appendTo($('body')).submit().remove();
      }
    });

  };
}(jQuery));