;(function($){
    var xhr = [];

    $.fn.extend({
        selectCascade: function(options){
            return this.each(function () {
                $(this).on("change", function(){
                    var options = $.extend({}, $.fn.selectCascade.defaults, options, $(this).data());

                    var $this = $(this),
                        $resultElement = $("select" + options.result_element).html("").val("").trigger("change").prop("disabled", true),
                        parameterName = options.parameter,
                        value = $this.val(),
                        valueStr,
                        $icon = options.generateLoadingIcon();

                    if (!$resultElement || $resultElement.length == 0) {
                        console.log("Necessário definir o elemento de resultados para a busca.");
                        return false;
                    }

                    if (!parameterName || parameterName.length == 0) {
                        console.log("Necessário definir o nome de parâmetro para a busca.");
                        return false;
                    }

                    if (parameterName.constructor == Object) {
                        valueStr = [];
                        valueStr.push('id=' + value);
                        for (let key in parameterName) {
                            valueStr.push(key + '=' + $(parameterName[key]).val());
                        }
                        valueStr = valueStr.join('&');
                    } else {
                        if ((typeof value) === "string") {
                            valueStr = parameterName + '=' + value;
                        } else {
                            valueStr = [];
                            for (let key in value) {
                                valueStr.push(parameterName + '[]=' + value[key]);
                            }
                            valueStr = valueStr.join('&');
                        }
                    }

                    var xhr_identifier = $(this).attr('id') + options.resource_url.replace(/\W/g, "");

                    if (xhr[xhr_identifier]) {
                        xhr[xhr_identifier].abort();
                    }

                    if(options.loading_icon === true){
                        options.addLoading($icon, $resultElement);
                    }

                    xhr[xhr_identifier] = $.ajax({
                        type: 'GET',
                        url: options.resource_url + "?" + valueStr + "&page_size=-1",
                        success: function(data) {
                        if (!data) {
                            return;
                        }

                        var list = (data.collection || data.items || data.list);

                        if (list && list.length > 0) {
                            var resultElementValues = $resultElement.val();

                            if (options.add_empty === true) {
                                $resultElement.append($("<option>"));
                            }

                            for (key in list) {
                                var $option = $("<option>");

                                var identifierAttr;
                                if (options.value_index) {
                                    identifierAttr = options.value_index;
                                } else if (list[key].hasOwnProperty('identifier')) {
                                    identifierAttr = 'identifier';
                                } else {
                                    identifierAttr = 'id';
                                }

                                if (Array.isArray(options.description_index)) {
                                    var descricaoList = [];
                                    for (let i = 0; i < options.description_index.length; i++) {
                                        descricaoList.push(list[key][options.description_index[i]]);
                                    }
                                    $option.html(descricaoList.join(' - '));
                                } else {
                                    $option.html(list[key][options.description_index]);
                                }

                                $option.val(list[key][identifierAttr]);

                                if (resultElementValues && Array.isArray(resultElementValues)) {
                                    if (resultElementValues.indexOf(list[key][identifierAttr].toString()) >= 0) {
                                        $option.prop('selected', true).attr('selected', 'selected');
                                    }
                                }
                                $resultElement.append($option);
                            }
                        }
                        $resultElement.prop("disabled", false);
                        options.removeLoading($icon);

                        $this.trigger("chainedSelect-ended");
                    },
                    fail: function(jqXHR, textStatus, errorThrown) {
                        if (textStatus !== 'abort') {
                            swal("Atenção!", "Problemas com a conexão com a internet. Tente novamente mais tarde.", "error");
                        }
                        options.removeLoading($icon);

                        $resultElement.prop("disabled", false);
                        options.removeLoading($icon);

                        $this.trigger("chainedSelect-ended");
                    },
                });

                    if (options.cascade) {
                        $resultElement.trigger("change");
                    }
                });
            });
        }
    })

    $.fn.selectCascade.defaults = {
        cascade: true,
        result_element: null,
        parameter: null,
        resource_url: null,
        loading_icon: true,
        add_empty: true,
        description_index: "name",
        addLoading: function($icon, $container) {
            $container.siblings("label").append($icon);
        },
        removeLoading: function($icon) {
            if ($icon && $icon.length > 0) {
                $icon.fadeOut("fast", function(){
                    $(this).remove();
                });
            }
        },
        generateLoadingIcon: function () {
            return $("<i>").addClass('fa fa-circle-o-notch fa-spin fa-pulse fa-fw');
        }
    };
})(jQuery);
