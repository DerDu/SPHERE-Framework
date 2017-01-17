(function ($)
{
    'use strict';
    $.fn.ModAlways = function ()
    {
        $(document).ready(function ()
        {
            // MOVED TO FORM
            // var worldForm = $('form:not(.AjaxSubmit)');
            // /**
            //  * Autocomplete Attribute OFF
            //  */
            // worldForm.attr('autocomplete', 'off');
            // $('input[type="password"]').attr('autocomplete', 'off');
            // $('input[type="text"]').attr('autocomplete', 'off');
            // $('input[type="number"]').attr('autocomplete', 'off');
            //
            // /**
            //  * Form Submit-Indicator
            //  */
            // worldForm.on("submit", function()
            // {
            //     $('button[type="submit"]:not(.disabled)').html(
            //         '<span class="loading-indicator-animate"></span> Bitte warten');
            // });

            //noinspection FunctionWithInconsistentReturnsJS
            /**
             * Page-Leave Confirmation-Handler
             * @file ModAlways.js, ModForm.js
             */
            $(window).on('beforeunload', function ()
            {
                var closingEvent = $.Event('browser:page:unload');
                // let other modules determine whether to prevent closing
                $(window).trigger(closingEvent);
                if (closingEvent.isDefaultPrevented()) {
                    // closingEvent.message is optional
                    return closingEvent.message || 'Warnung: Die Daten wurden noch nicht gespeichert.';
                }
            });

            /**
             * Activate MaxLength
             */
            // MOVED TO TEXTAREA
            // var worlTextAreaMaxLength = $("textarea[maxlength]");
            // worlTextAreaMaxLength.each(function(){
            //     var maxLength = $(this).attr('maxlength');
            //     $(this).parent('div').find('.TextAreaMaxLengthCounter').html( maxLength - $(this).val().length );
            // });
            // worlTextAreaMaxLength.on('input propertychange', function() {
            //     var maxLength = $(this).attr('maxlength');
            //     var disableLineFeed = $(this).hasClass('DisableLineFeed');
            //     if( disableLineFeed ) {
            //         $(this).val($(this).val().replace(/\r?\n/gi, ' '));
            //     }
            //     if ($(this).val().length > maxLength) {
            //         $(this).val($(this).val().substring(0, maxLength));
            //     }
            //     $(this).parent('div').find('.TextAreaMaxLengthCounter').html( maxLength - $(this).val().length );
            // })
            /**
             * Activate: Tooltip
             */
//            $('[data-toggle="tooltip"]').tooltip({
//                container: 'body',
//                placement: 'auto top'
//            });
            /**
             * Activate: External Link
             */
            $('a[rel="external"]').attr('target', '_blank');
            /**
             * Activate: Source-Code Highlighter
             */
            if (window.hljs) {
                hljs.initHighlighting();
            }
            /**
             * Activate: iFrame-Styling
             */
            var iFrame = jQuery('iframe.sphere-iframe-style');
            var iStyle = jQuery('link');

            var iRun = function (iFrame)
            {
                iFrame.hide();
                var iContent = iFrame.contents();
                var iHead = iContent.find("head");
                var iBody = iContent.find("body");
                iStyle.each(function (Link)
                {
                    jQuery(iHead).append(jQuery(iStyle[Link]).clone());
                }).promise().done(function ()
                {
                    iFrame.show();
                    // Resize
                    var lastHeight = 0, curHeight = 0;
                    var iSize = setInterval(function ()
                    {
                        curHeight = iFrame.contents().find('body').height();
                        if (curHeight > lastHeight || !curHeight) {
                            iFrame.css('height', (lastHeight = curHeight) + 'px');
                        } else {
                            clearInterval(iSize);
                            iFrame.css('height', (curHeight + 12) + 'px');
                        }
                    }, 100);
                });
                iBody.css('background-color', 'transparent');
            };

            jQuery(iFrame).each(function (Frame)
            {
                jQuery(iFrame[Frame]).before(
                    '<div class="alert alert-danger" >Im folgenden Feld befinden sich externe Inhalte</div>');

                if (
                    jQuery('<a>').prop('href', window.location).prop('hostname')
                    ==
                    jQuery('<a>').prop('href', jQuery(iFrame[Frame]).attr('src')).prop('hostname')
                ) {
                    iRun(jQuery(iFrame[Frame]));
                    jQuery(iFrame[Frame]).on('load', function ()
                    {
                        iRun(jQuery(iFrame[Frame]));
                    });
                } else {
                    jQuery(iFrame[Frame]).show().css({height: '300px'});
                }
            });
        });
        return this;

    };

}(jQuery));
