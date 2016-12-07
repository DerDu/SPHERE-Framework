(function($)
{
    'use strict';
    $.fn.ModCompleter = function(options)
    {
        var _self = this;

        // This is the easiest way to have default options.
        var settings = $.extend({
            // These are the defaults.
            data: [],
            displayKey: 'value'
        }, options);

        // constructs the suggestion engine
        var searchEngine = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            // `states` is an array of state names defined in "The Basics"
            local: $.map(settings.data, function(data)
            {
                return {value: data};
            }),
            sorter: function(a, b) {

                //get input text
                var InputString=$(_self).val();

                //move exact matches to top
                if(InputString==a.value){ return -1;}
                if(InputString==b.value){return 1;}

                //close match without case matching
                if(InputString.toLowerCase() ==a.value.toLowerCase()){ return -1;}
                if(InputString.toLowerCase()==b.value.toLowerCase()){return 1;}

                // everything else
                if( (InputString!=a.value) && (InputString!=b.value)){

                    if (a.value < b.value) {
                        return -1;
                    }
                    else if (a.value > b.value) {
                        return 1;
                    }
                    else return 0;
                }
            }
        });

        // kicks off the loading/processing of `local` and `prefetch`
        searchEngine.initialize();
        settings.source = searchEngine.ttAdapter();

        this.typeahead({
            hint: true,
            highlight: true,
            minLength: 1
        }, settings);
        return this;

    };

}(jQuery));
