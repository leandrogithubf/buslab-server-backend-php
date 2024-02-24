// Default libraries
import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import '@fortawesome/fontawesome-free/css/all.min.css';

// Libraries
import 'jquery-mask-plugin';
import 'jquery-maskmoney/dist/jquery.maskMoney.min.js';
import 'select2';
import 'select2/dist/css/select2.css';

// Jquery Validation
import 'jquery-validation';
import 'jquery-validation/dist/localization/messages_pt_BR.min.js';
import 'jquery-validation/dist/localization/methods_pt.js';
import './src/jquery-validation-extra.js';

// FAB
import '../css/mfb.min.css';
import '../js/mfb.min.js';

// Default styles
import '../css/app.css';
import '../css/g-recaptcha.css';

// Default custom libraries
import './src/Prototype.js';
import Modal from './src/Modal.js';
import App from './src/App.js';

// Imports the theme files
import 'eonasdan-bootstrap-datetimepicker';
import 'eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css';

(function ($){
    $("document").ready(function () {
        var app = new App();
        var modal = new Modal();

        modal.getModalElement().on('modal-content-open-ended', function() {
            // TODO: Apply dropzone

            app
                .mask($(this).find('form'))
                .validate($(this).find('form'))
                .select2($(this).find('form'))
                .datetimepicker($(this).find('form'))
            ;
        });

        $('form').submit(function(event) {
            if ($('form .g-recaptcha').length > 0 && grecaptcha.getResponse().length === 0) {
                $('.recaptcha-error-required').hide();
                $('form .g-recaptcha').append('<p class="recaptcha-error-required text-center">Você precisa confirmar que não é um robô!</p>');
                event.preventDefault();
                event.stopPropagation();
            } else {
                $('.recaptcha-error-required').hide();
            }
        });

        $("body").on("click", ".fab-form,.edit-form,.fab-form,.duplicate-form,.show-modal,.new-btn", function (e) {
            e.preventDefault();

            modal.formContent($(this));
        });

        $("body").on("click", ".show-modal", function (e) {
            e.preventDefault();

            var self = this;

            $.ajax({
                url: $(self).data('url'),
                method: 'GET'
            }).done(function (response) {
                modal.open(null, null, response);
            }).fail(function (response) {
                modal.open('.modal-content-error').find('.message').html(response.responseJSON.message);
            });
        });

        $("body").on("click", ".prompt-removal", function (e) {
            e.preventDefault();

            modal.deletionContent($(this));
        });

        $("body").on("click", ".prompt-reactivation", function (e) {
            e.preventDefault();

            modal.reactivationContent($(this));
        });

        $("body").on("click", ".submit", function() {
            $.each($('.select2-selection'), function (index, element) {
                var select = $(element).parent().parent().siblings('select')[0];
                var form = select.form;

                if (!$(form).validate().element(select)) {
                    $(element).addClass('error');
                } else {
                    $(element).removeClass('error');
                }
            });
        });

        $("#modal-form").on("click", ".change-form", function (e) {
            e.preventDefault();

            var $currentForm = $("#modal-form").find("form");
            $currentForm.trigger('submit', [$(this).data('form-route')]);
        });

        $("#modal-form").on("submit", "form", function(e, newForm = null) {
            e.preventDefault();

            var $this = $(this);
            var $parent = $this.parent();

            modal.open('.modal-content-loading');

            var formData = new FormData(this);

            $.ajax({
                url: $this.attr('action'),
                type: $this.attr('method'),
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                xhr: function() { // Custom XMLHttpRequest
                    var myXhr = $.ajaxSettings.xhr();
                    if (myXhr.upload) { // Avalia se tem suporte a propriedade upload
                        myXhr.upload.addEventListener('progress', function() {
                            /* faz alguma coisa durante o progresso do upload */
                        }, false);
                    }
                    return myXhr;
                }
            }).done(function (response) {
                $("body").trigger('onSubmitResponse', [response]);

                if (response.row) {
                    if (response.entity) {
                        let identifier = response.entity.identifier || response.entity.id;
                        if ($('#list-item-'+identifier).length > 0) {
                            $('#list-item-'+identifier).replaceWith($(response.row));
                        } else {
                            $(".table-entity-list tbody tr:first").before($(response.row));
                        }
                    } else {
                        $(".table-entity-list tbody tr:first").before($(response.row));
                    }
                }

                $('.empty-set').hide();

                if (newForm && newForm.length > 0) {
                    modal.ajax(newForm, 'GET');
                    return;
                }

                if (response.next_step && response.next_step.length > 0) {
                    modal.ajax(response.next_step, 'GET');
                    return;
                }

                modal.open('.modal-content-success').find('.message').html(response.message);

                if (response.redirect && response.redirect.length > 0) {
                    setTimeout(function () {
                        modal.open('.modal-content-loading');

                        window.location.replace(response.redirect);
                    }, 2000);
                }
            }).fail(function(jqXHR, textStatus, errorThrown){
                var messages = '';

                if (typeof jqXHR.responseJSON.errors === "string") {
                    messages += jqXHR.responseJSON.errors;
                } else {
                    $.each(jqXHR.responseJSON.errors, function (index, error) {
                        messages += error.message+'<br>';
                    });
                }

                modal.open('.modal-content-error');

                var $errorModal = modal.$modal;

                $errorModal.find('.message').html(messages);
                $errorModal.on('hidden.bs.modal', function (e) {
                    modal.open($parent);
                    $errorModal.off('hidden.bs.modal');
                })
            });
        });

        $('.forgot-pass').on('click', function(e) {
            e.preventDefault();

            window.location = $(this).data('url') + "?email=" + encodeURIComponent($('.email').val());
        });
    });

    /**
     * Resize the reCaptcha element
     */
    if ($(window).width() < 463) {
        $('.g-recaptcha').attr('data-size', 'compact');
    } else {
        $('.g-recaptcha').attr('data-size', 'normal');
    }

    var lastVerifyLoginRequest = false;

    function verifyLogged() {
        if (!lastVerifyLoginRequest) {
            lastVerifyLoginRequest = true;
            $.ajax({
                url: '/logged',
            })
            .done(function(response) {
                if (response.logged) {
                    $('.modal-login').hide();
                    if (response.logged !== $('.modal-login-content').data('user')) {
                        location.replace($('.modal-login-content').data('redirect'))
                    }
                } else {
                    if (!$('.modal-login').is(':visible')) {
                        $('.modal-login-iframe').attr('src', function ( i, val ) { return val; });
                        $('.modal-login').show();
                    }
                }
                lastVerifyLoginRequest = false;
            });
        }

        var timeout = 10000;
        if ($('.modal-login').is(':visible')) {
            timeout = 500;
        }

        setTimeout(() => {
            verifyLogged();
        }, timeout);
    }

    if ($('.modal-login').length > 0) {
        verifyLogged();
    }
})(jQuery);
