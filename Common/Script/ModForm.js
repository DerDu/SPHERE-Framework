(function($)
{
    'use strict';
    $.fn.ModForm = function(options)
    {
        var thisForm = this;
        // This is the easiest way to have default options.
        var settings = $.extend({
            saveDraftData: false,
            notifyChangedField: true,
            notifyChangedMessage: false
        }, options);

        var notifyFieldName;
        var notifyFieldList = this.find(':input:not(:button)');

        /**
         * Autocomplete Attribute OFF
         */
        thisForm.attr('autocomplete', 'off');
        thisForm.find('input[type="password"]').attr('autocomplete', 'off');
        thisForm.find('input[type="text"]').attr('autocomplete', 'off');
        thisForm.find('input[type="number"]').attr('autocomplete', 'off');
        /**
         * Form Submit-Indicator
         */
        if( !thisForm.hasClass('AjaxSubmit') ) {
            thisForm.on("submit", function () {
                thisForm.find('button[type="submit"]:not(.disabled)').html(
                    '<span class="loading-indicator-animate"></span> Bitte warten'
                );
            });
        }

        // script goes here
        /**
         * Page-Leave Draft-Save: All
         * @file ModForm.js
         */
        if (settings.saveDraftData) {
            thisForm.sisyphus({
                locationBased: true,
                onRestore: function(){
                    $.notifyClose();
                    $.notify({
                        // options
                        message:
                                    '<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>'
                                    + '&nbsp; Ihre noch nicht gespeicherten Eingaben wurden wieder hergestellt'

                    }, {
                        // settings
                        newest_on_top: true,
                        type: 'info',
                        delay: 20000,
                        placement: {
                            from: "top",
                            align: "center"
                        }
                    });
                }
            });
        }

        /**
         * Page-Leave Confirmation
         * @file ModAlways.js, ModForm.js
         */
        $(window).on('browser:page:unload', function(Event)
        {
            if (thisForm.find(':input[data-form-leave-prevention="1"]').length) {
                Event.preventDefault();
                if (settings.notifyChangedMessage) {
                    Event.message = settings.notifyChangedMessage;
                }
            }
        });
        this.notifyChangedField = function(FieldName)
        {
            this.find('[name="' + FieldName + '"]')
                .attr('data-form-leave-prevention', 0)
                .on('propertychange change click keyup input paste', function(Event)
                {
                    $(Event.target).attr('data-form-leave-prevention', 1);
                });
        };
        /**
         * Page-Leave Confirmation: All
         */
        if (true === settings.notifyChangedField) {
            for (notifyFieldName in notifyFieldList) {
                if (notifyFieldList.hasOwnProperty(notifyFieldName)) {
                    this.notifyChangedField($(notifyFieldList[notifyFieldName]).attr('name'))
                }
            }
        }
        /**
         * Page-Leave Confirmation: Submit
         */
        this.on('submit', function()
        {
            notifyFieldList.attr('data-form-leave-prevention', 0);
        });

        /**
         * Enable: Form-Validator
         */
        $.fn.validator.Constructor.INPUT_SELECTOR = ':input:not([type="hidden"], [type="submit"], [type="reset"], select, button)'
        this.validator();

        return this;
    };

}(jQuery));
