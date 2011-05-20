var Mapper = Mapper || { 'settings': {} };

$.fn.clearForm = function() {
  return this.each(function() {
 var type = this.type, tag = this.tagName.toLowerCase();
 if (tag == 'form')
   return $(':input',this).clearForm();
 if (type == 'text' || type == 'password' || tag == 'textarea')
   this.value = '';
 else if (type == 'checkbox' || type == 'radio')
   this.checked = false;
 else if (tag == 'select')
   this.selectedIndex = 0;
  });
};

function ___getPageSize() {
	var xScroll, yScroll;
	if (window.innerHeight && window.scrollMaxY) {	
		xScroll = window.innerWidth + window.scrollMaxX;
		yScroll = window.innerHeight + window.scrollMaxY;
	} else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
		xScroll = document.body.scrollWidth;
		yScroll = document.body.scrollHeight;
	} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
		xScroll = document.body.offsetWidth;
		yScroll = document.body.offsetHeight;
	}
	var windowWidth, windowHeight;
	if (self.innerHeight) {	// all except Explorer
		if(document.documentElement.clientWidth){
			windowWidth = document.documentElement.clientWidth; 
		} else {
			windowWidth = self.innerWidth;
		}
		windowHeight = self.innerHeight;
	} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
		windowWidth = document.documentElement.clientWidth;
		windowHeight = document.documentElement.clientHeight;
	} else if (document.body) { // other Explorers
		windowWidth = document.body.clientWidth;
		windowHeight = document.body.clientHeight;
	}
	// for small pages with total height less then height of the viewport
	if(yScroll < windowHeight){
		pageHeight = windowHeight;
	} else { 
		pageHeight = yScroll;
	}
	// for small pages with total width less then width of the viewport
	if(xScroll < windowWidth){	
		pageWidth = xScroll;		
	} else {
		pageWidth = windowWidth;
	}
	arrayPageSize = new Array(pageWidth,pageHeight,windowWidth,windowHeight);
	return arrayPageSize;
}

function ___getPageScroll() {
	var xScroll, yScroll;
	if (self.pageYOffset) {
		yScroll = self.pageYOffset;
		xScroll = self.pageXOffset;
	} else if (document.documentElement && document.documentElement.scrollTop) {	 // Explorer 6 Strict
		yScroll = document.documentElement.scrollTop;
		xScroll = document.documentElement.scrollLeft;
	} else if (document.body) {// all other Explorers
		yScroll = document.body.scrollTop;
		xScroll = document.body.scrollLeft;	
	}
	arrayPageScroll = new Array(xScroll,yScroll);
	return arrayPageScroll;
}

function __showCoords(c) {
	
	var x = parseInt(c.x);
	var y = parseInt(c.y);
	var x2 = parseInt(c.x2);
	var y2 = parseInt(c.y2);
	
	$('#bbox_rubberband').val(x+','+y+','+x2+','+y2);
}

function tabSelector(tab) {
  $("#tabs").tabs('select',tab);
}

$(window).resize(function() {
	// Get page sizes
	var arrPageSizes = ___getPageSize();
	// Style overlay and show it
	$('#mapper-overlay').css({
		width:		arrPageSizes[0],
		height:		arrPageSizes[1]
	});
	// Get page scroll
	var arrPageScroll = ___getPageScroll();
	$('#mapper-message').css({
		top:	arrPageScroll[1] + (arrPageSizes[3] / 10),
		left:	arrPageScroll[0],
		position: 'fixed',
		zIndex: 1001,
		margin: '0px auto',
		width: '100%'
	});
});

