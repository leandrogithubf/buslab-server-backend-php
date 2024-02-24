$.validator.messages.required = "Este campo é obrigatório";

$.validator.addMethod(
    "date",
    function (value, element) {
        var bits = value.match(/([0-9]+)/gi), str;
        if (!bits)
            return this.optional(element) || false;
        str = bits[1] + '/' + bits[0] + '/' + bits[2];
        return this.optional(element) || !/Invalid|NaN/.test(new Date(str));
    },
    "Por favor, forne&ccedil;a uma data correta."
);

$.validator.addMethod(
    "maxMoneyValue",
    function(value, element, params) {
        value = value.moneyToDouble();

        return (value <= params);
    },
    $.validator.format("O valor máximo permitido é de {0}")
);

$.validator.addMethod(
    "minMoneyValue",
    function(value, element, params) {
        value = value.moneyToDouble();
        return (value >= params);
    },
    $.validator.format("O valor mínimo permitido é de {0}")
);
