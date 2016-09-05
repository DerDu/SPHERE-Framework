(function($)
{
    'use strict';
    /**
     * @param options
     * @returns {$.fn.ModTable}
     * @constructor
     */
    $.fn.ModTable = function(options)
    {

        var Table;

        // This is the easiest way to have default options.
        var settings = $.extend(true, {
            // These are the defaults.
            "language": {
                "sEmptyTable": "Keine Daten in der Tabelle vorhanden",
                "sInfo": "_START_ bis _END_ von _TOTAL_ Einträgen",
                "sInfoEmpty": "0 bis 0 von 0 Einträgen",
                "sInfoFiltered": "(gefiltert von _MAX_ Einträgen)",
                "sInfoPostFix": "",
                "sInfoThousands": ".",
                "sLengthMenu": "_MENU_ Einträge anzeigen",
                "sLoadingRecords": "Wird geladen...",
                "sProcessing": "Bitte warten...",
                "sSearch": "Suchen",
                "sZeroRecords": "Keine Einträge vorhanden.",
                "oPaginate": {
                    "sFirst": "Erste",
                    "sPrevious": "Zurück",
                    "sNext": "Nächste",
                    "sLast": "Letzte"
                },
                "oAria": {
                    "sSortAscending": ": aktivieren, um Spalte aufsteigend zu sortieren",
                    "sSortDescending": ": aktivieren, um Spalte absteigend zu sortieren"
                }
            },
            "lengthChange": true,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Alle']],
            "pageLength": 10,
            "dom": "<'row'<'col-sm-5 hidden-xs'li><'col-sm-7 hidden-xs'fp>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            stateSave: true,
            responsive: true,
            autoWidth: false,
            // Setup RowReorder Extension
            ExtensionRowReorder: {
                Enabled: false,
                Url: '/Api/',
                Event: {
                    Success: function(Data)
                    {
                        try {
                            Data = $.parseJSON(Data);
                        } catch (e) {
                            Data = { 'Error': [], 'Data': '{Empty Response}' }
                        }
                        Table.processing(false);
                        $.notifyClose();
                        $.notify({
                            // options
                            message: Data.Error[0] ? Data.Error[0].Description : Data.Data
                        }, {
                            // settings
                            newest_on_top: true,
                            type: Data.Error[0] ? 'danger' : 'success',
                            delay: Data.Error[0] ? 5000 : 1000,
                            placement: {
                                from: "top",
                                align: "center"
                            }
                        });
                    },
                    Error: function(Data)
                    {
                        try {
                            Data = $.parseJSON(Data);
                        } catch (e) {
                            Data = { 'Error': [], 'Data': '{Empty Response}' }
                        }
                        Table.processing(false);
                        $.notifyClose();
                        $.notify({
                            // options
                            message: 'Die Aktion konnte nicht ausgeführt werden.'
                        }, {
                            // settings
                            newest_on_top: true,
                            type: 'danger',
                            placement: {
                                from: "top",
                                align: "center"
                            }
                        });
                    },
                },
                Data: {
                    // User-Data (additional)
                }
            },
            ExtensionRowExchange: {
                Enabled: false,
                Url: '/Api/',
                Handler: {
                    From: 'SourceHandlerClass',
                    To: 'TargetHandlerClass',
                },
                Connect: {
                    From: 'SourceTableClass',
                    To: 'TargetTableClass',
                },
                Event: {
                    Success: function(Data)
                    {
                        try {
                            Data = $.parseJSON(Data);
                        } catch (e) {
                            Data = { 'Error': [], 'Data': '{Empty Response}' }
                        }
                        Table.processing(false);
                        $.notifyClose();
                        $.notify({
                            // options
                            message: Data.Error[0] ? Data.Error[0].Description : Data.Data
                        }, {
                            // settings
                            newest_on_top: true,
                            type: Data.Error[0] ? 'danger' : 'success',
                            delay: Data.Error[0] ? 5000 : 1000,
                            placement: {
                                from: "top",
                                align: "center"
                            }
                        });
                    },
                    Error: function(Data)
                    {
                        try {
                            Data = $.parseJSON(Data);
                        } catch (e) {
                            Data = { 'Error': [], 'Data': '{Empty Response}' }
                        }
                        Table.processing(false);
                        $.notifyClose();
                        $.notify({
                            // options
                            message: 'Die Aktion konnte nicht ausgeführt werden.'
                        }, {
                            // settings
                            newest_on_top: true,
                            type: 'danger',
                            placement: {
                                from: "top",
                                align: "center"
                            }
                        });
                    },
                },
                Data: {
                    // User-Data (additional)
                }
            }
        }, options);

        /**
         * Register: Processing Api
         *
         * Table.processing(true) - Show processing message
         * Table.processing(false) - Hide processing message
         */
        jQuery.fn.dataTable.Api.register('processing()', function(show)
        {
            return this.iterator('table', function(ctx)
            {
                ctx.oApi._fnProcessingDisplay(ctx, show);
            });
        });

        // Rewrite Custom-Settings to Api-Settings
        if (settings.ExtensionRowReorder.Enabled) {
            settings.processing = true;

            if (settings.responsive) {
                settings.rowReorder = {
                    selector: 'td:nth-child(2)',
                    snapX: 0
                }
            } else {
                settings.rowReorder = {
                    snapX: 0
                };
            }

            if (settings.columnDefs) {
                settings.columnDefs = settings.columnDefs.concat([
//                     {orderable: false, targets: '_all'},
//                     {orderable: true, targets: 0},
//                     {className: 'reorder', targets: settings.responsive ? 1 : 0},
                ]);
            } else {
                settings.columnDefs = [
                    {orderable: true, targets: 0},
                    {orderable: false, targets: '_all'},
                    {className: 'reorder', targets: settings.responsive ? 1 : 0},
                ];
            }

            if (options.ExtensionRowReorder.Event) {
                if (options.ExtensionRowReorder.Event.Success) {
                    settings.ExtensionRowReorder.Event.Success = new Function(
                        options.ExtensionRowReorder.Event.Success
                    )
                }
                if (options.ExtensionRowReorder.Event.Error) {
                    settings.ExtensionRowReorder.Event.Error = new Function(
                        options.ExtensionRowReorder.Event.Error
                    )
                }
            }
        }

        // Rewrite Custom-Settings to Api-Settings
        if (settings.ExtensionRowExchange.Enabled) {
            settings.processing = true;
            settings.responsive = false;

            if (options.ExtensionRowExchange.Event) {
                if (options.ExtensionRowExchange.Event.Success) {
                    settings.ExtensionRowExchange.Event.Success = new Function(
                        options.ExtensionRowExchange.Event.Success
                    )
                }
                if (options.ExtensionRowExchange.Event.Error) {
                    settings.ExtensionRowExchange.Event.Error = new Function(
                        options.ExtensionRowExchange.Event.Error
                    )
                }
            }
        }

        /**
         * Activate: DataTable
         */
        Table = this.DataTable(settings);

        /**
         * Register: RowReorder-Extension
         */
        if (settings.ExtensionRowReorder.Enabled) {

            Table.on('row-reorder', function(Event, Diff)
            {
                Table.processing(true);
                var postData = {};
                for (var i = 0, ien = Diff.length; i < ien; i++) {
                    var rowData = Table.row(Diff[i].node).data();
                    postData[i] = {
                        pre: Diff[i].oldData,
                        post: Diff[i].newData
                    }
                }
                if (settings.ExtensionRowReorder.Url) {
                    $.post(settings.ExtensionRowReorder.Url,
                        /**
                         * @deprecated Reorder
                         * @see Data
                         */
                        {'Reorder': postData, 'Data': postData, 'Additional': settings.ExtensionRowReorder.Data},
                        "json")
                        .fail(settings.ExtensionRowReorder.Event.Error)
                        .fail(function()
                        {
                            Table.processing(false);
                        })
                        .success(settings.ExtensionRowReorder.Event.Success)
                        .done(function()
                        {
                            Table.processing(false);
                        })
                }
            });
        }

        /**
         * Register: RowExchange-Extension
         */
        if (settings.ExtensionRowExchange.Enabled) {

            $(this).addClass(settings.ExtensionRowExchange.Connect.From);
            var $Table = $(this);

            if (settings.ExtensionRowExchange.Handler.All) {
                $('span.' + settings.ExtensionRowExchange.Handler.All).on('click', function()
                {
                    Table.processing(true);

                    var ExchangeTarget = $('table.' + settings.ExtensionRowExchange.Connect.To).DataTable();

                    var SourceRows = Table.rows()[0];
                    $(SourceRows).each(function(Index)
                    {

                        var SourceRow = $Table.find('tbody tr:eq(' + Index + ')');
                        var Payload = SourceRow.find('td span.ExchangeData').html();
                        var Handler = SourceRow.find('td span.' + settings.ExtensionRowExchange.Handler.From);

                        if (Payload) {

                            Handler.removeClass(
                                settings.ExtensionRowExchange.Handler.From
                            ).addClass(
                                settings.ExtensionRowExchange.Handler.To
                            )

                            var PostData = $.parseJSON(Payload);
                            var TargetRow = Table.row(SourceRow);

                            if (settings.ExtensionRowExchange.Url) {
                                $.post(settings.ExtensionRowExchange.Url,
                                    {
                                        'Direction': settings.ExtensionRowExchange.Connect,
                                        'Data': PostData,
                                        'Additional': settings.ExtensionRowExchange.Data
                                    }, "json")
                                    .fail(settings.ExtensionRowExchange.Event.Error)
                                    .fail(function()
                                    {
                                        Table.processing(false);
                                        ExchangeTarget.processing(false);
                                    })
                                    .success(settings.ExtensionRowExchange.Event.Success)
                                    .success(function()
                                    {
                                        ExchangeTarget.row.add(SourceRow).draw();
                                        TargetRow.remove().draw();
                                    })
                                    .done(function()
                                    {
                                        Table.processing(false);
                                        ExchangeTarget.processing(false);
                                    })
                            }
                        }
                    })
                })
            }

            Table.on('click', 'tbody tr .' + settings.ExtensionRowExchange.Handler.From, function()
            {

                Table.processing(true);

                var ExchangeTarget = $('table.' + settings.ExtensionRowExchange.Connect.To).DataTable();
                var SourceRow = $(this).closest('tr');
                var Payload = $(this).closest('td').find('span.ExchangeData').html();
                var PostData = $.parseJSON(Payload);

                $(this).removeClass(
                    settings.ExtensionRowExchange.Handler.From
                ).addClass(
                    settings.ExtensionRowExchange.Handler.To
                )

                var TargetRow = Table.row(SourceRow);
                ExchangeTarget.row.add(SourceRow).draw();
                TargetRow.remove().draw();
                if (settings.ExtensionRowExchange.Url) {
                    $.post(settings.ExtensionRowExchange.Url,
                        {
                            'Direction': settings.ExtensionRowExchange.Connect,
                            'Data': PostData,
                            'Additional': settings.ExtensionRowExchange.Data
                        }, "json")
                        .fail(settings.ExtensionRowExchange.Event.Error)
                        .fail(function()
                        {
                            Table.processing(false);
                            ExchangeTarget.processing(false);
                        })
                        .success(settings.ExtensionRowExchange.Event.Success)
                        .done(function()
                        {
                            Table.processing(false);
                            ExchangeTarget.processing(false);
                        })
                }
            }).on('mouseover', 'tbody tr .' + settings.ExtensionRowExchange.Handler.From, function()
            {
                $(this).css('cursor', 'pointer');
            });
        }
        return this;
    };

}(jQuery));
