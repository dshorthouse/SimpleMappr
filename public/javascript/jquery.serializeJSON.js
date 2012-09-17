/*
 * jQuery Serialize JSON
 */
/*global $, jQuery, unescape */
(function() {

  "use strict";

  $.fn.serializeJSON = function() {
    var json = {}, matches = [];
    $.map($(this).serializeArray(), function(n, i){
      i = null;
      json[n.name] = n.value;
    });
    return json;
  };
}(jQuery));