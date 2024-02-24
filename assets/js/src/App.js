class App
{
    constructor()
    {
        $.fn.select2.defaults.set("allowClear", true);
        $.fn.select2.defaults.set("placeholder", "");
        $.fn.select2.defaults.set("minimumResultsForSearch", 100);
        $.fn.select2.defaults.set("sorter", function(data) {
            return data.sort(function (a, b) {
                a = a.text.toLowerCase();
                b = b.text.toLowerCase();

                if (a > b) {
                    return 1;
                }

                if (a < b) {
                    return -1;
                }

                return 0;
            });
        });

        this
            .mask($("body"))
            .validate($("body"))
            .select2($("body"))
            .datetimepicker($("body"))
        ;
    };

    validate ($container)
    {
        $container.validate({
            ignore: ":hidden, .ignore",
            errorPlacement: function(error, element) {
                var elem = $(element);
                if (elem.hasClass("select2-hidden-accessible")) {
                    element = $("#select2-" + elem.attr("id") + "-container").parent();
                }

                error.insertAfter(element);
            }
        });

        return this;
    }

    mask ($container)
    {
        $container.find('.date').mask('d0/m0/y000', {
            selectOnFocus: true,
            translation: {
                'd': {
                    pattern: /[0-3]/
                },
                'm': {
                    pattern: /[0-1]/
                },
                'y': {
                    pattern: /[1-2]/
                }
            }
        });

        $container.find('.time').mask('h0:m0', {
            selectOnFocus: true,
            translation: {
                'h': {
                    pattern: /[0-2]/
                },
                'm': {
                    pattern: /[0-5]/
                }
            }
        });

        $container.find('.time-full').mask('h0:m0:s0', {
            selectOnFocus: true,
            translation: {
                'h': {
                    pattern: /[0-2]/
                },
                'm': {
                    pattern: /[0-5]/
                },
                's': {
                    pattern: /[0-5]/
                }
            }
        });

        $container.find('.date-time').mask('d0/m0/y000 h0:m0', {
            selectOnFocus: true,
            translation: {
                'd': {
                    pattern: /[0-3]/
                },
                'm': {
                    pattern: /[0-1]/
                },
                'y': {
                    pattern: /[1-2]/
                },
                'h': {
                    pattern: /[0-2]/
                },
                'm': {
                    pattern: /[0-5]/
                }
            }
        });

        $container.find('.date-time-full').mask('d0/m0/y000 h0:m0:s0', {
            selectOnFocus: true,
            translation: {
                'd': {
                    pattern: /[0-3]/
                },
                'm': {
                    pattern: /[0-1]/
                },
                'y': {
                    pattern: /[1-2]/
                },
                'h': {
                    pattern: /[0-2]/
                },
                'm': {
                    pattern: /[0-5]/
                },
                's': {
                    pattern: /[0-5]/
                }
            }
        });

        var guessPhoneMaskBehavior = function (val) {
            return val.replace(/\D/g, '').length === 11 ? '(a0) b0000-0000' : '(a0) b000-00000';
        };
        $container.find('.guess-phone-br').mask(guessPhoneMaskBehavior, {
            selectOnFocus: true,
            onKeyPress: function(val, e, field, options) {
                field.mask(guessPhoneMaskBehavior.apply({}, arguments), options);
            },
            translation: {
                'a': {
                    pattern: /[1-9]/
                },
                'b': {
                    pattern: /[2-6,8-9]/
                }
            }
        });

        $container.find('.phone-br').mask('(a0) b000-0000', {
            selectOnFocus: true,
            translation: {
                'a': {
                    pattern: /[1-9]/
                },
                'b': {
                    pattern: /[2-5]/
                }
            }
        });

        $container.find('.mobilephone-br').mask('(a0) b0000-0000', {
            selectOnFocus: true,
            translation: {
                'a': {
                    pattern: /[1-9]/
                },
                'b': {
                    pattern: /[9]/
                }
            }
        });

        $container.find('.cpf').mask('000.000.000-00', {
            selectOnFocus: true
        });
        $container.find('.cnpj').mask('00.000.000/0000-00', {
            selectOnFocus: true
        });

        $container.find('.cep').mask('00000-000', {
            selectOnFocus: true
        });

        $container.find('.integer,.numeric').mask('0#', {
            selectOnFocus: true
        });

        $container.find('.alpha').mask('A', {
            selectOnFocus: true,
            translation: {
                'A': {
                    pattern: /[a-zA-Z]/,
                    optional: true,
                    recursive: true
                }
            }
        });

        $container.find('.alpha-spaced').mask('A', {
            selectOnFocus: true,
            translation: {
                'A': {
                    pattern: /[a-zA-Z\s]/,
                    optional: true,
                    recursive: true
                }
            },
            onKeyPress: function(val, e, field, options) {
                val = val.replace(/\s\s+/g, ' ');

                field.val(val);

                return val;
            },
        });

        $container.find('.alphanum').mask('A', {
            selectOnFocus: true,
            translation: {
                'A': {
                    pattern: /[a-zA-Z0-9]/,
                    optional: true,
                    recursive: true
                }
            }
        });

        $container.find('.alphanum-spaced').mask('A', {
            selectOnFocus: true,
            translation: {
                'A': {
                    pattern: /[a-zA-Z0-9\s]/,
                    optional: true,
                    recursive: true
                }
            },
            onKeyPress: function(val, e, field, options) {
                val = val.replace(/\s\s+/g, ' ');

                field.val(val);

                return val;
            },
        });

        $container.find('.alphanum-special').mask('A', {
            selectOnFocus: true,
            translation: {
                'A': {
                    pattern: /[a-zA-Z0-9\u00C0-\u017F]/,
                    optional: true,
                    recursive: true
                }
            }
        });

        $container.find('.alphanum-special-spaced').mask('A', {
            selectOnFocus: true,
            translation: {
                'A': {
                    pattern: /[a-zA-Z0-9\s\u00C0-\u017F]/,
                    optional: true,
                    recursive: true
                }
            },
            onKeyPress: function(val, e, field, options) {
                val = val.replace(/\s\s+/g, ' ');

                field.val(val);

                return val;
            },
        });

        $container.find('.percent').maskMoney({
            'suffix': '%',
            thousands: '.',
            decimal: ',',
            allowZero: true,
            selectAllOnFocus: true,
            affixesStay: false,
            precision: 2,
        });

        $container.find('.percent-negative').maskMoney({
            'suffix': '%',
            thousands: '.',
            decimal: ',',
            allowZero: true,
            allowNegative: true,
            selectAllOnFocus: true,
            affixesStay: false,
            precision: 2,
        });

        $container.find('.money').maskMoney({
            'prefix': '',
            thousands: '.',
            decimal: ',',
            allowZero: true,
            selectAllOnFocus: true,
            affixesStay: false
        });

        return this;
    }

    select2($container)
    {
        $container.find("select.no-sort").select2({
            sorter: function(data) {
                return data;
            }
        });
        $container.find("select").not('.no-select2').not('.no-sort').select2();
        // $container.find("select.cascade").selectCascade();

        return this;
    }

    datetimepicker ($container) {

        function datetimepickerdefaults(opts) {
            return $.extend({}, {
                sideBySide: true,
                locale:'pt-BR',
                icons: {
                    time: 'fa fa-clock',
                    date: 'fa fa-calendar',
                    up: 'fa fa-chevron-up',
                    down: 'fa fa-chevron-down',
                    previous: 'fa fa-chevron-left',
                    next: 'fa fa-chevron-right',
                    today: 'fa fa-calendar-day',
                    clear: 'fa fa-trash',
                    close: 'fa fa-times'
                }
            }, opts);
        }

        $container.find('.date').datetimepicker(datetimepickerdefaults({
            format: 'L',
        }));

        $container.find('.date-time').datetimepicker(datetimepickerdefaults({
            format: 'L LT',
        }));

        $container.find('.time').datetimepicker(datetimepickerdefaults({
            format: 'LT',
        }));

        $container.find('.time-full').datetimepicker(datetimepickerdefaults({
            format: 'LTS',
        }));

        $container.find('.date-time-full').datetimepicker(datetimepickerdefaults({
            format: 'L LTS',
        }));

        return this;
    }
};

export default App;
