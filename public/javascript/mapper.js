/*global $, jQuery, window, document, self, XMLHttpRequest, alert */

var Mappr = Mappr || { 'settings': {} };

$(function () {

  "use strict";

  Mappr.vars = {
    newPointCount      : 0,
    newRegionCount     : 0,
    newFreehandCount   : 0,
    maxTextareaCount   : 10,
    zoom               : true,
    fileDownloadTimer  : {},
    fillColor          : "",
    jCropType          : "zoom",
    cropUpdated        : false
  };

  $.ajaxSetup({
    xhr:function () { return new XMLHttpRequest(); }
  });

  $(window).resize(function () {
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

  Mappr.getPageSize = function () {
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


  Mappr.getPageScroll = function () {
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

  Mappr.showCoords = function (c) {
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

  Mappr.updateCropDimensions = function () {
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

  Mappr.updateCropCoordinates = function () {
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

  Mappr.pix2geo = function (point) {
    var deltaX = 0,
        deltaY = 0,
        bbox   = $('#bbox_map').val(),
        geo    = {};

    if(bbox === "") {
      bbox = "-180,-90,180,90";
    }
    bbox = bbox.split(",");

    deltaX = Math.abs(parseFloat($.trim(bbox[2])) - parseFloat($.trim(bbox[0])));
    deltaY = Math.abs(parseFloat($.trim(bbox[3])) - parseFloat($.trim(bbox[1])));

    geo.x = this.roundNumber(parseFloat(bbox[0]) + (parseFloat(point.x)*deltaX)/parseFloat($('#mapOutputImage').width()),2);
    geo.y = this.roundNumber(parseFloat(bbox[1]) + (parseFloat($('#mapOutputImage').height() - parseFloat(point.y))*deltaY)/parseFloat($('#mapOutputImage').height()),2);

    return geo;
  };

  Mappr.geo2pix = function (coord) {
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

  Mappr.roundNumber = function (num, dec) {
    return Math.round(num*Math.pow(10,dec))/Math.pow(10,dec);
  };

  Mappr.tabSelector = function (tab) {
    $("#tabs").tabs('select',tab);
  };

  Mappr.RGBtoHex = function (R,G,B) {
    return this.toHex(R)+this.toHex(G)+this.toHex(B);
  };

  Mappr.toHex = function (N) {
    if (N === null) { return "00"; }
    N = parseInt(N, 10);
    if (N === 0 || isNaN(N)) { return "00"; }
    N = Math.max(0,N);
    N = Math.min(N,255);
    N = Math.round(N);
    return "0123456789ABCDEF".charAt((N-N%16)/16) + "0123456789ABCDEF".charAt(N%16);
  };

  Mappr.bindToolbar = function () {
    var self = this;

    $("#actionsBar ul li").hover(function () {
      $(this).addClass("ui-state-hover");
    }, function () {
      $(this).removeClass("ui-state-hover");
    });

    $('.toolsZoomIn').click(function () {
      self.mapZoomIn();
      return false;
    });

    $('.toolsZoomOut').click(function () {
      self.mapZoomOut();
      return false;
    });

    $('.toolsCrop').click(function () {
      self.mapCrop();
      return false;
    });

    $('.toolsQuery').ColorPicker({
      onBeforeShow: function () {
        $(this).ColorPickerSetColor(self.RGBtoHex(150, 150, 150));
      },
      onShow: function (colpkr) {
        $(colpkr).show();
        self.destroyJcrop();
        return false;
      },
      onHide: function (colpkr) {
        $(colpkr).hide();
        return false;
      },
      onSubmit: function (hsb, hex, rgb, el) {
        hsb = null;
        hex = null;
        $(el).ColorPickerHide();
        self.vars.fillColor = rgb;
        self.initJquery();
        self.vars.zoom = false;
      }
    }).click(function () {
      self.resetJbbox();
      return false;
    });

    $('.toolsRefresh').click(function () {
      self.mapRefresh();
      return false;
    });

    $('.toolsRebuild').click(function () {
      self.mapRebuild();
      return false;
    });

  }; /** end Mappr.bindToolbar **/

  Mappr.mapCrop = function () {
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

  Mappr.mapRefresh = function () {
    //Note: method calls must be Mappr.x for hotkeys to work
    Mappr.resetJbbox();
    Mappr.showMap();
    $("#tabs").tabs('select',0);
  };

  Mappr.mapRebuild = function () {
    //Note: method calls must be Mappr.x for hotkeys to work
    $('#bbox_map').val('');
    $('#projection_map').val('');
    $('#bbox_rubberband').val('');
    $('#rotation').val('');
    $('#projection').val('');
    $('#pan').val('');
    Mappr.showMap();
  };

  Mappr.bindArrows = function () {
    var self = this;

    $('.arrows').click(function () {
      $('#pan').val($(this).attr("data-pan"));
      self.resetJbbox();
      self.showMap();
      return false;
    });
  };

  Mappr.mapPanUp = function () {
    //Note: method calls must be Mappr.x for hotkeys to work
    $('#pan').val('up');
    Mappr.resetJbbox();
    Mappr.showMap();
  };

  Mappr.mapPanDown = function () {
    //Note: method calls must be Mappr.x for hotkeys to work
    $('#pan').val('down');
    Mappr.resetJbbox();
    Mappr.showMap();
  };

  Mappr.mapPanLeft = function () {
    //Note: method calls must be Mappr.x for hotkeys to work
    $('#pan').val('left');
    Mappr.resetJbbox();
    Mappr.showMap();
  };

  Mappr.mapPanRight = function () {
    //Note: method calls must be Mappr.x for hotkeys to work
    $('#pan').val('right');
    Mappr.resetJbbox();
    Mappr.showMap();
  };

  Mappr.mapList = function () {
    $("#tabs").tabs('select',3);
  };

  Mappr.mapZoomIn = function () {
    //Note: method calls must be Mappr.x for hotkeys to work
    Mappr.initJzoom();
    Mappr.vars.zoom = true;
  };

  Mappr.mapZoomOut = function () {
    //Note: method calls must be Mappr.x for hotkeys to work
    Mappr.resetJbbox();
    $('#zoom_out').val(1);
    Mappr.showMap();
    $('#zoom_out').val('');
  };

  Mappr.bindHotkeys = function () {
    var self = this, keys = {}, arrows = {};

    keys = {
      'ctrl+s' : self.mapSave,
      'ctrl+d' : self.mapDownload,
      'ctrl+l' : self.mapList,
      'ctrl+r' : self.mapRefresh,
      'ctrl+n' : self.mapRebuild,
      'ctrl+x' : self.mapCrop,
      'ctrl++': self.mapZoomIn,
      'ctrl+-': self.mapZoomOut
    };

    arrows = {
      'up'    : self.mapPanUp,
      'down'  : self.mapPanDown,
      'left'  : self.mapPanLeft,
      'right' : self.mapPanRight
    };

    $.each(keys, function(key, value) {
      $(document).bind('keydown', key, value);
    });

    $('#mapOutput').hover(
      function () {
        $.each(arrows, function(key, value) {
          $(document).bind('keydown', key, value);
        });
      },
      function () {
        $.each(arrows, function(key, value) {
          key = null;
          $(document).unbind('keydown', value);
        });
      }
    );
  };

  Mappr.bindSettings = function () {
    var self = this;

    $('.layeropt').click(function () {
      self.resetJbbox();
      self.showMap();
    });

    $('.gridopt').click(function () {
      if(!$('#graticules').is(':checked')) { $('#graticules').attr('checked', true); }
      self.resetJbbox();
      self.showMap();
    });

    $('#projection').change(function () {
      if($(this).val() !== "") {
        $.cookie("jcrop_coords", null);
        self.resetJbbox();
        self.showMap();
      }
    });

    self.toggleFileFactor();

    $('.download-factor').change(function() {
      self.toggleFileFactor($(this).val());
    });

    $('.download-filetype').change(function () {
      self.toggleFileType(this);
    });
  };

  Mappr.toggleFileFactor = function (factor) {
    var scale      = "",
        rubberband = $('#bbox_rubberband').val().split(",");

    if(!factor) {
      factor = $('input[name="download-factor"]:checked').val();
    }

    if(Mappr.vars.jCropType === 'crop') {
      scale = factor*(rubberband[2]-rubberband[0]) + " X " + factor*(rubberband[3]-rubberband[1]);
    } else {
      scale = factor*($('#mapOutputImage').width()) + " X " + factor*($('#mapOutputImage').height());
    }
    $('span', '#scale-measure').text(scale).parent().show();
  };

  Mappr.toggleFileType = function (obj) {
    if($(obj).attr("id") === 'download-svg' || $(obj).attr("id") === 'download-pptx' || $(obj).attr("id") === 'download-docx') {
      $.each(["legend", "scalebar"], function () {
        $('#'+this).attr("checked", false).attr("disabled", "disabled");
      });
      $('#border').removeAttr("disabled");
    } else if($(obj).attr("id") === 'download-kml') {
      $.each(["legend", "scalebar", "border"], function () {
        $('#'+this).attr("checked", false).attr("disabled", "disabled");
      });
    } else {
      $.each(["border", "legend", "scalebar"], function () {
        $('#'+this).removeAttr("disabled");
      });
      $('#border').removeAttr("disabled");
    }
  };

  Mappr.bindColorPickers = function () {
    var self = this;

    $('.colorPicker').ColorPicker({
      element : $(this),
      onBeforeShow: function () {
        var color = $(this).val().split(" ");
        $(this).ColorPickerSetColor(self.RGBtoHex(color[0], color[1], color[2]));
      },
      onHide: function (colpkr) {
        $(colpkr).hide();
        return false;
      },
      onSubmit: function (hsb, hex, rgb, el) {
        hsb = null;
        hex = null;
        $(el).val(rgb.r + ' ' + rgb.g + ' ' + rgb.b);
        $(el).ColorPickerHide();
      }
    }).bind('keyup', function () {
      var color = $(this).val().split(" ");
      $(this).ColorPickerSetColor(self.RGBtoHex(color[0], color[1], color[2]));
    });
  };

  Mappr.bindClearButtons = function () {
    var self = this;

    $('.clearLayers, .clearRegions, .clearFreehand').click(function () {
      var fieldsets = $(this).parent().prev().prev().children();

      $(fieldsets).find('.m-mapTitle').val('');
      $(fieldsets).find('textarea').val('');
      if($(fieldsets).find('.m-mapShape').length > 0) {
        $(fieldsets).find('.m-mapShape')[0].selectedIndex = 3;
      }
      if($(fieldsets).find('.m-mapSize').length > 0) {
        $(fieldsets).find('.m-mapSize')[0].selectedIndex = 3;
      }
      if($(this).hasClass("clearLayers")) {
        $(fieldsets).find('.colorPicker').val('0 0 0');
      } else {
        $(fieldsets).find('.colorPicker').val('150 150 150');
      }

      return false;
    });

    $('.clearself').click(function () {
      self.clearSelf($(this));
      return false;
    });

  }; /** end Mappr.bindClearButtons **/

  Mappr.bindAutocomplete = function () {
    var self = this, term = "", terms = [];
    
    $('textarea', '.fieldset-regions').bind("keydown", function(event) {
      if (event.keyCode === $.ui.keyCode.TAB && $(this).data("autocomplete").menu.active) { event.preventDefault(); }
    }).autocomplete({
      source: function(request, response) {
        $.getJSON( "/places/" + self.extractLast(request.term), {}, response);
      },
      search: function() {
        term = self.extractLast(this.value);
        if (term.length < 2) { return false; }
      },
      focus: function() { return false; },
      select: function(event, ui) {
        event = null;
        terms = self.split(this.value);
        terms.pop();
        terms.push(ui.item.value);
        terms.push("");
        this.value = terms.join(", ");
        return false;
      }
    });
  };

  Mappr.split = function ( val, delimiter ) {
    switch(delimiter) {
      case '[':
       return val.split(/\]/);

      default:
        return val.split(/,\s*/);
    }
  };

  Mappr.extractLast = function ( term ) {
    return this.split(term).pop();
  };

  Mappr.clearSelf = function (el) {
    var box = $(el).parent();

    $(box).find('.m-mapTitle').val('');
    $(box).find('textarea').val('');
    if($(box).find('.m-mapShape').length > 0) {
      $(box).find('.m-mapShape')[0].selectedIndex = 3;
    }
    if($(box).find('.m-mapSize').length > 0) {
      $(box).find('.m-mapSize')[0].selectedIndex = 3;
    }
    if($(box).parent().hasClass("fieldset-points")) {
      $(box).find('.colorPicker').val('0 0 0');
    } else {
      $(box).find('.colorPicker').val('150 150 150');
    }
  };

  Mappr.destroyJcrop = function () {
    var vars = this.vars;

    if(typeof vars.jzoomAPI !== "undefined") { vars.jzoomAPI.destroy(); }
    if(typeof vars.jcropAPI !== "undefined") { vars.jcropAPI.destroy(); }
    if(typeof vars.jqueryAPI !== "undefined") { vars.jqueryAPI.destroy(); }

    $('#mapOutputImage').show();
    $('.jcrop-holder').remove();
    $('#mapCropMessage').hide();

    this.toggleFileFactor();
  };

  Mappr.resetJbbox = function () {
    this.vars.jCropType = "zoom";
    $('#bbox_rubberband').val('');
    $('#bbox_query').val('');
    this.toggleFileFactor();
  };

  Mappr.initJcrop = function (select) {
    var self = this, vars = this.vars;

    self.destroyJcrop();
    self.resetJbbox();

    self.vars.jCropType = "crop";

    vars.jcropAPI = $.Jcrop('#mapOutputImage', {
      bgColor   : ($('#mapOutputImage').attr("src") === "public/images/basemap.png") ? 'grey' : 'black',
      bgOpacity : 0.5,
      onChange  : self.showCoords,
      onSelect  : self.showCoords,
      setSelect : select
    });

    $('.jcrop-tracker').unbind('mouseup', self, self.aZoom);
    $('#mapCropMessage').show();
  };

  Mappr.initJzoom = function () {
    var self = this, vars = this.vars;

    self.destroyJcrop();
    self.resetJbbox();

    self.vars.jCropType = "zoom";

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

    $('.jcrop-tracker').bind('mouseup', self, self.aZoom);
  };

  Mappr.initJquery = function () {
    var self = this, vars = this.vars;

    self.destroyJcrop();
    self.resetJbbox();

    self.vars.jCropType = "query";

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

    $('.jcrop-tracker').bind('mouseup', self, self.aQuery);
  };

  Mappr.aZoom = function (event) {
    event.data.showMap();
  };

  Mappr.aQuery = function (event) {

    var self      = event.data,
        i         = 0,
        fillColor = self.vars.fillColor.r + " " + self.vars.fillColor.g + " " + self.vars.fillColor.b,
        formData  = {
          bbox           : $('#rendered_bbox').val(),
          bbox_query     : $('#bbox_query').val(),
          projection     : $('#projection').val(),
          projection_map : $('#projection_map').val(),
          qlayer         : ($('#stateprovince').is(':checked')) ? 'stateprovinces_polygon' : 'base'
        };

    self.destroyJcrop();

    self.showLoadingMessage('Building preview...');

    $.ajax({
      type : 'POST',
      url : self.settings.baseUrl + "/query/",
      data : formData,
      success: function (data) {
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

  Mappr.textareaCounter = function (type, action) {
    var self = this;

    switch(action) {
      case 'get':
        switch(type) {
          case 'coords':
            return self.vars.newPointCount;
          case 'regions':
            return self.vars.newRegionCount;
          case 'freehand':
            return self.vars.newFreehandCount;
        }
        break;

      case 'increase':
        switch(type) {
          case 'coords':
            return (self.vars.newPointCount += 1);
          case 'regions':
            return (self.vars.newRegionCount += 1);
          case 'freehands':
            return (self.vars.newFreehandCount += 1);
        }
        break;

      case 'decrease':
        switch(type) {
          case 'coords':
            return (self.vars.newPointCount -= 1);
          case 'regions':
            return (self.vars.newRegionCount -= 1);
          case 'freehands':
            return (self.vars.newFreehandCount -= 1);
        }
        break;
    }

  }; /** end Mappr.textareaCounter **/

  Mappr.addAccordionPanel = function (data_type) {
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
                .each(function () {
                  self.addGrippies(this);
                });

        clone.find("select.m-mapShape").attr("name", data_type + "["+num.toString()+"][shape]").val("circle");
        clone.find("select.m-mapSize").attr("name", data_type + "["+num.toString()+"][size]").val("10");
        clone.find("input.colorPicker").attr("name", data_type + "["+num.toString()+"][color]").val(color).ColorPicker({
          onBeforeShow: function () {
            var color = $(this).val().split(" ");
            $(this).ColorPickerSetColor(self.RGBtoHex(color[0], color[1], color[2]));
          },
          onHide: function (colpkr) {
            $(colpkr).hide();
            return false;
          },
          onSubmit: function (hsb, hex, rgb, el) {
            hsb = null;
            hex = null;
            $(el).val(rgb.r + " " + rgb.g + " " + rgb.b);
            $(el).ColorPickerHide();
          }
        }).bind('keyup', function () {
          var color = $(this).val().split(" ");
          $(this).ColorPickerSetColor(self.RGBtoHex(color[0], color[1], color[2]));
        });

        children = button.parent().prev().append(clone).children("div");

        children.each(function(i, val) {
          val = null;
          if (i === children.length-1) {
            $(this).find("button.removemore").show().click(function () {
              self.removeAccordionPanel(clone, data_type);
              counter = self.textareaCounter(data_type, 'decrease');
              return false;
            }).parent()
            .find("button.clearself").click(function () {
              self.clearSelf($(this));
              return false;
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

  Mappr.removeAccordionPanel = function (clone, data_type) {
    var button = $(".addmore[data-type='" + data_type + "']");

    clone.nextAll().each(function () {
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

  Mappr.addGrippies = function (obj) {
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

  Mappr.bindAddButtons = function () {
    var self = this;

    $('.addmore').click(function () {
      var data_type = $(this).attr("data-type"), fieldsets = 0;
      self.addAccordionPanel(data_type);
      fieldsets = $(this).parent().prev().children().length;
      $(this).parent().prev().accordion("activate", fieldsets-1);
      return false;
    });

  }; /** end Mappr.bindAddButtons **/

  Mappr.loadMapList = function () {
    var self = this, clone = $('.usermaps-loading').clone(true);

    $('#usermaps').html("").append(clone.show());

    $.ajax({
      type     : 'GET',
      url      : self.settings.baseUrl + "/usermaps/" + self.getLanguage(),
      dataType : 'html',
      success  : function(data) {
        if(data.indexOf("session timeout") !== -1) {
          window.location.reload();
        } else {
          $('#usermaps').find('.usermaps-loading').remove().end().html(data);
          $('.map-load').click(function () {
            self.loadMap(this);
            return false;
          });
          $('.map-delete').click(function () {
            self.deleteMapConfirmation(this);
            return false;
          });
        }
      }
    });

  };

  Mappr.removeExtraElements = function () {
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

  Mappr.loadMap = function (obj) {
    var self   = this,
        id     = $(obj).attr("data-mid"),
        filter = $('#filter-mymaps').val();

    $("#tabs").tabs('select',0);

    self.showLoadingMessage($('#mapper-loading-message').text());

    $.ajax({
      type     : 'GET',
      url      : self.settings.baseUrl + "/usermaps/" + id,
      dataType : 'json',
      success  : function (data) {
        self.removeExtraElements();
        $('#form-mapper').clearForm();
        $('#filter-mymaps').val(filter);
        self.loadCoordinates(data);
        self.loadRegions(data);
        self.loadLayers(data);
        self.loadSettings(data);
        self.activateEmbed(id);
        self.showMap(data);
      }
    });

  };

  Mappr.loadSettings = function (data) {
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
    $('input[name="bbox_map"]').val(data.map.bbox_map);
    $('input[name="projection_map"]').val(data.map.projection_map);
    $('input[name="rotation"]').val(data.map.rotation);

    self.setRotation(data.map.rotation);

    self.resetJbbox();

    $.each(["border", "legend", "scalebar"], function () {
      $('#'+this).attr('checked', false);
      $('input[name="options['+this+']"]').val("");
    });

    if(data.map.options !== undefined) {
      $.each(["border", "legend", "scalebar"], function () {
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

  }; //** end Mappr.loadSettings **/

  Mappr.loadCropSettings = function (data) {
    var self = this, rubberband = [];

    if(data.map.bbox_rubberband) {
      rubberband = data.map.bbox_rubberband.split(",");
      self.initJcrop([rubberband[2], rubberband[3], rubberband[0], rubberband[1]]);
      self.toggleFileFactor(data.map.download_factor);
    } else {
      self.destroyJcrop();
    }
  };

  Mappr.loadCoordinates = function (data) {
    var self        = this,
        i           = 0,
        coords      = data.map.coords || [],
        coord_title = "",
        coord_data  = "",
        coord_color = "";

    for(i = 0; i < coords.length; i += 1) {
      if(i > 2) {
        self.addAccordionPanel('coords');
      }

      coord_title = coords[i].title || "";
      coord_data  = coords[i].data  || "";
      coord_color = coords[i].color || "0 0 0";

      $('input[name="coords['+i.toString()+'][title]"]').val(coord_title);
      $('textarea[name="coords['+i.toString()+'][data]"]').val(coord_data);

      if(coords[i].shape === "") {
        $('select[name="coords['+i.toString()+'][shape]"]')[0].selectedIndex = 3;
      } else {
        $('select[name="coords['+i.toString()+'][shape]"]').val(coords[i].shape);
      }

      if(coords[i].size.toString() === "") {
        $('select[name="coords['+i.toString()+'][size]"]')[0].selectedIndex = 3;
      } else {
        $('select[name="coords['+i.toString()+'][size]"]').val(coords[i].size);
      }

      $('input[name="coords['+i.toString()+'][color]"]').val(coord_color);
    }

  };

  Mappr.loadRegions = function (data) {
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

  Mappr.loadLayers = function (data) {
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

  Mappr.activateEmbed = function (mid) {
    var self    = this,
        types   = ['img','kml','json'];

    $('.map-embed').attr("data-mid", mid).css('display', 'block').click(function () {
      $.each(types, function() {
        if(this === 'img') {
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
                      draggable     : false,
                      resizable     : false,
                      buttons       : {
                        OK: function () {
                          $(this).dialog("destroy");
                        }
                      }
                    }).show();
      return false;
    });
  };

  Mappr.deleteMapConfirmation = function (obj) {
    var self    = this,
        id      = $(obj).attr("data-mid"),
        message = '<em>' + $(obj).parent().parent().find(".title").html() + '</em>';

    $('#mapper-message-delete').find('span').html(message).end().dialog({
      height        : (250).toString(),
      width         : (500).toString(),
      modal         : true,
      closeOnEscape : false,
      draggable     : false,
      resizable     : false,
      buttons       : [
        {
          "text"  : $('#button-titles span.delete').text(),
          "click" : function () {
            $.ajax({
              type    : 'DELETE',
              url     :  self.settings.baseUrl + "/usermaps/" + id,
              success : function() {
                self.loadMapList();
              }
            });
            $(this).dialog("destroy");
          }
        },
        {
          "text"  : $('#button-titles span.cancel').text(),
          "class" : "ui-button-cancel",
          "click" : function () {
            $(this).dialog("destroy");
          }
        }]
    }).show();

  };

  Mappr.loadUserList = function () {
    var self = this, clone = $('.userdata-loading').clone(true);

    $('#userdata').html("").append(clone.show());

    $.ajax({
      type     : 'GET',
      url      : this.settings.baseUrl + "/users/" + self.getLanguage(),
      dataType : 'html',
      success  : function (data) {
        if(data.indexOf("access denied") !== -1) {
          window.location.reload();
        } else {
          $('#userdata').find('.userdata-loading').remove().end().html(data);
          $('.user-delete').click(function () {
            self.deleteUserConfirmation(this);
            return false;
          });
        }
      }
    });

  };

  Mappr.getParameterByName = function (name) {
    name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
    var regexS  = "[\\?&]" + name + "=([^&#]*)",
        regex   = new RegExp(regexS),
        results = regex.exec(window.location.href);

    if(results === null) {
      return "";
    } else {
      return decodeURIComponent(results[1].replace(/\+/g, " "));
    }
  };

  Mappr.getLanguage = function() {
    var param = "", lang = this.getParameterByName("lang");

    if(lang === "fr" || lang === "es") {
      param = "?lang=" + lang;
    }
    return param;
  };


  Mappr.deleteUserConfirmation = function (obj) {
    var self    = this,
        id      = $(obj).attr("data-uid"),
        message = '<em>' + $(obj).parent().parent().children("td:first").html() + '</em>';

    $('#mapper-message-delete').find("span").html(message).end().dialog({
      height        : (250).toString(),
      width         : (500).toString(),
      modal         : true,
      closeOnEscape : false,
      draggable     : false,
      resizable     : false,
      buttons       : [
        {
          "text"  : $('#button-titles span.delete').text(),
          "click" : function () {
            $.ajax({
              type    : 'DELETE',
              url     :  self.settings.baseUrl + "/users/" + id,
              success : function() {
                self.loadUserList();
              }
            });
            $(this).dialog("destroy");
          }
        },
        {
          "text"  : $('#button-titles span.cancel').text(),
          "class" : "ui-button-cancel",
          "click" : function () {
            $(this).dialog("destroy");
          }
        }]
    }).show();
  };

  Mappr.bindSave = function () {
    var self = this;

    $(".map-save").click(function () {
      self.mapSave();
      return false;
    });

  };

  Mappr.mapSave = function () {
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
      draggable     : false,
      resizable     : false,
      buttons       : [
        {
          "text"  : $('#button-titles span.save').text(),
          "click" : function () {
            if($.trim($('.m-mapSaveTitle').val()) === '') { missingTitle = true; }
            if(missingTitle) {
              $('.m-mapSaveTitle').addClass('ui-state-error').keyup(function () {
                $(this).removeClass('ui-state-error');
              });
            } else {
              $('input[name="save[title]"]').val($('.m-mapSaveTitle').val());
              $('input[name="download_factor"]').val($('input[name="download-factor"]:checked').val());
              $('input[name="download_filetype"]').val($('input[name="download-filetype"]:checked').val());
              $('input[name="grid_space"]').val($('input[name="gridspace"]:checked').val());

              Mappr.setFormOptions();
              Mappr.showLoadingMessage($('#mapper-saving-message').text());

              if(typeof Mappr.vars.jcropAPI === "undefined") { $('#bbox_rubberband').val(''); }

              $.ajax({
                type        : 'POST',
                url         :  Mappr.settings.baseUrl + "/usermaps/",
                data        :  $("form").serialize(),
                dataType    : 'json',
                success     : function(data) {
                  $('#mapTitle').text($('.m-mapSaveTitle').val());
                  map_title = $('.m-mapSaveTitle').val().replace(pattern, "_");
                  $('#file-name').val(map_title);
                  Mappr.activateEmbed(data.mid);
                  Mappr.loadMapList();
                  Mappr.hideLoadingMessage();
                }
              });

              $(this).dialog("destroy");
            }
          }
      },
      {
        "text"  : $('#button-titles span.cancel').text(),
        "class" : "ui-button-cancel",
        "click" : function () {
          $(this).dialog("destroy");
        }
      }]
    });
  };

  Mappr.bindDownload = function () {
    var self = this;

    $(".map-download").click(function () {
      self.mapDownload();
      return false;
    });
  };

  Mappr.mapDownload = function () {
    //Note: method calls must be Mappr.x for hotkeys to work

    $('#mapExport').dialog({
      autoOpen      : true,
      width         : (600).toString(),
      modal         : true,
      closeOnEscape : false,
      draggable     : false,
      resizable     : false,
      buttons       : [
        {
          "text"  : $('#button-titles span.download').text(),
          "click" : function() {
            Mappr.generateDownload();
          }
        },
        {
          "text"  : $('#button-titles span.cancel').text(),
          "class" : "ui-button-cancel",
          "click" : function () {
            $(this).dialog("destroy");
          }
        }]
    });
  };

  Mappr.bindSubmit = function () {
    var self = this, title = "", missingTitle = false;

    $(".submitForm").click(function () {
      missingTitle = false;
      $('.m-mapCoord').each(function () {
        title = $(this).parents('.ui-accordion-content').find('.m-mapTitle').keyup(function () {
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
        self.showMap();
        $("#tabs").tabs('select',0);
      }

      return false;
    });
  };

  Mappr.showMessage = function (message) {

    $('#mapper-message').html(message).dialog({
      autoOpen      : true,
      height        : (200).toString(),
      width         : (400).toString(),
      modal         : true,
      closeOnEscape : false,
      draggable     : false,
      resizable     : false,
      buttons       : {
        Ok : function () {
          $(this).dialog("destroy");
        }
      }
    }).show();
  };

  Mappr.drawLegend = function () {
    var legend_url = $('#legend_url').val();

    if(legend_url) {
      $('#mapLegend').html("<img src=\"" + legend_url + "\" />");
    } else {
      $('#mapLegend').html('<p><em>' + $('#mapper-legend-message').text() + '</em></p>');
    }
  };

  Mappr.drawScalebar = function () {
    $('#mapScale img').attr('src', $('#scalebar_url').val());
  };

  Mappr.showBadPoints = function () {
    var bad_points = $('#bad_points').val();

    if(bad_points) {
      $('#badRecords').html(bad_points);
      $('#badRecordsWarning').show();
    }
  };

  Mappr.showLoadingMessage = function (content) {
    var message = '<span class="mapper-loading-message ui-corner-all ui-widget-content">' + content + '</span>';

    $('#mapOutput').append(message);
  };

  Mappr.hideLoadingMessage = function () {
    $('#mapOutput .mapper-loading-message').remove();
  };

  Mappr.showMap = function (load_data) {
    var self         = this,
        token        = new Date().getTime(),
        formData     = {},
        toolsTabs    = $('#mapTools').tabs(),
        tabIndex     = ($('#selectedtab').val()) ? parseInt($('#selectedtab').val(), 10) : 0;

    self.destroyJcrop();

    $('#output').val('pnga');        // set the preview and output values
    $('#badRecordsWarning').hide();  // hide the bad records warning
    $('#download_token').val(token); // set a token to be used for cookie

    formData = $("form").serialize();

    self.showLoadingMessage($('#mapper-loading-message').text());

    $.ajax({
      type : 'POST',
      url : self.settings.baseUrl + "/application/",
      data : formData,
      dataType : 'json',
      success : function (data) {
        self.resetFormValues(data);
        self.resetJbbox();

        self.drawMap(data, load_data);
        self.drawLegend();
        self.drawScalebar();

        self.showBadPoints();
        self.addBadRecordsViewer();

        toolsTabs.tabs('select', tabIndex);
        $('#mapTools').bind('tabsselect', function (event,ui) {
          event = null;
          $('#selectedtab').val(ui.index);
        });
      }
    });

  }; /** end Mappr.showMap **/

  Mappr.resetFormValues = function (data) {
    $('#mapOutput input').each(function () {
      $(this).val('');
    });
    $('#rendered_bbox').val(data.rendered_bbox);
    $('#rendered_rotation').val(data.rendered_rotation);
    $('#rendered_projection').val(data.rendered_projection);
    $('#legend_url').val(data.legend_url);
    $('#scalebar_url').val(data.scalebar_url);
    $('#bad_points').val(data.bad_points);
    $('#bbox_map').val($('#rendered_bbox').val());             // set extent from previous rendering
    $('#projection_map').val($('#rendered_projection').val()); // set projection from the previous rendering
    $('#rotation').val($('#rendered_rotation').val());         // reset rotation value
    $('#pan').val('');                                         // reset pan value
  };

  Mappr.drawMap = function (data, load_data) {
    var self = this;

    $('#mapOutputImage').attr("src", data.mapOutputImage).one('load', function () {
      if(!load_data) { load_data = { "map" : { "bbox_rubberband" : "" }}; }
      self.loadCropSettings(load_data);
      self.hideLoadingMessage();
    });

  };

  Mappr.addBadRecordsViewer = function () {
    $('#badRecordsViewer').dialog({
      autoOpen      : false,
      height        : (200).toString(),
      width         : (500).toString(),
      position      : [200, 200],
      modal         : true,
      closeOnEscape : false,
      draggable     : false,
      resizable     : false,
      buttons: {
        Ok: function () {
          $(this).dialog("close");
        }
      }
    });

    $('.toolsBadRecords').click(function () {
      $('#badRecordsViewer').dialog("open");
      return false;
    });
  };

  Mappr.generateDownload = function () {
    var self        = this,
        pattern     = /[~$?*,:;{}\[\]\\ "'\/@#!%^()<>.+=|`&]+/g,
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

    $('.ui-dialog-buttonpane').hide();
    $('.download-dialog').hide();
    $('.download-message').show();

    switch(filetype) {
      case 'pptx':
        $('#output').val('pptx');
        if(self.vars.jcropAPI) { $('#crop').val(1); } else { self.resetJbbox(); }
        formData = $("form").serialize();
        $('body').download(self.settings.baseUrl + "/application/pptx/" + self.getLanguage(), formData, 'post');
        $('#output').val('pnga');
      break;

      case 'docx':
        $('#output').val('docx');
        if(self.vars.jcropAPI) { $('#crop').val(1); } else { self.resetJbbox(); }
        formData = $("form").serialize();
        $('body').download(self.settings.baseUrl + "/application/docx/" + self.getLanguage(), formData, 'post');
        $('#output').val('pnga');
      break;

      case 'kml':
        formData = $("form").serialize();
        $('body').download(self.settings.baseUrl + "/application/kml/", formData, 'post');
      break;

      default:
        $('#download').val(1);
        $('#output').val(filetype);
        if(self.vars.jcropAPI) { $('#crop').val(1); } else { self.resetJbbox(); }
        formData = $("form").serialize();
        $('body').download(self.settings.baseUrl + "/application/", formData, 'post');
        $('#download').val('');
        $('#output').val('pnga');
    }

    self.vars.fileDownloadTimer = window.setInterval(function () {
      cookieValue = $.cookie('fileDownloadToken');
      if (cookieValue === token) {
        self.finishDownload();
      }
    }, 1000);

  }; /** end Mappr.generateDownload **/

  Mappr.setFormOptions = function () {
    $.each(["border", "legend", "scalebar"], function () {
      if($('#'+this).is(':checked')) {
        $('input[name="options['+this+']"]').val(1);
      } else {
        $('input[name="options['+this+']"]').val("");
      }
    });
  };

  Mappr.finishDownload = function () {
    $('.download-message').hide();
    $('.download-dialog').show();
    $('.ui-dialog-buttonpane').show();
    window.clearInterval(this.vars.fileDownloadTimer);
    $.cookie('fileDownloadToken', null); //clears this cookie value
  };

  Mappr.showExamples = function() {
    var message = '<img src="public/images/help_data.png" alt="" />';

    $('#mapper-message-help').html(message).dialog({
      height        : (350).toString(),
      width         : (525).toString(),
      autoOpen      : true,
      modal         : true,
      closeOnEscape : false,
      draggable     : false,
      resizable     : false,
      buttons       : {
        OK: function () {
          $(this).dialog("destroy");
        }
      }
    }).show();

    return false;
  };

  Mappr.performRotation = function (element) {
    $('#rotation').val($(element).attr("data-rotate"));
    this.resetJbbox();
    this.showMap();
  };

  Mappr.setRotation = function (angle) {
    var control = $('#mapControls'),
        thumb   = $('.thumb', control),
        dots    = $('.overview', control).children(),
        rads    = 0,
        left    = 0,
        top     = 0;

    angle = parseFloat(angle) < 0 ? parseFloat(angle) +360 : parseFloat(angle);
    rads = angle * (Math.PI/180);

    $('.overview', control).css("left", -(angle / 360 * ((dots.outerWidth(true) * (dots.length)))) + 'px');  
    top = Math.round(-Math.cos(rads) * 28 + (control.outerHeight() /2 - thumb.outerHeight() /2)) + 'px';
    left = Math.round(Math.sin(rads) * 28 + (control.outerWidth() /2 - thumb.outerWidth() /2)) + 'px';
    $('.thumb', control).css('top',top).css('left',left);
  };

  Mappr.getParameterByName = function (name) {
    var cname   = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]"),
        regexS  = "[\\?&]" + cname + "=([^&#]*)",
        regex   = new RegExp(regexS),
        results = regex.exec(window.location.href);

    if(results === null) {
      return "";
    } else {
      return decodeURIComponent(results[1].replace(/\+/g, " "));
    }
  };

  Mappr.activateJanrain = function () {
    var tokenUrlparam = "/", e = "", s = "";

    if(Mappr.settings.active === "false") {
      if (typeof window.janrain !== 'object') { window.janrain = {}; }
      window.janrain.settings = {};

      if(this.getParameterByName('lang')) {
        tokenUrlparam = "/?lang=" + Mappr.getParameterByName('lang');
      }
    
      window.janrain.settings.tokenUrl = Mappr.settings.baseUrl + '/session' + tokenUrlparam;

      if (document.addEventListener) {
        document.addEventListener("DOMContentLoaded", this.isJanrainReady, false);
      } else {
        window.attachEvent('onload', this.isJanrainReady);
      }

      e = document.createElement('script');
      e.type = 'text/javascript';
      e.id = 'janrainAuthWidget';

      if (document.location.protocol === 'https:') {
        e.src = 'https://rpxnow.com/js/lib/simplemappr/engage.js';
      } else {
        e.src = 'http://widget-cdn.rpxnow.com/js/lib/simplemappr/engage.js';
      }

      s = document.getElementsByTagName('script')[0];
      s.parentNode.insertBefore(e, s);
    }
  };

  Mappr.isJainrainReady = function () {
    if (typeof window.janrain !== 'object') { window.janrain = {}; }
    window.janrain.ready = true;
  };

  Mappr.mapCircleSlider = function () {
    var i = 0, output = "";

    for(i = 0; i < 360; i += 1) {
      if(i % 5 === 0) {
        output += '<li data-rotate="' + i + '"></li>';
      }
    }
    return output;
  };

  Mappr.init = function () {
    var self = this;
    $('.overlay','#mapControls').css('background-image', 'url('+self.settings.baseUrl+'/public/images/bg-rotatescroll.png)');
    $('.overview', '#mapControls').append(self.mapCircleSlider());
    $('#mapControls').tinycircleslider({snaptodots:true,radius:28,callback:function(element,index){
      index = null;
      if($('#initial-message').is(':hidden')) { self.performRotation(element); }
    }});
    $('#initial-message').hide();
    $('#site-logout').show();
    $('#site-languages').show();
    $("#tabs").tabs({cache : false}).show();
    $('#mapTools').tabs();
    $('.fieldSets').accordion({
      header : 'h3',
      collapsible : true,
      autoHeight : false
    });
    $('#mapOutput').append('<img id="mapOutputImage" src="public/images/basemap.png" alt="" width="800" height="400" />').find("span.mapper-loading-message").remove();
    $('#mapScale').append('<img id="mapOutputScale" src="public/images/basemap_scalebar.png" width="200" height="27" />');
    $(".tooltip").tipsy({gravity : 's'});
    this.bindHotkeys();
    this.bindToolbar();
    this.bindArrows();
    this.bindSettings();
    this.bindColorPickers();
    this.bindAddButtons();
    this.bindClearButtons();
    this.bindAutocomplete();
    this.bindSave();
    this.bindDownload();
    this.bindSubmit();
    $('textarea.resizable:not(.textarea-processed)').TextAreaResizer();
    if($('#usermaps').length > 0) {
      $("#tabs").tabs('select',3);
      this.loadMapList();
    }
    if($('#userdata').length > 0) {
      $("#tabs").tabs('select',4);
      this.loadUserList();
    }
    $("input").keypress(function(event) { if (event.which === 13) { return false; } });
    this.activateJanrain();
  };

  Mappr.init();

});
