/*
 * jQuery SimpleMappr
 */
/*global jQuery, window, document, self, XMLHttpRequest, alert, encodeURIComponent, _gaq */
var SimpleMappr = (function($, window, document) {

  "use strict";

  var _private = {

    settings: {
      baseUrl: '',
      active: false,
      maxTextareaCount: 10,
      undoSize: 10
    },

    vars: {
      newPointCount      : 0,
      newRegionCount     : 0,
      zoom               : true,
      fileDownloadTimer  : {},
      fillColor          : "",
      jCropType          : "zoom",
      cropUpdated        : false,
      origins            : { "esri:102009" : -96, 
                           "esri:102015" : -60, 
                           "esri:102014" : 10, 
                           "esri:102012" : 105, 
                           "esri:102024" : 25,
                           "epsg:3112" : 134
                          }
    },

    trackEvent: function(category, action) {
      if (window._gaq !== undefined) { _gaq.push(['_trackEvent', category, action]); }
    },

    getPageSize: function() {
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
    },

    getPageScroll: function() {
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
    },

    showCoords: function(c) {
      var self      = this,
          x         = parseFloat(c.x),
          y         = parseFloat(c.y),
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

      switch(this.vars.jCropType) {
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

          ul_coord = self.pix2geo(ul_point);
          lr_coord = self.pix2geo(lr_point);
          $('#jcrop-coord-ul').val(ul_coord.x + ', ' + ul_coord.y);
          $('#jcrop-coord-lr').val(lr_coord.x + ', ' + lr_coord.y);
          $('#jcrop-dimension-w').val(w);
          $('#jcrop-dimension-h').val(h);
          $('#jcrop-dimension-wrapper').css({'left' : w/2-$('#jcrop-dimension-wrapper').width()/2, 'top' : h/2-$('#jcrop-dimension-wrapper').height()/2});

          $.cookie("jcrop_coords", "{ \"jcrop_coord_ul\" : \"" + $('#jcrop-coord-ul').val() + "\", \"jcrop_coord_lr\" : \"" + $('#jcrop-coord-lr').val() + "\" }" );

          $('.jcrop-coord').blur(function() {
            if(!self.vars.cropUpdated) { self.vars.cropUpdated = self.updateCropCoordinates(); }
          })
          .keypress(function(e) {
            var key = e.keyCode || e.which;
            if(key === 13 || key === 9) {
              e.preventDefault();
              self.vars.cropUpdated = false;
              this.blur();
            }
          });

          $('.jcrop-dimension').blur(function() {
            if(!self.vars.cropUpdated) { self.vars.cropUpdated = self.updateCropDimensions(); }
          })
          .keypress(function(e) {
            var key = e.keyCode || e.which;
            if(key === 13 || key === 9) {
              e.preventDefault();
              self.vars.cropUpdated = false;
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
    },

    updateCropDimensions: function() {
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
    },

    updateCropCoordinates: function() {
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
    },

    pix2geo: function(point) {
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
    },

    geo2pix: function(coord) {
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
    },

    roundNumber: function(num, dec) {
      return Math.round(num*Math.pow(10,dec))/Math.pow(10,dec);
    },

    tabSelector: function(tab) {
      var state = {};
      $("#tabs").tabs('select',tab);
      state.tabs = tab;
      $.bbq.pushState(state);
    },

    RGBtoHex: function(R,G,B) {
      return this.toHex(R)+this.toHex(G)+this.toHex(B);
    },

    toHex: function(N) {
      if (N === null) { return "00"; }
      N = parseInt(N, 10);
      if (N === 0 || isNaN(N)) { return "00"; }
      N = Math.max(0,N);
      N = Math.min(N,255);
      N = Math.round(N);
      return "0123456789ABCDEF".charAt((N-N%16)/16) + "0123456789ABCDEF".charAt(N%16);
    },

    bindToolbar: function() {
      var self = this;

      $("#actionsBar ul li").hover(function() {
        $(this).addClass("ui-state-hover");
      }, function() {
        $(this).removeClass("ui-state-hover");
      });

      $('.toolsZoomIn').click(function(e) {
        e.preventDefault();
        self.mapZoom("in");
        self.trackEvent('toolbar', 'zoomin');
      });

      $('.toolsZoomOut').click(function(e) {
        e.preventDefault();
        self.mapZoom("out");
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
    },

    mapCrop: function() {
      var coords   = {},
          ul_arr   = [],
          ul_point = {},
          lr_arr   = [],
          lr_point = {};

      if($.cookie("jcrop_coords")) {
        coords = $.parseJSON($.cookie("jcrop_coords"));
        ul_arr = coords.jcrop_coord_ul.split(",");
        lr_arr = coords.jcrop_coord_lr.split(",");
        ul_point = this.geo2pix({ 'x' : $.trim(ul_arr[0]), 'y' : $.trim(ul_arr[1]) });
        lr_point = this.geo2pix({ 'x' : $.trim(lr_arr[0]), 'y' : $.trim(lr_arr[1]) });
        this.loadCropSettings({ 'map' : { 'bbox_rubberband' : lr_point.x + "," + lr_point.y + "," + ul_point.x + "," + ul_point.y } });
      } else {
        this.initJcrop();
      }

      self.vars.zoom = false;
    },

    resetAndBuild: function() {
      this.resetJbbox();
      this.destroyRedo();
      this.showMap();
    },

    mapRefresh: function() {
      this.resetAndBuild();
      this.tabSelector(0);
    },

    mapRebuild: function() {
      $.each(['bbox_map', 'projection_map', 'bbox_rubberband', 'rotation', 'projection', 'pan'], function() {
        $('#' + this).val('');
      });
      this.destroyRedo();
      this.showMap();
    },

    bindArrows: function() {
      var self = this;

      $('.arrows').click(function(e) {
        e.preventDefault();
        $('#pan').val($(this).attr("data-pan"));
        self.resetJbbox();
        self.showMap();
        self.trackEvent('arrows', $(this).attr("data-pan"));
      });
    },

    mapPan: function(dir) {
      $('#pan').val(dir);
      this.resetAndBuild();
    },

    mapList: function() {
      this.tabSelector(3);
    },

    mapZoom: function(dir) {
      if(dir === "in") {
        this.initJzoom();
        this.vars.zoom = true;
      } else {
        this.resetJbbox();
        $('#zoom_out').val(1);
        this.destroyRedo();
        this.showMap();
        $('#zoom_out').val('');
      }
    },

    storageType: function(type) {
      var index = 0;

      index = $.grep($.jStorage.index(), function(value, i) {
        i = null;
        return (value.substring(0, type.length) === type);
      });

      return index;
    },

    toggleUndo: function(activate) {
      var self  = this,
          index = this.storageType("do");

      $('.toolsUndo').addClass('toolsUndoDisabled').removeClass('toolsUndo').unbind("click");

      if(activate && index.length > 1) {
        if(index.length > self.settings.undoSize) { $.jStorage.deleteKey(index.shift()); }
        $('.toolsUndoDisabled').addClass('toolsUndo').removeClass('toolsUndoDisabled').bind("click", function(e) {
          e.preventDefault();
          self.mapUndo();
          self.trackEvent('edit', 'undo');
        });
      }
    },

    toggleRedo: function(activate) {
      var self = this;

      $('.toolsRedo').addClass('toolsRedoDisabled').removeClass('toolsRedo').unbind("click");

      if(activate) {
        $('.toolsRedoDisabled').addClass('toolsRedo').removeClass('toolsRedoDisabled').bind("click", function(e) {
          e.preventDefault();
          self.mapRedo();
          self.trackEvent('edit', 'redo');
        });
      }
    },

    destroyRedo: function() {
      var index = this.storageType("undo");

      if(index.length > 0) {
        $('.toolsRedo').addClass('toolsRedoDisabled').removeClass('toolsRedo').unbind("click");
        $.jStorage.deleteKey(index.pop());
      }
    },

    mapUndo: function() {
      var index          = this.storageType("do"),
          curr_key       = "",
          curr_data      = {},
          prev_key       = "",
          prev_data      = {},
          prev_data_prep = {};

      if(index.length === 1) { return; }

      this.destroyRedo();

      curr_key       = index[index.length-1];
      curr_data      = $.jStorage.get(curr_key);
      prev_key       = index[index.length-2];
      prev_data      = $.jStorage.get(prev_key);
      prev_data_prep = this.prepareInputs(prev_data);

      this.loadInputs(prev_data_prep);

      $.jStorage.deleteKey(curr_key);
      $.jStorage.set("un" + curr_key, curr_data);

      if(prev_data.width !== curr_data.width) {
        this.mapToggleSettings();
      } else {
        this.showSpinner();
        this.postData(decodeURIComponent($.param(prev_data)), null);
      }

      this.toggleRedo(true);
      if(index.length === 2) { this.toggleUndo(); }
    },

    mapRedo: function() {
      var undo_index     = this.storageType("undo"),
          undo_key       = "",
          undo_data      = {},
          undo_data_prep = {},
          do_index       = this.storageType("do"),
          do_key         = "",
          do_data        = {},
          token          = new Date().getTime();

      if(undo_index.length === 0) { return; }

      this.toggleRedo();
      undo_key       = undo_index.pop();
      undo_data      = $.jStorage.get(undo_key);
      do_key         = do_index[do_index.length-1];
      do_data        = $.jStorage.get(do_key);
      undo_data_prep = this.prepareInputs(undo_data);
      this.loadInputs(undo_data_prep);

      $.jStorage.deleteKey(undo_key);
      $.jStorage.set("do-" + token.toString(), undo_data);

      if(undo_data.width !== do_data.width) {
        this.mapToggleSettings();
      } else {
        this.showSpinner();
        this.postData(decodeURIComponent($.param(undo_data)), null);
      }

      this.toggleUndo(true);
    },

    bindHotkeys: function() {
      var self = this, keys = {}, arrows = {};

      keys = {
        'ctrl+s' : self.bindCallback(self, self.mapSave),
        'ctrl+d' : self.bindCallback(self, self.mapDownload),
        'ctrl+l' : self.bindCallback(self, self.mapList),
        'ctrl+r' : self.bindCallback(self, self.mapRefresh),
        'ctrl+n' : self.bindCallback(self, self.mapRebuild),
        'ctrl+x' : self.bindCallback(self, self.mapCrop),
        'ctrl+e' : self.bindCallback(self, self.mapToggleSettings),
        'ctrl++' : self.bindCallback(self, self.mapZoom, "in"),
        'ctrl+-' : self.bindCallback(self, self.mapZoom, "out"),
        'esc'    : self.bindCallback(self, self.destroyJcrop),
        'ctrl+z' : self.bindCallback(self, self.mapUndo),
        'ctrl+y' : self.bindCallback(self, self.mapRedo)
      };

      arrows = {
        'up'    : self.bindCallback(self, self.mapPan, "up"),
        'down'  : self.bindCallback(self, self.mapPan, "down"),
        'left'  : self.bindCallback(self, self.mapPan, "left"),
        'right' : self.bindCallback(self, self.mapPan, "right")
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
    },

    hardResetShowMap: function() {
      this.resetJbbox();
      this.destroyRedo();
      this.showMap();
    },

    bindSettings: function() {
      var self = this;

      $('.layeropt').click(function() {
        self.hardResetShowMap();
      });

      $('.gridopt').click(function() {
        if(!$('#graticules').prop('checked')) { $('#graticules').prop('checked', true); }
        self.hardResetShowMap();
      });

      $('#gridlabel').click(function() {
        if(!$('#graticules').prop('checked')) { $('#graticules').prop('checked', true); }
        if($(this).prop('checked')) { $(this).val('false'); }
        self.hardResetShowMap();
      });

      $('#projection').change(function() {
        var origin_sel = $('#origin-selector');

        if($(this).val() !== "") {
          $('#origin').val(self.vars.origins[$(this).val()]);
          if(self.vars.origins.hasOwnProperty($("#projection").val())) { origin_sel.show(); } else { origin_sel.hide(); }
          $.cookie("jcrop_coords", null);
          self.hardResetShowMap();
        }
      });

      $('#origin').blur(function() {
        self.hardResetShowMap();
      }).keydown(function(e) {
        var key = e.keyCode || e.which;
        if(key === 9 || key === 13 ) { this.blur(); }
      });

      self.toggleFileFactor();

      $('.download-factor').change(function() {
        self.toggleFileFactor($(this).val());
      });

      $('.download-filetype').change(function() {
        self.toggleFileType(this);
      });
    },

    bindSlider: function() {
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
    },

    toggleFileFactor: function(factor) {
      var scale      = "",
          rubberband = $('#bbox_rubberband').val().split(",");

      if(!factor) { factor = $('input[name="download-factor"]:checked').val(); }

      if(this.vars.jCropType === 'crop') {
        scale = factor*(rubberband[2]-rubberband[0]) + " X " + factor*(rubberband[3]-rubberband[1]);
      } else {
        scale = factor*($('#mapOutputImage').width()) + " X " + factor*($('#mapOutputImage').height());
      }
      $('span', '#scale-measure').text(scale).parent().show();
    },

    toggleFileType: function(obj) {
      if($(obj).attr("id") === 'download-svg' || $(obj).attr("id") === 'download-pptx' || $(obj).attr("id") === 'download-docx') {
        $.each(["legend", "scalebar"], function() {
          $('#'+this).prop("checked", true).prop("disabled", true);
        });
        $.each(["border", "scalelinethickness"], function() {
          $('#'+this).prop("disabled", false);
        });
      } else if($(obj).attr("id") === 'download-kml') {
        $.each(["legend", "scalebar", "border", "scalelinethickness"], function() {
          $('#'+this).prop("checked", true).prop("disabled", true);
        });
      } else {
        $.each(["border", "legend", "scalebar", "scalelinethickness"], function() {
          $('#'+this).prop("disabled", false);
        });
      }
    },

    bindColorPickers: function() {
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
    },

    bindClearButtons: function() {
      var self = this;

      $('.clearLayers, .clearRegions').click(function(e) {
        var fieldsets = $(this).parent().prev().prev().children();

        e.preventDefault();
        $.each(['.m-mapTitle', 'textarea'], function() { $(fieldsets).find(this).val(''); });
        if($(fieldsets).find('.m-mapShape').length > 0) { $(fieldsets).find('.m-mapShape')[0].selectedIndex = 4; }
        if($(fieldsets).find('.m-mapSize').length > 0) { $(fieldsets).find('.m-mapSize')[0].selectedIndex = 3; }
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
    },

    bindAutocomplete: function() {
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
    },

    split: function(val,delimiter) {
      switch(delimiter) {
        case '[':
         return val.split(/\]/);

        default:
          return val.split(/,\s*/);
      }
    },

    extractLast: function(term) {
      return this.split(term).pop();
    },

    clearSelf: function(el) {
      var box = $(el).parent(),
          color_picker = $(box).find('.colorPicker');

      $.each(['.m-mapTitle', 'textarea'], function() { $(box).find(this).val(''); });
      if($(box).find('.m-mapShape').length > 0) { $(box).find('.m-mapShape')[0].selectedIndex = 4; }
      if($(box).find('.m-mapSize').length > 0) { $(box).find('.m-mapSize')[0].selectedIndex = 3; }
      if($(box).parent().hasClass("fieldset-points")) {
        color_picker.val('0 0 0');
      } else {
        color_picker.val('150 150 150');
      }
    },

    destroyJcrop: function() {
      var vars = this.vars;

      if(vars.jzoomAPI !== undefined) { vars.jzoomAPI.destroy(); }
      if(vars.jcropAPI !== undefined) { vars.jcropAPI.destroy(); }
      if(vars.jqueryAPI !== undefined) { vars.jqueryAPI.destroy(); }

      $('#mapOutputImage').show();
      $('.jcrop-holder').remove();
      $('#mapCropMessage').hide();

      this.toggleFileFactor();
    },

    resetJbbox: function() {
      this.vars.jCropType = "zoom";
      $.each(['rubberband', 'query'], function() { $('#bbox_' + this).val(''); });
      this.toggleFileFactor();
    },

    bindCallback: function(scope, fn) {
      var args = Array.prototype.slice.call(arguments, 2);
      return function() {
        fn.apply(scope, $.extend(arguments, args));
      };
    },

    initJcrop: function(select) {
      var self = this, vars = this.vars;

      self.destroyJcrop();
      self.resetJbbox();

      vars.jCropType = "crop";

      vars.jcropAPI = $.Jcrop('#mapOutputImage', {
        bgColor   : ($('#mapOutputImage').attr("src") === "public/images/basemap.png") ? 'grey' : 'black',
        bgOpacity : 0.5,
        onChange  : self.bindCallback(self, self.showCoords),
        onSelect  : self.bindCallback(self, self.showCoords),
        setSelect : select
      });

      $('#mapCropMessage').show();
    },

    initJzoom: function() {
      var self = this, vars = this.vars;

      self.destroyJcrop();
      self.resetJbbox();

      vars.jCropType = "zoom";

      vars.jzoomAPI = $.Jcrop('#mapOutputImage', {
        addClass      : "customJzoom",
        bgOpacity     : 1,
        bgColor       : "white",
        onChange      : self.bindCallback(self, self.showCoords),
        onSelect      : self.bindCallback(self, self.showCoords)
      });

      $('.jcrop-tracker').mousedown(function() { self.activateJcrop('zoom'); });
    },

    initJquery: function() {
      var self = this, vars = this.vars;

      self.destroyJcrop();
      self.resetJbbox();

      vars.jCropType = "query";

      vars.jqueryAPI = $.Jcrop('#mapOutputImage', {
        addClass      : "customJzoom",
        bgOpacity     : 1,
        bgColor       :'white',
        onChange      : self.bindCallback(self, self.showCoords),
        onSelect      : self.bindCallback(self, self.showCoords)
      });

      $('.jcrop-tracker').mousedown(function() { self.activateJcrop('query'); });
    },

    activateJcrop: function(type) {
      switch(type) {
        case 'zoom':
          $(document).bind("mouseup", this, this.aZoom);
        break;

        case 'query':
          $(document).bind("mouseup", this, this.aQuery);
        break;
      }
    },

    aZoom: function(event) {
      var self = event.data;
      self.destroyRedo();
      self.showMap();
      $(document).unbind("mouseup", self.aZoom);
    },

    dblclickZoom: function(obj, e) {
      var x = 0, y = 0, pos = {};

      pos = $(obj).offset();
      x   = (e.pageX - pos.left);
      y   = (e.pageY - pos.top);

      $('#bbox_rubberband').val(x+','+y+','+x+','+y);
      this.destroyRedo();
      this.showMap();
    },

    aQuery: function(e) {
      var self      = e.data,
          fillColor = self.vars.fillColor.r + " " + self.vars.fillColor.g + " " + self.vars.fillColor.b,
          formData  = {
            bbox           : $('#rendered_bbox').val(),
            bbox_query     : $('#bbox_query').val(),
            projection     : $('#projection').val(),
            projection_map : $('#projection_map').val(),
            origin         : $('#origin').val(),
            qlayer         : ($('#stateprovince').prop('checked')) ? 'stateprovinces_polygon' : 'base',
            width          : $('input[name="width"]').val(),
            height         : $('input[name="height"]').val()
          };

      $(document).unbind("mouseup", self.aQuery);

      self.destroyJcrop();
      self.destroyRedo();
      self.showSpinner();

      $.ajax({
        type    : 'POST',
        url     : self.settings.baseUrl + '/query/',
        data    : formData,
        timeout : 30000,
        success : function(data) {
          if(data.length > 0) {
            var regions = data.map(function(e) { return e; }).join(", "),
                num_fieldsets = $('.fieldset-regions').length;

            $.each($('.fieldset-regions'), function(i) {
              if(i === (num_fieldsets-1) && !$('button[data-type="regions"]').prop('disabled')) {
                self.addAccordionPanel('regions');
                num_fieldsets += 1;
              }
              if($('input[name="regions['+i+'][title]"]').val() === "" || $('textarea[name="regions['+i+'][data]"]').val() === "") {
                $('input[name="regions['+i+'][title]"]').val("Selected Region " + (i+1).toString());
                $('input[name="regions['+i+'][color]"]').val(fillColor);
                $('textarea[name="regions['+i+'][data]"]').val(regions);
                if(i > 0) { $('#fieldSetsRegions').accordion("activate", i); }
                return false;
              }
            });

            self.showMap();
          } else {
            self.hideSpinner();
          }
        },
        error   : function(xhr, ajaxOptions, thrownError) {
          xhr = thrownError = null;
          if(ajaxOptions === 'timeout') { self.hideSpinner(); }
        }
      });
    },

    textareaCounter: function(type, action) {
      var vars = this.vars;

      switch(action) {
        case 'get':
          switch(type) {
            case 'coords':
              return vars.newPointCount;
            case 'regions':
              return vars.newRegionCount;
          }
          break;

        case 'increase':
          switch(type) {
            case 'coords':
              vars.newPointCount += 1;
              return vars.newPointCount;
            case 'regions':
              vars.newRegionCount += 1;
              return vars.newRegionCount;
          }
          break;

        case 'decrease':
          switch(type) {
            case 'coords':
              vars.newPointCount -= 1;
              return vars.newPointCount;
            case 'regions':
              vars.newRegionCount -= 1;
              return vars.newRegionCount;
          }
          break;
      }
    },

    addAccordionPanel: function(data_type) {
      var self     = this,
          counter  = self.textareaCounter(data_type, 'get'),
          button   = $(".addmore[data-type='" + data_type + "']"),
          clone    = {},
          color    = (data_type === 'coords') ? "0 0 0" : "150 150 150",
          num      = 0,
          children = [];

      if(button.attr("data-type") === data_type) {

        if(counter < self.settings.maxTextareaCount) {
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

        if(counter >= self.settings.maxTextareaCount-3) {
          button.prop("disabled", true);
        }

      }
    },

    removeAccordionPanel: function(clone, data_type) {
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
      button.prop("disabled", false);
    },

    addGrippies: function(obj) {
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
    },

    bindAddButtons: function() {
      var self = this;

      $('.addmore').click(function(e) {
        var data_type = $(this).attr("data-type"), fieldsets = 0;
        e.preventDefault();
        self.addAccordionPanel(data_type);
        fieldsets = $(this).parent().prev().children().length;
        $(this).parent().prev().accordion("activate", fieldsets-1);
        return false;
      });
    },

    loadMapList: function(object) {
      var self  = this,
          obj = object || {},
          data = {};

      $('#usermaps').html('');

      self.showSpinner();

      data = {
        locale : self.getParameterByName("locale"),
        search : (obj.search) ? encodeURIComponent(obj.search.toLowerCase()) : null,
        uid    : obj.uid || null
      };

      if(obj.sort) {
        data.sort = obj.sort.item;
        data.dir = obj.sort.dir;
      }

      if(!data.locale) { delete data.locale; }
      if(!data.search) { delete data.search; }
      if(!data.uid) { delete data.uid; }

      $.ajax({
        type     : 'GET',
        url      : self.settings.baseUrl + "/usermap/",
        data     : data,
        dataType : 'html',
        success  : function(response) {
          if(response.indexOf("session timeout") !== -1) {
            window.location.reload();
          } else {
            $('#usermaps').html(response);
            self.hideSpinner();
            $(".toolsRefresh", ".grid-usermaps").click(function(e) { e.preventDefault(); self.loadMapList(); });
            $('#filter-mymaps')
              .val(obj.search)
              .keypress(function(e) {
                if (e.which === 13) {
                  e.preventDefault();
                  data.search = $(this).val();
                  self.loadMapList(data);
                  self.trackEvent('maplist', 'filter');
                }
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
    },

    removeExtraElements: function() {
      var self = this;

      $.each($('.fieldset-points'), function(i) {
        if(i > 2) {
          $('#fieldSetsPoints div.fieldset-points:eq('+i.toString()+')').remove();
          self.vars.newPointCount = 0;
        }
      });

      $.each($('.fieldset-regions'), function(i) {
        if(i > 2) {
          $('#fieldSetsRegions div.fieldset-regions:eq('+i.toString()+')').remove();
          self.vars.newRegionCount = 0;
        }
      });
    },

    prepareInputs: function(data) {
      var inputs = {}, item = [];

      inputs = {
        "status" : "ok",
        "mid"    : $('.map-embed').attr("data-id"),
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
    },

    loadInputs: function(data) {
      var filter = $('#filter-mymaps').val();

      this.removeExtraElements();
      $('#form-mapper').clearForm();
      $.each(['width', 'height'], function() { $('input[name="'+this+'"]').val($('input[name="'+this+'"]').val()); });
      $('.addmore').prop("disabled", false);
      $('#filter-mymaps').val(filter);
      $('#origin-selector').hide();
      this.loadCoordinates(data);
      this.loadRegions(data);
      this.loadLayers(data);
      this.loadSettings(data);
    },

    loadMap: function(obj) {
      var self     = this,
          id       = $(obj).attr("data-id");

      this.tabSelector(0);
      this.showSpinner();

      $.ajax({
        type     : 'GET',
        url      : self.settings.baseUrl + "/usermap/" + id,
        dataType : 'json',
        timeout  : 30000,
        success  : function(data) {
          self.hideSpinner();
          if(data.status === 'ok') {
            self.loadInputs(data);
            self.showMap(data);
            self.bindStorage();
            self.activateEmbed(id);
          } else {
            self.showErrorMessage($('#mapper-loading-error-message').text());
          }
          self.toggleUndo();
          self.toggleRedo();
        },
        error   : function(xhr, ajaxOptions, thrownError) {
          xhr = thrownError = null;
          if(ajaxOptions === 'timeout') {
            self.showErrorMessage($('#mapper-loading-error-message').text());
          }
        }
      });
    },

    loadSettings: function(data) {
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
      if(self.vars.origins.hasOwnProperty($("#projection").val())) { $('#origin-selector').show(); }
      $.each(['bbox_map', 'projection_map', 'rotation', 'origin'], function() { $('input[name="'+this+'"]').val(data.map[this]); });
      if(!data.map.origin) { $('#origin').val(self.vars.origins[$("#projection").val()]); }

      $('input[name="border_thickness"]').val(1.25);
      $('#border-slider').slider({value:1.25});
      if(data.map.border_thickness !== undefined && data.map.border_thickness) {
        $('input[name="border_thickness"]').val(data.map.border_thickness);
        $('#border-slider').slider({value:data.map.border_thickness});
      }

      self.setRotation(data.map.rotation);
      self.resetJbbox();

      $.each(["border", "legend", "scalebar", "scalelinethickness"], function() {
        $('#'+this).prop('checked', false);
        $('input[name="options['+this+']"]').val("");
      });

      if(data.map.options !== undefined) {
        $.each(["border", "legend", "scalebar", "scalelinethickness"], function() {
          if(data.map.options[this] && data.map.options[this] !== undefined) {
            $('#'+this).prop('checked', true);
            $('input[name="options['+this+']"]').val(1);
          }
        });
      }

      if(data.map.download_factor !== undefined && data.map.download_factor) {
        $('input[name="download_factor"]').val(data.map.download_factor);
        $('#download-factor-' + data.map.download_factor).prop('checked', true);
      } else {
        $('#download-factor-3').prop('checked', true);
      }

      if(data.map.download_filetype !== undefined && data.map.download_filetype) {
        $('input[name="download_filetype"]').val(data.map.download_filetype);
        download_filetype = $('#download-' + data.map.download_filetype).prop('checked', true);
        self.toggleFileType(download_filetype);
      } else {
        $('#download-svg').prop('checked', true);
      }

      if(data.map.grid_space !== undefined && data.map.grid_space) {
        $('input[name="gridspace"]').prop('checked', true);
        $('#gridspace-' + data.map.grid_space).prop('checked', true);
      } else {
        $('#gridspace').prop('checked', true);
      }

      if(data.map.gridlabel !== undefined && data.map.gridlabel) {
        $('input[name="gridlabel"]').prop('checked', true).val('false');
      } else {
        $('#gridlabel').prop('checked', true);
      }
    },

    loadCropSettings: function(data) {
      var rubberband = [];

      if(data.map.bbox_rubberband) {
        rubberband = data.map.bbox_rubberband.split(",");
        this.initJcrop([rubberband[2], rubberband[3], rubberband[0], rubberband[1]]);
        this.toggleFileFactor(data.map.download_factor);
      } else {
        this.destroyJcrop();
      }
    },

    loadShapeSize: function(i, coords) {
      $.each(['shape', 'size'], function() {
        if(coords[i][this].toString() === "") {
          $('select[name="coords['+i.toString()+']['+this+']"]')[0].selectedIndex = 3;
        } else {
          $('select[name="coords['+i.toString()+']['+this+']"]').val(coords[i][this]);
        }
      });
    },

    loadCoordinates: function(data) {
      var self        = this,
          coords      = data.map.coords || [],
          coord_title = "",
          coord_data  = "",
          coord_color = "",
          pattern     = /[?*{}\\]+/g;

      $.each(coords, function(i) {
        if(i > 2) { self.addAccordionPanel('coords'); }

        coord_title = coords[i].title || "";
        coord_data  = coords[i].data.replace(pattern, "")  || "";
        coord_color = coords[i].color || "0 0 0";

        $('input[name="coords['+i.toString()+'][title]"]').val(coord_title);
        $('textarea[name="coords['+i.toString()+'][data]"]').val(coord_data);

        self.loadShapeSize(i, coords);

        $('input[name="coords['+i.toString()+'][color]"]').val(coord_color);
      });
    },

    loadRegions: function(data) {
      var self         = this,
          regions      = data.map.regions || [],
          region_title = "",
          region_data  = "",
          region_color = "";

      $.each(regions, function(i) {
        if(i > 2) { self.addAccordionPanel('regions'); }

        region_title = regions[i].title || "";
        region_data  = regions[i].data  || "";
        region_color = regions[i].color || "150 150 150";

        $('input[name="regions['+i.toString()+'][title]"]').val(region_title);
        $('textarea[name="regions['+i.toString()+'][data]"]').val(region_data);
        $('input[name="regions['+i.toString()+'][color]"]').val(region_color);
      });
    },

    loadLayers: function(data) {
      if(data.map.layers) {
        $.each(data.map.layers, function(k,v) {
          $('input[name="layers['+k+']"]').prop('checked', true);
        });
      }
    },

    activateEmbed: function(mid) {
      var self    = this,
          types   = ['img','kml','svg','json'];

      $('.map-embed').attr("data-id", mid).css('display', 'block').click(function(e) {
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
                        width         : '525',
                        dialogClass   : 'ui-dialog-title-mapEmbed',
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
    },

    deleteMapConfirmation: function(obj) {
      var self    = this,
          id      = $(obj).attr("data-id"),
          message = '<em>' + $(obj).parent().parent().find(".title").text() + '</em>';

      $('#mapper-message-delete').find('span').html(message).end().dialog({
        height        : '250',
        width         : '500',
        dialogClass   : 'ui-dialog-title-mapper-message-delete',
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
                url     :  self.settings.baseUrl + "/usermap/" + id,
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
    },

    loadUserList: function(object) {
      var self  = this,
          obj   = object || {},
          data  = { locale : this.getParameterByName("locale") };

      self.showSpinner();

      if(obj.sort) {
        data.sort = obj.sort.item;
        data.dir = obj.sort.dir;
      }

      if(!data.locale) { delete data.locale; }

      $.ajax({
        type     : 'GET',
        url      : self.settings.baseUrl + '/user/',
        data     : data,
        dataType : 'html',
        success  : function(response) {
          if(response.indexOf("access denied") !== -1) {
            window.location.reload();
          } else {
            $('#userdata').html(response);
            self.hideSpinner();
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
              self.loadMapList({ uid : $(this).attr("data-uid") });
              self.tabSelector(3);
            });
          }
        }
      });
    },

    getLanguage: function() {
      var param = "", locale = this.getParameterByName("locale");

      if(locale === "fr_FR" || locale === "es_ES") {
        param = "?locale=" + locale;
      }
      return param;
    },

    deleteUserConfirmation: function(obj) {
      var self    = this,
          id      = $(obj).attr("data-id"),
          message = '<em>' + $(obj).parent().parent().children("td:first").text() + '</em>';

      $('#mapper-message-delete').find("span").html(message).end().dialog({
        height        : '250',
        width         : '500',
        dialogClass   : 'ui-dialog-title-mapper-message-delete',
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
                url     : self.settings.baseUrl + "/user/" + id,
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
    },

    bindSave: function() {
      var self = this;

      $(".map-save").click(function(e) {
        e.preventDefault();
        self.mapSave();
      });
    },

    mapSave: function() {
      var missingTitle = false,
          pattern      = /[?*:;{}\\ "'\/@#!%\^()<>.]+/g,
          map_title    = "",
          self         = this;

      $('#mapSave').dialog({
        autoOpen      : true,
        height        : '175',
        width         : '350',
        dialogClass   : 'ui-dialog-title-mapSave',
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

                self.setFormOptions();
                self.showSpinner();

                if(self.vars.jcropAPI === undefined) { $('#bbox_rubberband').val(''); }

                $.ajax({
                  type        : 'POST',
                  url         : self.settings.baseUrl + '/usermap/',
                  data        : $("form").serialize(),
                  dataType    : 'json',
                  success     : function(data) {
                    $('#mapTitle').text($('.m-mapSaveTitle').val());
                    map_title = $('.m-mapSaveTitle').val().replace(pattern, "_");
                    $('#file-name').val(map_title);
                    self.activateEmbed(data.mid);
                    self.loadMapList();
                    self.hideSpinner();
                    self.trackEvent('map', 'save');
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
    },

    bindDownload: function() {
      var self = this;

      $(".map-download").click(function(e) {
        e.preventDefault();
        self.mapDownload();
      });
    },

    mapDownload: function() {
      var self = this;

      $('#mapExport').dialog({
        autoOpen      : true,
        width         : '620',
        dialogClass   : 'ui-dialog-title-mapExport',
        modal         : true,
        closeOnEscape : false,
        draggable     : true,
        resizable     : false,
        buttons       : [
          {
            "text"  : $('#button-titles span.download').text(),
            "class" : "positive",
            "click" : function() {
              self.generateDownload();
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
    },

    bindSubmit: function() {
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
    },

    mapToggleSettings: function() {
      $('#mapToolsCollapse a').trigger('click');
    },

    bindPanelToggle: function() {
      var self = this;
      $('#mapToolsCollapse a').tipsy({ gravity : 'e' }).toggleClick(function(e) {
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
    },

    showMessage: function(message) {

      $('#mapper-message').html(message).dialog({
        autoOpen      : true,
        height        : '200',
        width         : '400',
        dialogClass   : 'ui-dialog-title-mapper-message',
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
    },

    drawLegend: function() {
      var legend_url = $('#legend_url').val(), legend = $('#mapLegend');

      if(legend_url) {
        legend.html("<img src=\"" + legend_url + "\" />");
      } else {
        legend.html('<p><em>' + $('#mapper-legend-message').text() + '</em></p>');
      }
    },

    drawScalebar: function() {
      $('#mapScale img').attr('src', $('#scalebar_url').val()).show();
    },

    showBadPoints: function() {
      var bad_points = $('#bad_points').val();

      if(bad_points) {
        $('#badRecords').html(bad_points);
        $('#badRecordsWarning').show();
      }
    },

    showSpinner: function() {
      $('.mapper-loading-spinner').show();
    },

    hideSpinner: function() {
      $('.mapper-loading-spinner').hide();
    },

    showErrorMessage: function(content) {
      var message = '<span class="mapper-message-error ui-corner-all ui-widget-content">' + content + '</span>';

      $('#mapOutput').append(message);
    },

    hideErrorMessage: function() {
      $('#mapOutput .mapper-message-error').remove();
    },

    showMap: function(load_data) {
      var self         = this,
          token        = new Date().getTime(),
          formString   = "",
          formObj      = {};

      self.destroyJcrop();

      $('#output').val('pnga');        // set the preview and output values
      $('#badRecordsWarning').hide();  // hide the bad records warning
      $('#download_token').val(token); // set a token to be used for cookie

      self.showSpinner();

      formString = $("form").serialize();
      formObj    = $("form").serializeJSON();
      self.postData(formString, load_data);
      $.jStorage.set("do-" + token.toString(), formObj);
      self.toggleUndo(true);
    },

    postData: function(formData, load_data) {
      var self      = this;

      self.hideErrorMessage();
      $.ajax({
        type     : 'POST',
        url      : self.settings.baseUrl + '/application/',
        data     : formData,
        dataType : 'json',
        timeout  : 30000,
        success  : function(data) {
          self.resetFormValues(data);
          self.resetJbbox();
          self.drawMap(data, load_data);
          self.drawLegend();
          self.drawScalebar();
          self.showBadPoints();
          self.addBadRecordsViewer();
        },
        error    : function(xhr, ajaxOptions, thrownError) {
          xhr = thrownError = null;
          if(ajaxOptions === 'timeout') {
            self.showErrorMessage($('#mapper-loading-error-message').text());
            self.hideSpinner();
          }
        }
      });
    },

    resetFormValues: function(data) {
      var ele = ["rendered_bbox", "rendered_rotation", "rendered_projection", "legend_url", "scalebar_url", "bad_points"];

      $('#mapOutput input').each(function() { $(this).val(''); });
      $.each(ele, function() { $('#' + this).val(data[this]); });
      $('#bbox_map').val($('#rendered_bbox').val());
      $('#projection_map').val($('#rendered_projection').val());
      $('#rotation').val($('#rendered_rotation').val());
      $('#pan').val('');
    },

    drawMap: function(data, load_data) {
      var self = this;

      $('#mapOutputImage').attr("width", data.size[0]).attr("height", data.size[1]).attr("src", data.mapOutputImage).one('load', function() {
        if(!load_data) { load_data = { "map" : { "bbox_rubberband" : "" }}; }
        self.loadCropSettings(load_data);
        self.hideSpinner();
      });
    },

    addBadRecordsViewer: function() {
      var self = this;

      $('#badRecordsViewer').dialog({
        autoOpen      : false,
        height        : '200',
        width         : '500',
        dialogClass   : 'ui-dialog-title-badRecordsViewer',
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
    },

    generateDownload: function() {
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
          $('body').download("/pptx/" + self.getLanguage(), formData, 'post');
          $('#output').val('pnga');
        break;

        case 'docx':
          $('#output').val('docx');
          if(self.vars.jcropAPI) { $('#crop').val(1); } else { self.resetJbbox(); }
          formData = $("form").serialize();
          $('body').download("/docx/" + self.getLanguage(), formData, 'post');
          $('#output').val('pnga');
        break;

        case 'kml':
          formData = $("form").serialize();
          $('body').download("/kml/", formData, 'post');
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

    },

    setFormOptions: function() {
      $.each(["border", "legend", "scalebar", "scalelinethickness"], function() {
        if($('#'+this).prop('checked')) {
          $('input[name="options['+this+']"]').val(1);
        } else {
          $('input[name="options['+this+']"]').val("");
        }
      });
    },

    finishDownload: function() {
      $('.download-message').hide();
      $.each(['download-dialog', 'ui-dialog-buttonpane'], function() { $('.'+this).show(); });
      window.clearInterval(this.vars.fileDownloadTimer);
      $.cookie('fileDownloadToken', null); //clears this cookie value
    },

    showExamples: function() {
      var message = '<img src="public/images/help-data.png" alt="" />';

      $('#mapper-message-help').html(message).dialog({
        height        : '355',
        width         : '525',
        dialogClass   : 'ui-dialog-title-mapper-message-help',
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
    },

    showCodes: function() {
      var data  = (this.getParameterByName("locale")) ? { locale : this.getParameterByName("locale") } : {};

      $('#mapper-message-codes').dialog({
        height        : '450',
        width         : '850',
        dialogClass   : 'ui-dialog-title-mapper-message-codes',
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

      this.loadCodes($('#mapper-message-codes'), data);
    },

    loadCodes: function(elem, data) {
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
    },

    performRotation: function(element) {
      $('#rotation').val($(element).attr("data-rotate"));
      this.resetJbbox();
      this.destroyRedo();
      this.showMap();
      this.trackEvent('rotate', $(element).attr("data-rotate"));
    },

    setRotation: function(angle) {
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
    },

    getParameterByName: function(name) {
      var cname   = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]"),
          regexS  = "[\\?&]" + cname + "=([^&#]*)",
          regex   = new RegExp(regexS),
          results = regex.exec(window.location.href);

      if(results === null) { return ""; }
      return decodeURIComponent(results[1].replace(/\+/g, " "));
    },

    mapCircleSlider: function() {
      var i = 0, output = "";

      for(i = 0; i < 360; i += 1) {
        if(i % 5 === 0) {
          output += '<li data-rotate="' + i + '"></li>';
        }
      }
      return output;
    },

    clearStorage: function() {
      this.destroyRedo();
      $.jStorage.flush();
    },

    bindStorage: function() {
      var formData = {}, token = new Date().getTime();

      this.clearStorage();
      formData = $("form").serializeJSON();
      $.jStorage.set("do-" + token.toString(), formData);
    },

    bindRotateWheel: function() {
      var self = this;
      $('.overlay','#mapControls').css('background-image', 'url("public/images/bg-rotatescroll.png")');
        $('.overview', '#mapControls').append(self.mapCircleSlider());
        $('#mapControls').tinycircleslider({snaptodots:true,radius:28,callback:function(element,index){
          index = null;
          if($('.mapper-loading-spinner').is(':hidden')) { self.performRotation(element); }
      }});
    },

    bindTabs: function() {
      var tab            = $('#tabs'),
          id             = 'tabs',
          tab_a_selector = 'ul.navigation a',
          url            = "",
          config         = {
            cache : true,
            load  : function(e, ui){
              e = null;
              $(ui.tab).data("cache.tabs",($(ui.panel).html() === "") ? false : true);
            },
            event : 'change'
          };

      $('#mapTools').tabs({selected: 0});
      tab.tabs(config).find(".ui-state-disabled").each(function() { $(this).removeClass("ui-state-disabled"); }).end().show();

      tab.find(tab_a_selector).click(function(){
        var state = {},
          idx = $(this).parent().prevAll().length;

        state[id] = idx;
        $.bbq.pushState(state);
        $.each($('#site-languages a'), function() {
          url = $(this).attr('href').split('#')[0];
          $(this).attr('href', url + '#' + id + '=' + idx);
        });
      });

      $(window).bind('hashchange', function(e) {
        var idx = $.bbq.getState(id, true) || 0;

        e = null;
        tab.find(tab_a_selector).eq(idx).triggerHandler('change');
        $.each($('#site-languages a'), function() {
          url = $(this).attr('href').split('#')[0];
          $(this).attr('href', url + '#' + id + '=' + idx);
        });
      });
      $(window).trigger('hashchange');
    },

    screenSizeListener: function() {
      var self = this;

      $(window).resize(function() {
        var arrPageSizes  = self.getPageSize(),
            arrPageScroll = self.getPageScroll();

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
    },

    init: function() {
      var self = this;
      this.screenSizeListener();
      this.bindRotateWheel();
      this.hideSpinner();
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
      if($('#usermaps').length > 0) {
        this.loadMapList();
        this.tabSelector(3);
      }
      if($('#userdata').length > 0) {
        this.loadUserList();
        this.tabSelector(4);
      }
      $("input").keypress(function(e) { if (e.which === 13) { return false; } });
    }

  };

  return {
    init: function(args) {
      $.extend(_private.settings, args);
      _private.init();
    },
    getParameterByName: function(param) {
      _private.getParameterByName(param);
    },
    loadCodes: function(elem,data) {
      _private.loadCodes(elem,data);
    }
  };

}(jQuery, window, document));