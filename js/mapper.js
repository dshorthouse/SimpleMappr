var Mapper = Mapper || { 'settings': {} };

$(function(){

    Mapper.vars = {
      displaySubmit      : false,
      addMorebtn         : $('.addmore'),
      newTextareaCount   : 0,
      maxTextareaCount   : 10,
      hiddenControls     : $("#controls .hiddenControls"),
      submitForm         : $(".submitForm"),
      hiddenClass        : "hidden",
      jcropAPI           : {},
      jzoomAPI           : {},
      jqueryAPI          : {},
      zoom               : true,
      fileDownloadTimer  : {},
      downloadDialog     : $('.download-dialog').html(),
      preview            : $('#map-preview')
    };

  $.ajaxSetup({
    xhr:function() { return new XMLHttpRequest(); }
  });

  $(window).resize(function() {
    var arrPageSizes = Mapper.getPageSize(),
        arrPageScroll = Mapper.getPageScroll();

    $('#mapper-overlay').css({
        width:      arrPageSizes[0],
        height:     arrPageSizes[1]
    });

    $('#mapper-message').css({
        top:    arrPageScroll[1] + (arrPageSizes[3] / 10),
        left:   arrPageScroll[0],
        position: 'fixed',
        zIndex: 1001,
        margin: '0px auto',
        width: '100%'
    });
  });

  Mapper.getPageSize = function() {
    var xScroll, yScroll, windowWidth, windowHeight;

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

    if (self.innerHeight) { // all except Explorer
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

    return new Array(pageWidth,pageHeight,windowWidth,windowHeight);

  }; /** end Mapper.getPageSize **/


  Mapper.getPageScroll = function() {
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

    return new Array(xScroll,yScroll);

  }; /** end Mapper.getPageScroll **/

  Mapper.showCoords = function(c) {
    var x = parseInt(c.x),
        y = parseInt(c.y),
       x2 = parseInt(c.x2),
       y2 = parseInt(c.y2);
  
    $('.jcrop-holder div:first').css('backgroundColor', 'white');
    $('#bbox_rubberband').val(x+','+y+','+x2+','+y2);
  };

  Mapper.showCoordsQuery = function(c) {
    var x = parseInt(c.x),
        y = parseInt(c.y),
       x2 = parseInt(c.x2),
       y2 = parseInt(c.y2);

    $('#bbox_query').val(x+','+y+','+x2+','+y2);
  };

  Mapper.tabSelector = function(tab) {
    $("#tabs").tabs('select',tab);
  };

  Mapper.RGBtoHex = function(R,G,B) {
    return this.toHex(R)+this.toHex(G)+this.toHex(B);
  };

  Mapper.toHex = function(N) {
    if (N == null) return "00";
    N = parseInt(N);
    if (N == 0 || isNaN(N)) return "00";
    N = Math.max(0,N);
    N = Math.min(N,255);
    N = Math.round(N);
    return "0123456789ABCDEF".charAt((N-N%16)/16) + "0123456789ABCDEF".charAt(N%16);
  };

  Mapper.bindToolbar = function() {
    var self = this;

    $("ul.dropdown li").hover(function(){
      $(this).addClass("ui-state-hover");
      $('ul:first',this).css('visibility', 'visible');
    }, function(){ 
      $(this).removeClass("ui-state-hover");
      $('ul:first',this).css('visibility', 'hidden');
    });

    $("ul.dropdown li ul li:has(ul)").find("a:first").append(" &raquo; ");

    $('.toolsZoomIn').click(function(){
      $('#mapCropMessage').hide();
      if($('#mapCropMessage').is(':hidden')) {
        self.initJzoom();
        self.vars.zoom = true;    
      }
      return false;   
    });

    $('.toolsZoomOut').click(function(){
      $('#mapCropMessage').hide();
      $('#zoom_out').val(1);
      self.showMap();
      $('#zoom_out').val('');
      return false;   
    });

    $('.toolsRotateAC5').click(function(){
      $('#rotation').val(parseInt($('#rendered_rotation').val())-5);
      self.showMap();
      return false;   
    });

    $('.toolsRotateAC10').click(function(){
      $('#rotation').val(parseInt($('#rendered_rotation').val())-10);
      self.showMap();
      return false;   
    });

    $('.toolsRotateAC15').click(function(){
      $('#rotation').val(parseInt($('#rendered_rotation').val())-15);
      self.showMap();
      return false;   
    });

    $('.toolsRotateC5').click(function(){
      $('#rotation').val(parseInt($('#rendered_rotation').val())+5);
      self.showMap();
      return false;   
    });

    $('.toolsRotateC10').click(function(){
      $('#rotation').val(parseInt($('#rendered_rotation').val())+10);
      self.showMap();
      return false;   
    });

    $('.toolsRotateC15').click(function(){
      $('#rotation').val(parseInt($('#rendered_rotation').val())+15);
      self.showMap();
      return false;   
    });
                    
    $('.toolsCrop').click(function(){
      if($('#mapCropMessage').is(':hidden')){
        self.initJcrop();
        self.vars.zoom = false;
        $('#mapCropMessage').show();    
      }
      return false;   
    });

    $('.toolsQuery').click(function() {
      $('#mapCropMessage').hide();
      self.initJquery();
      self.vars.zoom = false;
      return false;
    });

    $('.toolsDraw').click(function() {
      $('mapCropMessage').hide();
      self.initDraw();
      self.vars.zoom = false;
      return false;
    });

    $('.toolsRefresh').click(function(){
      self.showMap();  
    });

    $('.toolsRebuild').click(function(){
      $('#bbox_map').val('');
      $('#projection_map').val('');
      $('#bbox_rubberband').val('');
      $('#rotation').val('');
      $('#projection').val('');
      $('#pan').val('');
      self.showMap();  
    });

  }; /** end Mapper.bindToolbar **/

  Mapper.bindArrows = function() {
    $('#arrow-up').click(function() {
      $('#pan').val('up');
      this.showMap();
      return false;   
    });

    $('#arrow-right').click(function() {
      $('#pan').val('right');
      this.showMap();
      return false;
    });

    $('#arrow-down').click(function() {
      $('#pan').val('down');
      this.showMap();
      return false;
    });

    $('#arrow-left').click(function() {
      $('#pan').val('left');
      this.showMap();
      return false;   
    });

  }; /** end Mapper.bindArrows **/

  Mapper.bindSettings = function() {
    $('.layeropt').click(function() {
      this.showMap();    
    });
 
    $('#projection').change(function() {
      if($(this).val() !== "") { this.showMap(); }
    });
  };

  Mapper.bindColorPickers = function() {
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
  };

  Mapper.bindClearButtons = function() {
    $('.clearLayers, .clearRegions, .clearFreehand').click(function() {
      var fieldsets = $(this).parent().prev().prev().children();

      $(fieldsets).find('.m-mapTitle').val('');
      $(fieldsets).find('textarea').val('');
      $(fieldsets).find('.m-mapShape')[0].selectedIndex = 3;
      $(fieldsets).find('.m-mapSize')[0].selectedIndex = 3;
      $(fieldsets).find('.colorPicker').val('0 0 0');

      return false;
    });

  }; /** end Mapper.bindClearButtons **/

  Mapper.destroyJcrop = function() {
    var vars = this.vars;

    if(typeof vars.jzoom_api != "undefined") { vars.jzoom_api.destroy(); }
    if(typeof vars.jcrop_api != "undefined") { vars.jcrop_api.destroy(); }
    if(typeof vars.jquery_api != "undefined") { vars.jquery_api.destroy(); }
  };

  Mapper.initJcrop = function(){
    var self = this, vars = this.vars;

    self.destroyJcrop();
    
    vars.jcrop_api = $.Jcrop('#mapOutput img', {
      bgColor:'grey',
      bgOpacity:1,
      onChange: self.showCoords,
      onSelect: self.showCoords
    });
    
    $('.jcrop-tracker').unbind('mouseup', self.azoom );
  };

  Mapper.initJzoom = function(){
    var self = this, vars = this.vars;

    self.destroyJcrop();
    
    vars.jzoom_api = $.Jcrop('#mapOutput img', {
      addClass: 'customJzoom',
      sideHandles: false,
      cornerHandles: false,
      dragEdges: false,
      bgOpacity: 1,
      bgColor:'white',
      onChange: self.showCoords,
      onSelect: self.showCoords
    });

    $('.jcrop-tracker').bind('mouseup', self.azoom );
  };

  Mapper.initJquery = function(){
    var self = this, vars = this.vars;

    self.destroyJcrop();

    vars.jquery_api = $.Jcrop('#mapOutput img', {
      addClass: 'customJzoom',
      sideHandles: false,
      cornerHandles: false,
      dragEdges: false,
      bgOpacity: 1,
      bgColor:'white',
      onChange: self.showCoordsQuery,
      onSelect: self.showCoordsQuery
    });

    $('.jcrop-tracker').bind('mouseup', aQuery );
  };

  Mapper.initDraw = function() {
    var self = this, raphael = this.raphaelConfig;

    self.destroyJcrop();

    $('#mapOutput').mousedown(function(e) {
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

    $('#mapOutput').mouseup(function() {
      var wkt = "";

      $('#mapOutput').unbind('mousemove', raphael.mouseMove);
      $('input[name="freehand[0][title]"]').val("Freehand Drawing");

      $.ajax({
        url     : self.settings.baseUrl + '/query/',
        type    : 'POST',
        data    : { freehand : raphael.wkt },
        async   : false,
        success : function(results) {
          if(!results) return; 
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
        error : function() { return false; }
      });

    });

  };  /** end Mapper.initDraw **/
    
  Mapper.aZoom = function() {
    showMap();
  };

  Mapper.aQuery = function() {
    var self = this;

    self.destroyJcrop();
  
    var formData = {
      bbox           : $('#rendered_bbox').val(), 
      bbox_query     : $('#bbox_query').val(), 
      projection     : $('#projection').val(), 
      projection_map : $('#projection_map').val(),
      qlayer         : ($('#stateprovince').is(':checked')) ? 'stateprovinces_polygon' : 'base' 
    };

    $.post(Mapper.settings.baseUrl + "/query/", formData, function(data) {
      if(data.length > 0) {
        var regions = "",
            region_title = $('input[name="regions[0][title]"]'),
            region_data = $('textarea[name="regions[0][data]"]');

        region_data.val("");
        if(region_title.val() === "") { region_title.val("Selected Regions"); }
        for(var i = 0; i < data.length; i++) {
            regions += data[i];
            if(i < data.length-1) { regions += ", "; }
        }
        region_data.val(regions);
        self.showMap();
      }
    });

  }; /** end Mapper.aQuery **/

  Mapper.bindAddButtons = function() {
    var self = this;

    self.vars.addMorebtn.click(function() {
      if(self.vars.newTextareaCount < self.vars.maxTextareaCount) {
      }
    });
  };

/*
    Mapper.vars.addMorebtn.click(function(){
      if(newPointsCount < maxPoints) {
        newPointsCount++;
        var totalPoints = newPointsCount + 3;
        var inputCounter = totalPoints - 1;
        var lastFieldSet = $('#fieldSetsPoints div.fieldset-points:last').clone();

        $(lastFieldSet).find('h3 a').text("Layer "+totalPoints);
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
        $('#fieldSetsPoints div.fieldset-points:last').accordion({
          header : 'h3',
          collapsible : true,
          autoHeight : false,
          active : false, 
        }); 
        $('#fieldSetsPoints div.fieldset-points:last').accordion("enable");

        return false; //kill the browser default action
      }
    
      //disable button so you know you've reached the max
      if(newPointsCount >= maxPoints-6){
        addMorebtn.attr("disabled","disabled"); //set the "disabled" property on the button
        return false; //kill the browser default action
      }
 
      return false;  //kill the browser default action
    });

        Mapper.vars.addMoreRegionsbtn.click(function(){
          if(newRegionsCount < maxRegions) {
            newRegionsCount++;
            var totalRegions = newRegionsCount + 3;
            var inputCounter = totalRegions - 1;
            var lastFieldSet = $('#fieldSetsRegions div.fieldset-regions:last').clone();
        
            $(lastFieldSet).find('h3 a').text("Region "+totalRegions);
            $(lastFieldSet).find('input.m-mapTitle').attr('name','regions['+inputCounter+'][title]').val('');
            $(lastFieldSet).find('textarea').attr('name','regions['+inputCounter+'][data]').removeClass('textarea-processed').val('');

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

            $(lastFieldSet).find('input.colorPicker').attr('name','regions['+inputCounter+'][color]').ColorPicker({
                onSubmit: function(hsb, hex, rgb, el) {
                    $(el).val(rgb.r + ' ' + rgb.g + ' ' + rgb.b);
                    $(el).ColorPickerHide();
                },
                onBeforeShow: function () {
                    $(this).ColorPickerSetColor(this.value);
                }
            }).bind('keyup', function(){
                $(this).ColorPickerSetColor(this.value);
            }).val('150 150 150');

            $('#fieldSetsRegions').append(lastFieldSet);
        
            $('#fieldSetsRegions div.fieldset-regions:last').accordion({
              header : 'h3',
              collapsible : true,
              autoHeight : false,
              active : false, 
            }); 
            $('#fieldSetsRegions div.fieldset-regions:last').accordion("enable");

            return false; //kill the browser default action
          }

          //disable button so you know you've reached the max
          if(newRegionsCount >= maxRegions-6){
            addMoreRegionsbtn.attr("disabled","disabled"); //set the "disabled" property on the button
            return false; //kill the browser default action
          }

          return false;  //kill the browser default action
        });

                Mapper.vars.addMoreFreehandbtn.click(function(){
                  if(newFreehandCount < maxFreehand) {
                    newFreehandCount++;
                    var totalFreehand = newFreehandCount + 3;
                    var inputCounter = totalFreehand - 1;
                    var lastFieldSet = $('#fieldSetsFreehand div.fieldset-freehand:last').clone();

                    $(lastFieldSet).find('h3 a').text("Region "+totalFreehand);
                    $(lastFieldSet).find('input.m-mapTitle').attr('name','freehand['+inputCounter+'][title]').val('');
                    $(lastFieldSet).find('textarea').attr('name','freehand['+inputCounter+'][data]').removeClass('textarea-processed').val('');

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

                    $(lastFieldSet).find('input.colorPicker').attr('name','freehand['+inputCounter+'][color]').ColorPicker({
                        onSubmit: function(hsb, hex, rgb, el) {
                            $(el).val(rgb.r + ' ' + rgb.g + ' ' + rgb.b);
                            $(el).ColorPickerHide();
                        },
                        onBeforeShow: function () {
                            $(this).ColorPickerSetColor(this.value);
                        }
                    }).bind('keyup', function(){
                        $(this).ColorPickerSetColor(this.value);
                    }).val('150 150 150');

                    $('#fieldSetsFreehand').append(lastFieldSet);

                    $('#fieldSetsFreehand div.fieldset-freehand:last').accordion({
                      header : 'h3',
                      collapsible : true,
                      autoHeight : false,
                      active : false, 
                    }); 
                    $('#fieldSetsFreehand div.fieldset-freehand:last').accordion("enable");

                    return false; //kill the browser default action
                  }

                  //disable button so you know you've reached the max
                  if(newFreehandCount >= maxFreehand-6){
                    addMoreFreehandbtn.attr("disabled","disabled"); //set the "disabled" property on the button
                    return false; //kill the browser default action
                  }

                  return false;  //kill the browser default action
                });
*/

  Mapper.loadMapList = function() {
    var self      = this,
        message   = '<div id="usermaps-loading"><span id="mapper-building-map">Loading your maps...</span></div>';

    $('#usermaps').html(message);
    
    $.get(self.settings.baseUrl + "/usermaps/?action=list", {}, function(data) {
      $('#usermaps').html(data);

      $('.map-load').click(function() {
        self.loadMap(this);
        return false;
      }); 

      $('.map-delete').click(function() {
        self.deleteConfirmation(this);
        return false;
      });

    }, "html");
  };

  Mapper.removeExtraElements = function() {
    var numPoints  = $('.fieldset-points').size(),
        numRegions = $('.fieldset-regions').size();

    if(numPoints > 3) {
      for(i=numPoints-1; i>=3;i--) {
        $('#fieldSetsPoints div.fieldset-points:eq('+i+')').remove();
      }
      self.vars.newPointsCount = 0;
    }

    if(numRegions > 3) {
      for(i=numRegions-1; i>=3;i--) {
        $('#fieldSetsRegions div.fieldset-regions:eq('+i+')').remove();
      }
      self.vars.newRegionsCount = 0;
    }
  };

  Mapper.loadMap = function(obj) {
    var self       = this,
        id         = $(obj).attr("rel");

    $.get(self.settings.baseUrl + "/usermaps/?action=load&map=" + id, {}, function(data) {

      self.removeExtraElements();
      $('#form-mapper').clearForm();

      self.loadSettings(data);
      self.loadCoordinates(data);
      self.loadRegions(data);
      self.loadFreehand(data);
      self.loadLayers(data);
      self.showMap();

      $("#tabs").tabs('select',0);

    }, "json");

  };

  Mapper.loadSettings = function(data) {
    var pattern   = /[?*:;{}\\ "']+/g,
        map_title = "",
        keyMap    = [];

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
    $('.map-embed').attr("rel", data.mid).show();

    map_title = map_title.replace(pattern, "_");
    $('#file-name').val(map_title);

    if(data.map.options !== undefined) {
      for(key in data.map.options){
        keyMap[keyMap.length] = key;
      }
      for(i=0;i<keyMap.length;i++) {
        if(keyMap[i] == 'border') {
          $('#border').attr('checked', true);
          $('input[name="options[border]"]').val(1);
        } else if(keyMap[i] == 'legend') {
          $('#legend').attr('checked', true);
          $('input[name="options[legend]"]').val(1);
        } else {
          $('input[name="options['+keyMap[i]+']"]').attr('checked', true);
        }
      }
    }

  }; //** end Mapper.loadSettings **/

  Mapper.loadCoordinates = function(data) {
                  //load up all the coordinates
              var coords = (data.map.coords !== undefined) ? data.map.coords : [] ;
              for(i=0;i<coords.length;i++) {
                //add the fieldsets in case more than the default three are required
                if(i > 2) {
                    if(newPointsCount < maxPoints) {
                    newPointsCount++;
                    var totalPoints = newPointsCount + 3;
                    var inputCounter = totalPoints - 1;
                    var lastFieldSet = $('#fieldSetsPoints div.fieldset-points:last').clone();

                    $(lastFieldSet).find('h3 a').text("Layer "+totalPoints);
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
                    $('#fieldSetsPoints div.fieldset-points:last').accordion({
                      header : 'h3',
                      collapsible : true,
                      autoHeight : false,
                      active : false, 
                    }); 
                    $('#fieldSetsPoints div.fieldset-points:last').accordion("enable");

                  }

                  //disable button so you know you've reached the max
                  if(newPointsCount >= maxPoints){
                    addMorebtn.attr("disabled","disabled"); //set the "disabled" property on the button
                  }
                }
                
                var coord_title = (coords[i].title) ? coords[i].title : "";
                var coord_data = (coords[i].data) ? coords[i].data : "";
                $('input[name="coords['+i+'][title]"]').val(coord_title);
                $('textarea[name="coords['+i+'][data]"]').val(coord_data);
                
                if(coords[i].shape == "") {
                    $('select[name="coords['+i+'][shape]"]')[0].selectedIndex = 3;
                }
                else {
                    $('select[name="coords['+i+'][shape]"]').val(coords[i].shape);
                }
                
                if(coords[i].size == "") {
                    $('select[name="coords['+i+'][size]"]')[0].selectedIndex = 3;
                }
                else {
                    $('select[name="coords['+i+'][size]"]').val(coords[i].size);
                }
                
                if(coords[i].color == "") {
                    $('input[name="coords['+i+'][color]"]').val('0 0 0');
                }
                else {
                    $('input[name="coords['+i+'][color]"]').val(coords[i].color);
                }
              }
  };

  Mapper.loadRegions = function(data) {
                  //load up all the shaded regions 
              var regions = (data.map.regions !== undefined) ? data.map.regions : [] ;
              for(i=0;i<regions.length;i++) {
                //add the fieldsets in case more than the default three are required
                if(i > 2) {
                    if(newRegionsCount < maxRegions) {
                    newRegionsCount++;
                    var totalRegions = newRegionsCount + 3;
                    var inputCounter = totalRegions - 1;
                    var lastFieldSet = $('#fieldSetsRegions div.fieldset-regions:last').clone();

                    $(lastFieldSet).find('h3 a').text("Region "+totalRegions);
                    $(lastFieldSet).find('input.m-mapTitle').attr('name','regions['+inputCounter+'][title]').val('');
                    $(lastFieldSet).find('textarea').attr('name','regions['+inputCounter+'][data]').removeClass('textarea-processed').val('');

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

                    $(lastFieldSet).find('input.colorPicker').attr('name','regions['+inputCounter+'][color]').ColorPicker({
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

                    $('#fieldSetsRegions').append(lastFieldSet);
                
                    $('#fieldSetsRegions div.fieldset-regions:last').accordion({
                      header : 'h3',
                      collapsible : true,
                      autoHeight : false,
                      active : false, 
                    }); 
                    $('#fieldSetsRegions div.fieldset-regions:last').accordion("enable");

                  }

                  //disable button so you know you've reached the max
                  if(newRegionsCount >= maxRegions){
                    addMorebtn.attr("disabled","disabled"); //set the "disabled" property on the button
                  }
                }
                
                $('input[name="regions['+i+'][title]"]').val(regions[i].title);
                $('textarea[name="regions['+i+'][data]"]').val(regions[i].data);
                
                if(regions[i].color == "") {
                    $('input[name="regions['+i+'][color]"]').val("150 150 150");
                }
                else {
                    $('input[name="regions['+i+'][color]"]').val(regions[i].color);
                }
                
              }
  };

  Mapper.loadFreehand = function(data) {
                  //load up all the well-known text, freehand data
              var freehands = (data.map.freehand !== undefined) ? data.map.freehand : [];
              for(i=0;i<freehands.length;i++) {
                //add the fieldsets in case more than the default three are required
                if(i > 2) {
                    if(newFreehandCount < maxFreehand) {
                    newFreehandCount++;
                    var totalFreehand = newFreehandCount + 3;
                    var inputCounter = totalFreehand - 1;
                    var lastFieldSet = $('#fieldSetsFreehand div.fieldset-freehand:last').clone();

                    $(lastFieldSet).find('h3 a').text("Region "+totalFreehand);
                    $(lastFieldSet).find('input.m-mapTitle').attr('name','freehand['+inputCounter+'][title]').val('');
                    $(lastFieldSet).find('textarea').attr('name','freehand['+inputCounter+'][data]').removeClass('textarea-processed').val('');

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

                    $(lastFieldSet).find('input.colorPicker').attr('name','freehand['+inputCounter+'][color]').ColorPicker({
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

                    $('#fieldSetsFreehand').append(lastFieldSet);
                
                    $('#fieldSetsFreehand div.fieldset-freehand:last').accordion({
                      header : 'h3',
                      collapsible : true,
                      autoHeight : false,
                      active : false, 
                    }); 
                    $('#fieldSetsFreehand div.fieldset-freehand:last').accordion("enable");

                  }

                  //disable button so you know you've reached the max
                  if(newFreehandCount >= maxFreehand){
                    addMorebtn.attr("disabled","disabled"); //set the "disabled" property on the button
                  }
                }
                
                $('input[name="freehand['+i+'][title]"]').val(freehands[i].title);
                $('textarea[name="freehand['+i+'][data]"]').val(freehands[i].data);
                
                if(freehands[i].color == "") {
                    $('input[name="freehand['+i+'][color]"]').val("150 150 150");
                }
                else {
                    $('input[name="freehand['+i+'][color]"]').val(freehands[i].color);
                }
                
              }
  };

  Mapper.loadLayers = function(data) {
    var keyMap = [];

    $('#border').attr('checked', false);
    $('#legend').attr('checked', false);
    $('input[name="options[border]"]').val("");
    $('input[name="options[legend]"]').val("");
    if(data.map.layers) {
      for(key in data.map.layers){
        keyMap[keyMap.length] = key;
      }
      for(i=0;i<keyMap.length;i++) {
        $('input[name="layers['+keyMap[i]+']"]').attr('checked', true);
      }
    }
  };

  Mapper.embedDialog = function(obj) {
    var self    = this,
        message = 'Use the following HTML snippet to embed a png:';

    message += "<p><input type='text' size='65' value='&lt;img src=\"" + self.settings.baseUrl + "/?map=" + $(obj).attr("rel") + "\" alt=\"\" /&gt;'></input></p>";
    message += "<strong>Additional parameters</strong>:<span class=\"indent\">width, height (<em>e.g.</em> ?map=" + $(obj).attr("rel") + "&amp;width=200&amp;height=150)</span>";
    
    $('body').append('<div id="mapper-message" class="ui-state-highlight" title="Embed Map">' + message + '</div>');
    
    $('#mapper-message').dialog({
      height : 250,
      width : 525,
      modal : true,
      buttons: {
        Cancel: function() {
          $(this).dialog("destroy");
        }
      },
      draggable : false,
      resizable : false
    });
  };

  Mapper.deleteConfirmation = function(obj) {
    var self    = this,
        id      = $(obj).attr("rel")
        message = 'Are you sure you want to delete<p><em>' + $(obj).parent().parent().find(".title").html() + '</em>?</p>';

    $('body').append('<div id="mapper-message" class="ui-state-highlight" title="Delete Map">' + message + '</div>');
    
    $('#mapper-message').dialog({
      height : 250,
      width : 500,
      modal : true,
      buttons: {
        "Delete" : function() {
          $.get(self.settings.baseUrl + "/usermaps/?action=delete&map="+id, {}, function(data) {
            self.loadMapList();
          }, "json");
          $(this).dialog("destroy").remove();
        },
        Cancel: function() {
          $(this).dialog("destroy").remove();
        }
      },
      draggable : false,
      resizable : false
    });

  };

  Mapper.loadUsers = function() {
    var message = '<div id="users-loading"><span id="mapper-building-users">Loading users list...</span></div>';

    $('#userdata').html(message);
    $.get(Mapper.settings.baseUrl + "/usermaps/?action=users", {}, function(data) {
      $('#userdata').html(data);
    }, "html");
  };

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//Submit the form
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Mapper.vars.submitForm.click(function() {
    
      //some simple error checking
      var missingTitle = false;
    
      $('.m-mapCoord').each(function() {
        if($(this).val() && $(this).parents().find('.m-mapTitle').val() == '') {
          missingTitle = true;
        }
      });
    
      if(missingTitle) {
        var message = 'You are missing a legend for at least one of your Point Data, Regions, or Freehand layers';
        showMessage(message);   
      }
      else {
        showMap();
        $("#tabs").tabs('select',0);
      }
    
      return false;  //kill the browser default action
    });

  Mapper.bindSave = function() {
    $(".map-save").click(function() {

      var self         = this,
          formData     = $("form").serialize(),
          missingTitle = false

      $('#mapSave').dialog({
        autoOpen : true,
        height : 200,
        width : 500,
        modal : true,
        buttons: {
          "Save" : function() {

            if($.trim($('.m-mapSaveTitle').val()) == '') { missingTitleSave = true; }

            if(missingTitle) {
              $('.m-mapSaveTitle').css({'background-color':'#FFB6C1'}).keyup(function() {
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
            
              $.post(self.settings.baseUrl + "/usermaps/?action=save", formData, function(data) {
                $('#mapTitle').text($('.m-mapSaveTitle').val());
                $('.map-embed').attr("rel", data.mid).show();
                self.loadMapList();
              }, 'json');
              $(this).dialog("destroy");
            }
          },
          Cancel: function() {
            $(this).dialog("destroy");
          }
        },
        draggable : false,
        resizable : false
      });

      return false;
    });

  }; /** end Mapper.bindSave **/

  Mapper.bindDownload = function() {
    $(".map-download").click(function() {
      $('#mapExport').dialog({
        autoOpen : true,
        width    : 500,
        modal    : true,
        buttons  : {
          Cancel : function() {
            $(this).dialog("destroy").remove();
          } 
        },
        draggable : false,
        resizable : false
      });

      return false;
    });
  };

  Mapper.showMessage = function(message) {

    if($('#mapper-message').length === 0) {
      $('body').append('<div id="mapper-message" class="ui-state-error" title="Warning"></div>');
    }
    $('#mapper-message').html(message).dialog({
      autoOpen : true,
      height   : 200,
      modal    : true,
      buttons  : {
        Ok : function() {
          $(this).dialog("destroy");
        }
      },
      draggable : false,
      resizable : false
    });
  };

  Mapper.drawLegend = function() {
    var legend_url = $('#legend_url').val();

    if(legend_url) {
      $('#mapLegend').html("<img src=\"" + legend_url + "\" />");
    } else {
      $('#mapLegend').html('<p><em>legend will appear here</em></p>');
    }
  };

  Mapper.drawScalebar = function() {
    var scalebar_url = $('#scalebar_url').val();

    if(scalebar_url) {
      $('#mapScale').html('<img src="' + scalebar_url + '" />');    
    } else {
      $('#mapScale').html('');
    }
  };

  Mapper.showBadPoints = function() {
    var bad_points = $('#bad_points').val();

    if(bad_points) {
      $('#badRecords').html(bad_points);
      $('#badRecordsWarning').show();
    }
  };

  Mapper.showMap = function() {
    var self         = this,
        token        = new Date().getTime(),
        formData     = {},
        message      = '<span id="mapper-building-map">Building preview...</span>',
        toolsTabs    = $('#mapTools').tabs(),
        tabIndex     = ($('#selectedtab').val()) ? parseInt($('#selectedtab').val()) : 0

    self.destroyJcrop();

    $('#output').val('pnga');        // set the preview and output values
    $('#badRecordsWarning').hide();  // hide the bad records warning
    $('#download_token').val(token); // set a token to be used for cookie
  
    formData = $("form").serialize();

    $('#mapOutput').html(message);
    $('#mapScale').html('');

    $.post(Mapper.settings.baseUrl + "/application/", formData, function(data) {
      $('#mapOutput').html(data);

      self.drawLegend();
      self.drawScalebar();
      self.showBadPoints();

      toolsTabs.tabs('select', tabIndex);
      
      $('#mapTools').bind('tabsselect', function(event,ui) {
        $('#selectedtab').val(ui.index);
      });

      $('#bbox_rubberband').val('');                             // reset bounding box values, but get first for the crop function
      $('#bbox_map').val($('#rendered_bbox').val());             // set extent from previous rendering
      $('#projection_map').val($('#rendered_projection').val()); // set projection from the previous rendering
      $('#rotation').val($('#rendered_rotation').val());         // reset rotation value
      $('#pan').val('');                                         // reset pan value

      $('#mapExport a.toolsPng').click(function() {
        self.generateDownload('png');
        return false; 
      });
      
      $('#mapExport a.toolsTiff').click(function() {
        self.generateDownload('tif');
        return false; 
      });

      $('#mapExport a.toolsSvg').click(function() {
        self.generateDownload('svg');
        return false; 
      });

      $('#mapExport a.toolsEps').click(function() {
        self.generateDownload('eps');
        return false; 
      });
      
      $('#mapExport a.toolsKml').click(function() {
        self.generateDownload('kml');
        return false; 
      });
                          
      $('.toolsBadRecords').click(function() {
        self.showBadRecords();
        return false; 
      });
      
    }, "html");

  }; /** end Mapper.showMap **/

  Mapper.showBadRecords = function() {
    $('#badRecordsViewer').dialog({
      autoOpen : true,
      height : 200,
      width : 500,
      position : [200, 200],
      modal : true,
      buttons: {
        Ok: function() {
          $(this).dialog("destroy");
        }
      },
      draggable : false,
      resizable : false
    });
  };

  Mapper.generateDownload = function(filetype) {
    var self        = this,
        pattern     = /[?*:;{}\\ "'\/@#!%^()<>.]+/g,
        map_title   = $('#file-name').val(),
        token       = new Date().getTime(),
        cookieValue = "";
      
    if($('#border').is(':checked')) { 
      $('input[name="options[border]"]').val(1); 
    }
    else {
      $('input[name="options[border]"]').val("");
    }

    if($('#legend').is(':checked')) { 
      $('input[name="options[legend]"]').val(1); 
    }
    else { 
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
        if(jcrop_api) $('#crop').val(1);
        formData = $("form").serialize();
        $('.download-dialog').hide();
        $('.download-message').show();
        $.download(self.settings.baseUrl + "/application/", formData, 'post');
        $('#download').val('');
        $('#output').val('pnga'); 
    }
    
    self.vars.fileDownloadTimer = window.setInterval(function() {
      cookieValue = $.cookie('fileDownloadToken');
      if (cookieValue == token) {
        self.finishDownload();
      }
    }, 1000);

  }; /** end Mapper.generateDownload **/

  Mapper.finishDownload = function() {
    $('.download-message').hide();
    $('.download-dialog').show();
    window.clearInterval(this.vars.fileDownloadTimer);
    $.cookie('fileDownloadToken', null); //clears this cookie value
  };

  /************************************ 
  ** RAPHAEL: FREEHAND DRAWING TOOLS **
  ************************************/
  Mapper.raphaelConfig = {
    board         : Raphael('mapOutput', 800, 400),
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

  Mapper.raphaelConfig.position = function(e) {
    return {
      x: e.pageX - this.offset.left,
      y: e.pageY - this.offset.top
    };
  };

  Mapper.raphaelConfig.mouseMove = function(e) {
    var self = Mapper.raphaelConfig,
        pos  = self.position(e),
        x    = self.path[0][1],
        y    = self.path[0][2],
        dx   = (pos.x - x),
        dy   = (pos.y - y);

    switch(self.selectedTool){
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

  Mapper.raphaelConfig.forcePaint = function(){
    var self = Mapper.raphaelConfig;
    window.setTimeout(function(){
      var rect = self.board.rect(-99, -99, self.board.width + 99, self.board.height + 99).attr({stroke: "none"});
      setTimeout(function() {rect.remove();});
    },1);
  };

  Mapper.raphaelConfig.draw = function(path, color, size) {
    var self   = Mapper.raphaelConfig,
        result = self.board.path(path);

    result.attr({ stroke: color, 'stroke-width': size, 'stroke-linecap': 'round' });
    self.forcePaint();
    return result;
  };

  Mapper.init = function() {
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
    this.bindClearButtons();
    this.bindSave();
    this.bindDownload();
    $('textarea.resizable:not(.textarea-processed)').TextAreaResizer();
    if($('#usermaps').length > 0) {
      $("#tabs").tabs('select',4);
      this.loadMapList();
    }
    if($('#userdata').length > 0) {
      this.loadUsers();
    }
  };

  Mapper.init();  

});

/******* jQUERY EXTENSIONS *******/

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