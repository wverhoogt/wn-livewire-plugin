+function ($) { "use strict";

  var cp = $.wn.cmsPage;
  cp.updateModifiedCounter = function() {
    var counters = {
        component: { menu: 'pages', count: 0 }
    }

    $('> div.tab-content > div.tab-pane[data-modified]', '#cms-master-tabs').each(function(){
      var inputType = $('> form > input[name=templateType]', this).val()
      counters[inputType].count++
    })

    $.each(counters, function(type, data){
      $.wn.sideNav.setCounter('cms/' + data.menu, data.count);
    })
  }
}(window.jQuery);
