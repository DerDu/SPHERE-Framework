var Client = (function ()
{
    'use strict';
    var useDelay = 30;
    var useConfig = {};
    var setModule = function (Module, Depending, cTag)
    {
        useConfig[Module] = {
            Depending: Depending,
            Source: window.location.pathname.slice(
                0, window.location.pathname.search('/')
            ) + '/Common/Script/' + Module + '.js' + cTag,
            /**
             * @return {boolean}
             */
            Test: function ()
            {
                return 'undefined' !== typeof jQuery.fn[Module];
            },
            isUsed: false,
            isLoaded: false,
            Retry: 0,
            isReady: function (Callback)
            {
                var dependingModule, dependingSize = this.Depending.length - 1;
                for (dependingSize; 0 <= dependingSize; dependingSize--) {
                    dependingModule = this.Depending[dependingSize];
                    if (useConfig[dependingModule].Test()) {
                        if (!useConfig[dependingModule].isReady()) {
                            loadModule(dependingModule);
                            return false;
                        }
                    } else {
                        loadModule(dependingModule);
                        return false;
                    }
                }
                if (this.Test()) {
                    this.isLoaded = true;
                    return true;
                } else {
                    if ('undefined' !== typeof Callback) {
                        loadModule(Module, Callback);
                    }
                    return false;
                }
            }
        };
    };
    var setSource = function (Module, Source, Test)
    {
        defineSource(Module, [], Source, Test);
    };
    var defineSource = function (Module, Depending, Source, Test)
    {
        useConfig[Module] = {
            Depending: Depending,
            Source: Source,
            Test: Test,
            isUsed: false,
            isLoaded: false,
            Retry: 0,
            isReady: function (Callback)
            {
                var dependingModule, dependingSize = this.Depending.length - 1;
                for (dependingSize; 0 <= dependingSize; dependingSize--) {
                    dependingModule = this.Depending[dependingSize];
                    if (useConfig[dependingModule].Test()) {
                        if (!useConfig[dependingModule].isReady()) {
                            loadModule(dependingModule);
                            return false;
                        }
                    } else {
                        loadModule(dependingModule);
                        return false;
                    }
                }
                if (this.Test()) {
                    this.isLoaded = true;
                    return true;
                } else {
                    if ('undefined' !== typeof Callback) {
                        loadModule(Module, Callback);
                    }
                    return false;
                }
            }
        };
    };
    var loadScript = function (Source)
    {
        var htmlElement = document.createElement("script");
        htmlElement.src = Source;
        document.body.appendChild(htmlElement);
    };
    var loadModule = function (Module)
    {
        if (!useConfig[Module].isUsed) {
            loadScript(useConfig[Module].Source);
            useConfig[Module].isUsed = true;
        }
    };
    var waitModule = function (Module, Callback)
    {
        if (useConfig[Module].isReady(Callback)) {
            return Callback();
        } else {
            if (10000 < useConfig[Module].Retry) {
                if (console && console.log) {
                    console.log('Unable to load ' + Module)
                }
                if ('undefined' != typeof jQuery) {
                    jQuery('span.loading-indicator').hide();
                    jQuery('span.loading-error').show();
                }
                return false;
            } else {
                useConfig[Module].Retry++;
            }
            window.setTimeout(function ()
            {
                waitModule(Module, Callback);
            }, useDelay);
        }
        return null;
    };
    var setUse = function setUse(Module, Callback)
    {
        if ('function' !== typeof Callback) {
            //noinspection AssignmentToFunctionParameterJS
            Callback = function Callback()
            {
            };
        }
        return waitModule(Module, Callback);
    };
    var useIndicator = function useIndicator()
    {
        window.setTimeout(function ()
        {
            var isFinished = true;
            for (var Index in useConfig) {
                if (useConfig.hasOwnProperty(Index)) {
                    if (useConfig[Index].isUsed && !useConfig[Index].isLoaded) {
                        isFinished = false;
                    }
                }
            }
            if (!isFinished || 'undefined' == typeof jQuery) {
                useIndicator();
            } else {
                jQuery('span.loading-indicator').hide();
            }
        }, useDelay);
    };
    useIndicator();
    return {
        Module: setModule,
        Source: setSource,
        Use: setUse
    };
})();
