/*global $, window, document, self, XMLHttpRequest, setTimeout, Raphael */

var Mapper = Mapper || { 'settings': {} };

$(function () {

    "use strict";

    Mapper.vars = {
      newPointCount      : 0,
      newRegionCount     : 0,
      newFreehandCount   : 0,
      maxTextareaCount   : 10,
      zoom               : true,
      fileDownloadTimer  : {}
    };

  $.ajaxSetup({
    xhr:function () { return new XMLHttpRequest(); }
  });

  $(window).resize(function () {
    var arrPageSizes = Mapper.getPageSize(),
        arrPageScroll = Mapper.getPageScroll();

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

  Mapper.getPageSize = function () {
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

  }; /** end Mapper.getPageSize **/


  Mapper.getPageScroll = function () {
    var xScroll, yScroll;

    if (self.pageYOffset) {
      yScroll = self.pageYOffset;
      xScroll = self.pageXOffset;
    } else if (document.documentElement && document.documentElement.scrollTop) {     // Explorer 6 Strict
      yScroll = document.documentElement.scrollTop;
      xScroll = document.documentElement.scrollLeft;
    } else if (document.body) {// all other Explorers
      yScroll = document.body.scrollTop;
      xScroll = document.body.scrollLeft; 
    }

    return [xScroll,yScroll];

  }; /** end Mapper.getPageScroll **/

  Mapper.showCoords = function (c) {
    var x = parseInt(c.x, 10),
        y = parseInt(c.y, 10),
       x2 = parseInt(c.x2, 10),
       y2 = parseInt(c.y2, 10);
  
    $('.jcrop-holder div:first').css('backgroundColor', 'white');
    $('#bbox_rubberband').val(x+','+y+','+x2+','+y2);
  };

  Mapper.showCoordsQuery = function (c) {
    var x = parseInt(c.x, 10),
        y = parseInt(c.y, 10),
       x2 = parseInt(c.x2, 10),
       y2 = parseInt(c.y2, 10);

    $('#bbox_query').val(x+','+y+','+x2+','+y2);
  };

  Mapper.tabSelector = function (tab) {
    $("#tabs").tabs('select',tab);
  };

  Mapper.RGBtoHex = function (R,G,B) {
    return this.toHex(R)+this.toHex(G)+this.toHex(B);
  };

  Mapper.toHex = function (N) {
    if (N === null) { return "00"; }
    N = parseInt(N, 10);
    if (N === 0 || isNaN(N)) { return "00"; }
    N = Math.max(0,N);
    N = Math.min(N,255);
    N = Math.round(N);
    return "0123456789ABCDEF".charAt((N-N%16)/16) + "0123456789ABCDEF".charAt(N%16);
  };

  Mapper.bindToolbar = function () {
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
      $('#mapCropMessage').hide();
      if($('#mapCropMessage').is(':hidden')) {
        self.initJzoom();
        self.vars.zoom = true;    
      }
      return false;   
    });

    $('.toolsZoomOut').click(function () {
      $('#mapCropMessage').hide();
      $('#zoom_out').val(1);
      self.showMap();
      $('#zoom_out').val('');
      return false;   
    });

    $('.toolsRotate').click(function () {
      $('#rotation').val(parseInt($('#rendered_rotation').val(), 10)+parseInt($(this).attr("data-rotate"), 10));
      self.showMap();
      return false;
    });
                    
    $('.toolsCrop').click(function () {
      if($('#mapCropMessage').is(':hidden')) {
        self.initJcrop();
        self.vars.zoom = false;
        $('#mapCropMessage').show();    
      }
      return false;   
    });

    $('.toolsQuery').click(function () {
      $('#mapCropMessage').hide();
      self.initJquery();
      self.vars.zoom = false;
      return false;
    });

    $('.toolsDraw').click(function () {
      $('mapCropMessage').hide();
      self.initDraw();
      self.vars.zoom = false;
      return false;
    });

    $('.toolsRefresh').click(function () {
      self.showMap();
      return false; 
    });

    $('.toolsRebuild').click(function () {
      $('#bbox_map').val('');
      $('#projection_map').val('');
      $('#bbox_rubberband').val('');
      $('#rotation').val('');
      $('#projection').val('');
      $('#pan').val('');
      self.showMap();
      return false;  
    });

  }; /** end Mapper.bindToolbar **/

  Mapper.bindArrows = function () {
    var self = this;

    $('.arrows').click(function () {
      $('#pan').val($(this).attr("data-pan"));
      self.showMap();
      return false;   
    });
  };

  Mapper.bindSettings = function () {
    var self = this;

    $('.layeropt').click(function () {
      self.showMap();    
    });
 
    $('#projection').change(function () {
      if($(this).val() !== "") { self.showMap(); }
    });
  };

  Mapper.bindColorPickers = function () {
    $('.colorPicker').ColorPicker({
      onSubmit: function (hsb, hex, rgb, el) {
        hsb = null;
        hex = null;
        $(el).val(rgb.r + ' ' + rgb.g + ' ' + rgb.b);
        $(el).ColorPickerHide();
      },
      onBeforeShow: function () {
        $(this).ColorPickerSetColor(this.value);
      }
    }).bind('keyup', function () {
      $(this).ColorPickerSetColor(this.value);
    });
  };

  Mapper.bindClearButtons = function () {
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

  }; /** end Mapper.bindClearButtons **/

  Mapper.destroyJcrop = function () {
    var vars = this.vars;

    if(typeof vars.jzoomAPI !== "undefined") { vars.jzoomAPI.destroy(); }
    if(typeof vars.jcropAPI !== "undefined") { vars.jcropAPI.destroy(); }
    if(typeof vars.jqueryAPI !== "undefined") { vars.jqueryAPI.destroy(); }
  };

  Mapper.initJcrop = function () {
    var self = this, vars = this.vars;

    self.destroyJcrop();
    
    vars.jcropAPI = $.Jcrop('#mapOutput img', {
      bgColor:'grey',
      bgOpacity:1,
      onChange: self.showCoords,
      onSelect: self.showCoords
    });
    
    $('.jcrop-tracker').unbind('mouseup', self.aZoom );
  };

  Mapper.initJzoom = function () {
    var self = this, vars = this.vars;

    self.destroyJcrop();
    
    vars.jzoomAPI = $.Jcrop('#mapOutput img', {
      addClass      : "customJzoom",
      sideHandles   : false,
      cornerHandles : false,
      dragEdges     : false,
      bgOpacity     : 1,
      bgColor       : "white",
      onChange      : self.showCoords,
      onSelect      : self.showCoords
    });

    $('.jcrop-tracker').bind('mouseup', self.aZoom );
  };

  Mapper.initJquery = function () {
    var self = this, vars = this.vars;

    self.destroyJcrop();

    vars.jqueryAPI = $.Jcrop('#mapOutput img', {
      addClass      : "customJzoom",
      sideHandles   : false,
      cornerHandles : false,
      dragEdges     : false,
      bgOpacity     : 1,
      bgColor       :'white',
      onChange      : self.showCoordsQuery,
      onSelect      : self.showCoordsQuery
    });

    $('.jcrop-tracker').bind('mouseup', self.aQuery );
  };

  Mapper.initDraw = function () {
    var self = this, raphael = this.raphaelConfig;

    self.destroyJcrop();

    $('#mapOutput').mousedown(function (e) {
      var pos     = raphael.position(e),
          color = $('input[name="freehand[0][color]"]').val();

      color = color.split(" ");
      raphael.path = [['M', pos.x, pos.y]];
      raphael.wkt = [[pos.x + " " + pos.y]];
      raphael.color = "#" + self.RGBtoHex(color[0], color[1], color[2]);
      raphael.size = raphael.selectedSize;
      raphael.line = raphael.draw(self.path, self.color, self.size);
      $('#mapOutput').bind('mousemove', raphael.mouseMove);
    });

    $('#mapOutput').mouseup(function () {
      var wkt = "";

      $('#mapOutput').unbind('mousemove', raphael.mouseMove);
      $('input[name="freehand[0][title]"]').val("Freehand Drawing");

      $.ajax({
        url     : self.settings.baseUrl + '/query/',
        type    : 'POST',
        data    : { freehand : raphael.wkt },
        async   : false,
        success : function (results) {
          if(!results) { return; } 
          switch(raphael.selectedTool) {
            case 'pencil':
              wkt = "LINESTRING(" + results + ")";
            break;
            case 'rectangle':
              wkt = "POLYGON((" + results + "))";
            break;
            case 'circle':
            break;
            case 'line':
              wkt = "LINESTRING(" + results + ")";  
            break;
          }
          $('textarea[name="freehand[0][data]"]').val(wkt);
        },
        error : function () { return false; }
      });

    });

  };  /** end Mapper.initDraw **/
    
  Mapper.aZoom = function () {
    Mapper.showMap();
  };

  Mapper.aQuery = function () {
  
    var i = 0, formData = {
      bbox           : $('#rendered_bbox').val(), 
      bbox_query     : $('#bbox_query').val(), 
      projection     : $('#projection').val(), 
      projection_map : $('#projection_map').val(),
      qlayer         : ($('#stateprovince').is(':checked')) ? 'stateprovinces_polygon' : 'base' 
    };

    Mapper.destroyJcrop();

    $.post(Mapper.settings.baseUrl + "/query/", formData, function (data) {
      if(data.length > 0) {
        var regions = "",
            region_title = $('input[name="regions[0][title]"]'),
            region_data = $('textarea[name="regions[0][data]"]');

        region_data.val("");
        if(region_title.val() === "") { region_title.val("Selected Regions"); }
        for(i = 0; i < data.length; i += 1) {
            regions += data[i];
            if(i < data.length-1) { regions += ", "; }
        }
        region_data.val(regions);
        Mapper.showMap();
      }
    });

  }; /** end Mapper.aQuery **/

  Mapper.textareaCounter = function (type, action) {
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

  }; /** end Mapper.textareaCounter **/

  Mapper.addAccordionPanel = function (data_type) {
    var self    = this,
        counter = self.textareaCounter(data_type, 'get'),
        button  = $(".addmore[data-type='" + data_type + "']"),
        clone   = {},
        color   = (data_type === 'coords') ? "0 0 0" : "150 150 150",
        num     = 0;

    if(button.attr("data-type") === data_type) {
      clone = button.parent().prev().children("div:last").clone();

      num = parseInt($(clone).find("h3 a").text().split(" ")[1],10);

      if(counter < self.vars.maxTextareaCount) {
        counter = self.textareaCounter(data_type, 'increase');

        $(clone).find("h3 a").text($(clone).find("h3 a").text().split(" ")[0] + " " + (num+1).toString());
        $(clone).find("input.m-mapTitle").attr("name", data_type + "["+num.toString()+"][title]").val("");
        $(clone).find("textarea")
                .attr("name", data_type + "["+num.toString()+"][data]")
                .removeClass("textarea-processed")
                .val("")
                .each(function () {
                  self.addGrippies(this);
                });
        $(clone).find("select.m-mapShape").attr("name", data_type + "["+num.toString()+"][shape]").val("circle");
        $(clone).find("select.m-mapSize").attr("name", data_type + "["+num.toString()+"][size]").val("10");
        $(clone).find("input.colorPicker").attr("name", data_type + "["+num.toString()+"][color]").ColorPicker({
          onSubmit: function (hsb, hex, rgb, el) {
            hsb = null;
            hex = null;
            $(el).val(rgb.r + " " + rgb.g + " " + rgb.b);
            $(el).ColorPickerHide();
          },
          onBeforeShow: function () {
            $(this).ColorPickerSetColor(this.value);
          }
        }).bind('keyup', function () {
            $(this).ColorPickerSetColor(this.value);
        }).val(color);

        $(button).parent().prev().append(clone).children("div:last").accordion({
          header      : 'h3',
          collapsible : true,
          autoHeight  : false,
          active      : true 
        }).find("button.removemore").show().click(function () {
          $(clone).remove();
          counter = self.textareaCounter(data_type, 'decrease');
          $(button).removeAttr("disabled");
          return false;
        }); 

      }

      if(counter >= self.vars.maxTextareaCount-4) {
        $(button).attr("disabled","disabled");
      }
    }

  }; /** end Mapper.addAccordionPanel **/

  Mapper.addGrippies = function (obj) {
    var textarea     = $(obj).addClass("textarea-processed"),
        staticOffset = null,
        grippie      = $("div.grippie", $(obj).parent())[0];

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
    grippie.style.marginRight = (parseInt(grippie.offsetWidth,10)-parseInt($(this)[0].offsetWidth,10)).toString() + "px";
  };

  Mapper.bindAddButtons = function () {
    var self = this;

    $('.addmore').click(function () {
      var data_type = $(this).attr("data-type");

      self.addAccordionPanel(data_type);
      return false;
    });

  }; /** end Mapper.bindAddButtons **/

  Mapper.loadMapList = function () {
    var self      = this,
        message   = '<div id="usermaps-loading"><span id="mapper-building-map">Loading your maps...</span></div>';

    $('#usermaps').html(message);
    
    $.get(self.settings.baseUrl + "/usermaps/?action=list", {}, function (data) {
      $('#usermaps').html(data);

      $('.map-load').click(function () {
        self.loadMap(this);
        return false;
      }); 

      $('.map-delete').click(function () {
        self.deleteConfirmation(this);
        return false;
      });

    }, "html");
  };

  Mapper.removeExtraElements = function () {
    var self = this,
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

  Mapper.loadMap = function (obj) {
    var self = this,
        id   = $(obj).attr("data-mid");

    $.get(self.settings.baseUrl + "/usermaps/?action=load&map=" + id, {}, function (data) {

      self.removeExtraElements();
      $('#form-mapper').clearForm();

      self.loadSettings(data);
      self.activateEmbed(id);
      self.loadCoordinates(data);
      self.loadRegions(data);
      self.loadFreehands(data);
      self.loadLayers(data);
      self.showMap();

      $("#tabs").tabs('select',0);

    }, "json");

  };

  Mapper.loadSettings = function (data) {
    var pattern   = /[?*:;{}\\ "']+/g,
        map_title = "",
        i         = 0,
        keyMap    = [],
        key       = "";

    $("#projection").val(data.map.projection);
    $('input[name="bbox_map"]').val(data.map.bbox_map);
    $('input[name="projection_map"]').val(data.map.projection_map);
    $('input[name="rotation"]').val(data.map.rotation);
    if(data.map.download_factor) {
      $('input[name="download_factor"]').val(data.map.download_factor);
      $('#download-factor').val(data.map.download_factor);
    } else {
      $('input[name="download_factor"]').val("");
      $('#download-factor')[0].selectedIndex = 0;
    }

    map_title = data.map.save.title;

    $('input[name="save[title]"]').val(map_title);
    $('.m-mapSaveTitle').val(map_title);

    $('#mapTitle').text(map_title);

    map_title = map_title.replace(pattern, "_");
    $('#file-name').val(map_title);

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

  }; //** end Mapper.loadSettings **/

  Mapper.loadCoordinates = function (data) {
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

  Mapper.loadRegions = function (data) { 
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

  Mapper.loadFreehands = function (data) {
    var self           = this,
        i              = 0,
        freehands      = data.map.freehand || [],
        freehand_title = "",
        freehand_data  = "",
        freehand_color = "";

    for(i = 0; i < freehands.length; i += 1) {
      if(i > 2) {
        self.addAccordionPanel('freehands');
      }

      freehand_title = freehands[i].title || "";
      freehand_data  = freehands[i].data  || "";
      freehand_color = freehands[i].color || "150 150 150";

      $('input[name="freehand['+i.toString()+'][title]"]').val(freehand_title);
      $('textarea[name="freehand['+i.toString()+'][data]"]').val(freehand_data);
      $('input[name="freehand['+i.toString()+'][color]"]').val(freehand_color);         
    }
  };

  Mapper.loadLayers = function (data) {
    var i = 0, keyMap = [], key = 0;

    $('#border').attr('checked', false);
    $('#legend').attr('checked', false);
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

  Mapper.activateEmbed = function (mid) {
    var self    = this,
        message = '';

    $('.map-embed').attr("data-mid", mid).click(function () {
      message = 'Use the following HTML snippet to embed a png:';
      message += "<p><input type='text' size='65' value='&lt;img src=\"" + self.settings.baseUrl + "/?map=" + mid + "\" alt=\"\" /&gt;'></input></p>";
      message += "<strong>Additional parameters</strong>:<span class=\"indent\">width, height (<em>e.g.</em> ?map=" + mid + "&amp;width=200&amp;height=150)</span>";

      if($('body').find('#mapper-message').length > 0) {
        $('#mapper-message').html(message).dialog("open");
      } else {
        $('body').append('<div id="mapper-message" class="ui-state-highlight" title="Embed Map">' + message + '</div>');
    
        $('#mapper-message').dialog({
          height        : (250).toString(),
          width         : (525).toString(),
          autoOpen      : true,
          modal         : true,
          closeOnEscape : false,
          draggable     : false,
          resizable     : false,
          buttons       : {
            Cancel: function () {
              $(this).dialog("destroy").remove();
            }
          }
        });
      }

      return false;
    }).show();

  };

  Mapper.deleteConfirmation = function (obj) {
    var self    = this,
        id      = $(obj).attr("data-mid"),
        message = 'Are you sure you want to delete<p><em>' + $(obj).parent().parent().find(".title").html() + '</em>?</p>';

    $('body').append('<div id="mapper-message" class="ui-state-highlight" title="Delete Map">' + message + '</div>');
    
    $('#mapper-message').dialog({
      height        : (250).toString(),
      width         : (500).toString(),
      modal         : true,
      closeOnEscape : false,
      draggable     : false,
      resizable     : false,
      buttons       : {
        "Delete" : function () {
          $.get(self.settings.baseUrl + "/usermaps/?action=delete&map="+id, {}, function () {
            self.loadMapList();
          }, "json");
          $(this).dialog("destroy").remove();
        },
        Cancel: function () {
          $(this).dialog("destroy").remove();
        }
      }
    });

  };

  Mapper.loadUsers = function () {
    var message = '<div id="users-loading"><span id="mapper-building-users">Loading users list...</span></div>';

    $('#userdata').html(message);
    $.get(Mapper.settings.baseUrl + "/usermaps/?action=users", {}, function (data) {
      $('#userdata').html(data);
    }, "html");
  };

  Mapper.bindSave = function () {
    var self = this;

    $(".map-save").click(function () {
      var missingTitle = false;

      $('#mapSave').dialog({
        autoOpen      : true,
        height        : (200).toString(),
        width         : (500).toString(),
        modal         : true,
        closeOnEscape : false,
        draggable     : false,
        resizable     : false,
        buttons       : {
          "Save" : function () {

            if($.trim($('.m-mapSaveTitle').val()) === '') { missingTitle = true; }

            if(missingTitle) {
              $('.m-mapSaveTitle').css({'background-color':'#FFB6C1'}).keyup(function () {
                $(this).css({'background-color':'transparent'});
              });
            } else {
              $('input[name="save[title]"]').val($('.m-mapSaveTitle').val());
              $('input[name="download_factor"]').val($('#download-factor').val());
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

              $.post(self.settings.baseUrl + "/usermaps/?action=save", $("form").serialize(), function (data) {
                $('#mapTitle').text($('.m-mapSaveTitle').val());
                self.activateEmbed(data.mid);
                self.loadMapList();
              }, 'json');
              $(this).dialog("destroy");
            }
          },
          Cancel: function () {
            $(this).dialog("destroy");
          }
        }
      });

      return false;
    });

  }; /** end Mapper.bindSave **/

  Mapper.bindDownload = function () {
    var self = this;

    $('#mapExport a.export').click(function () {
      self.generateDownload($(this).attr("data-export"));
      return false; 
    });

    $(".map-download").click(function () {
      $('#mapExport').dialog({
        autoOpen      : true,
        width         : (500).toString(),
        modal         : true,
        closeOnEscape : false,
        draggable     : false,
        resizable     : false,
        buttons       : {
          Cancel : function () {
            $(this).dialog("destroy");
          } 
        }
      });

      return false;
    });
  };

  Mapper.bindSubmit = function () {
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

  Mapper.showMessage = function (message) {

    if($('#mapper-message').length === 0) {
      $('body').append('<div id="mapper-message" class="ui-state-error" title="Warning"></div>');
    }
    $('#mapper-message').html(message).dialog({
      autoOpen      : true,
      height        : (200).toString(),
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

  Mapper.drawLegend = function () {
    var legend_url = $('#legend_url').val();

    if(legend_url) {
      $('#mapLegend').html("<img src=\"" + legend_url + "\" />");
    } else {
      $('#mapLegend').html('<p><em>legend will appear here</em></p>');
    }
  };

  Mapper.drawScalebar = function () {
    var scalebar_url = $('#scalebar_url').val();

    if(scalebar_url) {
      $('#mapScale').html('<img src="' + scalebar_url + '" />');    
    } else {
      $('#mapScale').html('');
    }
  };

  Mapper.showBadPoints = function () {
    var bad_points = $('#bad_points').val();

    if(bad_points) {
      $('#badRecords').html(bad_points);
      $('#badRecordsWarning').show();
    }
  };

  Mapper.showMap = function () {
    var self         = this,
        token        = new Date().getTime(),
        formData     = {},
        message      = '<span id="mapper-building-map">Building preview...</span>',
        toolsTabs    = $('#mapTools').tabs(),
        tabIndex     = ($('#selectedtab').val()) ? parseInt($('#selectedtab').val(), 10) : 0;

    self.destroyJcrop();

    $('#output').val('pnga');        // set the preview and output values
    $('#badRecordsWarning').hide();  // hide the bad records warning
    $('#download_token').val(token); // set a token to be used for cookie
  
    formData = $("form").serialize();

    $('#mapOutput').html(message);
    $('#mapScale').html('');

    $.post(Mapper.settings.baseUrl + "/application/", formData, function (data) {
      $('#mapOutput').html(data);

      self.drawLegend();
      self.drawScalebar();
      self.showBadPoints();

      toolsTabs.tabs('select', tabIndex);
      
      $('#mapTools').bind('tabsselect', function (event,ui) {
        event = null;
        $('#selectedtab').val(ui.index);
      });

      $('#bbox_rubberband').val('');                             // reset bounding box values, but get first for the crop function
      $('#bbox_map').val($('#rendered_bbox').val());             // set extent from previous rendering
      $('#projection_map').val($('#rendered_projection').val()); // set projection from the previous rendering
      $('#rotation').val($('#rendered_rotation').val());         // reset rotation value
      $('#pan').val('');                                         // reset pan value

      self.addBadRecordsViewer();
                          
      $('.toolsBadRecords').click(function () {
        $('#badRecordsViewer').dialog("open");
        return false; 
      });
      
    }, "html");

  }; /** end Mapper.showMap **/

  Mapper.addBadRecordsViewer = function () {
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

  Mapper.generateDownload = function (filetype) {
    var self        = this,
        pattern     = /[?*:;{}\\ "'\/@#!%\^()<>.]+/g,
        map_title   = $('#file-name').val(),
        token       = new Date().getTime().toString(),
        cookieValue = "",
        formData    = "";
      
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

    $('input[name="download_factor"]').val($('#download-factor').val());

    map_title = map_title.replace(pattern, "_");
    $('#file-name').val(map_title);

    $('input[name="file_name"]').val(map_title);

    $('#download_token').val(token);
    
    switch(filetype) {
      case 'kml':
        formData = $("form").serialize();
        $.download(self.settings.baseUrl + "/application/kml/", formData, 'post');
      break;
  
      default:
        $('#download').val(1);
        $('#output').val(filetype);
        if(self.vars.jcropAPI) { $('#crop').val(1); }
        formData = $("form").serialize();
        $('.download-dialog').hide();
        $('.download-message').show();
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

  }; /** end Mapper.generateDownload **/

  Mapper.finishDownload = function () {
    $('.download-message').hide();
    $('.download-dialog').show();
    window.clearInterval(this.vars.fileDownloadTimer);
    $.cookie('fileDownloadToken', null); //clears this cookie value
  };

  /************************************ 
  ** RAPHAEL: FREEHAND DRAWING TOOLS **
  ************************************/
  Mapper.raphaelConfig = {
    board         : new Raphael('mapOutput', 800, 400),
    line          : null,
    path          : null,
    wkt           : null,
    color         : null,
    size          : null,
    selectedColor : 'mosaic',
    selectedSize  : 4,
    selectedTool  : 'pencil',
    offset        : $('#mapOutput').offset()
  };  

  Mapper.raphaelConfig.position = function (e) {
    return {
      x: (parseInt(e.pageX,10)-parseInt(this.offset.left,10)).toString(),
      y: (parseInt(e.pageY,10)-parseInt(this.offset.top,10)).toString()
    };
  };

  Mapper.raphaelConfig.mouseMove = function (e) {
    var self = Mapper.raphaelConfig,
        pos  = self.position(e),
        x    = self.path[0][1],
        y    = self.path[0][2],
        dx   = (pos.x - x),
        dy   = (pos.y - y);

    switch(self.selectedTool) {
      case 'pencil':
        self.path.push(['L', pos.x, pos.y]);
        self.wkt.push([pos.x + " " + pos.y]);
        break;
      case 'rectangle':
        self.path[1] = ['L', x + dx, y     ];
        self.path[2] = ['L', x + dx, y + dy];
        self.path[3] = ['L', x     , y + dy];
        self.path[4] = ['L', x     , y     ];
        self.path[5] = ['L', x,      y     ];
        break;
      case 'line':
        self.path[1] = ['L', pos.x, pos.y];
        self.wkt[1] = [pos.x + " " + pos.y];
        break;                        
      case 'circle':
        self.path[1] = ['A', (dx / 2), (dy / 2), 0, 1, 0, pos.x, pos.y];
        self.path[2] = ['A', (dx / 2), (dy / 2), 0, 0, 0, x, y];
        break;
    }
    self.line.attr({ path: self.path });

  }; /** end Mapper.raphaelConfig.mouseMove **/

  Mapper.raphaelConfig.forcePaint = function () {
    var self = Mapper.raphaelConfig;
    window.setTimeout(function () {
      var rect = self.board.rect(-99, -99, parseInt(self.board.width,10) + 99, parseInt(self.board.height,10) + 99).attr({stroke: "none"});
      setTimeout(function () { rect.remove(); });
    },1);
  };

  Mapper.raphaelConfig.draw = function (path, color, size) {
    var self   = Mapper.raphaelConfig,
        result = self.board.path(path);

    result.attr({ stroke: color, 'stroke-width': size, 'stroke-linecap': 'round' });
    self.forcePaint();
    return result;
  };

  Mapper.init = function () {
    $('#initial-message').hide();
    $("#tabs").tabs().show();
    $('#mapTools').tabs();
    $('.fieldSets').accordion({
      header : 'h3',
      collapsible : true,
      autoHeight : false 
    });                
    $(".tooltip").tipsy({gravity: 's'});
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
      this.loadUsers();
    }
  };

  Mapper.init();  

});

/******* jQUERY EXTENSIONS *******/

(function ($) {
  $.fn.clearForm = function () {
    "use strict";
    return this.each(function () {
      var type = this.type, tag = this.tagName.toLowerCase();
      if (tag === 'form') {
        return $(':input',this).clearForm();
      }
      if (type === 'text' || type === 'password' || tag === 'textarea') {
        this.value = '';
      } else if (type === 'checkbox' || type === 'radio') {
       this.checked = false;
      } else if (tag === 'select') {
       this.selectedIndex = 0;
      }
    });
  };
})(jQuery);