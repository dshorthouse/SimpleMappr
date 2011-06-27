//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Function to offer download
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
jQuery.download = function(url, data, method) {
  //url and data options required
  if( url && data ) { 
    //data can be string of parameters or array/object
    data = typeof data == 'string' ? data : jQuery.param(data);
    //split params into form inputs
    var inputs = '';
    jQuery.each(data.split('&'), function() { 
      var pair = this.split('=');
      var value = pair[1].replace(/\+/g,' ');
      inputs+='<input type="hidden" name="'+ unescape(pair[0]) +'" value="'+ unescape(value) +'" />';
    });
    //send request
    jQuery('<form action="'+ url +'" method="'+ (method||'post') +'">'+inputs+'</form>').appendTo('body').submit().remove();
  }
};