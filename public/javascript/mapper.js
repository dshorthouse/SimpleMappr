/*global $, jQuery, window, document, self, XMLHttpRequest, setTimeout, alert */

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
       ul_holder = '<input type="text" id="jcrop-coord-ul" class="jcrop-coord"></input>',
       lr_holder = '<input type="text" id="jcrop-coord-lr" class="jcrop-coord"></input>',
       ul_point  = { 'x' : x, 'y' : y },
       lr_point  = { 'x' : x2, 'y' : y2 },
       ul_coord  = {},
       lr_coord  = {};

    switch(Mappr.vars.jCropType) {
      case 'crop':
        $('.jcrop-holder div:first').css('backgroundColor', 'white');
        $('#bbox_rubberband').val(x+','+y+','+x2+','+y2);

        if($('#projection option:selected').text() === 'Geographic') {
          $('.jcrop-coord').css("width", "100px");
        } else {
          $('.jcrop-coord').css("width", "175px");
        }

        if($('#jcrop-coord-ul').length === 0 && $('#jcrop-coord-lr').length === 0) {
          $('.jcrop-tracker').eq(0).after(ul_holder).after(lr_holder);
        }

        ul_coord = Mappr.pix2geo(ul_point);
        lr_coord = Mappr.pix2geo(lr_point);
        $('#jcrop-coord-ul').val(ul_coord.x + ', ' + ul_coord.y);
        $('#jcrop-coord-lr').val(lr_coord.x + ', ' + lr_coord.y);

        $.cookie("jcrop_coords", "{ \"jcrop_coord_ul\" : \"" + $('#jcrop-coord-ul').val() + "\", \"jcrop_coord_lr\" : \"" + $('#jcrop-coord-lr').val() + "\" }" );

        $('.jcrop-coord').live("blur", function() {
          if(!Mappr.vars.cropUpdated) {
            Mappr.vars.cropUpdated = Mappr.updateCrop();
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

  Mappr.updateCrop = function () {
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

    $("ul.dropdown li").hover(function () {
      $(this).addClass("ui-state-hover");
      $('ul:first',this).css('visibility', 'visible');
    }, function () {
      $(this).removeClass("ui-state-hover");
      $('ul:first',this).css('visibility', 'hidden');
    });

    $("ul.dropdown li ul li:has(ul)").find("a:first").append(" &raquo; ");

    $('.toolsZoomIn').click(function () {
      self.mapZoomIn();
      return false;
    });

    $('.toolsZoomOut').click(function () {
      self.mapZoomOut();
      return false;
    });

    $('.toolsRotate').click(function () {
      var rotation = (!$('#rendered_rotation').val()) ? 0 : parseInt($('#rendered_rotation').val(), 10);

      self.resetJbbox();
      $('#rotation').val(rotation+parseInt($(this).attr("data-rotate"), 10));
      self.showMap();
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

  Mappr.clearSelf = function(el) {
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
  };

  Mappr.resetJbbox = function () {
    $('#bbox_rubberband').val('');
    $('#bbox_query').val('');
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
              continue;
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

      }

      if(counter >= self.vars.maxTextareaCount-3) {
        button.attr("disabled","disabled");
      }

    }

  }; /** end Mappr.addAccordionPanel **/

  Mappr.removeAccordionPanel = function (clone, data_type) {
    var self   = this,
        button = $(".addmore[data-type='" + data_type + "']");

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
    var self    = this,
        message = '<div id="usermaps-loading"><span class="mapper-loading-message ui-corner-all ui-widget-content">Loading your maps...</span></div>';

    $('#usermaps').html(message);

    $.ajax({
      type     : 'GET',
      url      : self.settings.baseUrl + "/usermaps/",
      dataType : 'html',
      success  : function(data) {
        $('#usermaps').html(data);

        $('.map-load').click(function () {
          self.loadMap(this);
          return false;
        });

        $('.map-delete').click(function () {
          self.deleteMapConfirmation(this);
          return false;
        });
      }
    });

  };

  Mappr.removeExtraElements = function () {
    var self         = this,
        i            = 0,
        numPoints    = $('.fieldset-points').size(),
        numRegions   = $('.fieldset-regions').size(),
        numFreehands = $('.fieldset-freehands').size();

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

    if(numFreehands > 3) {
      for(i = numFreehands-1; i >= 3; i -= 1) {
        $('#fieldSetsFreehands div.fieldset-freehands:eq('+i.toString()+')').remove();
      }
      self.vars.newFreehandCount = 0;
    }
  };

  Mappr.loadMap = function (obj) {
    var self   = this,
        id     = $(obj).attr("data-mid"),
        filter = $('#filter-mymaps').val();

    $("#tabs").tabs('select',0);

    self.showLoadingMessage('Building preview...');

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
    var pattern   = /[?*:;{}\\ "']+/g,
        map_title = "",
        i         = 0,
        keyMap    = [],
        key       = "",
        self      = this;

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

    self.resetJbbox();

    if(data.map.download_factor !== undefined && data.map.download_factor) {
      $('input[name="download_factor"]').val(data.map.download_factor);
      $('#download-factor-' + data.map.download_factor).attr('checked', true);
    } else {
      $('#download-factor-3').attr('checked', true);
    }

    if(data.map.download_filetype !== undefined && data.map.download_filetype) {
      $('input[name="download_filetype"]').val(data.map.download_filetype);
      $('#download-' + data.map.download_filetype).attr('checked', true);
    } else {
      $('#download-svg').attr('checked', true);
    }

    if(data.map.grid_space !== undefined && data.map.grid_space) {
      $('input[name="gridspace"]').attr('checked', false);
      $('#gridspace-' + data.map.grid_space).attr('checked', true);
    } else {
      $('#gridspace').attr('checked', true);
    }

    if(data.map.options !== undefined) {
      for(key in data.map.options) {
        if(data.map.options.hasOwnProperty(key)) { keyMap[keyMap.length] = key; }
      }
      for(i = 0 ; i < keyMap.length; i += 1) {
        if(keyMap[i] === 'border') {
          $('#border').attr('checked', true);
          $('input[name="options[border]"]').val(1);
        } else if (keyMap[i] === 'legend') {
          $('#legend').attr('checked', true);
          $('input[name="options[legend]"]').val(1);
        } else {
          $('input[name="options['+keyMap[i]+']"]').attr('checked', true);
        }
      }
    }

  }; //** end Mappr.loadSettings **/

  Mappr.loadCropSettings = function (data) {
    var self = this, rubberband = [];

    if(data.map.bbox_rubberband) {
      rubberband = data.map.bbox_rubberband.split(",");
      self.initJcrop([rubberband[2], rubberband[3], rubberband[0], rubberband[1]]);
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

    $('input[name="options[border]"]').val("");
    $('input[name="options[legend]"]').val("");
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
        message = '';

    $('.map-embed').attr("data-mid", mid).click(function () {
      message  = "<p><input type='text' size='65' value='&lt;img src=\"" + self.settings.baseUrl + "/?map=" + mid + "\" alt=\"\" /&gt;'></input></p>";
      message += "<p><strong>Additional parameters</strong>:<br><span class=\"indent\">width, height (<em>e.g.</em> ?map=" + mid + "&amp;width=200&amp;height=150)</span></p>";

      if($('body').find('#mapEmbed').length > 0) {
        $('#mapEmbed').html(message).dialog("open");
      } else {
        $('body').append('<div id="mapEmbed" class="ui-state-highlight" title="Embed">' + message + '</div>');

        $('#mapEmbed').dialog({
          width         : (525).toString(),
          autoOpen      : true,
          modal         : true,
          closeOnEscape : false,
          draggable     : false,
          resizable     : false,
          buttons       : {
            OK: function () {
              $(this).dialog("destroy").remove();
            }
          }
        });
      }

      self.analytics('/embed');

      return false;
    }).show();

  };

  Mappr.deleteMapConfirmation = function (obj) {
    var self    = this,
        id      = $(obj).attr("data-mid"),
        message = 'Are you sure you want to delete<p><em>' + $(obj).parent().parent().find(".title").html() + '</em>?</p>';

    $('body').append('<div id="mapper-message-delete" class="ui-state-highlight" title="Delete">' + message + '</div>');

    $('#mapper-message-delete').dialog({
      height        : (250).toString(),
      width         : (500).toString(),
      modal         : true,
      closeOnEscape : false,
      draggable     : false,
      resizable     : false,
      buttons       : [
        {
          "text"  : "Delete",
          "click" : function () {
            $.ajax({
              type    : 'DELETE',
              url     :  self.settings.baseUrl + "/usermaps/" + id,
              success : function() {
                self.loadMapList();
              }
            });
            $(this).dialog("destroy").remove();
          }
        },
        {
          "text"  : "Cancel",
          "class" : "ui-button-cancel",
          "click" : function () {
            $(this).dialog("destroy").remove();
          }
        }]
    });

  };

  Mappr.loadUserList = function () {
    var self    = this,
        message = '<div id="userdata-loading"><span class="mapper-loading-message ui-corner-all ui-widget-content">Loading user list...</span></div>';

    $('#userdata').html(message);

    $.ajax({
      type     : 'GET',
      url      : this.settings.baseUrl + "/users/",
      dataType : 'html',
      success  : function (data) {
        $('#userdata').html(data);

        $('.user-delete').click(function () {
          self.deleteUserConfirmation(this);
          return false;
        });

      }
    });

  };

  Mappr.deleteUserConfirmation = function (obj) {
    var self    = this,
        id      = $(obj).attr("data-uid"),
        message = 'Are you sure you want to delete <em>' + $(obj).parent().parent().children("td:first").html() + '</em>?<br>All their map data will also be deleted.';

    $('body').append('<div id="mapper-message-delete" class="ui-state-highlight" title="Delete">' + message + '</div>');

    $('#mapper-message-delete').dialog({
      height        : (250).toString(),
      width         : (500).toString(),
      modal         : true,
      closeOnEscape : false,
      draggable     : false,
      resizable     : false,
      buttons       : [
        {
          "text"  : "Delete",
          "click" : function () {
            $.ajax({
              type    : 'DELETE',
              url     :  self.settings.baseUrl + "/users/" + id,
              success : function() {
                self.loadUserList();
              }
            });
            $(this).dialog("destroy").remove();
          }
        },
        {
          "text"  : "Cancel",
          "class" : "ui-button-cancel",
          "click" : function () {
            $(this).dialog("destroy").remove();
          }
        }]
    });
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
          "text"  : "Save",
          "click" : function () {
            if($.trim($('.m-mapSaveTitle').val()) === '') { missingTitle = true; }
            if(missingTitle) {
              $('.m-mapSaveTitle').css({'background-color':'#FFB6C1'}).keyup(function () {
                $(this).css({'background-color':'transparent'});
              });
            } else {
              $('input[name="save[title]"]').val($('.m-mapSaveTitle').val());
              $('input[name="download_factor"]').val($('input[name="download-factor"]:checked').val());
              $('input[name="download_filetype"]').val($('input[name="download-filetype"]:checked').val());
              $('input[name="grid_space"]').val($('input[name="gridspace"]:checked').val());
              if($('#border').is(':checked')) {
                $('input[name="options[border]"]').val(1);
              } else {
                $('input[name="options[border]"]').val("");
              }
              if($('#legend').is(':checked')) {
                $('input[name="options[legend]"]').val(1);
              } else {
                $('input[name="options[legend]"]').val("");
              }

              Mappr.showLoadingMessage('Saving...');

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
                  Mappr.analytics('/save');
                }
              });

              $(this).dialog("destroy");
            }
          }
      },
      {
        "text"  : "Cancel",
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
      width         : (350).toString(),
      modal         : true,
      closeOnEscape : false,
      draggable     : false,
      resizable     : false,
      buttons       : [
        {
          "text"  : "Download",
          "click" : function() {
            Mappr.generateDownload();
          }
        },
        {
          "text"  : "Cancel",
          "class" : "ui-button-cancel",
          "click" : function () {
            $(this).dialog("destroy");
          }
        }]
    });
  };

  Mappr.bindSubmit = function () {
    var self = this, missingTitle = false;

    $(".submitForm").click(function () {

      $('.m-mapCoord').each(function () {
        if($(this).val() && $(this).parents().find('.m-mapTitle').val() === '') {
          missingTitle = true;
        }
      });

      if(missingTitle) {
        var message = 'You are missing a legend for at least one of your Point Data or Regions layers';
        self.showMessage(message);
      }
      else {
        self.showMap();
        $("#tabs").tabs('select',0);
      }

      return false;
    });
  };

  Mappr.showMessage = function (message) {

    if($('#mapper-message').length === 0) {
      $('body').append('<div id="mapper-message" class="ui-state-error" title="Warning"></div>');
    }
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
          $(this).dialog("destroy").remove();
        }
      }
    });
  };

  Mappr.drawLegend = function () {
    var legend_url = $('#legend_url').val();

    if(legend_url) {
      $('#mapLegend').html("<img src=\"" + legend_url + "\" />");
    } else {
      $('#mapLegend').html('<p><em>legend will appear here</em></p>');
    }
  };

  Mappr.drawScalebar = function () {
    var scalebar_url = $('#scalebar_url').val();

    if(scalebar_url) {
      $('#mapScale').html('<img src="' + scalebar_url + '" />');
    } else {
      $('#mapScale').html('');
    }
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

    if($('.mapper-loading-message').length === 0) { $('#mapOutput').append(message); }
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

    self.showLoadingMessage('Building preview...');

    $('#mapScale').html('');

    $.ajax({
      type : 'POST',
      url : self.settings.baseUrl + "/application/",
      data : formData,
      dataType : 'json',
      success : function (data) {
        self.parseMapResponse(data, load_data);

        self.drawLegend();
        self.drawScalebar();
        self.showBadPoints();

        toolsTabs.tabs('select', tabIndex);

        $('#mapTools').bind('tabsselect', function (event,ui) {
          event = null;
          $('#selectedtab').val(ui.index);
        });

        self.resetJbbox();
        $('#bbox_map').val($('#rendered_bbox').val());             // set extent from previous rendering
        $('#projection_map').val($('#rendered_projection').val()); // set projection from the previous rendering
        $('#rotation').val($('#rendered_rotation').val());         // reset rotation value
        $('#pan').val('');                                         // reset pan value

        self.addBadRecordsViewer();

        $('.toolsBadRecords').click(function () {
          $('#badRecordsViewer').dialog("open");
          return false;
        });

      }
    });

  }; /** end Mappr.showMap **/

  Mappr.parseMapResponse = function (data, load_data) {
    var self = this;

    $('#mapOutput input').each(function () {
      $(this).val('');
    });
    $('#rendered_bbox').val(data.rendered_bbox);
    $('#rendered_rotation').val(data.rendered_rotation);
    $('#rendered_projection').val(data.rendered_projection);
    $('#legend_url').val(data.legend_url);
    $('#scalebar_url').val(data.scalebar_url);
    $('#bad_points').val(data.bad_points);
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
  };

  Mappr.generateDownload = function () {
    var self        = this,
        pattern     = /[?*:;{}\\ "'\/@#!%\^()<>.]+/g,
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

    if($('#border').is(':checked')) {
      $('input[name="options[border]"]').val(1);
    } else {
      $('input[name="options[border]"]').val("");
    }

    if($('#legend').is(':checked')) {
      $('input[name="options[legend]"]').val(1);
    } else {
      $('input[name="options[legend]"]').val("");
    }

    $('#download_token').val(token);

    $('.ui-dialog-buttonpane').hide();
    $('.download-dialog').hide();
    $('.download-message').show();

    switch(filetype) {
      case 'kml':
        formData = $("form").serialize();
        $.download(self.settings.baseUrl + "/application/kml/", formData, 'post');
      break;

      default:
        $('#download').val(1);
        $('#output').val(filetype);
        if(self.vars.jcropAPI) { $('#crop').val(1); } else { self.resetJbbox(); }
        formData = $("form").serialize();
        $.download(self.settings.baseUrl + "/application/", formData, 'post');
        $('#download').val('');
        $('#output').val('pnga');
    }

    self.vars.fileDownloadTimer = window.setInterval(function () {
      cookieValue = $.cookie('fileDownloadToken');
      if (cookieValue === token) {
        self.finishDownload();
      }
    }, 1000);

    self.analytics('/downloads/' + filetype);

  }; /** end Mappr.generateDownload **/

  Mappr.analytics = function (url) {
    if(typeof _gaq === 'function') {
       _gaq.push(['_trackPageview', url]);
    }
  };

  Mappr.finishDownload = function () {
    $('.download-message').hide();
    $('.download-dialog').show();
    $('.ui-dialog-buttonpane').show();
    window.clearInterval(this.vars.fileDownloadTimer);
    $.cookie('fileDownloadToken', null); //clears this cookie value
  };

  Mappr.showExamples = function() {
    var message = '<img src="public/images/help_data.png" alt="Example Data Entry" />';

    if($('body').find('#mapper-message-help').length > 0) {
      $('#mapper-message-help').html(message).dialog("open");
    } else {
      $('body').append('<div id="mapper-message-help" class="ui-state-highlight" title="Example Coordinates">' + message + '</div>');

      $('#mapper-message-help').dialog({
        height        : (350).toString(),
        width         : (525).toString(),
        autoOpen      : true,
        modal         : true,
        closeOnEscape : false,
        draggable     : false,
        resizable     : false,
        buttons       : {
          OK: function () {
            $(this).dialog("destroy").remove();
          }
        }
      });
    }
    return false;
  };

  Mappr.init = function () {
    $('#initial-message').hide();
    $("#tabs").tabs().show();
    $('#mapTools').tabs();
    $('.fieldSets').accordion({
      header : 'h3',
      collapsible : true,
      autoHeight : false
    });
    $('#mapOutput').append('<img id="mapOutputImage" src="public/images/basemap.png" alt="" width="800" height="400" />').find("span.mapper-loading-message").remove();
    $(".tooltip").tipsy({gravity: 's'});
    this.bindHotkeys();
    this.bindToolbar();
    this.bindArrows();
    this.bindSettings();
    this.bindColorPickers();
    this.bindAddButtons();
    this.bindClearButtons();
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
  };

  Mappr.init();

});

/******* jQUERY EXTENSIONS *******/

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
