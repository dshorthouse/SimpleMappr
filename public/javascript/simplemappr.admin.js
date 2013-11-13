/*
 * jQuery SimpleMapprAdmin
 */
/*global SimpleMappr, jQuery, window, document, self, XMLHttpRequest, alert, encodeURIComponent, _gaq */
var SimpleMapprAdmin = (function($, window, document) {

  "use strict";

  var _private = {
    
    citations_list: $('#admin-citations-list'),
    
    init: function() {
      this.loadUserList();
      this.bindTools();
      this.loadCitationList();
      this.bindCreateCitation();
      SimpleMappr.tabSelector(4);
    },

    loadUserList: function(object) {
      var self  = this,
          obj   = object || {},
          data  = { locale : SimpleMappr.getParameterByName("locale") };

      SimpleMappr.showSpinner();

      if(obj.sort) {
        data.sort = obj.sort.item;
        data.dir = obj.sort.dir;
      }

      if(!data.locale) { delete data.locale; }

      $.ajax({
        type     : 'GET',
        url      : SimpleMappr.settings.baseUrl + '/user/',
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
                SimpleMappr.loadMapList({ uid : $(this).attr("data-uid") });
                SimpleMappr.tabSelector(3);
            });
            SimpleMappr.hideSpinner();
          }
        }
      });
    },
    
    bindTools: function() {
      var self = this;

      $('#map-admin').on('click', 'a.admin-tool', function(e) {
        e.preventDefault();
        SimpleMappr.showSpinner();
        if($(this).has('#flush-caches')) {
          self.flushCaches();
        }
      });
    },
    
    flushCaches: function() {
      $.ajax({
        type     : 'GET',
        url      : SimpleMappr.settings.baseUrl + "/flush_cache/",
        dataType : 'json',
        success  : function(response) {
          if(response.files === true) {
            SimpleMappr.hideSpinner();
            alert("Caches flushed");
            window.location.reload();
          }
        },
        error    : function() {
          SimpleMappr.hideSpinner();
          alert("Error flushing caches");
        }
      });
    },
    
    loadCitationList: function() {
      var self = this, citations = "", doi = "", link = "";

      SimpleMappr.showSpinner();
      $.ajax({
        type     : 'GET',
        url      : SimpleMappr.settings.baseUrl + "/citation/",
        dataType : 'json',
        timeout  : 30000,
        success  : function(data) {
          if(data.status === 'ok') {
            $.each(data.citations, function() {
              doi = (this.doi) ? ' doi:<a href="http://doi.org/' + this.doi + '">' + this.doi + '</a>.' : "";
              link = (this.link) ? ' (<a href="' + this.link + '">link</a>)' : "";
              citations += '<p class="citation">' + this.reference + link + doi + '<a class="sprites-before citation-delete" data-id="' + this.id + '" href="#">Delete</a></p>';
            });
            self.citations_list.html(citations);
            self.bindDeleteCitations();
            SimpleMappr.hideSpinner();
          }
        },
        error : function() {
          alert("Error loading citations");
          SimpleMappr.hideSpinner();
        }
      });
    },
    
    bindDeleteCitations: function() {
      var self = this;

      this.citations_list.on('click', 'a.citation-delete', function(e) {
        e.preventDefault();
        self.deleteCitationConfirmation(this);
      });
    },
    
    bindCreateCitation: function() {
      var self = this;

      $('#map-admin').on('click', 'button.addmore', function(e) {
        e.preventDefault();
        if($('#citation-reference').val() !== "" && $('#citation-surname').val() !== "" && $('#citation-year').val() !== "") {
          SimpleMappr.showSpinner();
          $.ajax({
            type        : 'POST',
            url         : SimpleMappr.settings.baseUrl + '/citation/',
            data        : $("form").serialize(),
            dataType    : 'json',
            success     : function(data) {
              if(data.status === "ok") {
                $('#map-admin').find(".citation").val("");
                $.each(["reference", "surname", "year"], function() {
                  $('#citation-'+this).removeClass('ui-state-error');
                });
                self.loadCitationList();
                SimpleMappr.hideSpinner();
              }
            }
          });
        } else {
          $.each(["reference", "surname", "year"], function() {
            $('#citation-'+this).addClass('ui-state-error');
          });
        }
      });
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
              SimpleMappr.showSpinner();
              $.ajax({
                type    : 'DELETE',
                url     : SimpleMappr.settings.baseUrl + "/user/" + id,
                success : function() {
                  self.loadUserList();
                  SimpleMappr.hideSpinner();
                  SimpleMappr.trackEvent('user', 'delete');
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

    deleteCitationConfirmation: function(obj) {
      var self    = this,
          id      = $(obj).attr("data-id"),
          message = ':<br><br>' + $(obj).parent().text().replace("Delete", "");

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
              SimpleMappr.showSpinner();
              $.ajax({
                type    : 'DELETE',
                url     : SimpleMappr.settings.baseUrl + "/citation/" + id,
                success : function() {
                  SimpleMappr.hideSpinner();
                  self.loadCitationList();
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
    }

  };

  return {
    init: function() {
      _private.init();
    }
  };

}(jQuery, window, document));