(function ($) {
    'use strict';
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

// Init Notify

        if (typeof document.ModAjax == "undefined" || typeof document.ModAjax.NotifyHandler == "undefined") {
            document.ModAjax = {};
            document.ModAjax.NotifyHandler = {};
        }
        var getNotifyObject = function () {
            if (document.ModAjax.NotifyHandler[settings.Notify.Hash]) {
                return document.ModAjax.NotifyHandler[settings.Notify.Hash];
            } else {
                return false;
            }
        }
        var destroyNotifyObject = function () {
            if (document.ModAjax.NotifyHandler[settings.Notify.Hash]) {
                window.setTimeout(function () {
                    document.ModAjax.NotifyHandler[settings.Notify.Hash].close();
                    document.ModAjax.NotifyHandler[settings.Notify.Hash] = null;
                    delete document.ModAjax.NotifyHandler[settings.Notify.Hash];
                }, 1000);
            }
        }
        var parseAjaxError = function (request, status, error) {
            // Parse Error
            var ErrorMessage = '';
            if (request.status === 0) {
                ErrorMessage = ('Not connected.\nPlease verify your network connection.');
            } else if (request.status == 400) {
                ErrorMessage = ('Bad Request. [400]');
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
                ErrorMessage = ('Uncaught Error.\n' + request.responseText);
            }
            return ErrorMessage;
        }
        var domLoadTemplate = '<div data-notify="container" class="col-xs-11 col-sm-4 alert alert-{0}" role="alert">' +
            '<button type="button" aria-hidden="true" class="close" data-notify="dismiss">×</button>' +
            '<span data-notify="icon"><span class="loading-indicator-animate"></span>&nbsp;</span>' +
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
                        message: settings.Notify.onLoad.Message
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
                    Notify.update({message: settings.Notify.onLoad.Message});
                }
                Notify.update({progress: getRandomInt(15, 25)});
            }
        };
        var isErrorEvent = false;
        var onErrorEvent = function (request, status, error) {
            isErrorEvent = true;
            var ErrorMessage = parseAjaxError(request, status, error);
            console.log(request.responseText);
            document.ModAjax.NotifyHandler[settings.Notify.Hash + '-Error'] = $.notify({
                title: ErrorMessage,
                message: '<span class="text-muted"><small>' + this.url + '</small></span>'
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
        };
        var onSuccessEvent = function (Response) {
            var Notify = getNotifyObject();
            if (Notify) {
                Notify.update({progress: getRandomInt(75, 85)});
            }

            for (var Index in settings.Receiver) {
                var callReceiver;
                if (settings.Receiver.hasOwnProperty(Index)) {
                    try {
                        callReceiver = new Function('Response', settings.Receiver[Index]);
                        callReceiver(Response);
                    } catch (ErrorMessage) {
                        if( console && console.log ) {
                            console.log(ErrorMessage);
                        }
                        document.ModAjax.NotifyHandler[settings.Notify.Hash + '-Error'] = $.notify({
                            title: 'Script-Error',
                            message: '<span class="text-muted"><small>' + ErrorMessage + '</small></span>'
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
            executeScript = function (Script) {
                Script();
            };
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
                if (settings.Notify.onSuccess.Message.length > 0 || settings.Notify.onSuccess.Title.length > 0) {
                    var Notify = getNotifyObject();
                    if (Notify) {
                        Notify.update({
                            progress: getRandomInt(90, 95),
                            type: 'success',
                            title: settings.Notify.onSuccess.Title,
                            message: settings.Notify.onSuccess.Message
                        });
                    }
                }
                destroyNotifyObject();
            });
        };
        var callContent = function (Content, Callback) {
            executeScript = function (Script) {
                Script();
            };
            onSuccessEvent(Content);
            if (Callback == false) {
                Callback = function () {
                    if (settings.Notify.onSuccess.Message.length > 0 || settings.Notify.onSuccess.Title.length > 0) {
                        var Notify = getNotifyObject();
                        if (Notify) {
                            Notify.update({
                                progress: getRandomInt(90, 95),
                                type: 'success',
                                title: settings.Notify.onSuccess.Title,
                                message: settings.Notify.onSuccess.Message
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

