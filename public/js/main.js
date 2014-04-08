var App;

/**
 * AppView, the main view/controller of the App
 *
 * @type @exp;Backbone@pro;View@call;extend
 */
var AppView = Backbone.View.extend({
    el: 'body',
    siteId: null,
    activePage: null,
    events: {
        'click .overviewTableClickableTd'       : "overviewTableRowClick",
        'click .clear-cache'                    : 'clearCache'
    },
    initialize: function() {
        $('.iframe-btn').fancybox({
            'width'     : 900,
            'height'    : 600,
            'type'      : 'iframe',
            'autoSize'  : false,
            'fitToView' : false,
        });
    }
});

//Initialize the view on domready
$(function(){
    App = new AppView();

});
