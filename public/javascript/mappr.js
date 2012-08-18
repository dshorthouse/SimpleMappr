/*global $, jQuery, window, document, self, XMLHttpRequest, alert, encodeURIComponent, _gaq */

var Mappr = Mappr || { 'settings': {} };

$(function() {

  "use strict";

  Mappr.settings = {
    'baseUrl' : Mappr.settings.baseUrl || '',
    'active'  : Mappr.settings.active || false
  };

  Mappr.vars = {
    newPointCount      : 0,
    newRegionCount     : 0,
    maxTextareaCount   : 10,
    zoom               : true,
    fileDownloadTimer  : {},
    fillColor          : "",
    jCropType          : "zoom",
    cropUpdated        : false,
    undoSize           : 10
  };

  $.ajaxSetup({
    xhr:function() { return new XMLHttpRequest(); }
  });

  $(window).resize(function() {
    var arrPageSizes  = Mappr.getPageSize(),
        arrPageScroll = Mappr.getPageScroll();

    $('#mapper-overlay').css({
      width :  arrPageSizes[0],
      height:  arrPageSizes[1]
    });

    $('#mapper-message').css({
      top     : arrPageScroll[1] + (arrPageSizes[3] / 10),
      left    : arrPageScroll[0],
      position: 'fixed',
      zIndex  : 1001,
      margin  : '0px auto',
      width   : '100%'
    });
  });

  Mappr.trackEvent = function(category, action) {
    if (window._gaq !== undefined) { _gaq.push(['_trackEvent', category, action]); }
  };

  Mappr.getPageSize = function() {
    var xScroll, yScroll, windowWidth, windowHeight, pageHeight, pageWidth;

    if (window.innerHeight && window.scrollMaxY) {
      xScroll = window.innerWidth + window.scrollMaxX;
      yScroll = window.innerHeight + window.scrollMaxY;
    } else if (document.body.scrollHeight > document.body.offsetHeight) { // all but Explorer Mac
      xScroll = document.body.scrollWidth;
      yScroll = document.body.scrollHeight;
    } else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
      xScroll = document.body.offsetWidth;
      yScroll = document.body.offsetHeight;
    }

    if (self.innerHeight) { // all except Explorer
      if(document.documentElement.clientWidth) {
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
    if(yScroll < windowHeight) {
      pageHeight = windowHeight;
    } else {
      pageHeight = yScroll;
    }
    // for small pages with total width less then width of the viewport
    if(xScroll < windowWidth) {
      pageWidth = xScroll;
    } else {
      pageWidth = windowWidth;
    }

    return [pageWidth,pageHeight,windowWidth,windowHeight];

  }; /** end Mappr.getPageSize **/

  Mappr.getPageScroll = function() {
    var xScroll, yScroll;

    if (self.pageYOffset) {
      yScroll = self.pageYOffset;
      xScroll = self.pageXOffset;
    } else if (document.documentElement && document.documentElement.scrollTop) {// Explorer 6 Strict
      yScroll = document.documentElement.scrollTop;
      xScroll = document.documentElement.scrollLeft;
    } else if (document.body) {// all other Explorers
      yScroll = document.body.scrollTop;
      xScroll = document.body.scrollLeft;
    }

    return [xScroll,yScroll];

  }; /** end Mappr.getPageScroll **/

  Mappr.showCoords = function(c) {
    var x        = parseFloat(c.x),
        y        = parseFloat(c.y),
       x2        = parseFloat(c.x2),
       y2        = parseFloat(c.y2),
       w         = parseFloat(c.w),
       h         = parseFloat(c.h),
       ul_holder = '<input type="text" id="jcrop-coord-ul" class="jcrop-coord"></input>',
       lr_holder = '<input type="text" id="jcrop-coord-lr" class="jcrop-coord"></input>',
       d_holder  = '<div id="jcrop-dimension-wrapper"><input type="text" id="jcrop-dimension-w" class="jcrop-dimension"></input>X<input type="text" id="jcrop-dimension-h" class="jcrop-dimension"></input></div>',
       ul_point  = { 'x' : x, 'y' : y },
       lr_point  = { 'x' : x2, 'y' : y2 },
       ul_coord  = {},
       lr_coord  = {},
       factor    = $('input[name="download-factor"]:checked').val();

    switch(Mappr.vars.jCropType) {
      case 'crop':
        $('.jcrop-holder div:first').css('backgroundColor', 'white');
        $('#bbox_rubberband').val(x+','+y+','+x2+','+y2);

        if($('#projection option:selected').val() === 'epsg:4326') {
          $('.jcrop-coord').css("width", "100px");
        } else {
          $('.jcrop-coord').css("width", "175px");
        }

        if($('#jcrop-coord-ul').length === 0 && $('#jcrop-coord-lr').length === 0) {
          $('.jcrop-tracker').eq(0).after(ul_holder).after(lr_holder).after(d_holder);
        }

        ul_coord = Mappr.pix2geo(ul_point);
        lr_coord = Mappr.pix2geo(lr_point);
        $('#jcrop-coord-ul').val(ul_coord.x + ', ' + ul_coord.y);
        $('#jcrop-coord-lr').val(lr_coord.x + ', ' + lr_coord.y);
        $('#jcrop-dimension-w').val(w);
        $('#jcrop-dimension-h').val(h);
        $('#jcrop-dimension-wrapper').css({'left' : w/2-$('#jcrop-dimension-wrapper').width()/2, 'top' : h/2-$('#jcrop-dimension-wrapper').height()/2});

        $.cookie("jcrop_coords", "{ \"jcrop_coord_ul\" : \"" + $('#jcrop-coord-ul').val() + "\", \"jcrop_coord_lr\" : \"" + $('#jcrop-coord-lr').val() + "\" }" );

        $('.jcrop-coord').live("blur", function() {
          if(!Mappr.vars.cropUpdated) {
            Mappr.vars.cropUpdated = Mappr.updateCropCoordinates();
          }
        })
        .live("keypress", function(e) {
          var key = e.keyCode || e.which;
          if(key === 13 || key === 9) {
            e.preventDefault();
            Mappr.vars.cropUpdated = false;
            this.blur();
          }
        });

        $('.jcrop-dimension').live("blur", function() {
          if(!Mappr.vars.cropUpdated) {
            Mappr.vars.cropUpdated = Mappr.updateCropDimensions();
          }
        })
        .live("keypress", function(e) {
          var key = e.keyCode || e.which;
          if(key === 13 || key === 9) {
            e.preventDefault();
            Mappr.vars.cropUpdated = false;
            this.blur();
          }
        });

        $('span', '#scale-measure').text(factor*w + ' X ' + factor*h).parent().show();
      break;

      case 'zoom':
        $('.jcrop-holder div:first').css('backgroundColor', 'white');
        $('#bbox_rubberband').val(x+','+y+','+x2+','+y2);
      break;

      case 'query':
        $('#bbox_query').val(x+','+y+','+x2+','+y2);
      break;
    }
  };

  Mappr.updateCropDimensions = function() {
    var rubberband   = $('#bbox_rubberband').val().split(","),
        rubberband_w = parseFloat(rubberband[2])-parseFloat(rubberband[0]),
        rubberband_h = parseFloat(rubberband[3])-parseFloat(rubberband[1]),
        image_w      = $('#mapOutputImage').width(),
        image_h      = $('#mapOutputImage').height(),
        w            = $('#jcrop-dimension-w').val(),
        h            = $('#jcrop-dimension-h').val(),
        x            = rubberband[0],
        x2           = rubberband[2],
        y            = rubberband[1],
        y2           = rubberband[3];

    w = (w > image_w) ? image_w : (rubberband_w - w)/2;
    h = (h > image_h) ? image_h : (rubberband_h - h)/2;
    x  = parseFloat(rubberband[2])-w;
    x2 = parseFloat(rubberband[0])+w;
    y  = parseFloat(rubberband[1])+h;
    y2 = parseFloat(rubberband[3])-h;

    if(w >= image_w) {
      x  = 0;
      x2 = image_w; 
    }
    if(h >= image_h) {
      y  = 0;
      y2 = image_h;
    }

    this.loadCropSettings({ 'map' : { 'bbox_rubberband' : x.toString() + "," + y.toString() + "," + x2.toString() + "," + y2.toString() } });
    return true;
  };

  Mappr.updateCropCoordinates = function() {
    var ul_arr   = [],
        ul_point = {},
        lr_arr   = [],
        lr_point = {};

    ul_arr = $('#jcrop-coord-ul').val().split(",");
    ul_point = this.geo2pix({ 'x' : $.trim(ul_arr[0]), 'y' : $.trim(ul_arr[1]) });

    lr_arr = $('#jcrop-coord-lr').val().split(",");
    lr_point = this.geo2pix({ 'x' : $.trim(lr_arr[0]), 'y' : $.trim(lr_arr[1]) });

    $.cookie("jcrop_coords", "{ \"jcrop_coord_ul\" : \"" + $('#jcrop-coord-ul').val() + "\", \"jcrop_coord_lr\" : \"" + $('#jcrop-coord-lr').val() + "\" }" );

    this.loadCropSettings({ 'map' : { 'bbox_rubberband' : lr_point.x + "," + lr_point.y + "," + ul_point.x + "," + ul_point.y } });
    return true;
  };

  Mappr.pix2geo = function(point) {
    var deltaX = 0,
        deltaY = 0,
        bbox   = $('#bbox_map').val(),
        width  = parseFloat($('#mapOutputImage').width()),
        height = parseFloat($('#mapOutputImage').height()),
        geo    = {};

    if(bbox === "") {
      bbox = "-180,-90,180,90";
    }
    bbox = bbox.split(",");

    deltaX = Math.abs(parseFloat($.trim(bbox[2])) - parseFloat($.trim(bbox[0])));
    deltaY = Math.abs(parseFloat($.trim(bbox[3])) - parseFloat($.trim(bbox[1])));

    geo.x = this.roundNumber(parseFloat(bbox[0]) + (parseFloat(point.x)*deltaX)/width,2);
    geo.y = this.roundNumber(parseFloat(bbox[1]) + (parseFloat(height - parseFloat(point.y))*deltaY)/height,2);

    return geo;
  };

  Mappr.geo2pix = function(coord) {
    var deltaX = 0,
        deltaY = 0,
        bbox   = $('#bbox_map').val(),
        point  = {};

    if(bbox === "") {
      bbox = "-180,-90,180,90";
    }
    bbox = bbox.split(",");

    deltaX = Math.abs(parseFloat($.trim(bbox[2])) - parseFloat($.trim(bbox[0])));
    deltaY = Math.abs(parseFloat($.trim(bbox[3])) - parseFloat($.trim(bbox[1])));

    point.x = $('#mapOutputImage').width()*(Math.abs(parseFloat(coord.x) - parseFloat($.trim(bbox[0]))))/deltaX;
    point.y = $('#mapOutputImage').height()*(deltaY - Math.abs(parseFloat(coord.y) - parseFloat($.trim(bbox[1]))))/deltaY;

    return point;
  };

  Mappr.roundNumber = function(num, dec) {
    return Math.round(num*Math.pow(10,dec))/Math.pow(10,dec);
  };

  Mappr.tabSelector = function(tab) {
    var state = {};
    $("#tabs").tabs('select',tab);
    state['tabs'] = tab;
    $.bbq.pushState(state);
  };

  Mappr.RGBtoHex = function(R,G,B) {
    return this.toHex(R)+this.toHex(G)+this.toHex(B);
  };

  Mappr.toHex = function(N) {
    if (N === null) { return "00"; }
    N = parseInt(N, 10);
    if (N === 0 || isNaN(N)) { return "00"; }
    N = Math.max(0,N);
    N = Math.min(N,255);
    N = Math.round(N);
    return "0123456789ABCDEF".charAt((N-N%16)/16) + "0123456789ABCDEF".charAt(N%16);
  };

  Mappr.bindToolbar = function() {
    var self = this;

    $("#actionsBar ul li").hover(function() {
      $(this).addClass("ui-state-hover");
    }, function() {
      $(this).removeClass("ui-state-hover");
    });

    $('.toolsZoomIn').click(function(e) {
      e.preventDefault();
      self.mapZoomIn();
      self.trackEvent('toolbar', 'zoomin');
    });

    $('.toolsZoomOut').click(function(e) {
      e.preventDefault();
      self.mapZoomOut();
      self.trackEvent('toolbar', 'zoomout');
    });

    $('.toolsCrop').click(function(e) {
      e.preventDefault();
      self.mapCrop();
      self.trackEvent('toolbar', 'crop');
    });

    $('.toolsQuery').ColorPicker({
      onBeforeShow: function() {
        $(this).ColorPickerSetColor(self.RGBtoHex(150, 150, 150));
      },
      onShow: function(colpkr) {
        $(colpkr).show();
        self.destroyJcrop();
        return false;
      },
      onHide: function(colpkr) {
        $(colpkr).hide();
        return false;
      },
      onSubmit: function(hsb, hex, rgb, el) {
        hsb = null;
        hex = null;
        $(el).ColorPickerHide();
        self.vars.fillColor = rgb;
        self.initJquery();
        self.vars.zoom = false;
        self.trackEvent('toolbar', 'query');
      }
    }).click(function(e) {
      e.preventDefault();
      self.resetJbbox();
    });

    $('.toolsRefresh').click(function(e) {
      e.preventDefault();
      self.mapRefresh();
      self.trackEvent('toolbar', 'refresh');
    });

    $('.toolsRebuild').click(function(e) {
      e.preventDefault();
      self.mapRebuild();
      self.trackEvent('toolbar', 'rebuild');
    });

  }; /** end Mappr.bindToolbar **/

  Mappr.mapCrop = function() {
    //Note: method calls must be Mappr.x for hotkeys to work
    var coords   = {},
        ul_arr   = [],
        ul_point = {},
        lr_arr   = [],
        lr_point = {};

    if($.cookie("jcrop_coords")) {
      coords = $.parseJSON($.cookie("jcrop_coords"));
      ul_arr = coords.jcrop_coord_ul.split(",");
      lr_arr = coords.jcrop_coord_lr.split(",");
      ul_point = Mappr.geo2pix({ 'x' : $.trim(ul_arr[0]), 'y' : $.trim(ul_arr[1]) });
      lr_point = Mappr.geo2pix({ 'x' : $.trim(lr_arr[0]), 'y' : $.trim(lr_arr[1]) });
      Mappr.loadCropSettings({ 'map' : { 'bbox_rubberband' : lr_point.x + "," + lr_point.y + "," + ul_point.x + "," + ul_point.y } });
    } else {
      Mappr.initJcrop();
    }

    Mappr.vars.zoom = false;
  };

  Mappr.resetAndBuild = function() {
    Mappr.resetJbbox();
    Mappr.destroyRedo();
    Mappr.showMap();
  };

  Mappr.mapRefresh = function() {
    //Note: method calls must be Mappr.x for hotkeys to work
    Mappr.resetAndBuild();
    Mappr.tabSelector(0);
  };

  Mappr.mapRebuild = function() {
    //Note: method calls must be Mappr.x for hotkeys to work
    $.each(['bbox_map', 'projection_map', 'bbox_rubberband', 'rotation', 'projection', 'pan'], function() {
      $('#' + this).val('');
    });
    Mappr.destroyRedo();
    Mappr.showMap();
  };

  Mappr.bindArrows = function() {
    var self = this;

    $('.arrows').click(function(e) {
      e.preventDefault();
      $('#pan').val($(this).attr("data-pan"));
      self.resetJbbox();
      self.showMap();
      self.trackEvent('arrows', $(this).attr("data-pan"));
    });
  };

  Mappr.mapPanUp = function() {
    //Note: method calls must be Mappr.x for hotkeys to work
    $('#pan').val('up');
    Mappr.resetAndBuild();
  };

  Mappr.mapPanDown = function() {
    //Note: method calls must be Mappr.x for hotkeys to work
    $('#pan').val('down');
    Mappr.resetAndBuild();
  };

  Mappr.mapPanLeft = function() {
    //Note: method calls must be Mappr.x for hotkeys to work
    $('#pan').val('left');
    Mappr.resetAndBuild();
  };

  Mappr.mapPanRight = function() {
    //Note: method calls must be Mappr.x for hotkeys to work
    $('#pan').val('right');
    Mappr.resetAndBuild();
  };

  Mappr.mapList = function() {
    //Note: method calls must be Mappr.x for hotkeys to work
    Mappr.tabSelector(3);
  };

  Mappr.mapZoomIn = function() {
    //Note: method calls must be Mappr.x for hotkeys to work
    Mappr.initJzoom();
    Mappr.vars.zoom = true;
  };

  Mappr.mapZoomOut = function() {
    //Note: method calls must be Mappr.x for hotkeys to work
    Mappr.resetJbbox();
    $('#zoom_out').val(1);
    Mappr.destroyRedo();
    Mappr.showMap();
    $('#zoom_out').val('');
  };

  Mappr.storageType = function(type) {
    var index = 0;

    index = $.grep($.jStorage.index(), function(value, i) {
      i = null;
      return (value.substring(0, type.length) === type);
    });

    return index;
  };

  Mappr.toggleUndo = function(activate) {
    var self  = this,
        index = this.storageType("do");

    $('.toolsUndo').addClass('toolsUndoDisabled').removeClass('toolsUndo').unbind("click");

    if(activate && index.length > 1) {
      if(index.length > self.vars.undoSize) { $.jStorage.deleteKey(index.shift()); }
      $('.toolsUndoDisabled').addClass('toolsUndo').removeClass('toolsUndoDisabled').bind("click", function(e) {
        e.preventDefault();
        self.mapUndo();
        self.trackEvent('edit', 'undo');
      });
    }
  };

  Mappr.toggleRedo = function(activate) {
    var self = this;

    $('.toolsRedo').addClass('toolsRedoDisabled').removeClass('toolsRedo').unbind("click");

    if(activate) {
      $('.toolsRedoDisabled').addClass('toolsRedo').removeClass('toolsRedoDisabled').bind("click", function(e) {
        e.preventDefault();
        self.mapRedo();
        self.trackEvent('edit', 'redo');
      });
    }
  };

  Mappr.destroyRedo = function() {
    var index = Mappr.storageType("undo");

    if(index.length > 0) {
      $('.toolsRedo').addClass('toolsRedoDisabled').removeClass('toolsRedo').unbind("click");
      $.jStorage.deleteKey(index.pop());
    }
  };

  Mappr.mapUndo = function() {
    //Note: method calls must be Mappr.x for hotkeys to work
    var index          = Mappr.storageType("do"),
        curr_key       = "",
        curr_data      = {},
        prev_key       = "",
        prev_data      = {},
        prev_data_prep = {},
        showloader     = false;

    if(index.length === 1) { return; }

    Mappr.destroyRedo();

    curr_key       = index[index.length-1];
    curr_data      = $.jStorage.get(curr_key);
    prev_key       = index[index.length-2];
    prev_data      = $.jStorage.get(prev_key);
    prev_data_prep = Mappr.prepareInputs(prev_data);

    Mappr.loadInputs(prev_data_prep);

    $.jStorage.deleteKey(curr_key);
    $.jStorage.set("un" + curr_key, curr_data);

    if(prev_data.width !== curr_data.width) {
      Mappr.mapToggleSettings();
    } else {
      if(prev_data.layers.relief || prev_data.layers.reliefgrey || JSON.stringify(prev_data).length > 7000 || prev_data.projection !== "epsg:4326") { showloader = true; }
      Mappr.postData(decodeURIComponent($.param(prev_data)), null, showloader);
    }

    Mappr.toggleRedo(true);
    if(index.length === 2) { Mappr.toggleUndo(); }
  };

  Mappr.mapRedo = function() {
    //Note: method calls must be Mappr.x for hotkeys to work
    var undo_index     = Mappr.storageType("undo"),
        undo_key       = "",
        undo_data      = {},
        undo_data_prep = {},
        do_index       = Mappr.storageType("do"),
        do_key         = "",
        do_data        = {},
        token          = new Date().getTime(),
        showloader     = false;

    if(undo_index.length === 0) { return; }

    Mappr.toggleRedo();
    undo_key       = undo_index.pop();
    undo_data      = $.jStorage.get(undo_key);
    do_key         = do_index[do_index.length-1];
    do_data        = $.jStorage.get(do_key);
    undo_data_prep = Mappr.prepareInputs(undo_data);
    Mappr.loadInputs(undo_data_prep);

    $.jStorage.deleteKey(undo_key);
    $.jStorage.set("do-" + token.toString(), undo_data);

    if(undo_data.width !== do_data.width) {
      Mappr.mapToggleSettings();
    } else {
      if(undo_data.layers.relief || undo_data.layers.reliefgrey || JSON.stringify(undo_data).length > 7000 || undo_data.projection !== "epsg:4326") { showloader = true; }
      Mappr.postData(decodeURIComponent($.param(undo_data)), null, showloader);
    }

    Mappr.toggleUndo(true);
  };

  Mappr.bindHotkeys = function() {
    var self = this, keys = {}, arrows = {};

    keys = {
      'ctrl+s' : self.mapSave,
      'ctrl+d' : self.mapDownload,
      'ctrl+l' : self.mapList,
      'ctrl+r' : self.mapRefresh,
      'ctrl+n' : self.mapRebuild,
      'ctrl+x' : self.mapCrop,
      'ctrl+e' : self.mapToggleSettings,
      'ctrl++' : self.mapZoomIn,
      'ctrl+-' : self.mapZoomOut,
      'esc'    : self.destroyJcrop,
      'ctrl+z' : self.mapUndo,
      'ctrl+y' : self.mapRedo
    };

    arrows = {
      'up'    : self.mapPanUp,
      'down'  : self.mapPanDown,
      'left'  : self.mapPanLeft,
      'right' : self.mapPanRight
    };

    if(self.settings.active === "false") { delete keys['ctrl+s']; delete keys['ctrl+l']; }

    $.each(keys, function(key, value) {
      $(document).bind('keydown', key, value);
    });

    $('#mapOutput').hover(
      function() {
        $.each(arrows, function(key, value) {
          $(document).bind('keydown', key, value);
        });
        $('#mapOutputImage').dblclick(function(e) { self.dblclickZoom(this, e); });
      },
      function() {
        $.each(arrows, function(key, value) {
          key = null;
          $(document).unbind('keydown', value);
        });
        $('#mapOutputImage').unbind('dblclick');
      }
    );
  };

  Mappr.hardResetShowMap = function() {
    this.resetJbbox();
    this.destroyRedo();
    this.showMap();
  };

  Mappr.bindSettings = function() {
    var self = this;

    $('.layeropt').click(function() {
      self.hardResetShowMap();
    });

    $('.gridopt').click(function() {
      if(!$('#graticules').prop('checked')) { $('#graticules').attr('checked', true); }
      self.hardResetShowMap();
    });

    $('#gridlabel').click(function() {
      if(!$('#graticules').prop('checked')) { $('#graticules').attr('checked', true); }
      if($(this).prop('checked')) { $(this).val('false'); }
      self.hardResetShowMap();
    });

    $('#projection').change(function() {
      if($(this).val() !== "") {
        $.cookie("jcrop_coords", null);
        self.hardResetShowMap();
      }
    });

    self.toggleFileFactor();

    $('.download-factor').change(function() {
      self.toggleFileFactor($(this).val());
    });

    $('.download-filetype').change(function() {
      self.toggleFileType(this);
    });
  };

  Mappr.bindSlider = function() {
    var self = this;

    $("#border-slider").slider({
      value : 1.25,
      min   : 1,
      max   : 2,
      step  : 0.25,
      slide: function(e, ui) {
        e = null;
        $('input[name="border_thickness"]').val(ui.value);
        self.destroyRedo();
        self.showMap();
        self.trackEvent('slider', ui.value);
      }
    });
  };

  Mappr.toggleFileFactor = function(factor) {
    var scale      = "",
        rubberband = $('#bbox_rubberband').val().split(",");

    if(!factor) {
      factor = $('input[name="download-factor"]:checked').val();
    }

    if(this.vars.jCropType === 'crop') {
      scale = factor*(rubberband[2]-rubberband[0]) + " X " + factor*(rubberband[3]-rubberband[1]);
    } else {
      scale = factor*($('#mapOutputImage').width()) + " X " + factor*($('#mapOutputImage').height());
    }
    $('span', '#scale-measure').text(scale).parent().show();
  };

  Mappr.toggleFileType = function(obj) {
    if($(obj).attr("id") === 'download-svg' || $(obj).attr("id") === 'download-pptx' || $(obj).attr("id") === 'download-docx') {
      $.each(["legend", "scalebar"], function() {
        $('#'+this).attr("checked", false).attr("disabled", "disabled");
      });
      $('#border').removeAttr("disabled");
    } else if($(obj).attr("id") === 'download-kml') {
      $.each(["legend", "scalebar", "border"], function() {
        $('#'+this).attr("checked", false).attr("disabled", "disabled");
      });
    } else {
      $.each(["border", "legend", "scalebar"], function() {
        $('#'+this).removeAttr("disabled");
      });
      $('#border').removeAttr("disabled");
    }
  };

  Mappr.bindColorPickers = function() {
    var self = this;

    $('.colorPicker').ColorPicker({
      element : $(this),
      onBeforeShow: function() {
        var color = $(this).val().split(" ");
        $(this).ColorPickerSetColor(self.RGBtoHex(color[0], color[1], color[2]));
      },
      onHide: function(colpkr) {
        $(colpkr).hide();
        return false;
      },
      onSubmit: function(hsb, hex, rgb, el) {
        hsb = null;
        hex = null;
        $(el).val(rgb.r + ' ' + rgb.g + ' ' + rgb.b);
        $(el).ColorPickerHide();
      }
    }).bind('keyup', function() {
      var color = $(this).val().split(" ");
      $(this).ColorPickerSetColor(self.RGBtoHex(color[0], color[1], color[2]));
    });
  };

  Mappr.bindClearButtons = function() {
    var self = this;

    $('.clearLayers, .clearRegions, .clearFreehand').click(function(e) {
      var fieldsets = $(this).parent().prev().prev().children();

      e.preventDefault();
      $.each(['.m-mapTitle', 'textarea'], function() { $(fieldsets).find(this).val(''); });
      $.each(['Shape', 'Size'], function() {
        if($(fieldsets).find('.m-map'+this).length > 0) { $(fieldsets).find('.m-map'+this)[0].selectedIndex = 3; }
      });
      if($(this).hasClass("clearLayers")) {
        $(fieldsets).find('.colorPicker').val('0 0 0');
      } else {
        $(fieldsets).find('.colorPicker').val('150 150 150');
      }

      return false;
    });

    $('.clearself').click(function(e) {
      e.preventDefault();
      self.clearSelf($(this));
    });

  }; /** end Mappr.bindClearButtons **/

  Mappr.bindAutocomplete = function() {
    var self = this, term = "", terms = [];
    
    $('textarea', '.fieldset-regions').bind("keydown", function(e) {
      if (e.keyCode === $.ui.keyCode.TAB && $(this).data("autocomplete").menu.active) { e.preventDefault(); }
    }).autocomplete({
      source: function(request, response) {
        $.getJSON( "/places/" + self.extractLast(request.term), {}, response);
      },
      search: function() {
        term = self.extractLast(this.value);
        if (term.length < 2) { return false; }
      },
      focus: function() { return false; },
      select: function(e, ui) {
        e = null;
        terms = self.split(this.value);
        terms.pop();
        terms.push(ui.item.value);
        terms.push("");
        this.value = terms.join(", ");
        return false;
      }
    });
  };

  Mappr.split = function( val, delimiter ) {
    switch(delimiter) {
      case '[':
       return val.split(/\]/);

      default:
        return val.split(/,\s*/);
    }
  };

  Mappr.extractLast = function( term ) {
    return this.split(term).pop();
  };

  Mappr.clearSelf = function(el) {
    var box = $(el).parent(),
        color_picker = $(box).find('.colorPicker');

    $.each(['.m-mapTitle', 'textarea'], function() { $(box).find(this).val(''); });
    $.each(['Shape', 'Size'], function() {
      if($(box).find('.m-map'+this).length > 0) { $(box).find('.m-map'+this)[0].selectedIndex = 3; }
    });
    if($(box).parent().hasClass("fieldset-points")) {
      color_picker.val('0 0 0');
    } else {
      color_picker.val('150 150 150');
    }
  };

  Mappr.destroyJcrop = function() {
    //Note: object reference must be Mappr.x for hotkeys to work
    var vars = Mappr.vars;

    if(vars.jzoomAPI !== undefined) { vars.jzoomAPI.destroy(); }
    if(vars.jcropAPI !== undefined) { vars.jcropAPI.destroy(); }
    if(vars.jqueryAPI !== undefined) { vars.jqueryAPI.destroy(); }

    $('#mapOutputImage').show();
    $('.jcrop-holder').remove();
    $('#mapCropMessage').hide();

    Mappr.toggleFileFactor();
  };

  Mappr.resetJbbox = function() {
    this.vars.jCropType = "zoom";
    $.each(['rubberband', 'query'], function() { $('#bbox_' + this).val(''); });
    this.toggleFileFactor();
  };

  Mappr.initJcrop = function(select) {
    var self = this, vars = this.vars;

    self.destroyJcrop();
    self.resetJbbox();

    vars.jCropType = "crop";

    vars.jcropAPI = $.Jcrop('#mapOutputImage', {
      bgColor   : ($('#mapOutputImage').attr("src") === "public/images/basemap.png") ? 'grey' : 'black',
      bgOpacity : 0.5,
      onChange  : self.showCoords,
      onSelect  : self.showCoords,
      setSelect : select
    });

    $('#mapCropMessage').show();
  };

  Mappr.initJzoom = function() {
    var self = this, vars = this.vars;

    self.destroyJcrop();
    self.resetJbbox();

    vars.jCropType = "zoom";

    vars.jzoomAPI = $.Jcrop('#mapOutputImage', {
      addClass      : "customJzoom",
      sideHandles   : false,
      cornerHandles : false,
      dragEdges     : false,
      bgOpacity     : 1,
      bgColor       : "white",
      onChange      : self.showCoords,
      onSelect      : self.showCoords
    });

    $('.jcrop-tracker').mousedown(function() { self.activateJcrop('zoom'); });
  };

  Mappr.initJquery = function() {
    var self = this, vars = this.vars;

    self.destroyJcrop();
    self.resetJbbox();

    vars.jCropType = "query";

    vars.jqueryAPI = $.Jcrop('#mapOutputImage', {
      addClass      : "customJzoom",
      sideHandles   : false,
      cornerHandles : false,
      dragEdges     : false,
      bgOpacity     : 1,
      bgColor       :'white',
      onChange      : self.showCoords,
      onSelect      : self.showCoords
    });

    $('.jcrop-tracker').mousedown(function() { self.activateJcrop('query'); });
  };

  Mappr.activateJcrop = function(type) {
    switch(type) {
      case 'zoom':
        $(document).bind("mouseup", this, this.aZoom);
      break;

      case 'query':
        $(document).bind("mouseup", this, this.aQuery);
      break;
    }
  };

  Mappr.aZoom = function(event) {
    var self = event.data;
    self.destroyRedo();
    self.showMap();
    $(document).unbind("mouseup", self.aZoom);
  };

  Mappr.dblclickZoom = function(obj, e) {
    var x = 0, y = 0, pos = {};

    pos = $(obj).offset();
    x   = (e.pageX - pos.left);
    y   = (e.pageY - pos.top);

    $('#bbox_rubberband').val(x+','+y+','+x+','+y);
    this.destroyRedo();
    this.showMap();
  };

  Mappr.aQuery = function(e) {
    var self      = e.data,
        i         = 0,
        fillColor = self.vars.fillColor.r + " " + self.vars.fillColor.g + " " + self.vars.fillColor.b,
        formData  = {
          bbox           : $('#rendered_bbox').val(),
          bbox_query     : $('#bbox_query').val(),
          projection     : $('#projection').val(),
          projection_map : $('#projection_map').val(),
          qlayer         : ($('#stateprovince').prop('checked')) ? 'stateprovinces_polygon' : 'base',
          width          : $('input[name="width"]').val(),
          height         : $('input[name="height"]').val()
        };

    $(document).unbind("mouseup", self.aQuery);

    self.destroyJcrop();
    self.destroyRedo();

    self.showLoadingMessage($('#mapper-loading-message').text());

    $.ajax({
      type : 'POST',
      url  : self.settings.baseUrl + '/query/',
      data : formData,
      success: function(data) {
        if(data.length > 0) {
          var regions       = "",
              num_fieldsets = $('.fieldset-regions').length;

          for(i = 0; i < data.length; i += 1) {
            regions += data[i];
            if(i < data.length-1) { regions += ", "; }
          }

          for(i = 0; i < num_fieldsets; i += 1) {
            if($('input[name="regions['+i+'][title]"]').val() === "" || $('textarea[name="regions['+i+'][data]"]').val() === "") {
              $('input[name="regions['+i+'][title]"]').val("Selected Region " + (i+1).toString());
              $('input[name="regions['+i+'][color]"]').val(fillColor);
              $('textarea[name="regions['+i+'][data]"]').val(regions);
              if(i === (num_fieldsets-1) && !$('button[data-type="regions"]').is(':disabled')) {
                self.addAccordionPanel('regions');
              }
              break;
            } else {
              if(i === (num_fieldsets-1)) { self.addAccordionPanel('regions'); num_fieldsets += 1; }
            }
          }

          $('#fieldSetsRegions').accordion("activate", i-1);
          self.showMap();
        } else {
          self.hideLoadingMessage();
        }
      }
    });

  }; /** end Mappr.aQuery **/

  Mappr.textareaCounter = function(type, action) {
    var self = this;

    switch(action) {
      case 'get':
        switch(type) {
          case 'coords':
            return self.vars.newPointCount;
          case 'regions':
            return self.vars.newRegionCount;
        }
        break;

      case 'increase':
        switch(type) {
          case 'coords':
            return (self.vars.newPointCount += 1);
          case 'regions':
            return (self.vars.newRegionCount += 1);
        }
        break;

      case 'decrease':
        switch(type) {
          case 'coords':
            return (self.vars.newPointCount -= 1);
          case 'regions':
            return (self.vars.newRegionCount -= 1);
        }
        break;
    }

  }; /** end Mappr.textareaCounter **/

  Mappr.addAccordionPanel = function(data_type) {
    var self     = this,
        counter  = self.textareaCounter(data_type, 'get'),
        button   = $(".addmore[data-type='" + data_type + "']"),
        clone    = {},
        color    = (data_type === 'coords') ? "0 0 0" : "150 150 150",
        num      = 0,
        children = [];

    if(button.attr("data-type") === data_type) {

      if(counter < self.vars.maxTextareaCount) {

        button.parent().prev().accordion("activate", false);
        clone = button.parent().prev().children("div:last").clone();
        num = parseInt(clone.find("h3 a").text().split(" ")[1],10);
        counter = self.textareaCounter(data_type, 'increase');
        clone.find("h3 a").text(clone.find("h3 a").text().split(" ")[0] + " " + (num+1).toString());
        clone.find("input.m-mapTitle").attr("name", data_type + "["+num.toString()+"][title]").val("");
        clone.find("textarea")
                .attr("name", data_type + "["+num.toString()+"][data]")
                .removeClass("textarea-processed")
                .val("")
                .each(function() {
                  self.addGrippies(this);
                });

        clone.find("select.m-mapShape").attr("name", data_type + "["+num.toString()+"][shape]").val("circle");
        clone.find("select.m-mapSize").attr("name", data_type + "["+num.toString()+"][size]").val("10");
        clone.find("input.colorPicker").attr("name", data_type + "["+num.toString()+"][color]").val(color).ColorPicker({
          onBeforeShow: function() {
            var color = $(this).val().split(" ");
            $(this).ColorPickerSetColor(self.RGBtoHex(color[0], color[1], color[2]));
          },
          onHide: function(colpkr) {
            $(colpkr).hide();
            return false;
          },
          onSubmit: function(hsb, hex, rgb, el) {
            hsb = null;
            hex = null;
            $(el).val(rgb.r + " " + rgb.g + " " + rgb.b);
            $(el).ColorPickerHide();
          }
        }).bind('keyup', function() {
          var color = $(this).val().split(" ");
          $(this).ColorPickerSetColor(self.RGBtoHex(color[0], color[1], color[2]));
        });

        children = button.parent().prev().append(clone).children("div");

        children.each(function(i, val) {
          val = null;
          if (i === children.length-1) {
            $(this).find("button.removemore").show().click(function(e) {
              e.preventDefault();
              self.removeAccordionPanel(clone, data_type);
              counter = self.textareaCounter(data_type, 'decrease');
            }).parent()
            .find("button.clearself").click(function(e) {
              e.preventDefault();
              self.clearSelf($(this));
            }).parent().parent()
            .find(".ui-icon:last").remove();
          }
        });

        button.parent().prev().accordion("destroy").accordion({
          header      : 'h3',
          collapsible : true,
          autoHeight  : false,
          active      : false
        });

        if(data_type === 'regions') { self.bindAutocomplete(); }

      }

      if(counter >= self.vars.maxTextareaCount-3) {
        button.attr("disabled","disabled");
      }

    }

  }; /** end Mappr.addAccordionPanel **/

  Mappr.removeAccordionPanel = function(clone, data_type) {
    var button = $(".addmore[data-type='" + data_type + "']");

    clone.nextAll().each(function() {
      var num = parseInt($(this).find("h3 a").text().split(" ")[1],10);
      $(this).find("h3 a").text($(this).find("h3 a").text().split(" ")[0] + " " + (num-1).toString());
      $(this).find("input.m-mapTitle").attr("name", data_type + "["+(num-2).toString()+"][title]");
      $(this).find("textarea").attr("name", data_type + "["+(num-2).toString()+"][data]");
      $(this).find("select.m-mapShape").attr("name", data_type + "["+(num-2).toString()+"][shape]");
      $(this).find("select.m-mapSize").attr("name", data_type + "["+(num-2).toString()+"][size]");
      $(this).find("input.colorPicker").attr("name", data_type + "["+(num-2).toString()+"][color]");
    });
    clone.remove();
    button.removeAttr("disabled");
  };

  Mappr.addGrippies = function(obj) {
    var textarea     = $(obj).addClass("textarea-processed"),
        staticOffset = null;

    function performDrag(e) {
      textarea.height(Math.max(32, staticOffset + e.pageY) + "px");
      return false;
    }

    function endDrag() {
      $(document).unbind("mousemove", performDrag).unbind("mouseup", endDrag);
      textarea.css("opacity", 1);
    }

    function startDrag(e) {
      staticOffset = textarea.height() - e.pageY;
      textarea.css("opacity", 0.25);
      $(document).bind('mousemove', performDrag).bind('mouseup', endDrag);
      return false;
    }

    $(obj).parent().find(".grippie").bind('mousedown', startDrag);
  };

  Mappr.bindAddButtons = function() {
    var self = this;

    $('.addmore').click(function(e) {
      var data_type = $(this).attr("data-type"), fieldsets = 0;
      e.preventDefault();
      self.addAccordionPanel(data_type);
      fieldsets = $(this).parent().prev().children().length;
      $(this).parent().prev().accordion("activate", fieldsets-1);
      return false;
    });

  }; /** end Mappr.bindAddButtons **/

  Mappr.loadMapList = function(object) {
    var self  = this,
        obj = object || {},
        clone = $('.usermaps-loading').clone(true),
        data = {};

    $('#usermaps').html("").append(clone.show());

    data = {
      locale : self.getParameterByName("locale"),
      q      : (obj.q) ? encodeURIComponent(obj.q.toLowerCase()) : null,
      uid    : obj.uid || null
    };

    if(obj.sort) {
      data.sort = obj.sort.item;
      data.dir = obj.sort.dir;
    }

    if(!data.locale) { delete data.locale; }
    if(!data.q) { delete data.q; }
    if(!data.uid) { delete data.uid; }

    $.ajax({
      type     : 'GET',
      url      : self.settings.baseUrl + "/usermaps/",
      data     : data,
      dataType : 'html',
      success  : function(response) {
        if(response.indexOf("session timeout") !== -1) {
          window.location.reload();
        } else {
          $('#usermaps').find('.usermaps-loading').remove().end().html(response);
          $(".toolsRefresh", ".grid-usermaps").click(function(e) { e.preventDefault(); self.loadMapList(); });
          $('#filter-mymaps')
            .val(obj.q)
            .keypress(function(e) {
              if (e.which === 13) {
                e.preventDefault();
                data.q = $(this).val();
                self.loadMapList(data);
                self.trackEvent('maplist', 'filter');
              }
            })
            .blur(function(e) {
              e.preventDefault();
              self.loadMapList(data);
              self.trackEvent('maplist', 'filter');
          }).focus();
          $(".ui-icon-triangle-sort", ".grid-usermaps").click(function(e) {
            e.preventDefault();
            data.sort = { item : $(this).attr("data-sort"), dir : "asc" };
            if($(this).hasClass("asc")) { data.sort.dir = "desc"; }
            self.loadMapList(data);
            self.trackEvent('maplist', 'sort');
          });
          $('.map-load').click(function(e) {
            e.preventDefault();
            self.loadMap(this);
            self.trackEvent('map', 'load');
          });
          $('.map-delete').click(function(e) {
            e.preventDefault();
            self.deleteMapConfirmation(this);
          });
        }
      }
    });

  };

  Mappr.removeExtraElements = function() {
    var self         = this,
        i            = 0,
        numPoints    = $('.fieldset-points').size(),
        numRegions   = $('.fieldset-regions').size();

    if(numPoints > 3) {
      for(i = numPoints-1; i >= 3; i -= 1) {
        $('#fieldSetsPoints div.fieldset-points:eq('+i.toString()+')').remove();
      }
      self.vars.newPointCount = 0;
    }

    if(numRegions > 3) {
      for(i = numRegions-1; i >= 3; i -= 1) {
        $('#fieldSetsRegions div.fieldset-regions:eq('+i.toString()+')').remove();
      }
      self.vars.newRegionCount = 0;
    }
  };

  Mappr.prepareInputs = function(data) {
    var inputs = {}, item = [];

    inputs = {
      "status" : "ok",
      "mid"    : $('.map-embed').attr("data-mid"),
      "map"    : data
    };

    inputs.map.coords  = inputs.map.coords || [];
    inputs.map.regions = inputs.map.regions || [];
    inputs.map.layers  = inputs.map.layers || {};
    inputs.map.options = inputs.map.options || {};

    String.prototype.clean = function() {
      return this.replace(/[\[\]]/g, "");
    };

    $.each(data, function(key, value) {
      if(key.indexOf("coords") !== -1) {
        item = key.match(/\[[A-Za-z0-9]*?\]/g);
        if(item){
          if(inputs.map.coords[parseInt(item[0].clean(),10)] === undefined) { inputs.map.coords[parseInt(item[0].clean(),10)] = {}; }
          inputs.map.coords[parseInt(item[0].clean(),10)][item[1].clean()] = value;
          delete inputs.map["coords" + item[0] + item[1]];
        }
      }
      if(key.indexOf("regions") !== -1) {
        item = key.match(/\[[A-Za-z0-9]*?\]/g);
        if(item) {
          if(inputs.map.regions[parseInt(item[0].clean(),10)] === undefined) { inputs.map.regions[parseInt(item[0].clean(),10)] = {}; }
          inputs.map.regions[parseInt(item[0].clean(),10)][item[1].clean()] = value;
          delete inputs.map["regions" + item[0] + item[1]];
        }
      }
      if(key.indexOf("layers") !== -1) {
        item = key.match(/\[[A-Za-z0-9]*?\]/g);
        if(item) {
          inputs.map.layers[item[0].clean()] = value;
          delete inputs.map["layers" + item[0]];
        }
      }
      if(key.indexOf("options") !== -1) {
        item = key.match(/\[[A-Za-z0-9]*?\]/g);
        if(item) {
          inputs.map.options[item[0].clean()] = value;
          delete inputs.map["options" + item[0]];
        }
      }
      if(key === "save[title]") {
        inputs.map.save = { 'title' : value };
        delete inputs.map["save[title]"];
      }
      if(key === "gridspace") {
        inputs.map.grid_space = value;
      }
      if(key === "download-filetype") {
        inputs.map.download_filetype = value;
      }
      if(key === "download-factor") {
        inputs.map.download_factor = value;
      }
    });

    return inputs;
  };

  Mappr.loadInputs = function(data) {
    var self   = this,
        filter = $('#filter-mymaps').val();

    self.removeExtraElements();
    $('#form-mapper').clearForm();
    $.each(['width', 'height'], function() { $('input[name="'+this+'"]').val($('input[name="'+this+'"]').val()); });
    $('#filter-mymaps').val(filter);
    self.loadCoordinates(data);
    self.loadRegions(data);
    self.loadLayers(data);
    self.loadSettings(data);
  };

  Mappr.loadMap = function(obj) {
    var self     = this,
        id       = $(obj).attr("data-mid");

    self.tabSelector(0);
    self.showLoadingMessage($('#mapper-loading-message').text());

    $.ajax({
      type     : 'GET',
      url      : self.settings.baseUrl + "/usermaps/" + id,
      dataType : 'json',
      success  : function(data) {
        self.loadInputs(data);
        self.showMap(data);
        self.bindStorage();
        self.activateEmbed(id);
        self.toggleUndo();
        self.toggleRedo();
      }
    });
  };

  Mappr.loadSettings = function(data) {
    var pattern           = /[?*:;{}\\ "']+/g,
        map_title         = "",
        download_filetype = "",
        self              = this;

    map_title = data.map.save.title;

    $('input[name="save[title]"]').val(map_title);
    $('.m-mapSaveTitle').val(map_title);

    $('#mapTitle').text(map_title);

    map_title = map_title.replace(pattern, "_");
    $('#file-name').val(map_title);
    $("#projection").val(data.map.projection);
    $.each(['bbox_map', 'projection_map', 'rotation'], function() { $('input[name="'+this+'"]').val(data.map[this]); });

    $('input[name="border_thickness"]').val(1.25);
    $('#border-slider').slider({value:1.25});
    if(data.map.border_thickness !== undefined && data.map.border_thickness) {
      $('input[name="border_thickness"]').val(data.map.border_thickness);
      $('#border-slider').slider({value:data.map.border_thickness});
    }

    self.setRotation(data.map.rotation);
    self.resetJbbox();

    $.each(["border", "legend", "scalebar"], function() {
      $('#'+this).attr('checked', false);
      $('input[name="options['+this+']"]').val("");
    });

    if(data.map.options !== undefined) {
      $.each(["border", "legend", "scalebar"], function() {
        if(data.map.options[this] && data.map.options[this] !== undefined) {
          $('#'+this).attr('checked', true);
          $('input[name="options['+this+']"]').val(1);
        }
      });
    }

    if(data.map.download_factor !== undefined && data.map.download_factor) {
      $('input[name="download_factor"]').val(data.map.download_factor);
      $('#download-factor-' + data.map.download_factor).attr('checked', true);
    } else {
      $('#download-factor-3').attr('checked', true);
    }

    if(data.map.download_filetype !== undefined && data.map.download_filetype) {
      $('input[name="download_filetype"]').val(data.map.download_filetype);
      download_filetype = $('#download-' + data.map.download_filetype).attr('checked', true);
      self.toggleFileType(download_filetype);
    } else {
      $('#download-svg').attr('checked', true);
    }

    if(data.map.grid_space !== undefined && data.map.grid_space) {
      $('input[name="gridspace"]').attr('checked', false);
      $('#gridspace-' + data.map.grid_space).attr('checked', true);
    } else {
      $('#gridspace').attr('checked', true);
    }

    if(data.map.gridlabel !== undefined && data.map.gridlabel) {
      $('input[name="gridlabel"]').attr('checked', true).val('false');
    } else {
      $('#gridlabel').attr('checked', false);
    }

  }; //** end Mappr.loadSettings **/

  Mappr.loadCropSettings = function(data) {
    var self = this, rubberband = [];

    if(data.map.bbox_rubberband) {
      rubberband = data.map.bbox_rubberband.split(",");
      self.initJcrop([rubberband[2], rubberband[3], rubberband[0], rubberband[1]]);
      self.toggleFileFactor(data.map.download_factor);
    } else {
      self.destroyJcrop();
    }
  };

  Mappr.loadShapeSize = function(i, coords) {
    $.each(['shape', 'size'], function() {
      if(coords[i][this].toString() === "") {
        $('select[name="coords['+i.toString()+']['+this+']"]')[0].selectedIndex = 3;
      } else {
        $('select[name="coords['+i.toString()+']['+this+']"]').val(coords[i][this]);
      }
    });
  };

  Mappr.loadCoordinates = function(data) {
    var self        = this,
        i           = 0,
        coords      = data.map.coords || [],
        coord_title = "",
        coord_data  = "",
        coord_color = "",
        pattern     = /[?*{}\\]+/g;

    for(i = 0; i < coords.length; i += 1) {
      if(i > 2) {
        self.addAccordionPanel('coords');
      }

      coord_title = coords[i].title || "";
      coord_data  = coords[i].data.replace(pattern, "")  || "";
      coord_color = coords[i].color || "0 0 0";

      $('input[name="coords['+i.toString()+'][title]"]').val(coord_title);
      $('textarea[name="coords['+i.toString()+'][data]"]').val(coord_data);

      self.loadShapeSize(i, coords);

      $('input[name="coords['+i.toString()+'][color]"]').val(coord_color);
    }

  };

  Mappr.loadRegions = function(data) {
    var self         = this,
        i            = 0,
        regions      = data.map.regions || [],
        region_title = "",
        region_data  = "",
        region_color = "";

    for(i = 0; i < regions.length; i += 1) {
      if(i > 2) {
        self.addAccordionPanel('regions');
      }

      region_title = regions[i].title || "";
      region_data  = regions[i].data  || "";
      region_color = regions[i].color || "150 150 150";

      $('input[name="regions['+i.toString()+'][title]"]').val(region_title);
      $('textarea[name="regions['+i.toString()+'][data]"]').val(region_data);
      $('input[name="regions['+i.toString()+'][color]"]').val(region_color);
    }

  };

  Mappr.loadLayers = function(data) {
    var i = 0, keyMap = [], key = 0;

    if(data.map.layers) {
      for(key in data.map.layers) {
        if(data.map.layers.hasOwnProperty(key)) { keyMap[keyMap.length] = key; }
      }
      for(i = 0; i < keyMap.length; i += 1) {
        $('input[name="layers['+keyMap[i]+']"]').attr('checked', true);
      }
    }
  };

  Mappr.activateEmbed = function(mid) {
    var self    = this,
        types   = ['img','kml','svg','json'];

    $('.map-embed').attr("data-mid", mid).css('display', 'block').click(function(e) {
      e.preventDefault();
      $.each(types, function() {
        if(this.toString() === 'img') {
          $('#embed-'+this, '#mapEmbed').val("<img src=\"" + self.settings.baseUrl + "/map/" + mid + "\" alt=\"\" />");
        } else {
          $('#embed-'+this, '#mapEmbed').val(self.settings.baseUrl + "/map/" + mid + "." + this);
        }
      });
      
      $('#mapEmbed').find("span.mid").text(mid).end()
                    .dialog({
                      width         : (525).toString(),
                      autoOpen      : true,
                      modal         : true,
                      closeOnEscape : false,
                      draggable     : true,
                      resizable     : false,
                      buttons       : [
                        {
                          "text"  : "OK",
                          "class" : "positive",
                          "click" : function() {
                            $(this).dialog("destroy");
                          }
                        }
                      ]
                    }).show();
    });
  };

  Mappr.deleteMapConfirmation = function(obj) {
    var self    = this,
        id      = $(obj).attr("data-mid"),
        message = '<em>' + $(obj).parent().parent().find(".title").html() + '</em>';

    $('#mapper-message-delete').find('span').html(message).end().dialog({
      height        : (250).toString(),
      width         : (500).toString(),
      modal         : true,
      closeOnEscape : false,
      draggable     : true,
      resizable     : false,
      buttons       : [
        {
          "text"  : $('#button-titles span.delete').text(),
          "class" : "negative",
          "click" : function() {
            $.ajax({
              type    : 'DELETE',
              url     :  self.settings.baseUrl + "/usermaps/" + id,
              success : function() {
                self.loadMapList();
                self.trackEvent('map', 'delete');
              }
            });
            $(this).dialog("destroy");
          }
        },
        {
          "text"  : $('#button-titles span.cancel').text(),
          "class" : "ui-button-cancel",
          "click" : function() {
            $(this).dialog("destroy");
          }
        }]
    }).show();

  };

  Mappr.loadUserList = function(object) {
    var self  = this,
        clone = $('.userdata-loading').clone(true),
        obj   = object || {},
        data  = { locale : this.getParameterByName("locale") };

    $('#userdata').html("").append(clone.show());

    if(obj.sort) {
      data.sort = obj.sort.item;
      data.dir = obj.sort.dir;
    }

    if(!data.locale) { delete data.locale; }

    $.ajax({
      type     : 'GET',
      url      : self.settings.baseUrl + '/users/',
      data     : data,
      dataType : 'html',
      success  : function(response) {
        if(response.indexOf("access denied") !== -1) {
          window.location.reload();
        } else {
          $('#userdata').find('.userdata-loading').remove().end().html(response);
          $(".toolsRefresh", ".grid-users").click(function(e) {
            e.preventDefault();
            self.loadUserList();
          });
          $(".ui-icon-triangle-sort", ".grid-users").click(function(e) {
            e.preventDefault();
            data.sort = { item : $(this).attr("data-sort"), dir : "asc" };
            if($(this).hasClass("asc")) { data.sort.dir = "desc"; }
            self.loadUserList(data);
          });
          $('.user-delete').click(function(e) {
            e.preventDefault();
            self.deleteUserConfirmation(this);
          });
          $('.user-load').click(function(e) {
            e.preventDefault();
            self.tabSelector(3);
            self.loadMapList({ uid : $(this).attr("data-uid") });
          });
        }
      }
    });

  };

  Mappr.getLanguage = function() {
    var param = "", locale = this.getParameterByName("locale");

    if(locale === "fr_FR" || locale === "es_ES") {
      param = "?locale=" + locale;
    }
    return param;
  };


  Mappr.deleteUserConfirmation = function(obj) {
    var self    = this,
        id      = $(obj).attr("data-uid"),
        message = '<em>' + $(obj).parent().parent().children("td:first").html() + '</em>';

    $('#mapper-message-delete').find("span").html(message).end().dialog({
      height        : (250).toString(),
      width         : (500).toString(),
      modal         : true,
      closeOnEscape : false,
      draggable     : true,
      resizable     : false,
      buttons       : [
        {
          "text"  : $('#button-titles span.delete').text(),
          "class" : "negative",
          "click" : function() {
            $.ajax({
              type    : 'DELETE',
              url     : self.settings.baseUrl + "/users/" + id,
              success : function() {
                self.loadUserList();
                self.trackEvent('user', 'delete');
              }
            });
            $(this).dialog("destroy");
          }
        },
        {
          "text"  : $('#button-titles span.cancel').text(),
          "class" : "ui-button-cancel",
          "click" : function() {
            $(this).dialog("destroy");
          }
        }]
    }).show();
  };

  Mappr.bindSave = function() {
    var self = this;

    $(".map-save").click(function(e) {
      e.preventDefault();
      self.mapSave();
    });

  };

  Mappr.mapSave = function() {
    //Note: method calls must be Mappr.x for hotkeys to work
    var missingTitle = false,
        pattern      = /[?*:;{}\\ "'\/@#!%\^()<>.]+/g,
        map_title    = "";

    $('#mapSave').dialog({
      autoOpen      : true,
      height        : (175).toString(),
      width         : (350).toString(),
      modal         : true,
      closeOnEscape : false,
      draggable     : true,
      resizable     : false,
      buttons       : [
        {
          "text"  : $('#button-titles span.save').text(),
          "class" : "positive",
          "click" : function() {
            if($.trim($('.m-mapSaveTitle').val()) === '') { missingTitle = true; }
            if(missingTitle) {
              $('.m-mapSaveTitle').addClass('ui-state-error').keyup(function() {
                $(this).removeClass('ui-state-error');
              });
            } else {
              $('input[name="save[title]"]').val($('.m-mapSaveTitle').val());
              $.each(['factor', 'filetype'], function() { $('input[name="download_'+this+'"]').val($('input[name="download-'+this+'"]:checked').val()); });
              $('input[name="grid_space"]').val($('input[name="gridspace"]:checked').val());

              Mappr.setFormOptions();
              Mappr.showLoadingMessage($('#mapper-saving-message').text());

              if(Mappr.vars.jcropAPI === undefined) { $('#bbox_rubberband').val(''); }

              $.ajax({
                type        : 'POST',
                url         : Mappr.settings.baseUrl + '/usermaps/',
                data        : $("form").serialize(),
                dataType    : 'json',
                success     : function(data) {
                  $('#mapTitle').text($('.m-mapSaveTitle').val());
                  map_title = $('.m-mapSaveTitle').val().replace(pattern, "_");
                  $('#file-name').val(map_title);
                  Mappr.activateEmbed(data.mid);
                  Mappr.loadMapList();
                  Mappr.hideLoadingMessage();
                  Mappr.trackEvent('map', 'save');
                }
              });

              $(this).dialog("destroy");
            }
          }
      },
      {
        "text"  : $('#button-titles span.cancel').text(),
        "class" : "ui-button-cancel",
        "click" : function() {
          $(this).dialog("destroy");
        }
      }]
    });
  };

  Mappr.bindDownload = function() {
    var self = this;

    $(".map-download").click(function(e) {
      e.preventDefault();
      self.mapDownload();
    });
  };

  Mappr.mapDownload = function() {
    //Note: method calls must be Mappr.x for hotkeys to work

    $('#mapExport').dialog({
      autoOpen      : true,
      width         : (620).toString(),
      modal         : true,
      closeOnEscape : false,
      draggable     : true,
      resizable     : false,
      buttons       : [
        {
          "text"  : $('#button-titles span.download').text(),
          "class" : "positive",
          "click" : function() {
            Mappr.generateDownload();
          }
        },
        {
          "text"  : $('#button-titles span.cancel').text(),
          "class" : "ui-button-cancel",
          "click" : function() {
            $(this).dialog("destroy");
          }
        }]
    });
  };

  Mappr.bindSubmit = function() {
    var self = this, title = "", missingTitle = false;

    $(".submitForm").click(function(e) {
      e.preventDefault();
      missingTitle = false;
      $('.m-mapCoord').each(function() {
        title = $(this).parents('.ui-accordion-content').find('.m-mapTitle').keyup(function() {
          missingTitle = false;
          $(this).removeClass('ui-state-error');
        });
        if($(this).val() && $(title).val() === '') {
          missingTitle = true;
          $(title).addClass('ui-state-error');
        }
      });
      if(missingTitle) {
        self.showMessage($('#mapper-missing-legend').text());
      } else {
        self.destroyRedo();
        self.showMap();
        self.tabSelector(0);
      }
    });
  };

  Mappr.mapToggleSettings = function() {
    //Note: method calls must be Mappr.x for hotkeys to work
    $('#mapToolsCollapse a').trigger('click');
  };

  Mappr.bindPanelToggle = function() {
    var self = this;
    $('#mapToolsCollapse a').tipsy({ gravity : 'e' }).toggle(function(e) {
      e.preventDefault();
      $('#mapOutputImage').attr("width", 0).attr("height", 0);
      $('#mapOutputScale').hide();
      $(this).parent().addClass("mapTools-collapsed");
      $('#mapTools').hide("slide", { direction : "right" }, 250, function() {
        var new_width = $(window).width()*0.98;
        $('#actionsBar').animate({ width : new_width }, 250);
        $('#map').animate({ width : new_width }, 250, function() {
          $('input[name="width"]').val(new_width);
          self.mapRefresh();
        });
      });
    }, function(e) {
      e.preventDefault();
      $('#mapOutputImage').attr("width", 0).attr("height", 0);
      $('#mapOutputScale').hide();
      $(this).parent().removeClass("mapTools-collapsed");
      $('#mapTools').show("slide", { direction : "right" }, 250, function() {
        $('#actionsBar').animate({ width : "810px" }, 250);
        $('#map').animate({ width : "800px" }, function() {
           $('input[name="width"]').val(800);
           self.mapRefresh();
        });
      });
    });
  };

  Mappr.showMessage = function(message) {

    $('#mapper-message').html(message).dialog({
      autoOpen      : true,
      height        : (200).toString(),
      width         : (400).toString(),
      modal         : true,
      closeOnEscape : false,
      draggable     : true,
      resizable     : false,
      buttons       : [
        {
          "text"  : "OK",
          "class" : "positive",
          "click" : function() {
            $(this).dialog("destroy");
          }
        }
      ]
    }).show();
  };

  Mappr.drawLegend = function() {
    var legend_url = $('#legend_url').val();

    if(legend_url) {
      $('#mapLegend').html("<img src=\"" + legend_url + "\" />");
    } else {
      $('#mapLegend').html('<p><em>' + $('#mapper-legend-message').text() + '</em></p>');
    }
  };

  Mappr.drawScalebar = function() {
    $('#mapScale img').attr('src', $('#scalebar_url').val()).show();
  };

  Mappr.showBadPoints = function() {
    var bad_points = $('#bad_points').val();

    if(bad_points) {
      $('#badRecords').html(bad_points);
      $('#badRecordsWarning').show();
    }
  };

  Mappr.showLoadingMessage = function(content) {
    var message = '<span class="mapper-loading-message ui-corner-all ui-widget-content">' + content + '</span>';

    $('#mapOutput').append(message);
  };

  Mappr.hideLoadingMessage = function() {
    $('#mapOutput .mapper-loading-message').remove();
  };

  Mappr.showMap = function(load_data) {
    var self         = this,
        token        = new Date().getTime(),
        formString   = "",
        formObj      = {},
        showloader   = false;

    self.destroyJcrop();

    $('#output').val('pnga');        // set the preview and output values
    $('#badRecordsWarning').hide();  // hide the bad records warning
    $('#download_token').val(token); // set a token to be used for cookie

    formString = $("form").serialize();
    formObj    = $("form").serializeJSON();
    if(formObj["layers[relief]"] || formObj["layers[reliefgrey]"] || formString.length > 7000 || formObj.projection !== "epsg:4326") { showloader = true; }
    self.postData(formString, load_data, showloader);
    $.jStorage.set("do-" + token.toString(), formObj);
    self.toggleUndo(true);
  }; /** end Mappr.showMap **/

  Mappr.postData = function(formData, load_data, loader) {
    var self      = this;

    if(loader) { self.showLoadingMessage($('#mapper-loading-message').text()); }
    $.ajax({
      type     : 'POST',
      url      : self.settings.baseUrl + '/application/',
      data     : formData,
      dataType : 'json',
      success  : function(data) {
        self.resetFormValues(data);
        self.resetJbbox();
        self.drawMap(data, load_data);
        self.drawLegend();
        self.drawScalebar();
        self.showBadPoints();
        self.addBadRecordsViewer();
      }
    });
  };

  Mappr.resetFormValues = function(data) {
    $('#mapOutput input').each(function() { $(this).val(''); });
    $.each(["rendered_bbox", "rendered_rotation", "rendered_projection", "legend_url", "scalebar_url", "bad_points"], function() {
      $('#' + this).val(data[this]);
      if(this === 'rendered_bbox') { $('#bbox_map').val($('#' + this).val()); }
      if(this === 'rendered_projection') { $('#projection_map').val($('#' + this).val()); }
      if(this === 'rendered_rotation') { $('#rotation').val($('#' + this).val()); }
    });
    $('#pan').val('');
  };

  Mappr.drawMap = function(data, load_data) {
    var self = this;

    $('#mapOutputImage').attr("width", data.size[0]).attr("height", data.size[1]).attr("src", data.mapOutputImage).one('load', function() {
      if(!load_data) { load_data = { "map" : { "bbox_rubberband" : "" }}; }
      self.loadCropSettings(load_data);
      self.hideLoadingMessage();
    });
  };

  Mappr.addBadRecordsViewer = function() {
    var self = this;

    $('#badRecordsViewer').dialog({
      autoOpen      : false,
      height        : (200).toString(),
      width         : (500).toString(),
      position      : [200, 200],
      modal         : true,
      closeOnEscape : false,
      draggable     : true,
      resizable     : false,
      buttons: [
        {
          "text"  : "OK",
          "class" : "positive",
          "click" : function() {
            $(this).dialog("destroy");
          }
        }
      ]
    });

    $('.toolsBadRecords').click(function(e) {
      e.preventDefault();
      self.addBadRecordsViewer();
      $('#badRecordsViewer').dialog("open");
    });
  };

  Mappr.generateDownload = function() {
    var self        = this,
        pattern     = /[~$?*,:;{}\[\]\\ "'\/@#!%\^()<>.+=|`&]+/g,
        map_title   = $('#file-name').val(),
        token       = new Date().getTime().toString(),
        cookieValue = "",
        formData    = "",
        filetype    = "png";

    map_title = map_title.replace(pattern, "_");
    $('#file-name').val(map_title);
    $('input[name="file_name"]').val(map_title);

    $('input[name="download_factor"]').val($('input[name="download-factor"]:checked').val());

    filetype = $("input[name='download-filetype']:checked").val();

    self.setFormOptions();
    $('#download_token').val(token);

    $.each(['ui-dialog-buttonpane', 'download-dialog'], function() { $('.' + this).hide(); });
    $('.download-message').show();

    switch(filetype) {
      case 'pptx':
        $('#output').val('pptx');
        if(self.vars.jcropAPI) { $('#crop').val(1); } else { self.resetJbbox(); }
        formData = $("form").serialize();
        $('body').download("/application/pptx/" + self.getLanguage(), formData, 'post');
        $('#output').val('pnga');
      break;

      case 'docx':
        $('#output').val('docx');
        if(self.vars.jcropAPI) { $('#crop').val(1); } else { self.resetJbbox(); }
        formData = $("form").serialize();
        $('body').download("/application/docx/" + self.getLanguage(), formData, 'post');
        $('#output').val('pnga');
      break;

      case 'kml':
        formData = $("form").serialize();
        $('body').download("/application/kml/", formData, 'post');
      break;

      default:
        $('#download').val(1);
        $('#output').val(filetype);
        if(self.vars.jcropAPI) { $('#crop').val(1); } else { self.resetJbbox(); }
        formData = $("form").serialize();
        $('body').download("/application/", formData, 'post');
        $('#download').val('');
        $('#output').val('pnga');
    }

    self.vars.fileDownloadTimer = window.setInterval(function() {
      cookieValue = $.cookie('fileDownloadToken');
      if (cookieValue === token) {
        self.finishDownload();
      }
    }, 1000);

    self.trackEvent('download', filetype);

  }; /** end Mappr.generateDownload **/

  Mappr.setFormOptions = function() {
    $.each(["border", "legend", "scalebar"], function() {
      if($('#'+this).prop('checked')) {
        $('input[name="options['+this+']"]').val(1);
      } else {
        $('input[name="options['+this+']"]').val("");
      }
    });
  };

  Mappr.finishDownload = function() {
    $('.download-message').hide();
    $.each(['download-dialog', 'ui-dialog-buttonpane'], function() { $('.'+this).show(); });
    window.clearInterval(this.vars.fileDownloadTimer);
    $.cookie('fileDownloadToken', null); //clears this cookie value
  };

  Mappr.showExamples = function() {
    var message = '<img src="public/images/help-data.png" alt="" />';

    $('#mapper-message-help').html(message).dialog({
      height        : (350).toString(),
      width         : (525).toString(),
      autoOpen      : true,
      modal         : true,
      closeOnEscape : false,
      draggable     : true,
      resizable     : false,
      buttons       : [
        {
          "text"  : "OK",
          "class" : "positive",
          "click" : function() {
            $(this).dialog("destroy");
          }
        }
      ]
    }).show();
  };

  Mappr.showCodes = function() {
    var data  = (this.getParameterByName("locale")) ? { locale : this.getParameterByName("locale") } : {};

    $('#mapper-message-codes').dialog({
      height        : (450).toString(),
      width         : (850).toString(),
      autoOpen      : true,
      modal         : true,
      closeOnEscape : false,
      draggable     : true,
      resizable     : false,
      buttons       : [
        {
          "text"  : "OK",
          "class" : "positive",
          "click" : function() {
            $(this).dialog("destroy");
          }
        }
      ]
    }).show();

    if($('#mapper-message-codes .mapper-loading-message').length > 0) {
      this.loadCodes($('#mapper-message-codes'), data);
    }
  };

  Mappr.loadCodes = function(elem, data) {
    var self = this,
        filter = "";
    elem.find("tbody tr td").text("\xa0");
    elem.find("tbody tr td:first").text("\xa0\xa0\xa0").addClass("loading");
    $.ajax({
      type     : 'GET',
      url      : self.settings.baseUrl + '/places/',
      data     : data,
      dataType : 'html',
      success  : function(response) {
        elem.html(response);
        filter = elem.find('.filter-countries');
        filter.val("");
        if(data.filter !== undefined) { filter.val(data.filter); }
        filter.keypress(function(e) {
          var key = e.keyCode || e.which;
          if(key === 13 || key === 9) {
            e.preventDefault();
            data.filter = filter.val();
            self.loadCodes(elem, data);
          }
        });
        filter.blur(function() {
          data.filter = filter.val();
          self.loadCodes(elem, data);
        });
      }
    });
  };

  Mappr.performRotation = function(element) {
    $('#rotation').val($(element).attr("data-rotate"));
    this.resetJbbox();
    this.destroyRedo();
    this.showMap();
    this.trackEvent('rotate', $(element).attr("data-rotate"));
  };

  Mappr.setRotation = function(angle) {
    var control = $('#mapControls'),
        thumb   = $('.thumb', control),
        dots    = $('.overview', control).children(),
        rads    = 0,
        left    = 0,
        top     = 0;

    if(!angle) { angle = 0; }

    angle = parseFloat(angle) < 0 ? parseFloat(angle) +360 : parseFloat(angle);
    rads = angle * (Math.PI/180);

    $('.overview', control).css("left", -(angle / 360 * ((dots.outerWidth(true) * (dots.length)))) + 'px');  
    top = Math.round(-Math.cos(rads) * 28 + (control.outerHeight() /2 - thumb.outerHeight() /2)) + 'px';
    left = Math.round(Math.sin(rads) * 28 + (control.outerWidth() /2 - thumb.outerWidth() /2)) + 'px';
    $('.thumb', control).css('top',top).css('left',left);
  };

  Mappr.getParameterByName = function(name) {
    var cname   = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]"),
        regexS  = "[\\?&]" + cname + "=([^&#]*)",
        regex   = new RegExp(regexS),
        results = regex.exec(window.location.href);

    if(results === null) { return ""; }
    return decodeURIComponent(results[1].replace(/\+/g, " "));
  };

  Mappr.mapCircleSlider = function() {
    var i = 0, output = "";

    for(i = 0; i < 360; i += 1) {
      if(i % 5 === 0) {
        output += '<li data-rotate="' + i + '"></li>';
      }
    }
    return output;
  };

  Mappr.clearStorage = function() {
    this.destroyRedo();
    $.jStorage.flush();
  };

  Mappr.bindStorage = function() {
    var formData = {}, token = new Date().getTime();

    this.clearStorage();
    formData = $("form").serializeJSON();
    $.jStorage.set("do-" + token.toString(), formData);
  };

  Mappr.bindRotateWheel = function() {
    var self = this;
    $('.overlay','#mapControls').css('background-image', 'url("public/images/bg-rotatescroll.png")');
      $('.overview', '#mapControls').append(self.mapCircleSlider());
      $('#mapControls').tinycircleslider({snaptodots:true,radius:28,callback:function(element,index){
        index = null;
        if($('#initial-message').is(':hidden')) { self.performRotation(element); }
    }});
  };

  Mappr.bindTabs = function() {
    var tab = $('#tabs'),
        id  = 'tabs',
      tab_a_selector = 'ul.navigation a',
      config = {
        cache : true,
        load  : function(e, ui){
          e = null;
          $(ui.tab).data("cache.tabs",($(ui.panel).html() === "") ? false : true);
        },
        event : 'change'
      },
      url = "";

    $('#mapTools').tabs({selected: 0});
    tab.tabs(config).find(".ui-state-disabled").each(function() { $(this).removeClass("ui-state-disabled"); }).end().show();

    tab.find(tab_a_selector).click(function(){
      var state = {},
        idx = $(this).parent().prevAll().length;

      state[id] = idx;
      $.bbq.pushState(state);
      $.each($('#site-languages a'), function() {
        var url = $(this).attr('href').split('#')[0];
        $(this).attr('href', url + '#' + id + '=' + idx);
      });
    });

    $(window).bind('hashchange', function(e) {
      var idx = $.bbq.getState(id, true) || 0;
      tab.find(tab_a_selector).eq(idx).triggerHandler('change');
      $.each($('#site-languages a'), function() {
        var url = $(this).attr('href').split('#')[0];
        $(this).attr('href', url + '#' + id + '=' + idx);
      });
    });
    $(window).trigger('hashchange');
  };

  Mappr.init = function() {
    var self = this;
    this.bindRotateWheel();
    $('#initial-message').hide();
    $('#header>div').show();
    this.bindTabs();
    $('#mapOutput').append('<img id="mapOutputImage" src="public/images/basemap.png" alt="" width="800" height="400" />').find("span.mapper-loading-message").remove();
    $('#mapScale').append('<img id="mapOutputScale" src="public/images/basemap-scalebar.png" width="200" height="27" />');
    $('a.login','#site-session').click(function(e) { e.preventDefault(); self.tabSelector(3); });
    $('a.show-examples').click(function(e) { e.preventDefault(); self.showExamples(); });
    $('a.show-codes').click(function(e) { e.preventDefault(); self.showCodes(); });
    $('.fieldSets').accordion({header : 'h3', collapsible : true, autoHeight : false});
    $(".tooltip").tipsy({gravity : 's'});
    this.bindStorage();
    this.bindHotkeys();
    this.bindToolbar();
    this.bindArrows();
    this.bindSettings();
    this.bindSlider();
    this.bindColorPickers();
    this.bindAddButtons();
    this.bindClearButtons();
    this.bindAutocomplete();
    this.bindSave();
    this.bindDownload();
    this.bindSubmit();
    this.bindPanelToggle();
    $('.toolsUndoDisabled').click(false);
    $('.toolsRedoDisabled').click(false);
    $('textarea.resizable:not(.textarea-processed)').TextAreaResizer();
    if($('#usermaps').length > 0) { this.tabSelector(3); this.loadMapList(); }
    if($('#userdata').length > 0) { this.tabSelector(4); this.loadUserList(); }
    $("input").keypress(function(e) { if (e.which === 13) { return false; } });
  };

  Mappr.init();
});