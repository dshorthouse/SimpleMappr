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
                           },
      spinner            : $('#map-loader').find('span.mapper-loading-spinner'),
      fieldSetsPoints    : $('#fieldSetsPoints'),
      fieldSetsRegions   : $('#fieldSetsRegions'),
      mapOutput          : $('#mapOutput'),
      mapOutputImage     : ""
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
          factor    = $('#mapExport').find('input[name="download-factor"]:checked').val();

      switch(this.vars.jCropType) {
        case 'crop':
          self.vars.mapOutput.find('div.jcrop-holder div:first').css({backgroundColor:'white'});
          $('#bbox_rubberband').val(x+','+y+','+x2+','+y2);

          if($('#projection option:selected').val() === 'epsg:4326') {
            self.vars.mapOutput.find('input.jcrop-coord').css({width: "100px"});
          } else {
            self.vars.mapOutput.find('input.jcrop-coord').css({width: "175px"});
          }

          if($('#jcrop-coord-ul').length === 0 && $('#jcrop-coord-lr').length === 0) {
            self.vars.mapOutput.find('div.jcrop-tracker').eq(0).after(ul_holder).after(lr_holder).after(d_holder);
          }

          ul_coord = self.pix2geo(ul_point);
          lr_coord = self.pix2geo(lr_point);
          $('#jcrop-coord-ul').val(ul_coord.x + ', ' + ul_coord.y);
          $('#jcrop-coord-lr').val(lr_coord.x + ', ' + lr_coord.y);
          $('#jcrop-dimension-w').val(w);
          $('#jcrop-dimension-h').val(h);
          $('#jcrop-dimension-wrapper').css({left : w/2-$('#jcrop-dimension-wrapper').width()/2, top : h/2-$('#jcrop-dimension-wrapper').height()/2});

          $.cookie("jcrop_coords", "{ \"jcrop_coord_ul\" : \"" + $('#jcrop-coord-ul').val() + "\", \"jcrop_coord_lr\" : \"" + $('#jcrop-coord-lr').val() + "\" }" );

          self.vars.mapOutput.on('blur', 'input.jcrop-coord', function() {
            if(!self.vars.cropUpdated) { self.vars.cropUpdated = self.updateCropCoordinates(); }
          })
          .on('keypress', 'input.jcrop-coord', function(e) {
            var key = e.keyCode || e.which;
            if(key === 13 || key === 9) {
              e.preventDefault();
              self.vars.cropUpdated = false;
              this.blur();
            }
          }).on('blur', 'input.jcrop-dimension', function() {
            if(!self.vars.cropUpdated) { self.vars.cropUpdated = self.updateCropDimensions(); }
          })
          .on('keypress', 'input.jcrop-dimension', function(e) {
            var key = e.keyCode || e.which;
            if(key === 13 || key === 9) {
              e.preventDefault();
              self.vars.cropUpdated = false;
              this.blur();
            }
          });

          $('#scale-measure').find('span').text(factor*w + ' X ' + factor*h).parent().show();
        break;

        case 'zoom':
          self.vars.mapOutput.find('div.jcrop-holder div:first').css({backgroundColor: 'white'});
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
          image_w      = this.vars.mapOutputImage.width(),
          image_h      = this.vars.mapOutputImage.height(),
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
      var ul_val   = $('#jcrop-coord-ul').val(),
          ul_arr   = ul_val.split(","),
          ul_point = this.geo2pix({ 'x' : $.trim(ul_arr[0]), 'y' : $.trim(ul_arr[1]) }),
          lr_val   = $('#jcrop-coord-lr').val(),
          lr_arr   = lr_val.split(","),
          lr_point = this.geo2pix({ 'x' : $.trim(lr_arr[0]), 'y' : $.trim(lr_arr[1]) });

      $.cookie("jcrop_coords", "{ \"jcrop_coord_ul\" : \"" + ul_val + "\", \"jcrop_coord_lr\" : \"" + lr_val + "\" }" );
      this.loadCropSettings({ 'map' : { 'bbox_rubberband' : lr_point.x + "," + lr_point.y + "," + ul_point.x + "," + ul_point.y } });
      return true;
    },

    pix2geo: function(point) {
      var deltaX = 0,
          deltaY = 0,
          bbox   = $('#bbox_map').val(),
          width  = parseFloat(this.vars.mapOutputImage.width()),
          height = parseFloat(this.vars.mapOutputImage.height()),
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

      point.x = this.vars.mapOutputImage.width()*(Math.abs(parseFloat(coord.x) - parseFloat($.trim(bbox[0]))))/deltaX;
      point.y = this.vars.mapOutputImage.height()*(deltaY - Math.abs(parseFloat(coord.y) - parseFloat($.trim(bbox[1]))))/deltaY;

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
      var self = this, action = "";

      $("#actionsBar").find("li").hover(function() {
        $(this).toggleClass("ui-state-hover");
      }).end().find("a.toolsQuery").ColorPicker({
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
          self.unusedVariables(hsb, hex);
          $(el).ColorPickerHide();
          self.vars.fillColor = rgb;
          self.initJquery();
          self.vars.zoom = false;
        }
      }).end().on('click', 'a', function(e) {
        e.preventDefault();
        action = $(this).attr("class").match(/tools([\w\W]+)/)[1].toLowerCase();
        switch(action) {
          case 'zoomin':
            self.mapZoom("in");
          break;
          
          case 'zoomout':
            self.mapZoom("out");
          break;
          
          case 'crop':
            self.mapCrop();
          break;
          
          case 'query':
            self.resetJbbox();
          break;
          
          case 'refresh':
            self.mapRefresh();
          break;
          
          case 'rebuild':
            self.mapRebuild();
          break;
          
          case 'save':
            self.mapSave();
          break;
          
          case 'download':
            self.mapDownload();
          break;
        }
        self.trackEvent('toolbar', action);
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

      this.vars.zoom = false;
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
      $.each(['bbox_map', 'projection_map', 'bbox_rubberband', 'rotation', 'pan'], function() {
        $('#' + this).val('');
      });
      $('#projection')[0].selectedIndex = 0;
      this.destroyRedo();
      this.showMap();
    },

    bindArrows: function() {
      var self = this;

      $('#wheel-overlay').on('click', 'a.arrows', function(e) {
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
      var zoom = "";
      if(dir === "in") {
        this.initJzoom();
        this.vars.zoom = true;
      } else {
        zoom = $('#zoom_out');
        this.resetJbbox();
        zoom.val(1);
        this.destroyRedo();
        this.showMap();
        zoom.val('');
      }
    },

    storageType: function(type) {
      var index = 0, self = this;

      index = $.grep($.jStorage.index(), function(value, i) {
        self.unusedVariables(i);
        return (value.substring(0, type.length) === type);
      });

      return index;
    },

    toggleUndo: function(activate) {
      var self  = this,
          index = this.storageType("do"),
          actionsBar = $('#actionsBar');

      actionsBar.find('a.toolsUndo').removeClass('toolsUndo').addClass('toolsUndoDisabled').off('click');

      if(activate && index.length > 1) {
        if(index.length > self.settings.undoSize) { $.jStorage.deleteKey(index.shift()); }
        actionsBar.find('a.toolsUndoDisabled').addClass('toolsUndo').removeClass('toolsUndoDisabled').on('click', function(e) {
          e.preventDefault();
          self.mapUndo();
          self.trackEvent('edit', 'undo');
        });
      }
    },

    toggleRedo: function(activate) {
      var self = this,
          actionsBar = $('#actionsBar');

      actionsBar.find('a.toolsRedo').addClass('toolsRedoDisabled').removeClass('toolsRedo').off('click');

      if(activate) {
        actionsBar.find('a.toolsRedoDisabled').addClass('toolsRedo').removeClass('toolsRedoDisabled').on('click', function(e) {
          e.preventDefault();
          self.mapRedo();
          self.trackEvent('edit', 'redo');
        });
      }
    },

    destroyRedo: function() {
      var index = this.storageType("undo"),
          actionsBar = $('#actionsBar');

      if(index.length > 0) {
        actionsBar.find('a.toolsRedo').addClass('toolsRedoDisabled').removeClass('toolsRedo').off('click');
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
        $(document).off('keydown', value).on('keydown', null, key, value);
      });

      this.vars.mapOutput.hover(
        function() {
          $.each(arrows, function(key, value) {
            $(document).on('keydown', null, key, value);
          });
          self.vars.mapOutputImage.on('dblclick', function(e) { self.dblclickZoom(this, e); });
        },
        function() {
          $.each(arrows, function(key, value) {
            self.unusedVariables(key);
            $(document).off('keydown', value);
          });
          self.vars.mapOutputImage.off('dblclick');
        }
      );
    },

    hardResetShowMap: function() {
      this.resetJbbox();
      this.destroyRedo();
      this.showMap();
    },

    bindSettings: function() {
      var self = this, graticules = $('#graticules');
      
      $('#mapOptions').on('click', '.layeropt', function() {
        self.hardResetShowMap();
      }).on('click', '.gridopt', function() {
        if(!graticules.prop('checked')) { graticules.prop('checked', true); }
        self.hardResetShowMap();
      }).on('click', '#gridlabel', function() {
        if(!graticules.prop('checked')) { graticules.prop('checked', true); }
        if($(this).prop('checked')) { $(this).val('false'); }
        self.hardResetShowMap();
      });

      $('#projection').on('change', function() {
        var origin_sel = $('#origin-selector');

        if($(this).val() !== "") {
          $('#origin').val(self.vars.origins[$(this).val()]);
          if(self.vars.origins.hasOwnProperty($("#projection").val())) { origin_sel.show(); } else { origin_sel.hide(); }
          $.cookie("jcrop_coords", null);
          self.hardResetShowMap();
        }
      });

      $('#origin').on('blur', function() {
        self.hardResetShowMap();
      }).on('keydown', function(e) {
        var key = e.keyCode || e.which;
        if(key === 9 || key === 13 ) { this.blur(); }
      });

      self.toggleFileFactor();

      $('#mapExport').find('input.download-factor').on('change', function() {
        self.toggleFileFactor($(this).val());
      }).end().find('.download-filetype').on('change', function() {
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
          self.unusedVariables(e);
          $('#border_thickness').val(ui.value);
          self.destroyRedo();
          self.showMap();
          self.trackEvent('slider', ui.value);
        }
      });
    },

    toggleFileFactor: function(factor) {
      var scale      = "",
          rubberband = $('#bbox_rubberband').val().split(",");

      if(!factor) { factor = $('#mapExport').find('input[name="download-factor"]:checked').val(); }

      if(this.vars.jCropType === 'crop') {
        scale = factor*(rubberband[2]-rubberband[0]) + " X " + factor*(rubberband[3]-rubberband[1]);
      } else {
        scale = factor*(this.vars.mapOutputImage.width()) + " X " + factor*(this.vars.mapOutputImage.height());
      }
      $('#scale-measure').find('span').text(scale).parent().show();
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
      $.each([this.vars.fieldSetsPoints, this.vars.fieldSetsRegions], function(){
        $(this).find('input.colorPicker').ColorPicker({
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
            self.unusedVariables(hsb,hex);
            $(el).val(rgb.r + ' ' + rgb.g + ' ' + rgb.b).ColorPickerHide();
          }
        }).on('keyup', function() {
          var color = $(this).val().split(" ");
          $(this).ColorPickerSetColor(self.RGBtoHex(color[0], color[1], color[2]));
        });
      });
    },

    bindClearButtons: function() {
      var self = this;

      $('#clearLayers, #clearRegions').on('click', function(e) {
        e.preventDefault();
        self.clearZone($(this).parent().prev().prev().children());
      });
      
      $.each([this.vars.fieldSetsPoints, this.vars.fieldSetsRegions], function() {
        $(this).on('click', 'button.clearself', function(e) {
          e.preventDefault();
          self.clearZone($(this).parent().parent());
        });
      });
    },

    clearZone: function(zone) {
      var shape_picker = zone.find('select.m-mapShape'),
          size_picker = zone.find('select.m-mapSize'),
          color_picker = zone.find('input.colorPicker');

      $.each(['input.m-mapTitle', 'textarea'], function() { zone.find(this).val(''); });
      if(shape_picker.length > 0) { shape_picker[0].selectedIndex = 4; }
      if(size_picker.length > 0) { size_picker[0].selectedIndex = 3; }
      $.each(zone, function() {
        if($(this).hasClass("fieldset-points")) {
          color_picker.val('0 0 0');
        } else {
          color_picker.val('150 150 150');
        }
      });
    },

    bindAutocomplete: function() {
      var self = this, term = "", terms = [];

      this.vars.fieldSetsRegions.find('textarea').on('keydown', function(e) {
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
          self.unusedVariables(e);
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

    destroyJcrop: function() {
      var vars = this.vars;

      if(vars.jzoomAPI !== undefined) { vars.jzoomAPI.destroy(); }
      if(vars.jcropAPI !== undefined) { vars.jcropAPI.destroy(); }
      if(vars.jqueryAPI !== undefined) { vars.jqueryAPI.destroy(); }

      this.vars.mapOutputImage.show();
      this.vars.mapOutput.find('div.jcrop-holder').remove();
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
      var self = this;

      this.destroyJcrop();
      this.resetJbbox();
      this.vars.jCropType = "crop";

      this.vars.jcropAPI = $.Jcrop('#' + self.vars.mapOutputImage.attr("id"), {
        bgColor   : (self.vars.mapOutputImage.attr("src") === "public/images/basemap.png") ? 'grey' : 'black',
        bgOpacity : 0.5,
        onChange  : self.bindCallback(self, self.showCoords),
        onSelect  : self.bindCallback(self, self.showCoords),
        setSelect : select
      });

      $('#mapCropMessage').show();
    },

    initJzoom: function() {
      var self = this;

      this.destroyJcrop();
      this.resetJbbox();
      this.vars.jCropType = "zoom";

      this.vars.jzoomAPI = $.Jcrop('#' + self.vars.mapOutputImage.attr("id"), {
        addClass      : "customJzoom",
        bgOpacity     : 1,
        bgColor       : "white",
        onChange      : self.bindCallback(self, self.showCoords),
        onSelect      : self.bindCallback(self, self.showCoords)
      });

      $('.jcrop-tracker').mousedown(function() { self.activateJcrop('zoom'); });
    },

    initJquery: function() {
      var self = this;

      this.destroyJcrop();
      this.resetJbbox();
      this.vars.jCropType = "query";

      this.vars.jqueryAPI = $.Jcrop('#' + self.vars.mapOutputImage.attr("id"), {
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
            width          : $('#width').val(),
            height         : $('#height').val()
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
                fieldsets = self.vars.fieldsetsRegions.find('div.fieldset-regions'),
                num_fieldsets = fieldsets.length;

            $.each(fieldsets, function(i) {
              if(i === (num_fieldsets-1) && !self.vars.fieldsetsRegions.find('button[data-type="regions"]').prop('disabled')) {
                self.addAccordionPanel('regions');
                num_fieldsets += 1;
              }
              if(self.vars.fieldsetsRegions.find('input[name="regions['+i+'][title]"]').val() === "" || self.vars.fieldsetsRegions.find('textarea[name="regions['+i+'][data]"]').val() === "") {
                self.vars.fieldsetsRegions.find('input[name="regions['+i+'][title]"]').val("Selected Region " + (i+1).toString());
                self.vars.fieldsetsRegions.find('input[name="regions['+i+'][color]"]').val(fillColor);
                self.vars.fieldsetsRegions.find('textarea[name="regions['+i+'][data]"]').val(regions);
                if(i > 0) { self.vars.fieldSetsRegions.accordion("activate", i); }
                return false;
              }
            });

            self.showMap();
          } else {
            self.hideSpinner();
          }
        },
        error   : function(xhr, ajaxOptions, thrownError) {
          self.unusedVariables(xhr,thrownError);
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
          button   = $("button.addmore[data-type='" + data_type + "']"),
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
              self.unusedVariables(hsb,hex);
              $(el).val(rgb.r + " " + rgb.g + " " + rgb.b);
              $(el).ColorPickerHide();
            }
          }).on('keyup', function() {
            var color = $(this).val().split(" ");
            $(this).ColorPickerSetColor(self.RGBtoHex(color[0], color[1], color[2]));
          });

          children = button.parent().prev().append(clone).children("div");

          children.each(function(i, val) {
            self.unusedVariables(val);
            if (i === children.length-1) {
              $(this).find("button.removemore").show().on('click', function(e) {
                e.preventDefault();
                self.removeAccordionPanel(clone, data_type);
                counter = self.textareaCounter(data_type, 'decrease');
              }).parent()
              .find("button.clearself").on('click', function(e) {
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
      var button = $("button.addmore[data-type='" + data_type + "']");

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
        textarea.css({opacity: 1});
      }

      function startDrag(e) {
        staticOffset = textarea.height() - e.pageY;
        textarea.css({opacity: 0.25});
        $(document).bind('mousemove', performDrag).bind('mouseup', endDrag);
        return false;
      }

      $(obj).parent().find(".grippie").bind('mousedown', startDrag);
    },

    bindAddButtons: function() {
      var self = this;

      $('#map-points, #map-regions').on('click', 'button.addmore', function(e) {
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
            $('#usermaps').off().html(response)
              .on('click', 'a.toolsRefresh', function(e) { e.preventDefault(); self.loadMapList(); })
              .on('click', 'a.ui-icon-triangle-sort', function(e) {
                e.preventDefault();
                data.sort = { item : $(this).attr("data-sort"), dir : "asc" };
                if($(this).hasClass("asc")) { data.sort.dir = "desc"; }
                self.loadMapList(data);
                self.trackEvent('maplist', 'sort');
              })
              .on('click', 'a.map-load', function(e) {
                e.preventDefault();
                self.loadMap(this);
                self.trackEvent('map', 'load');
              })
              .on('click', 'a.map-delete', function(e) {
                e.preventDefault();
                self.deleteMapConfirmation(this);
            });
            $('#filter-mymaps')
              .val(obj.search)
              .on('keypress', function(e) {
                var key = e.keyCode || e.which;
                if(key === 13 || key === 9) {
                  e.preventDefault();
                  data.search = $(this).val();
                  self.loadMapList(data);
                  self.trackEvent('maplist', 'filter');
                }
              }).focus();
            self.hideSpinner();
          }
        }
      });
    },

    removeExtraElements: function() {
      var self = this;

      $.each(this.vars.fieldSetsPoints.find('.fieldset-points'), function(i) {
        if(i > 2) { $(this).remove(); }
      });

      $.each(this.vars.fieldSetsRegions.find('.fieldset-regions'), function(i) {
        if(i > 2) { $(this).remove(); }
      });
      
      self.vars.newPointCount = 0;
      self.vars.newRegionCount = 0;
    },

    prepareInputs: function(data) {
      var inputs = {}, item = [];

      inputs = {
        "status" : "ok",
        "mid"    : $('#actionsBar').find('a.map-embed').attr("data-id"),
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
      $.each(['width', 'height'], function() { $('#'+this).val($('#'+this).val()); });
      $('#map-points, #map-regions').find('button.addmore').prop("disabled", false);
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
          self.unusedVariables(xhr,thrownError);
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

      $('#save\\[title\\]').val(map_title);
      $('#m-mapSaveTitle').val(map_title);

      $('#mapTitle').text(map_title);

      map_title = map_title.replace(pattern, "_");
      $('#file-name').val(map_title);
      $("#projection").val(data.map.projection);
      if(self.vars.origins.hasOwnProperty($("#projection").val())) { $('#origin-selector').show(); }
      $.each(['bbox_map', 'projection_map', 'rotation', 'origin'], function() { $('#'+this).val(data.map[this]); });
      if(!data.map.origin) { $('#origin').val(self.vars.origins[$("#projection").val()]); }

      $('#border_thickness').val(1.25);
      $('#border-slider').slider({value:1.25});
      if(data.map.border_thickness !== undefined && data.map.border_thickness) {
        $('#border_thickness').val(data.map.border_thickness);
        $('#border-slider').slider({value:data.map.border_thickness});
      }

      self.setRotation(data.map.rotation);
      self.resetJbbox();

      $.each(["border", "legend", "scalebar", "scalelinethickness"], function() {
        $('#'+this).prop('checked', false);
        $('#options\\['+this+'\\]').val("");
      });

      if(data.map.options !== undefined) {
        $.each(["border", "legend", "scalebar", "scalelinethickness"], function() {
          if(data.map.options[this] && data.map.options[this] !== undefined) {
            $('#'+this).prop('checked', true);
            $('#options\\['+this+'\\]').val(1);
          }
        });
      }

      if(data.map.download_factor !== undefined && data.map.download_factor) {
        $('#download_factor').val(data.map.download_factor);
        $('#download-factor-' + data.map.download_factor).prop('checked', true);
      } else {
        $('#download-factor-3').prop('checked', true);
      }

      if(data.map.download_filetype !== undefined && data.map.download_filetype) {
        $('#download_filetype').val(data.map.download_filetype);
        download_filetype = $('#download-' + data.map.download_filetype).prop('checked', true);
        self.toggleFileType(download_filetype);
      } else {
        $('#download-svg').prop('checked', true);
      }

      if(data.map.grid_space !== undefined && data.map.grid_space) {
        $('#gridspace-' + data.map.grid_space).prop('checked', true);
      } else {
        $('#gridspace').prop('checked', true);
      }

      if(data.map.gridlabel !== undefined && data.map.gridlabel) {
        $('#gridlabel').prop('checked', true);
      } else {
        $('#gridlabel').prop('checked', false);
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
      var self = this;
      $.each(['shape', 'size'], function() {
        if(coords[i][this].toString() === "") {
          self.vars.fieldSetsPoints.find('select[name="coords['+i.toString()+']['+this+']"]')[0].selectedIndex = 3;
        } else {
          self.vars.fieldSetsPoints.find('select[name="coords['+i.toString()+']['+this+']"]').val(coords[i][this]);
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

        self.vars.fieldSetsPoints.find('input[name="coords['+i.toString()+'][title]"]').val(coord_title);
        self.vars.fieldSetsPoints.find('textarea[name="coords['+i.toString()+'][data]"]').val(coord_data);
        self.loadShapeSize(i, coords);
        self.vars.fieldSetsPoints.find('input[name="coords['+i.toString()+'][color]"]').val(coord_color);
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

        self.vars.fieldSetsRegions.find('input[name="regions['+i.toString()+'][title]"]').val(region_title);
        self.vars.fieldSetsRegions.find('textarea[name="regions['+i.toString()+'][data]"]').val(region_data);
        self.vars.fieldSetsRegions.find('input[name="regions['+i.toString()+'][color]"]').val(region_color);
      });
    },

    loadLayers: function(data) {
      var self = this;
      if(data.map.layers) {
        $.each(data.map.layers, function(k,v) {
          self.unusedVariables(v);
          $('#'+k).prop('checked', true);
        });
      }
    },

    activateEmbed: function(mid) {
      var self    = this,
          types   = ['img','kml','svg','json'];

      $('#actionsBar').find('a.toolsEmbed').attr("data-id", mid).css({display: 'block'}).on('click', function(e) {
        e.preventDefault();
        $.each(types, function() {
          if(this.toString() === 'img') {
            $('#embed-'+this).val("<img src=\"" + self.settings.baseUrl + "/map/" + mid + "\" alt=\"\" />");
          } else {
            $('#embed-'+this).val(self.settings.baseUrl + "/map/" + mid + "." + this);
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
                      });
      });
    },

    deleteMapConfirmation: function(obj) {
      var self    = this,
          id      = $(obj).attr("data-id"),
          message = '<em>' + $(obj).parent().parent().find("td.title").text() + '</em>';

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
            "text"  : $('#button-titles').find('span.delete').text(),
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
            "text"  : $('#button-titles').find('span.cancel').text(),
            "class" : "ui-button-cancel",
            "click" : function() {
              $(this).dialog("destroy");
            }
          }]
      });
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
            $('#userdata').off().html(response)
              .on('click', 'a.toolsRefresh', function(e) {
                e.preventDefault();
                self.loadUserList();
              })
              .on('click', 'a.ui-icon-triangle-sort', function(e) {
                e.preventDefault();
                data.sort = { item : $(this).attr("data-sort"), dir : "asc" };
                if($(this).hasClass("asc")) { data.sort.dir = "desc"; }
                self.loadUserList(data);
              })
              .on('click', 'a.user-delete', function(e) {
                e.preventDefault();
                self.deleteUserConfirmation(this);
              })
              .on('click', 'a.user-load', function(e) {
                e.preventDefault();
                self.loadMapList({ uid : $(this).attr("data-uid") });
                self.tabSelector(3);
            });
            self.hideSpinner();
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
            "text"  : $('#button-titles').find('span.delete').text(),
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
            "text"  : $('#button-titles').find('span.cancel').text(),
            "class" : "ui-button-cancel",
            "click" : function() {
              $(this).dialog("destroy");
            }
          }]
      }).show();
    },

    mapSave: function() {
      var missingTitle = false,
          pattern      = /[?*:;{}\\ "'\/@#!%\^()<>.]+/g,
          map_title    = $('#m-mapSaveTitle'),
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
            "text"  : $('#button-titles').find('span.save').text(),
            "class" : "positive",
            "click" : function() {
              if($.trim(map_title.val()) === '') { missingTitle = true; }
              if(missingTitle) {
                map_title.addClass('ui-state-error').on('keyup', function() {
                  $(this).removeClass('ui-state-error');
                });
              } else {
                $('#save\\[title\\]').val(map_title.val());
                $.each(['factor', 'filetype'], function() { $('#download_'+this).val($('#mapExport').find('input[name="download-'+this+'"]:checked').val()); });
                $('#grid_space').val($('#graticules-selection').find('input[name="gridspace"]:checked').val());

                self.setFormOptions();
                self.showSpinner();

                if(self.vars.jcropAPI === undefined) { $('#bbox_rubberband').val(''); }

                $.ajax({
                  type        : 'POST',
                  url         : self.settings.baseUrl + '/usermap/',
                  data        : $("form").serialize(),
                  dataType    : 'json',
                  success     : function(data) {
                    $('#mapTitle').text(map_title.val());
                    $('#file-name').val(map_title.val().replace(pattern, "_"));
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
          "text"  : $('#button-titles').find('span.cancel').text(),
          "class" : "ui-button-cancel",
          "click" : function() {
            $(this).dialog("destroy");
          }
        }]
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
            "text"  : $('#button-titles').find('span.download').text(),
            "class" : "positive",
            "click" : function() {
              self.generateDownload();
            }
          },
          {
            "text"  : $('#button-titles').find('span.cancel').text(),
            "class" : "ui-button-cancel",
            "click" : function() {
              $(this).dialog("destroy");
            }
          }]
      });
    },

    bindSubmit: function() {
      var self = this, title = "", missingTitle = false;

      $('#map-points, #map-regions').find('button.submitForm').on('click', function(e) {
        e.preventDefault();
        missingTitle = false;
        $.each($(this).parent().parent().find('div.fieldSets').children(), function() {
          title = $(this).find('input.m-mapTitle').on('keyup', function() {
            missingTitle = false;
            $(this).removeClass('ui-state-error');
          });
          if($(this).find('textarea.m-mapCoord').val() && title.val() === '') {
            missingTitle = true;
            title.addClass('ui-state-error');
            return false;
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
      $('#mapToolsCollapse').find('a').trigger('click');
    },

    bindPanelToggle: function() {
      var self = this;
      $('#mapToolsCollapse').find('a').tipsy({ gravity : 'e' }).toggleClick(function(e) {
        e.preventDefault();
        self.vars.mapOutputImage.attr("width", 0).attr("height", 0).css({width:'0px', height:'0px'});
        $('#mapOutputScale').hide();
        $(this).parent().addClass("mapTools-collapsed");
        $('#mapTools').hide("slide", { direction : "right" }, 250, function() {
          var new_width = $(window).width()*0.98;
          $('#actionsBar').animate({ width : new_width }, 250);
          $('#map').animate({ width : new_width }, 250, function() {
            $('#width').val(new_width);
            self.mapRefresh();
          });
        });
      }, function(e) {
        e.preventDefault();
        self.vars.mapOutputImage.attr("width", 0).attr("height", 0).css({width:'0px', height:'0px'});
        $('#mapOutputScale').hide();
        $(this).parent().removeClass("mapTools-collapsed");
        $('#mapTools').show("slide", { direction : "right" }, 250, function() {
          $('#actionsBar').animate({ width : "910px" }, 250);
          $('#map').animate({ width : "900px" }, 250, function() {
             $('#width').val(900);
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
      $('#mapScale').find('img').attr('src', $('#scalebar_url').val()).show();
    },

    showBadPoints: function() {
      var bad_points = $('#bad_points').val();

      if(bad_points) {
        $('#badRecords').html(bad_points);
        $('#badRecordsWarning').show();
      }
    },

    showSpinner: function() {
      this.vars.spinner.show();
    },

    hideSpinner: function() {
      this.vars.spinner.hide();
    },

    showErrorMessage: function(content) {
      var message = '<span class="mapper-message-error ui-corner-all ui-widget-content">' + content + '</span>';

      this.vars.mapOutput.append(message);
    },

    hideErrorMessage: function() {
      this.vars.mapOutput.find('.mapper-message-error').remove();
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
          self.unusedVariables(xhr,thrownError);
          if(ajaxOptions === 'timeout') {
            self.showErrorMessage($('#mapper-loading-error-message').text());
            self.hideSpinner();
          }
        }
      });
    },

    resetFormValues: function(data) {
      var ele = ["rendered_bbox", "rendered_rotation", "rendered_projection", "legend_url", "scalebar_url", "bad_points"];

      $.each(this.vars.mapOutput.find('input'), function() { $(this).val(''); });
      $.each(ele, function() { $('#' + this).val(data[this]); });
      $('#bbox_map').val($('#rendered_bbox').val());
      $('#projection_map').val($('#rendered_projection').val());
      $('#rotation').val($('#rendered_rotation').val());
      $('#pan').val('');
    },

    drawMap: function(data, load_data) {
      var self = this;

      this.vars.mapOutputImage
        .attr("width", data.size[0])
        .attr("height", data.size[1])
        .css({width:data.size[0]+'px', height:data.size[1]+'px'})
        .attr("src", data.mapOutputImage)
        .one('load', function() {
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

      $('#badRecordsWarning').find('a.toolsBadRecords').on('click', function(e) {
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
      $('#file_name').val(map_title);

      $('#download_factor').val($('#mapExport').find('input[name="download-factor"]:checked').val());

      filetype = $('#mapExport').find('input[name="download-filetype"]:checked').val();

      self.setFormOptions();
      $('#download_token').val(token);

      $('#mapExport').find('div.download-dialog').hide().end().next().hide().end().find('div.download-message').show();

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
        $('#options\\['+this+'\\]').val("");
        if($('#'+this).prop('checked')) {
          $('#options\\['+this+'\\]').val(1);
        }
      });
    },

    finishDownload: function() {
      $('#mapExport').find('div.download-message').hide().end().next().show().end().find('div.download-dialog').show();
      window.clearInterval(this.vars.fileDownloadTimer);
      $.cookie('fileDownloadToken', null);
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
      });
    },

    showCodes: function() {
      var data  = (this.getParameterByName("locale")) ? { locale : this.getParameterByName("locale") } : {},
           messageCodes = $('#mapper-message-codes');

      messageCodes.dialog({
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
              messageCodes.find('table').remove();
              $(this).dialog("destroy");
            }
          }
        ]
      });

      this.loadCodes(messageCodes, data);
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
          filter = elem.find('input.filter-countries');
          filter.val("");
          if(data.filter !== undefined) { filter.val(data.filter); }
          filter.on('keypress', function(e) {
            var key = e.keyCode || e.which;
            if(key === 13 || key === 9) {
              e.preventDefault();
              data.filter = filter.val();
              self.loadCodes(elem, data);
            }
          }).on('blur', function() {
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
      var control  = $('#mapControls'),
          thumb    = control.find('div.thumb'),
          overview = control.find('ul.overview'),
          dots     = overview.find("li"),
          rads     = 0,
          left     = 0,
          top      = 0;

      if(!angle) { angle = 0; }

      angle = parseFloat(angle) < 0 ? parseFloat(angle) +360 : parseFloat(angle);
      rads = angle * (Math.PI/180);

      overview.css({left: -(angle / 360 * ((dots.outerWidth(true) * (dots.length)))) + 'px'});  
      top = Math.round(-Math.cos(rads) * 28 + (control.outerHeight() /2 - thumb.outerHeight() /2)) + 'px';
      left = Math.round(Math.sin(rads) * 28 + (control.outerWidth() /2 - thumb.outerWidth() /2)) + 'px';
      thumb.css({top:top,left:left});
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
      $('#wheel-overlay').css({backgroundImage: 'url("public/images/bg-rotatescroll.png")'});
      $('#mapControls').find('ul.overview').append(self.mapCircleSlider())
        .end()
        .tinycircleslider({snaptodots:true,radius:28,callback:function(element,index){
          self.unusedVariables(index);
          if($('#map-loader').find('span.mapper-loading-spinner').is(':hidden')) { self.performRotation(element); }
      }});
    },

    bindTabs: function() {
      var self           = this,
          tab            = $('#tabs'),
          id             = 'tabs',
          tab_a_selector = 'ul.navigation a',
          config         = {
            cache : true,
            load  : function(e, ui){
              self.unusedVariables(e);
              $(ui.tab).data("cache.tabs",($(ui.panel).html() === "") ? false : true);
            },
            event : 'change'
          };

      $('#mapTools').tabs({selected: 0});
      tab.tabs(config).find("div.ui-state-disabled").each(function() { $(this).removeClass("ui-state-disabled"); }).end().show();

      tab.on('click', tab_a_selector, function(){
        var state = {},
          idx = $(this).parent().prevAll().length;

        state[id] = idx;
        $.bbq.pushState(state);
        self.adjustLanguageLinks(id, idx);
      });

      $(window).bind('hashchange', function() {
        var idx = $.bbq.getState(id, true) || 0;

        tab.find(tab_a_selector).eq(idx).triggerHandler('change');
        self.adjustLanguageLinks(id, idx);
      });
      $(window).trigger('hashchange');
    },
    
    adjustLanguageLinks: function(id, idx) {
      var url = "";
      $.each($('#site-languages').find('a'), function() {
        url = $(this).attr('href').split('#')[0];
        $(this).attr('href', url + '#' + id + '=' + idx);
      });
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
    
    unusedVariables: function() {
      return;
    },
    
    appendImages: function() {
      this.vars.mapOutput.append('<img id="mapOutputImage" src="public/images/basemap.png" alt="" width="900" height="450" />');
      this.vars.mapOutputImage = $('#mapOutputImage');
      $('#mapScale').append('<img id="mapOutputScale" src="public/images/basemap-scalebar.png" width="200" height="27" />');
    },
    
    bindAccordions: function() {
      $.each([this.vars.fieldSetsPoints, this.vars.fieldSetsRegions], function() {
        $(this).accordion({header : 'h3', collapsible : true, autoHeight : false});
      });
    },

    bindTextAreaResizers: function() {
      $.each([this.vars.fieldSetsPoints, this.vars.fieldSetsRegions], function() {
        $(this).find('textarea.resizable:not(.textarea-processed)').TextAreaResizer();
      });
    },

    disableDefaultButtons: function() {
      $("input").on('keypress', function(e) {
        var key = e.keyCode || e.which;
        if(key === 13 || key === 9) { return false; }
      });
    },

    bindSpecialClicks: function() {
      var self = this;
      $('#site-session').find('a.login').on('click', function(e) { e.preventDefault(); self.tabSelector(3); });
      $('#general-points').find('a.show-examples').on('click', function(e) { e.preventDefault(); self.showExamples(); });
      $('#regions-introduction').find('a.show-codes').on('click', function(e) { e.preventDefault(); self.showCodes(); });
      $('#actionsBar')
        .find('a.toolsUndoDisabled').off('click').end()
        .find('a.toolsRedoDisabled').off('click');
    },
    
    bindTooltips: function() {
      $('#mapWrapper').find('a.tooltip').tipsy({gravity : 's'});
    },

    getUserData: function() {
      if($('#usermaps').length > 0) {
        this.loadMapList();
        this.tabSelector(3);
      }
      if($('#userdata').length > 0) {
        this.loadUserList();
        this.tabSelector(4);
      }
    },

    init: function() {
      var self = this;
      console.time("init");
      this.disableDefaultButtons();
      this.screenSizeListener();
      this.bindRotateWheel();
      this.hideSpinner();
      $('#header').find('div').show();
      this.bindTooltips();
      this.bindTabs();
      this.appendImages();
      this.bindSpecialClicks();
      this.bindAccordions();
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
      this.bindSubmit();
      this.bindPanelToggle();
      this.bindTextAreaResizers();
      this.getUserData();
      console.timeEnd("init");
    }

  };

  return {
    init: function(args) {
      $.extend(_private.settings, args);
      _private.init();
    },
    loadCodes: function(elem,data) {
      _private.loadCodes(elem,data);
    }
  };

}(jQuery, window, document));