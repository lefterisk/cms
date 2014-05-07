var App;

/**
 * AppView, the main view/controller of the App
 *
 * @type @exp;Backbone@pro;View@call;extend
 */
var AppView = Backbone.View.extend({
    el                 : 'body',
    fileManagerTrigger : null,
    imageFancyPreview  : null,
    tinyMceEditorClass : null,
    events: {
        'click .overviewTableClickableTd'       : "overviewTableRowClick",
        'click .clear-cache'                    : 'clearCache'
    },
    initialize: function() {
        fileManagerTrigger = $('.iframe-btn');
        imageFancyPreview  = $('.image-preview');
        tinyMceEditorClass = '.tinyMce';
        this.initializePlugins();

    },
    initializePlugins: function() {
        fileManagerTrigger.fancybox({
            'width'     : 900,
            'height'    : 600,
            'type'      : 'iframe',
            'autoSize'  : false,
            'fitToView' : true
        });
        imageFancyPreview.fancybox({

        });
        tinymce.init({
            selector: tinyMceEditorClass,
            plugins: [
                "advlist autolink link image lists charmap print preview hr anchor pagebreak",
                "searchreplace wordcount visualblocks visualchars insertdatetime media nonbreaking",
                "table contextmenu directionality paste responsivefilemanager paste code"
            ],
            forced_root_block : "",
            force_br_newlines : true,
            force_p_newlines  : false,
            toolbar1          : "bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent ",
            toolbar2          : "styleselect | responsivefilemanager | link unlink anchor | image media | forecolor backcolor | code",
            image_advtab      : true ,
            theme             : 'modern',
            height            : 400,
            paste_as_text     : true,

            external_filemanager_path : "/filemanager/",
            filemanager_title         : "File manager" ,
            external_plugins          : { "filemanager" : "/filemanager/plugin.min.js" }
        });

        $('.datePicker').datetimepicker({
            format: "yyyy-mm-dd hh:ii:ss",
            autoclose: true,
            todayBtn: true
        });
        $('.datetimePickerClear').click(function(){
            $(this).closest('.input-group').find('input').val('');
            return false;
        });
    }
});

//Initialize the view on domready
$(function(){
    App = new AppView();
});
