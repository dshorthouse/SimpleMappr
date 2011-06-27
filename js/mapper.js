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
    arrayPageSize = new Array(pageWidth,pageHeight,windowWidth,windowHeight);
    return arrayPageSize;
}

function ___getPageScroll() {
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
    arrayPageScroll = new Array(xScroll,yScroll);
    return arrayPageScroll;
}

function __showCoords(c) {
    
    var x = parseInt(c.x);
    var y = parseInt(c.y);
    var x2 = parseInt(c.x2);
    var y2 = parseInt(c.y2);
    
    $('.jcrop-holder div:first').css('backgroundColor', 'white');
    
    $('#bbox_rubberband').val(x+','+y+','+x2+','+y2);
}

function __showCoordsQuery(c) {
    
    var x = parseInt(c.x);
    var y = parseInt(c.y);
    var x2 = parseInt(c.x2);
    var y2 = parseInt(c.y2);
    
    $('#bbox_query').val(x+','+y+','+x2+','+y2);
}

function tabSelector(tab) {
  $("#tabs").tabs('select',tab);
}

$(window).resize(function() {
    // Get page sizes
    var arrPageSizes = ___getPageSize();
    // Style overlay and show it
    $('#mapper-overlay').css({
        width:      arrPageSizes[0],
        height:     arrPageSizes[1]
    });
    // Get page scroll
    var arrPageScroll = ___getPageScroll();
    $('#mapper-message').css({
        top:    arrPageScroll[1] + (arrPageSizes[3] / 10),
        left:   arrPageScroll[0],
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

  $('.fieldSets').accordion({
    header : 'h3',
    collapsible : true,
    autoHeight : false 
  });
                    
  $('#initial-message').hide();
  $('#tabs').show();

  //set the tooltips
  $(".tooltip").tipsy({gravity: 's'});

  //override the ActiveX jQuery 1.4.4 settings for IE
  $.ajaxSetup({
    xhr:function() { return new XMLHttpRequest(); }
  });

  function RGBtoHex(R,G,B) {
    return toHex(R)+toHex(G)+toHex(B);
  }

  function toHex(N) {
    if (N == null) return "00";
    N = parseInt(N);
    if (N == 0 || isNaN(N)) return "00";
    N = Math.max(0,N);
    N = Math.min(N,255);
    N = Math.round(N);
    return "0123456789ABCDEF".charAt((N-N%16)/16) + "0123456789ABCDEF".charAt(N%16);
  }

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//Define some variables - edit to suit your needs
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ 
    var displaySubmit=false;

    var $addMorebtn = $('#addMore');
    var newPointsCount=0;
    var maxPoints=10;
    
    var $addMoreRegionsbtn = $('#addMoreRegions');
    var newRegionsCount=0;
    var maxRegions=10;

    var $addMoreFreehandbtn = $('#addMoreFreehand');
    var newFreehandCount=0;
    var maxFreehand=10;
    
    var $hiddenControls = $("#controls .hiddenControls");
    var $submitForm = $(".submitForm");
    var hiddenClass = "hidden";

    var jcrop_api;
    var jzoom_api;
    var jquery_api;
    var zoom = true;
    
    var fileDownloadCheckTimer;

    var download_dialog = $('.download-dialog').html();

/******************** toolbar actions ************************/
    $("ul.dropdown li").hover(function(){
        $(this).addClass("ui-state-hover");
        $('ul:first',this).css('visibility', 'visible');
    }, function(){ 
        $(this).removeClass("ui-state-hover");
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
    
    $preview.find('.toolsQuery').click(function() {
        $('#mapCropMessage').hide();
        initJquery();
        zoom = false;
        return false;
    });

    $preview.find('.toolsDraw').click(function() {
       $('mapCropMessage').hide();
       initDraw();
       zoom = false;
       return false;
    });

    $preview.find('.toolsRefresh').click(function(){
        showMap();  
    });

    $preview.find('.toolsRebuild').click(function(){
        $('#bbox_map').val('');
        $('#projection_map').val('');
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
        if(typeof jcrop_api != "undefined") {
          jcrop_api.destroy();
        }
        if(typeof jquery_api != "undefined") {
          jquery_api.destroy();
        }
        
        jcrop_api = $.Jcrop('#mapOutput img', {
            bgColor:'grey',
            bgOpacity:1,
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
        if(typeof jquery_api != "undefined") {
          jquery_api.destroy();
        }
        
        jzoom_api = $.Jcrop('#mapOutput img', {
            addClass: 'customJzoom',
            sideHandles: false,
            cornerHandles: false,
            dragEdges: false,
            bgOpacity: 1,
            bgColor:'white',
            onChange: __showCoords,
            onSelect: __showCoords
        });

        $('.jcrop-tracker').bind('mouseup', aZoom );
    };

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        function initJquery(){
            //destroy existing apis first
            if(typeof jzoom_api != "undefined") {
              jzoom_api.destroy();
            }
            if(typeof jcrop_api != "undefined") {
              jcrop_api.destroy();
            }
            if(typeof jquery_api != "undefined") {
              jquery_api.destroy();
            }

            jquery_api = $.Jcrop('#mapOutput img', {
                addClass: 'customJzoom',
                sideHandles: false,
                cornerHandles: false,
                dragEdges: false,
                bgOpacity: 1,
                bgColor:'white',
                onChange: __showCoordsQuery,
                onSelect: __showCoordsQuery
            });

            $('.jcrop-tracker').bind('mouseup', aQuery );
        };

    function initDraw() {
        if(typeof jzoom_api != "undefined") {
          jzoom_api.destroy();
        }
        if(typeof jcrop_api != "undefined") {
          jcrop_api.destroy();
        }
        if(typeof jquery_api != "undefined") {
          jquery_api.destroy();
        }

        //set Raphael, freehand drawing tools
      var raphaelConfig = {
        board : Raphael('mapOutput', 800, 400),
        line : null,
        path : null,
        wkt : null,
        color : null,
        size : null,
        selectedColor : 'mosaic',
        selectedSize : 4,
        selectedTool : 'pencil',
        offset : $('#mapOutput').offset(),
      };  

      raphaelConfig.position = function(e) {
        return {
          x: e.pageX - this.offset.left,
          y: e.pageY - this.offset.top
        };
      };

      raphaelConfig.mouseMove = function(e) {
        var self = raphaelConfig;
        var pos = self.position(e);
        var x = self.path[0][1];
        var y = self.path[0][2];
        var dx = (pos.x - x);
        var dy = (pos.y - y);
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
    
//          self.wkt[1] = [(x + dx) + " " + y];
//          self.wkt[2] = [(x + dx) + " " + (y + dy)];
//          self.wkt[3] = [x + " " + (y + dy)];
//          self.wkt[4] = [x + " " + y];
//          self.wkt[5] = [x + " " + y];
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
      };

      // stupid chrome has a repaint bug
      raphaelConfig.forcePaint = function(){
        var self = this;
        window.setTimeout(function(){
          var rect = self.board.rect(-99, -99, self.board.width + 99, self.board.height + 99).attr({stroke: "none"});
          setTimeout(function() {rect.remove();});
        },1);
      };

      raphaelConfig.draw = function(path, color, size) {
        var result = this.board.path(path);
        result.attr({ stroke: color, 'stroke-width': size, 'stroke-linecap': 'round' });
        this.forcePaint();
        return result;
      };

        $('#mapOutput').mousedown(function(e) {
          var self = raphaelConfig;
          var pos = self.position(e);
          var color = $('input[name="freehand[0][color]"]').val();
          color = color.split(" ");
          self.path = [['M', pos.x, pos.y]];
          self.wkt = [[pos.x + " " + pos.y]];
          self.color = "#" + RGBtoHex(color[0], color[1], color[2]);
          self.size = self.selectedSize;
          self.line = self.draw(self.path, self.color, self.size);
          $('#mapOutput').bind('mousemove', self.mouseMove);
        });

        $('#mapOutput').mouseup(function() {
          var self = raphaelConfig;
          var wkt = "";

          $('#mapOutput').unbind('mousemove', self.mouseMove);
          $('input[name="freehand[0][title]"]').val("Freehand Drawing");

          $.ajax({
            url     : Mapper.settings.baseUrl + '/query/',
            type    : 'POST',
            data    : { freehand : self.wkt },
            async: false,
            success : function(results) {
              if(!results) return; 
              switch(self.selectedTool) {
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
            error : function() {
              return false;
            },
            complete : function() {
            }
          });

        });
    }
    
    function aZoom() {
      showMap();
    }

    function aQuery() {
        //destroy existing apis first
          if(typeof jzoom_api != "undefined") {
              jzoom_api.destroy();
          }
          if(typeof jcrop_api != "undefined") {
              jcrop_api.destroy();
          }
          if(typeof jquery_api != "undefined") {
              jquery_api.destroy();
          }
        
          var formData = {
            bbox : $('#rendered_bbox').val(), 
            bbox_query : $('#bbox_query').val(), 
            projection : $('#projection').val(), 
            projection_map : $('#projection_map').val(),
            qlayer : ($('#stateprovince').is(':checked')) ? 'stateprovinces_polygon' : 'base' 
          };

          $.post(Mapper.settings.baseUrl + "/query/", formData, function(data) {
            if(data.length > 0) {
                $region_title = $('input[name="regions[0][title]"]');
                $region_data = $('textarea[name="regions[0][data]"]');
                $region_data.val("");
                if($region_title.val() == "") $region_title.val("Selected Regions");
                var regions = "";
                for(var i = 0; i < data.length; i++) {
                    regions += data[i];
                    if(i < data.length-1) regions += ", ";
                }
                $region_data.val(regions);
                showMap();
            }
          });
          
    }

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//click the add more button
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    $addMorebtn.click(function(){
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
        $addMorebtn.attr("disabled","disabled"); //set the "disabled" property on the button
        return false; //kill the browser default action
      }
 
      return false;  //kill the browser default action
    });

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//click the add more regions button
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        $addMoreRegionsbtn.click(function(){
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
            $addMoreRegionsbtn.attr("disabled","disabled"); //set the "disabled" property on the button
            return false; //kill the browser default action
          }

          return false;  //kill the browser default action
        });

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//click the add more freehand button
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
                $addMoreFreehandbtn.click(function(){
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
                    $addMoreFreehandbtn.attr("disabled","disabled"); //set the "disabled" property on the button
                    return false; //kill the browser default action
                  }

                  return false;  //kill the browser default action
                });
            
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//Load user map data if usermaps div present
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
if($('#usermaps').length > 0) {
    $("#tabs").tabs('select',4);
    loadMyMaps();
}

function loadMyMaps() {
    
    var message = '<div id="usermaps-loading"><span id="mapper-building-map">Loading your maps...</span></div>';
    $('#usermaps').html(message);
    
    $.get(Mapper.settings.baseUrl + "/usermaps/?action=list", {}, function(data) {
        $('#usermaps').html(data);
        $('.map-load').click(function() {
            var id = $(this).attr("rel");

            //clear the form elements
            $('#form-mapper').clearForm();
            
            //remove extraneous sections & reset the counters
            var numPoints = $('.fieldset-points').size();
            if(numPoints > 3) {
                for(i=numPoints-1; i>=3;i--) {
                    $('#fieldSetsPoints div.fieldset-points:eq('+i+')').remove();
                }
                newPointsCount = 0;
            }
            var numRegions = $('.fieldset-regions').size();
            if(numRegions > 3) {
                for(i=numRegions-1; i>=3;i--) {
                    $('#fieldSetsRegions div.fieldset-regions:eq('+i+')').remove();
                }
                newRegionsCount = 0;
            }
            $.get(Mapper.settings.baseUrl + "/usermaps/?action=load&map="+id, {}, function(data) {
              //load up all the data
            
              //get single items like projection, rotation, title, etc.
              $('select[name="projection"]').val(data.map.projection);
              $('input[name="bbox_map"]').val(data.map.bbox_map);
              $('input[name="projection_map"]').val(data.map.projection_map);
              $('input[name="rotation"]').val(data.map.rotation);
              if(data.map.download_factor) {
                $('input[name="download_factor"]').val(data.map.download_factor);
                $('#download-factor').val(data.map.download_factor);
              }
              else {
                $('input[name="download_factor"]').val("");
                $('#download-factor')[0].selectedIndex = 0;
              }

              var map_title = data.map.save.title;

              $('input[name="save[title]"]').val(map_title);
              $('.m-mapSaveTitle').val(map_title);
              $('#mapTitle').text(map_title);

              var pattern = /[?*:;{}\\ "']+/g;
              map_title = map_title.replace(pattern, "_");
              $('#file-name').val(map_title);
              
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
                    $addMorebtn.attr("disabled","disabled"); //set the "disabled" property on the button
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
                    $addMorebtn.attr("disabled","disabled"); //set the "disabled" property on the button
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
                    $addMorebtn.attr("disabled","disabled"); //set the "disabled" property on the button
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
              
              
              //load up all the selected layers
              $('#border').attr('checked', false);
              $('#legend').attr('checked', false);
              $('input[name="options[border]"]').val("");
              $('input[name="options[legend]"]').val("");
              if(data.map.layers) {
                var keyMap = [];
                for(key in data.map.layers){
                    keyMap[keyMap.length] = key;
                }
                for(i=0;i<keyMap.length;i++) {
                    $('input[name="layers['+keyMap[i]+']"]').attr('checked', true);
                }
              }
              
              //load up all the options
              if(data.map.options !== undefined) {
                var keyMap = [];
                for(key in data.map.options){
                    keyMap[keyMap.length] = key;
                }
                for(i=0;i<keyMap.length;i++) {
                    if(keyMap[i] == 'border') {
                        $('#border').attr('checked', true);
                        $('input[name="options[border]"]').val(1);
                    }
                    else if(keyMap[i] == 'legend') {
                        $('#legend').attr('checked', true);
                        $('input[name="options[legend]"]').val(1);
                    }
                    else {
                        $('input[name="options['+keyMap[i]+']"]').attr('checked', true);
                    }
                    
                }
              }
            
              showMap();

              $("#tabs").tabs('select',0);
            }, "json");
        });
        
        $('.map-url').click(function() {
            var message = 'Use the following HTML snippet to embed a png:';
            message += "<p><input type='text' size='65' value='&lt;img src=\"" + Mapper.settings.baseUrl + "/?map=" + $(this).attr("rel") + "\" alt=\"\" /&gt;'></input></p>";
            message += "<strong>Additional parameters</strong>:<span class=\"indent\">width, height (<em>e.g.</em> ?map=" + $(this).attr("rel") + "&amp;width=200&amp;height=150)</span>";
            $('body').append('<div id="mapper-message" class="ui-state-highlight" title="URL">' + message + '</div>');
            $('#mapper-message').dialog({
                height : 250,
                width : 525,
                modal : true,
                buttons: {
                    Cancel: function() {
                        hideMessage();
                    }
                },
                draggable : false,
                resizable : false
            });
            return false;
        });
        
        $('.map-delete').click(function() {
            var message = 'Are you sure you want to delete<p><em>'+$(this).parent().parent().find(".title").html()+'</em>?</p>';
            $('body').append('<div id="mapper-message" class="ui-state-highlight" title="Delete Confirmation">' + message + '</div>');
            var id = $(this).attr("rel");
            $('#mapper-message').dialog({
                height : 250,
                width : 500,
                modal : true,
                buttons: {
                    "Delete" : function() {
                        $.get(Mapper.settings.baseUrl + "/usermaps/?action=delete&map="+id, {}, function(data) {
                            hideMessage();
                            loadMyMaps();
                        }, "json");
                    },
                    Cancel: function() {
                        hideMessage();
                    }
                },
                draggable : false,
                resizable : false
            });
            return false;
        });
    }, "html");
}

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//Load user data if usermaps div present
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
if($('#userdata').length > 0) {
    loadUsers();
}

function loadUsers() {
    var message = '<div id="users-loading"><span id="mapper-building-users">Loading users list...</span></div>';
    $('#userdata').html(message);
    
    $.get(Mapper.settings.baseUrl + "/usermaps/?action=users", {}, function(data) {
        $('#userdata').html(data);
    }, "html");
}

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//Submit the form
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    $submitForm.click(function() {
    
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

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Save a map
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    $('#mapSave').dialog({
        autoOpen : false,
        height : 200,
        width : 500,
        modal : true,
        buttons: {
            "Save" : function() {
            
              //some simple error checking
              var missingTitleSave = false;

              if(jQuery.trim($('.m-mapSaveTitle').val()) == '') {
                missingTitleSave = true;
              }

              if(missingTitleSave) {
                $('.m-mapSaveTitle').css({'background-color':'#FFB6C1'}).keyup(function() {
                    $(this).css({'background-color':'transparent'});
                });
              }
              else {
                $('input[name="save[title]"]').val($('.m-mapSaveTitle').val());
                $('input[name="download_factor"]').val($('#download-factor').val());
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
                
                var formData = $("form").serialize();
                $.post(Mapper.settings.baseUrl + "/usermaps/?action=save", formData, function(data) {
                    $('#mapTitle').text($('.m-mapSaveTitle').val());
                    loadMyMaps();
                });
                $(this).dialog("close");
              }
            },
            Cancel: function() {
                $(this).dialog("close");
            }
        },
        draggable : false,
        resizable : false
    });
    
    $(".map-save").click(function() {
       $('#mapSave').dialog("open");
    });

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Export a map
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    $('#mapExport').dialog({
        autoOpen : false,
        width : 500,
        modal : true,
        buttons : {
            Cancel: function() {
                $(this).dialog("close");
            } 
        },
        draggable : false,
        resizable : false
    });
    
    $(".map-download").click(function() {
       $('#mapExport').dialog("open"); 
    });

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Clear the layers data
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$('.clearLayers').click(function() {
    var fieldsets = $('#fieldSetsPoints div.fieldset-points').length;
    for(i=0;i<fieldsets;i++) {
        $('input[name="coords['+i+'][title]"]').val('');
        $('textarea[name="coords['+i+'][data]"]').val('');
        $('select[name="coords['+i+'][shape]"]')[0].selectedIndex = 3;
        $('select[name="coords['+i+'][size]"]')[0].selectedIndex = 3;
        $('input[name="coords['+i+'][color]"]').val('0 0 0');
    }
    return false;  //prevent form submission
});

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Clear the regions data
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$('.clearRegions').click(function() {
  var fieldsets = $('#fieldSetsRegions div.fieldset-regions').length;
  for(i=0;i<fieldsets;i++) {
    $('input[name="regions['+i+'][title]"]').val('');
    $('textarea[name="regions['+i+'][data]"]').val('');
    $('input[name="regions['+i+'][color]"]').val('150 150 150');
  }
  return false; //prevent form submission
});

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Clear the regions data
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$('.clearFreehand').click(function() {
  var fieldsets = $('#fieldSetsFreehand div.fieldset-freehand').length;
  for(i=0;i<fieldsets;i++) {
    $('input[name="freehand['+i+'][title]"]').val('');
    $('textarea[name="freehand['+i+'][data]"]').val('');
    $('input[name="freehand['+i+'][color]"]').val('150 150 150');
  }
  return false; //prevent form submission
});


//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//Function to hide messages
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function showMessage(message) {
        $('body').append('<div id="mapper-message" class="ui-state-error" title="Warning">' + message + '</div>');
        $('#mapper-message').dialog({
            height : 200,
            modal : true,
            buttons: {
                Ok: function() {
                    $(this).dialog("close").dialog("destroy").remove();
                }
            },
            draggable : false,
            resizable : false
        });
    }
    
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//Function to hide messages
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function hideMessage() {
      $('#mapper-message').dialog("destroy");
      $('#mapper-message').hide().remove();
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
      if(typeof jquery_api != "undefined") {
          jquery_api.destroy();
      }

      //set the preview and output values
      $('#output').val('pnga');

      //hide the bad records warning
      $('#badRecordsWarning').hide();

      var x = $('#x').val();

      var token = new Date().getTime();
      $('#download_token').val(token);
    
      var formData = $("form").serialize();
    
      var message = '<span id="mapper-building-map">Building preview...</span>';
      $('#mapOutput').html(message);
      $('#mapScale').html('');

      $.post(Mapper.settings.baseUrl + "/application/", formData, function(data) {
        $('#mapOutput').html(data);
        
        //draw the legend if present
        var legend_url = $('#legend_url').val();
        var legend_dl = '';
        if(legend_url) {
            legend_dl += "<img src=\"" + legend_url + "\" />";
            $('#mapLegend').html(legend_dl);
        }
        else {
          $('#mapLegend').html('<p><em>legend will appear here</em></p>');
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

        //set the projection from the previous rendering
        $('#projection_map').val($('#rendered_projection').val());

        //reset the rotation value
        $('#rotation').val($('#rendered_rotation').val());

        //reset the pan value
        $('#pan').val('');
            
        //map download
        $('#mapExport a.toolsPng').click(function(){
          generateDownload('png');
          return false; 
        });
        
        $('#mapExport a.toolsTiff').click(function(){
          generateDownload('tif');
          return false; 
        });

        $('#mapExport a.toolsSvg').click(function(){
          generateDownload('svg');
          return false; 
        });

        $('#mapExport a.toolsEps').click(function(){
          generateDownload('eps');
          return false; 
        });
        
        $('#mapExport a.toolsKml').click(function(){
          generateDownload('kml');
          return false; 
        });
                            
        $preview.find('.toolsBadRecords').click(function() {
          showBadRecords();
          return false; 
        });
        
      }, "html");

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

$('#badRecordsViewer').dialog({
    autoOpen : false,
    height : 200,
    width : 500,
    position : [200, 200],
    modal : true,
    buttons: {
        Ok: function() {
            $( this ).dialog( "close" );
        }
    },
    draggable : false,
    resizable : false
});

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//Function to make bad records visible in the viewport
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function showBadRecords() {
      $('#badRecordsViewer').dialog("open");
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
    $('.toolsCrop a').click(function(e) {
      initJcrop();
      return nothing(e);
    });

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Function to generate download
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    function generateDownload(filetype) {
        
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

        var pattern = /[?*:;{}\\ "'\/@#!%^()<>.]+/g;
        var map_title = $('#file-name').val();

        map_title = map_title.replace(pattern, "_");
        $('#file-name').val(map_title);

        $('input[name="file_name"]').val(map_title);
        
        var token = new Date().getTime();
        $('#download_token').val(token);
        
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
              $('.download-dialog').hide();
              $('.download-message').show();
              $.download(Mapper.settings.baseUrl + "/application/", formData, 'post');
              $('#download').val('');
              $('#output').val('pnga'); 
          }
        
        fileDownloadCheckTimer = window.setInterval(function() {
          var cookieValue = $.cookie('fileDownloadToken');
          if (cookieValue == token) {
            finishDownload();
          }
        }, 1000);
    }

function finishDownload() {
   $('.download-message').hide();
   $('.download-dialog').show();
   window.clearInterval(fileDownloadCheckTimer);
   $.cookie('fileDownloadToken', null); //clears this cookie value
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