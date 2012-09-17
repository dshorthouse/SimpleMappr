/*
 * jQuery Clearform
 */
/*global $, jQuery */
(function () {

  "use strict";

  $.fn.clearForm = function () {
    return this.each(function () {
      var type = this.type, tag = this.tagName.toLowerCase();
      if (tag === 'form') {
        return $(':input',this).clearForm();
      }
      if (type === 'text' || type === 'password' || type === 'hidden' || tag === 'textarea') {
        this.value = '';
      } else if (type === 'checkbox' || type === 'radio') {
       this.checked = false;
      } else if (tag === 'select') {
       this.selectedIndex = 0;
      }
    });
  };

}( jQuery ));