(function($)
{
    'use strict';
    $.fn.ModCleanStorage = function(
        options
    )
    {

        // This is the easiest way to have default options.
        var settings = $.extend({
            pattern: /(^(DataTables_|Widget-)|]$)/
        }, options);

        // script goes here
        Object.keys(localStorage).forEach(function(key)
        {
            if (settings.pattern.test(key)) {
                localStorage.removeItem(key);
            }
        });

        return this;
    };

}(jQuery));
