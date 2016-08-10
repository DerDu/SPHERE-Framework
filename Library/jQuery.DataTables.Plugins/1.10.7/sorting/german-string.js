/**
 * Sorting in Javascript for German Characters. This plug-in will replace the special
 * german letters (non english characters) and replace in English.
 *
 *
 *  @name German
 *  @summary Sort German characters
 *
 *  @example
 *    $('#example').dataTable({
 *       'aoColumns' : [
 *                       {'sType' : 'german-string'}
 *       ]
 *   });
 *
 *  array(
 *      'columnDefs' => array(
 *          array('type' => 'german-string', 'targets' => 0),
 *      )
 *  )
 */
jQuery.extend(jQuery.fn.dataTableExt.oSort, {

    'german-string-pre': function(str)
    {
        var stringsToExclude = [
            "der",
            "die",
            "das",
            "den",
            "dem",
            "des",
            "ein",
            "eine",
            "einen",
            "einem",
            "eines",
            "the",
            "a",
            "an",
            "la",
            "le",
            "les",
            "un",
            "une",
            "des",
            "l'",
            "von"
        ];
        //exclude html tags
//         str = str.replace(//g, '');
        str = str.toLowerCase();
        //replace german umlauts
        str = str.replace(/ä/g, "ae");
        str = str.replace(/ö/g, "oe");
        str = str.replace(/ü/g, "ue");
        str = str.replace(/ß/g, "ss");
        for (var i = 0; i < stringsToExclude.length; i++) {
            //we need to escape special characters for use in the regex object constructor, e.g. \\s instead of \s
            var myRegex = "^" + stringsToExclude[i] + "\\s";
            str = str.replace(new RegExp(myRegex, "i"), "");
        }
        return str;
    },
    'german-string-asc': function(a, b)
    {
        a = prepareForSorting(a);
        b = prepareForSorting(b);

        return (a == b) ? 0 : (a > b) ? 1 : -1;
    },
    'german-string-desc': function(a, b)
    {

        a = prepareForSorting(a);
        b = prepareForSorting(b);
        //reverse sorting
        return (a == b) ? 0 : (a > b) ? -1 : 1;
    }
});
