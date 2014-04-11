var App;

/**
 * AppView, the main view/controller of the App
 *
 * @type @exp;Backbone@pro;View@call;extend
 */
var AppView = Backbone.View.extend({
    el                 : 'body',
    events: {
        'click .overviewTableClickableTd'       : "overviewTableRowClick",
        'click .clear-cache'                    : 'clearCache'
    },
    initialize: function() {


    }
});

//Initialize the view on domready
$(function(){
    App = new AppView();
});
