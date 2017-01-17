(function ($) {
    // 'use strict';
    $.fn.ModAjax = function (options) {

// Settings

        var settings = $.extend(true, {
            Receiver: [],
            Notify: {
                Hash: 'default',
                onLoad: {
                    Title: '',
                    Message: '',
                },
                onSuccess: {
                    Title: '',
                    Message: '',
                }
            }
        }, options);

// Init Pipeline

        /**
         * Skip Requests if Pipeline is "busy"
         */
        var occupyPipeline = function () {
            // Set Script-Wrapper from Deferred I/O to Direct I/O
            executeScript = function (Script) {
                Script();
            };
            // Block new Execution Request
            if (document.ModAjax.Running) {
                $.notify({
                    title: 'Die Anfrage kann nicht verarbeitet werden',
                    message: 'Bitte warten Sie bis alle Aktionen abgeschlossen sind'
                }, {
                    z_index: 32768,
                    showProgressbar: false,
                    newest_on_top: true,
                    placement: {from: 'top', align: 'right'},
                    type: 'warning',
                    delay: 1500,
                    template: domWarningTemplate
                });
                return false;
            } else {
                document.ModAjax.Running = true;
                return true;
            }
        };
        /**
         * Listen for Requests if Pipeline not "busy"
         */
        var freePipeline = function () {
            document.ModAjax.Running = false;
        };

// Init Notify

        if (
            typeof document.ModAjax == "undefined"
        ) {
            document.ModAjax = {};
            document.ModAjax.NotifyHandler = {};
            document.ModAjax.NotifyTimeout = {};
            freePipeline();
        }
        var getNotifyObject = function () {
            if (document.ModAjax.NotifyHandler[settings.Notify.Hash]) {
                return document.ModAjax.NotifyHandler[settings.Notify.Hash];
            } else {
                return false;
            }
        };
        var destroyNotifyObject = function (Timeout) {
            if (typeof Timeout == "undefined") {
                Timeout = 1000;
            }

            if (document.ModAjax.NotifyHandler[settings.Notify.Hash]) {
                if (document.ModAjax.NotifyTimeout[settings.Notify.Hash]) {
                    window.clearTimeout(document.ModAjax.NotifyTimeout[settings.Notify.Hash]);
                }
                document.ModAjax.NotifyTimeout[settings.Notify.Hash]
                    = window.setTimeout(function () {
                    document.ModAjax.NotifyHandler[settings.Notify.Hash].close();
                    document.ModAjax.NotifyHandler[settings.Notify.Hash] = null;
                    delete document.ModAjax.NotifyHandler[settings.Notify.Hash];
                    document.ModAjax.NotifyTimeout[settings.Notify.Hash] = null;
                    delete document.ModAjax.NotifyTimeout[settings.Notify.Hash];
                }, Timeout);
            }
        }
        var parseAjaxError = function (request, status, error) {
            // Parse Error
            var ErrorMessage = '';
            if (request.status === 0) {
                ErrorMessage = ('Not connected.\nPlease verify your network connection.');
            } else if (request.status == 400) {
                ErrorMessage = ('Bad Request. [400]');
            } else if (request.status == 403) {
                ErrorMessage = ('Forbidden. [403]');
            } else if (request.status == 404) {
                ErrorMessage = ('The requested page not found. [404]');
            } else if (request.status == 500) {
                ErrorMessage = ('Internal Server Error [500]');
            } else if (request.status == 511) {
                ErrorMessage = ('Network Authentication Required [511]');
            } else if (status === 'parsererror') {
                ErrorMessage = ('Requested JSON parse failed');
            } else if (status === 'timeout') {
                ErrorMessage = ('Time out error.');
            } else if (status === 'abort') {
                ErrorMessage = ('Ajax request aborted.');
            } else {
                ErrorMessage = ('Uncaught Error. ' + request.status + '\n' + request.responseText);
            }
            return ErrorMessage;
        }
        var domLoadTemplate = '<div data-notify="container" class="col-xs-11 col-sm-4 alert alert-{0}" role="alert">' +
            '<button type="button" aria-hidden="true" class="close" data-notify="dismiss">×</button>' +
            '<span data-notify="icon"></span>&nbsp;' +
            '<span data-notify="title">{1}</span><br/>' +
            '<div class="progress" data-notify="progressbar" style="height: 3px; margin: 0;">' +
            '<div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>' +
            '</div>' +
            '<span data-notify="message">{2}</span>' +
            '<a href="{3}" target="{4}" data-notify="url"></a>' +
            '</div>';
        var domWarningTemplate = '<div data-notify="container" class="col-xs-11 col-sm-4 alert alert-{0}" role="alert">' +
            '<button type="button" aria-hidden="true" class="close" data-notify="dismiss">×</button>' +
            '<span data-notify="icon"><span class="glyphicons glyphicons-warning-sign"></span>&nbsp;</span>' +
            '<span data-notify="title">{1}</span><br/>' +
            '<div class="progress" data-notify="progressbar" style="height: 3px; margin: 0;">' +
            '<div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>' +
            '</div>' +
            '<span data-notify="message">{2}</span>' +
            '<a href="{3}" target="{4}" data-notify="url"></a>' +
            '</div>';
        var domErrorTemplate = '<div data-notify="container" class="col-xs-11 col-sm-11 alert alert-{0}" role="alert">' +
            '<button type="button" aria-hidden="true" class="close" data-notify="dismiss">×</button>' +
            '<span data-notify="icon"></span>' +
            '<span data-notify="title">{1}</span><br/>' +
            '<div class="progress" data-notify="progressbar" style="height: 3px; margin: 0;">' +
            '<div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>' +
            '</div>' +
            '<span data-notify="message">{2}</span>' +
            '<a href="{3}" target="{4}" data-notify="url"></a>' +
            '</div>';
        var getRandomInt = function getRandomInt(min, max) {
            min = Math.ceil(min);
            max = Math.floor(max);
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }

// Event Handler

        var onLoadEvent = function (jqXHR, Config) {
            if (settings.Notify.onLoad.Message.length > 0 || settings.Notify.onLoad.Title.length > 0) {
                var Notify = getNotifyObject();
                if (!Notify) {
                    document.ModAjax.NotifyHandler[settings.Notify.Hash] = $.notify({
                        title: settings.Notify.onLoad.Title,
                        message: settings.Notify.onLoad.Message,
                        icon: 'loading-indicator-animate'
                    }, {
                        z_index: 32768,
                        showProgressbar: true,
                        newest_on_top: true,
                        placement: {from: 'top', align: 'right'},
                        type: 'info',
                        delay: 0,
                        template: domLoadTemplate
                    });
                    Notify = getNotifyObject();
                } else {
                    Notify.update('message', settings.Notify.onLoad.Message);
                    Notify.update('type', 'info');
                    Notify.update('icon', 'loading-indicator-animate');
                }
                Notify.update('progress', getRandomInt(5, 25));
                Notify.update('type', 'info');
                Notify.update('icon', 'loading-indicator-animate');
            }
        };

        var isErrorEvent = false;
        var onErrorEvent = function (request, status, error) {
            isErrorEvent = true;
            var ErrorMessage = parseAjaxError(request, status, error);
            if (console && console.log) {
                console.log(request.responseText);
            }
            document.ModAjax.NotifyHandler[settings.Notify.Hash + '-Error'] = $.notify({
                title: ErrorMessage,
                message: '<span class="text-muted"><small><small>' + this.url + '</small></small></span><hr/>' + request.responseText
            }, {
                z_index: 32768,
                newest_on_top: true,
                showProgressbar: false,
                placement: {from: "top", align: "right"},
                type: 'danger',
                delay: 0,
                animate: {enter: 'animated fadeInRight', exit: 'animated fadeOutRight'},
                template: domErrorTemplate
            });
            freePipeline();
        };

        var onSuccessEvent = function (Response) {
            var Notify = getNotifyObject();
            if (Notify) {
                Notify.update({progress: getRandomInt(50, 80)});
            }
            freePipeline();
            for (var Index in settings.Receiver) {
                var callReceiver;
                if (settings.Receiver.hasOwnProperty(Index)) {
                    try {
                        callReceiver = new Function('Response', settings.Receiver[Index]);
                        callReceiver(Response);
                    } catch (ErrorMessage) {
                        if (console && console.log) {
                            console.log(ErrorMessage, Response);
                        }
                        document.ModAjax.NotifyHandler[settings.Notify.Hash + '-Error'] = $.notify({
                            title: 'Script-Error',
                            message: '<span class="text-muted"><small><small>' + ErrorMessage + '</small></small></span><hr/>' + Response
                        }, {
                            z_index: 32768,
                            newest_on_top: true,
                            showProgressbar: false,
                            placement: {from: "top", align: "right"},
                            type: 'danger',
                            delay: 0,
                            animate: {enter: 'animated fadeInRight', exit: 'animated fadeOutRight'},
                            template: domErrorTemplate
                        });
                    }
                }
            }
        };

// Execute

        var callAjax = function (Method, Url, Data, Callback) {
            if (!occupyPipeline()) return;
            try {
                var Payload = JSON.parse(Data);
            } catch (Error) {
                var Trigger = new Function(Data);
                var Payload = Trigger();
            }
            jQuery.ajax({
                method: Method,
                url: Url,
                data: Payload,
                dataType: "json",
                cache: false,
                beforeSend: onLoadEvent,
                error: onErrorEvent,
                success: onSuccessEvent
            }).always(function () {
                if (Callback && !isErrorEvent) {
                    Callback();
                }
                var Notify = getNotifyObject();
                if (Notify) {
                    if (settings.Notify.onSuccess.Message.length > 0 || settings.Notify.onSuccess.Title.length > 0) {
                        Notify.update({
                            progress: 100,
                            type: 'success',
                            title: settings.Notify.onSuccess.Title,
                            message: settings.Notify.onSuccess.Message,
                            icon: 'glyphicons glyphicons-ok'
                        });
                    } else {
                        Notify.update({
                            progress: 100,
                            type: 'success',
                            icon: 'glyphicons glyphicons-ok',
                            message: ''
                        });
                    }
                }
                destroyNotifyObject();
            });
        };
        var callContent = function (Content, Callback) {
            if (!occupyPipeline()) return;
            onSuccessEvent(Content);
            if (Callback == false) {
                Callback = function () {
                    var Notify = getNotifyObject();
                    if (Notify) {
                        if (settings.Notify.onSuccess.Message.length > 0 || settings.Notify.onSuccess.Title.length > 0) {
                            Notify.update({
                                progress: 100,
                                type: 'success',
                                title: settings.Notify.onSuccess.Title,
                                message: settings.Notify.onSuccess.Message,
                                icon: 'glyphicons glyphicons-ok'
                            });
                        } else {
                            Notify.update({
                                progress: 100,
                                type: 'success',
                                icon: 'glyphicons glyphicons-ok',
                                message: ''
                            });
                        }
                        destroyNotifyObject();
                    }
                }
            }
            Callback();
        }

        return {
            'loadAjax': callAjax,
            'loadContent': callContent
        };
    };
}(jQuery));