$(function(){
  
  //set the tabs & make them sortable
  $("#tabs").tabs();
  $('#mapTools').tabs();

  //override the ActiveX jQuery 1.3.2 settings for IE
  $.ajaxSetup({
	xhr:function() { return new XMLHttpRequest(); }
  });

//Setting some variables needed, don't edit these
	var displaySubmit=false, newRowCount=0, newFieldSetCount=0;
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//Define some variables - edit to suit your needs
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	var maxFieldSets=5;
	var maxRows=10;
	var rowSpeed = 300;
	var $addMorebtn = $('#addMore');
	var $hiddenControls = $("#controls .hiddenControls");
	var $submitForm = $(".submitForm");
	var hiddenClass = "hidden";

	var jcrop_api;
	var jzoom_api;
	var zoom = true;

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	showMap();

/******************** toolbar actions ************************/
	$("ul.dropdown li").hover(function(){
  		$(this).addClass("hover").find('a:first').addClass("hover");
  		$('ul:first',this).css('visibility', 'visible');
	}, function(){ 
  		$(this).removeClass("hover").find('a:first').removeClass("hover");
  		$('ul:first',this).css('visibility', 'hidden');
	});

	$("ul.dropdown li ul li:has(ul)").find("a:first").append(" &raquo; ");

	var $preview = $('#map-preview');

	$preview.find('.toolsZoomIn').click(function(){
  		$('#mapCropMessage').hide();
  		if($('#mapCropMessage').is(':hidden')) {
    		initJzoom();
			zoom = true;	
  		}
  		return false;	
	});

	$preview.find('.toolsZoomOut').click(function(){
  		$('#mapCropMessage').hide();
  		$('#zoom_out').val(1);
  		showMap();
  		$('#zoom_out').val('');
  		return false;	
	});

	$preview.find('.toolsRotateAC5').click(function(){
  		$('#rotation').val(parseInt($('#rendered_rotation').val())-5);
  		showMap();
  		return false;	
	});

	$preview.find('.toolsRotateAC10').click(function(){
  		$('#rotation').val(parseInt($('#rendered_rotation').val())-10);
  		showMap();
  		return false;	
	});

	$preview.find('.toolsRotateAC15').click(function(){
  		$('#rotation').val(parseInt($('#rendered_rotation').val())-15);
  		showMap();
  		return false;	
	});

	$preview.find('.toolsRotateC5').click(function(){
  		$('#rotation').val(parseInt($('#rendered_rotation').val())+5);
  		showMap();
  		return false;	
	});

	$preview.find('.toolsRotateC10').click(function(){
  		$('#rotation').val(parseInt($('#rendered_rotation').val())+10);
  		showMap();
  		return false;	
	});

	$preview.find('.toolsRotateC15').click(function(){
  		$('#rotation').val(parseInt($('#rendered_rotation').val())+15);
  		showMap();
  		return false;	
	});
						
	$preview.find('.toolsCrop').click(function(){
  		if($('#mapCropMessage').is(':hidden')){
    		initJcrop();
			zoom = false;
			$('#mapCropMessage').show();	
  		}
  		return false;	
	});

	$preview.find('.toolsRefresh').click(function(){
  		showMap();	
	});

	$preview.find('.toolsRebuild').click(function(){
		$('#bbox_map').val('');
  		$('#bbox_rubberband').val('');
  		$('#rotation').val('');
  		$('#projection').val('');
  		$('#pan').val('');
  		showMap();	
	});

	//controls for arrow scrollers

	$('#arrow-up').click(function() {
  		$('#pan').val('up');
  		showMap();
  		return false;	
	});

	$('#arrow-right').click(function() {
  		$('#pan').val('right');
  		showMap();
  		return false;
	});

	$('#arrow-down').click(function() {
  		$('#pan').val('down');
  		showMap();
  		return false;
	});

	$('#arrow-left').click(function() {
  		$('#pan').val('left');
  		showMap();
  		return false;	
	});
	
	$('.layeropt').click(function() {
	  showMap();	
	});
	
	$('#projection').change(function() {
	  	if($(this).val() != "") {
		  showMap();
	    }
	});

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//Initialize the cropper
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	function initJcrop(){
		//destroy existing apis first
		if(typeof jzoom_api != "undefined") {
		  jzoom_api.destroy();
		}
		if(typeof crop_api != "undefined") {
		  jcrop_api.destroy();
		}
		
		jcrop_api = $.Jcrop('#mapOutput img', {
			onChange: __showCoords,
			onSelect: __showCoords
		});
		
		$('.jcrop-tracker').unbind('mouseup', aZoom );
	};

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function initJzoom(){
		//destroy existing apis first
		if(typeof jzoom_api != "undefined") {
		  jzoom_api.destroy();
		}
		if(typeof jcrop_api != "undefined") {
		  jcrop_api.destroy();
		}
		
	    jzoom_api = $.Jcrop('#mapOutput img', {
		    addClass: 'customJzoom',
		    sideHandles: false,
		    cornerHandles: false,
		    dragEdges: false,
		    bgOpacity: 1,
			onChange: __showCoords,
			onSelect: __showCoords
	    });

	    $('.jcrop-tracker').bind('mouseup', aZoom );
    };

    function aZoom() {
	  showMap();
    }

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//click the add more button
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    $addMorebtn.click(function(){
	  if(newFieldSetCount < maxFieldSets) {
	    newFieldSetCount++;
	    var totFieldSets = newFieldSetCount + 3;
	    var inputCounter = totFieldSets - 1;
	    var lastFieldSet = $('#fieldSetsPoints fieldset:last').clone();
	    $(lastFieldSet).addClass("new");
	    $(lastFieldSet).find('legend a').text("Layer "+totFieldSets);
	    $(lastFieldSet).find('input.m-mapTitle').attr('name','coords['+inputCounter+'][title]').val('');
	    $(lastFieldSet).find('textarea').attr('name','coords['+inputCounter+'][data]').removeClass('textarea-processed').val('');
	
	    $(lastFieldSet).find('textarea').each(function() {
		  var textarea = $(this).addClass('textarea-processed'), staticOffset = null;
		  $(this).parent().find('.grippie').mousedown(startDrag);

		  var grippie = $('div.grippie', $(this).parent())[0];
		  grippie.style.marginRight = (grippie.offsetWidth - $(this)[0].offsetWidth) +'px';

		  function startDrag(e) {
		    staticOffset = textarea.height() - e.pageY;
		    textarea.css('opacity', 0.25);
		    $(document).mousemove(performDrag).mouseup(endDrag);
		    return false;
		  }

		  function performDrag(e) {
		    textarea.height(Math.max(32, staticOffset + e.pageY) + 'px');
		    return false;
		  }

		  function endDrag(e) {
		    $(document).unbind("mousemove", performDrag).unbind("mouseup", endDrag);
		    textarea.css('opacity', 1);
		  }
		});
	
	    $(lastFieldSet).find('select.m-mapShape').attr('name','coords['+inputCounter+'][shape]').val('circle');
	    $(lastFieldSet).find('select.m-mapSize').attr('name','coords['+inputCounter+'][size]').val('10');
	    $(lastFieldSet).find('input.colorPicker').attr('name','coords['+inputCounter+'][color]').ColorPicker({
			onSubmit: function(hsb, hex, rgb, el) {
				$(el).val(rgb.r + ' ' + rgb.g + ' ' + rgb.b);
				$(el).ColorPickerHide();
			},
			onBeforeShow: function () {
				$(this).ColorPickerSetColor(this.value);
			}
		}).bind('keyup', function(){
			$(this).ColorPickerSetColor(this.value);
		}).val('0 0 0');
		
	    $('#fieldSetsPoints').append(lastFieldSet);

        $('fieldset.new legend').click(function() {
	      $(this).parent().removeClass("new");
	      $(this).parent().find(".fieldset-wrapper").slideToggle("fast");
		  $(this).parent().toggleClass("collapsed");
		  return false;  //kill the browser default action
        });
        return false; //kill the browser default action
	  }
	
	  //disable button so you know you've reached the max
	  if(newFieldSetCount >= maxFieldSets){
	    $addMorebtn.attr("disabled","disabled"); //set the "disabled" property on the button
	    return false; //kill the browser default action
	  }
 
	  return false;  //kill the browser default action
    });

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//Load user data if usermaps div present
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
if($('#usermaps').length > 0) {
	$("#tabs").tabs('select',3);
	loadMyMaps();
}

function loadMyMaps() {
	$('#usermaps').html("Loading your list...");
	$.get(Mapper.settings.baseUrl + "/usermaps/?action=list", {}, function(data) {
		$('#usermaps').html(data);
		$('.map-load').click(function() {
			var id = $(this).attr("rel");
			var message = '<div id="mapper-warning-message" style="margin:0px auto">Loading your data...</div>';
			showMessage(message);
			//clear the form elements
			$('#form-mapper').clearForm();
			//remove extraneous fieldsets & reset the fieldset counter
			var numFields = $('#fieldSetsPoints fieldset').size();
			if(numFields > 3) {
				for(i=numFields-1; i>=3;i--) {
					$('#fieldSetsPoints fieldset:eq('+i+')').remove();
				}
				newFieldSetCount = 0;
			}
			$.get(Mapper.settings.baseUrl + "/usermaps/?action=load&map="+id, {}, function(data) {
			  //load up all the data
			  var coords = data.map.coords;
			  for(i=0;i<coords.length;i++) {
				//add the fieldsets in case more than the default three are required
				if(i > 2) {
					if(newFieldSetCount < maxFieldSets) {
				    newFieldSetCount++;
				    var totFieldSets = newFieldSetCount + 3;
				    var inputCounter = totFieldSets - 1;
				    var lastFieldSet = $('#fieldSetsPoints fieldset:last').clone();
				    $(lastFieldSet).addClass("new");
				    $(lastFieldSet).find('legend a').text("Layer "+totFieldSets);
				    $(lastFieldSet).find('input.m-mapTitle').attr('name','coords['+inputCounter+'][title]').val('');
				    $(lastFieldSet).find('textarea').attr('name','coords['+inputCounter+'][data]').removeClass('textarea-processed').val('');

				    $(lastFieldSet).find('textarea').each(function() {

					  var textarea = $(this).addClass('textarea-processed'), staticOffset = null;

					  $(this).parent().find('.grippie').mousedown(startDrag);

					  var grippie = $('div.grippie', $(this).parent())[0];
					  grippie.style.marginRight = (grippie.offsetWidth - $(this)[0].offsetWidth) +'px';

					  function startDrag(e) {
					    staticOffset = textarea.height() - e.pageY;
					    textarea.css('opacity', 0.25);
					    $(document).mousemove(performDrag).mouseup(endDrag);
					    return false;
					  }

					  function performDrag(e) {
					    textarea.height(Math.max(32, staticOffset + e.pageY) + 'px');
					    return false;
					  }

					  function endDrag(e) {
					    $(document).unbind("mousemove", performDrag).unbind("mouseup", endDrag);
					    textarea.css('opacity', 1);
					  }
					});

				    $(lastFieldSet).find('select.m-mapShape').attr('name','coords['+inputCounter+'][shape]');
				    $(lastFieldSet).find('select.m-mapSize').attr('name','coords['+inputCounter+'][size]');
				    $(lastFieldSet).find('input.colorPicker').attr('name','coords['+inputCounter+'][color]').ColorPicker({
						onSubmit: function(hsb, hex, rgb, el) {
							$(el).val(rgb.r + ' ' + rgb.g + ' ' + rgb.b);
							$(el).ColorPickerHide();
						},
						onBeforeShow: function () {
							$(this).ColorPickerSetColor(this.value);
						}
					}).bind('keyup', function(){
						$(this).ColorPickerSetColor(this.value);
					});

				    $('#fieldSetsPoints').append(lastFieldSet);

			        $('fieldset.new legend').click(function() {
				      $(this).parent().removeClass("new");
				      $(this).parent().find(".fieldset-wrapper").slideToggle("fast");
					  $(this).parent().toggleClass("collapsed");
					  return false;  //kill the browser default action
			        });
				  }

				  //disable button so you know you've reached the max
				  if(newFieldSetCount >= maxFieldSets){
				    $addMorebtn.attr("disabled","disabled"); //set the "disabled" property on the button
				  }
				}
				
				$('input[name=coords\\['+i+'\\]\\[title\\]]').val(coords[i].title);
				$('textarea[name=coords\\['+i+'\\]\\[data\\]]').val(coords[i].data);
				$('select[name=coords\\['+i+'\\]\\[shape\\]]').val(coords[i].shape);
				$('select[name=coords\\['+i+'\\]\\[size\\]]').val(coords[i].size);
				$('input[name=coords\\['+i+'\\]\\[color\\]]').val(coords[i].color);
			  }
			$('#firstTextBox').val(data.map.coords[0].data);
			$('input[name=coords\\[0\\]\\[shape\\]]').val(data.map.coords[0].shape);
			
			  var regions = data.map.regions;
			  $('input[name=regions\\[title\\]]').val(regions.title);
			  $('textarea[name=regions\\[data\\]]').val(regions.data);
			  $('input[name=regions\\[color\\]]').val(regions.color);
			
			  $('select[name=projection]').val(data.map.projection);
			  $('input[name=bbox_map]').val(data.map.bbox_map);
			  $('input[name=rotation]').val(data.map.rotation);

			  if(data.map.layers) {
				var keyMap = [];
				for(key in data.map.layers){
				    keyMap[keyMap.length] = key;
				}
				for(i=0;i<keyMap.length;i++) {
					$('input[name=layers\\['+keyMap[i]+'\\]]').attr('checked', true);
				}
			  }
			
			  if(data.map.options) {
				var keyMap = [];
				for(key in data.map.options){
				    keyMap[keyMap.length] = key;
				}
				for(i=0;i<keyMap.length;i++) {
					$('input[name=options\\['+keyMap[i]+'\\]]').attr('checked', true);
				}
			  }
			
			  $('input[name=download_factor]').val(data.map.download_factor);
			
			  $('input[name=save\\[title\\]]').val(data.map.save.title);
			  showMap();
			  hideMessage();
			  $("#tabs").tabs('select',0);
			}, "json");
		});
		$('.map-url').click(function() {
			var message = '<div id="mapper-warning-message" style="margin:0px auto">Coming soon...';
			message += '<p>';
			message += '<span id="cancelButton"><button id="okClose" class="positive">OK</button></span>';
			message += '</p>';
			message += '</div>';
			showMessage(message);
			$('#okClose').click(function() {
			  hideMessage();	
			});
		});
		$('.map-delete').click(function() {
			var message = '<div id="mapper-warning-message" style="margin:0px auto">Are you sure you want to delete, <em>"'+$(this).parent().parent().find(".title").html()+'"</em>?';
			message += '<p>';
			message += '<span id="deleteButton"><button id="okDelete" class="positive">Yes</button></span>';
			message += '<span id="cancelButton"><button id="okClose" class="negative">Cancel</button></span>';
			message += '</p>';
			message += '</div>';
			showMessage(message);
			$('#okClose').click(function() {
			  hideMessage();	
			});
			var id = $(this).attr("rel");
			$('#okDelete').click(function() {
				$.get(Mapper.settings.baseUrl + "/usermaps/?action=delete&map="+id, {}, function(data) {
					hideMessage();
					loadMyMaps();
				}, "json");
			});
		});
	}, "html");
}

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//Submit the form
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    $submitForm.click(function() {
	
	  //some simple error checking
	  var missingTitleCoord = false;
	  var missingTitleRegion = false;
	
	  $('.m-mapCoord').each(function() {
	    if($(this).val() && $(this).parents().find('.m-mapTitle').val() == '') {
		  missingTitleCoord = true;
	    }
	  });
	
	  $('.m-mapRegion').each(function() {
		if($(this).val() && $(this).parents().find('.m-mapTitle').val() == '') {
		  missingTitleRegion = true;	
		}
	  });
	
	  if(missingTitleCoord) {
		var message = '<div id="mapper-warning-message" style="margin:0px auto">You are missing a legend for at least one of your Data Layers<br />';
		message += '<div id="okButton"><button id="okClose" class="positive">OK</button></div></div>';
		showMessage(message);	
	  }
	  else if(missingTitleRegion) {
		var message = '<div id="mapper-warning-message" style="margin:0px auto">You are missing a legend for your Shaded Regions<br />';
		message += '<div id="okButton"><button id="okClose" class="positive">OK</button></div></div>';
		showMessage(message);
	  }
	  else {
	    showMap();
	    $("#tabs").tabs('select',0);
	  }
	
	  return false;  //kill the browser default action
    });

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//Save the form
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$('.saveForm').click(function() {

  //some simple error checking
  var missingTitleSave = false;

  if($('.m-mapSaveTitle').val() == '') {
	missingTitleSave = true;
  }

  if(missingTitleSave) {
	var message = '<div id="mapper-warning-message" style="margin:0px auto">You are missing a title for your saved map.<br />';
	message += '<div id="okButton"><button id="okClose" class="positive">OK</button></div></div>';
	showMessage(message);	
  }
  else {
	var message = '<div id="mapper-warning-message" style="margin:0px auto">Saving...</div>';
	showMessage(message);
    var formData = $("form").serialize();
    $.post(Mapper.settings.baseUrl + "/usermaps/?action=save", formData, function(data) {
		hideMessage();
		$("#tabs").tabs('select',3);
		loadMyMaps();
	});
  }

  return false;  //kill the browser default action
});

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Clear the layers data
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$('.clearLayers').click(function() {
	var fieldsets = $('#fieldSetsPoints fieldset').length;
	for(i=0;i<fieldsets;i++) {
		$('input[name=coords\\['+i+'\\]\\[title\\]]').val('');
		$('textarea[name=coords\\['+i+'\\]\\[data\\]]').val('');
		$('select[name=coords\\['+i+'\\]\\[shape\\]]')[0].selectedIndex = 3;
		$('select[name=coords\\['+i+'\\]\\[size\\]]')[0].selectedIndex = 3;
		$('input[name=coords\\['+i+'\\]\\[color\\]]').val('0 0 0');
	}
	return false;  //prevent form submission
});

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Clear the regions data
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$('.clearRegions').click(function() {
  $('input[name=regions\\[title\\]]').val('');
  $('textarea[name=regions\\[data\\]]').val('');
  $('input[name=regions\\[color\\]]').val('150 150 150');
  return false;	//prevent form submission
});
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//Function to hide messages
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	function showMessage(message) {
		$('body').append('<div id="mapper-overlay"></div><div id="mapper-message"></div>');
		var arrPageSizes = ___getPageSize();
		$('#mapper-overlay').css({
			backgroundColor: 'black',
			opacity: 0.66,
			width: arrPageSizes[0],
			height: arrPageSizes[1]
		});
		var arrPageScroll = ___getPageScroll();
		$('#mapper-message').css({
			top: 150,
			left: arrPageScroll[0],
			position: 'fixed',
			zIndex: 1001,
			margin: '0px auto',
			width: '100%'
		});
	    $('#mapper-overlay').show();
	    $('#mapper-message').html(message).show();
	    $('#okClose').click(function() {
	      hideMessage();
	      return false; //kill the browser default action
	    });
	}
	
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//Function to hide messages
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function hideMessage() {
	  $('#mapper-message').hide().remove();
	  $('#mapper-overlay').hide().remove();
    }

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//Function to show the preview map
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function showMap() {
	
	  //destroy existing apis first
	  if(typeof jzoom_api != "undefined") {
		  jzoom_api.destroy();
	  }
	  if(typeof jcrop_api != "undefined") {
		  jcrop_api.destroy();
	  }

      //set the preview and output values
      $('#output').val('pnga');

	  //hide the bad records warning
	  $('#badRecordsWarning').hide();

      var x = $('#x').val();
	
	  var formData = $("form").serialize();
	
	  var message = '<span id="mapper-building-map">Building preview...</span>';
	  $('#mapOutput').html(message);
	  $('#mapScale').html('');

	  $.post(Mapper.settings.baseUrl + "/application/", formData, function(data) {
		$('#mapOutput').html(data);
		
		//draw the legend if present
		var legend_url = $('#legend_url').val();
		if(legend_url) {
		  $('#mapLegend').html('<img src="' + legend_url + '" />');	
		}
		else {
		  $('#mapLegend').html('');
		}
		
		//draw the scalebar if requested
		var scalebar_url = $('#scalebar_url').val();
		if(scalebar_url) {
		  $('#mapScale').html('<img src="' + scalebar_url + '" />');	
		}
		else {
		  $('#mapScale').html('');
		}
		
		//show the bad records if there are any
		var bad_points = $('#bad_points').val();
		if(bad_points) {
			$('#badRecords').html(bad_points);
			$('#badRecordsWarning').show();
		}
		
		var $toolsTabs = $('#mapTools').tabs();
		var tabIndex = ($('#selectedtab').val()) ? parseInt($('#selectedtab').val()) : 0;
		$toolsTabs.tabs('select', tabIndex);
		
		$('#mapTools').bind('tabsselect', function(event,ui) {
		  $('#selectedtab').val(ui.index);
		});

        //reset the bounding box values, but get them first for the crop function
        $('#bbox_rubberband').val('');

        //set the extent from previous rendering
        $('#bbox_map').val($('#rendered_bbox').val());

        //reset the rotation value
        $('#rotation').val($('#rendered_rotation').val());

        //reset the pan value
        $('#pan').val('');
			
		//map download
		$('#mapExport').find('.toolsPng').click(function(){
		  generateDownload('png');
		  return false;	
		});
		
		$('#mapExport').find('.toolsTiff').click(function(){
		  generateDownload('tif');
		  return false;	
		});

		$('#mapExport').find('.toolsSvg').click(function(){
		  generateDownload('svg');
		  return false;	
		});
		
		$('#mapExport').find('.toolsKml').click(function(){
		  generateDownload('kml');
		  return false;	
		});
							
		$preview.find('.toolsBadRecords').click(function() {
		  showBadRecords();
		  return false;	
		});
		
	  }, "html");

	  var arrPageSizes = ___getPageSize();
	  $('html,body').animate( {scrollTop: arrPageSizes[1] },1);
    }

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//Function to show or hide the submit/cancel buttons
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	function showHideSubmit(){
		if(newRowCount > 0 && !displaySubmit){
				//at least one new row is visible, show the hidden controls
				$hiddenControls.fadeIn(rowSpeed);
				displaySubmit= true;
		}else if(newRowCount <= 0){
			//no new rows are shown, hide the controls
			$hiddenControls.fadeOut(rowSpeed);
			//fade old rows back in and re-enable links
			$tableBody.find("tr:not(#"+blankRowID+", ."+newRowClass+")").fadeTo(rowSpeed,1,function(){
				$(this).find("a").unbind("click");//removes the click event we site above
			});
			newRowCount=0;//Make sure the count is reset to 0...just in case
			displaySubmit= false;
		}
	}

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//Function to make bad records visible in the viewport
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function showBadRecords() {
	  $('#badRecordsViewer').show();
	  $('#badRecordsClose a').click(function() {
	    $('#badRecordsViewer').hide();
	  });
    }

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Color picker
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    $('.colorPicker').ColorPicker({
	  onSubmit: function(hsb, hex, rgb, el) {
		$(el).val(rgb.r + ' ' + rgb.g + ' ' + rgb.b);
		$(el).ColorPickerHide();
	  },
	  onBeforeShow: function () {
		$(this).ColorPickerSetColor(this.value);
	  }
    }).bind('keyup', function(){
	  $(this).ColorPickerSetColor(this.value);
    });

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//Functions to handle the form
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    $('textarea.resizable:not(.textarea-processed)').TextAreaResizer();

    $("fieldset legend").click(function(){
      $(this).parent().find(".fieldset-wrapper").slideToggle("fast");
      $(this).parent().toggleClass("collapsed"); 
      return false;
    });

    $('.toolsCrop a').click(function(e) {
	  initJcrop();
	  return nothing(e);
    });

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Function to generate download
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	function generateDownload(filetype) {
		  switch(filetype) {
		    case 'kml':
		      formData = $("form").serialize();
			  $.download(Mapper.settings.baseUrl + "/application/kml/", formData, 'post');
		    break;
		
		    default:
		      $('#download').val(1);
			  $('#output').val(filetype);
			  if(jcrop_api) $('#crop').val(1);
			  formData = $("form").serialize();
			  $.download(Mapper.settings.baseUrl + "/application/", formData, 'post');
			  $('#download').val('');
			  $('#output').val('pnga');	
		  }
	}

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Function to offer download
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    jQuery.download = function(url, data, method) {
	  //url and data options required
	  if( url && data ){ 
		//data can be string of parameters or array/object
		data = typeof data == 'string' ? data : jQuery.param(data);
		//split params into form inputs
		var inputs = '';
		jQuery.each(data.split('&'), function(){ 
			var pair = this.split('=');
			var value = pair[1].replace(/\+/g,' ');
			inputs+='<input type="hidden" name="'+ unescape(pair[0]) +'" value="'+ unescape(value) +'" />';
		});
		//send request
		jQuery('<form action="'+ url +'" method="'+ (method||'post') +'">'+inputs+'</form>')
		.appendTo('body').submit().remove();
	  }
    };

});