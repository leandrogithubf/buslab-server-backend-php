import App from './App.js';

var submitModalTriggerBinded = false;

/**
 * @todo Get options from a json generated on server side and set on twig
 * @todo review method detectSetData
 * @todo review method setHandler
 * @todo review method displaySelect
 */
class Modal {
    constructor ()
    {
        this.$modal = $("#modal-form");

        this.$modal.on('hiden.bs.modal hide.bs.modal', function () {
            $(this).find(".modal-content-form").html('').hide();
            $('.modal-content:visible').hide();
        });
    }

    getModalElement()
    {
        return this.$modal;
    }

    /**
     * Opens a giving modal by its selector and replaces the content with the
     * content parameter.
     *
     * @param  {string} selector   The modal selector to be opened
     * @param  {string} content    The modal content
     *
     * @return {jQuery} The shown element by the selector inside the modal
     */
    open(selector, content, modal)
    {
        var self = this;

        // First it hides the modal, so if any other content was visible, it can
        // be changed accordingly
        self.$modal.modal({
            backdrop: 'static',
            keyboard: false,
        });

        // Action to submit form inside modal pressing ctrl + enter
        if (!submitModalTriggerBinded) {
            $(document).bind("keydown", function (e) {
                if (self.$modal.is(':visible')) {
                    if (e.ctrlKey && e.keyCode == 13) {
                        self.$modal.find('form').submit();
                    }
                } else {
                    $(this).unbind(e);
                    submitModalTriggerBinded = false;
                }
            });

            submitModalTriggerBinded = true;
        }

        // As all the modals are inside a single modal element, changing only
        // the content, you must first hide any shown one and display the
        // current modal with the following two lines
        $('.modal-content:visible, .modal-content-loading').hide();

        if (modal) {
            var $div = $(modal);
            $div.appendTo('.modal-dialog').fadeIn();
        } else {
            // Displays the given content by selector
            var $div = self.$modal.find(selector).fadeIn();
        }

        if (content) { // If the content is changing only
            $div.html(content);

            this.$modal.trigger('modal-content-open-ended');
        }

        return $div;
    }

    /**
     * Closes the modal.
     *
     * @return {void}
     */
    close()
    {
        $("#modal-form").modal('close');
    }

    /**
     * Opens an ajax modal with the url and method from the element being passed
     *
     * @param  {jQuery} $element
     *
     * @return {void}
     */
    formContent($element, customDataUrl = null, customMethod = null)
    {
        var attrUrl = customDataUrl || 'url';

        var method = $element.data('method') || customMethod || 'GET';

        if (!$element.data(attrUrl) || $element.data(attrUrl).length == 0) {
            return;
        }

        this.ajax($element.data(attrUrl), method);
    }

    /**
     * Opens the modal to handle data removal from the system.
     *
     * @param  {jQuery} $element    The event dispatcher element
     * @return {void}
     *
     */
    deletionContent($element, customDataUrl = null)
    {
        var attrUrl = customDataUrl || 'url';

        if (!$element.data(customDataUrl) || $element.data(customDataUrl).length == 0) {
            return;
        }

        // Otherwise, if there is no treatment, just open the deletion modal
        var self = this;

        self.open('.modal-content-confirm');

        self.$modal.find(".remove").off("click").on("click", function (e) {
            self.deleteAjax($element, false, false, customDataUrl);
        });
    }

    /**
     * Opens the modal to handle data reactivation.
     *
     * @param  {jQuery} $element The event dispatcher element
     * @return {void}
     */
    reactivationContent($element, customDataUrl = null, $resultElement = null)
    {
        var attrUrl = customDataUrl || 'url';
        if (!$element.data(attrUrl) || $element.data(attrUrl).length == 0) {
            return;
        }

        var self = this;

        self.open('.modal-content-loading');

        $.ajax({
            url: $element.data('url'),
            method: ($element.data('method') || 'GET'),
        }).done(function (response) {
            var $row = $resultElement || $element.closest('tr');
            $row.replaceWith(response.row);

            self.open('.modal-content-success').find('.message').html(response.message);
        }).fail(function(jqXHR, textStatus, errorThrown){
            self.open('.modal-content-error');
        });
    }

    /**
     * Loads a modal with an ajax call to the url and method passed as arguments
     *
     * @param  {string} url
     * @param  {string} method
     * @return {void}
     */
    ajax(url, method)
    {
        var self = this;

        self.open('.modal-content-loading');

        $.ajax({
            url: url,
            method: method || 'GET',
        }).done(function (response) {
            self.open('.modal-content-default', response);
        }).fail(function(jqXHR, textStatus, errorThrown){
            self.open('.modal-content-error');
        });
    }

    /**
     * Makes the ajax call to remove an element
     *
     * @param  {jQuery} $element
     * @param  {boolean}  $isConfirmed
     * @return {void}
     */
    deleteAjax($element, $isConfirmed, $setNewId, customDataUrl = null)
    {
        var self = this;

        var attrUrl = customDataUrl || 'url';

        self.open('.modal-content-loading');

        $.ajax({
            url: $element.data(attrUrl),
            method: 'DELETE',
            data: {
                confirmed: $isConfirmed,
                setNewId: $setNewId
            }
        }).done(function (response) {
            var $row = $element.closest('tr');
            $row.replaceWith($(response.row));

            self.open('.modal-content-success').find('.message').html(response.message);
        }).fail(function (response) {
            if (response.status == 300) {
                self.open(null, null, response.responseJSON.modal);

                self.$modal.find(".remove").off("click").on("click", function (e) {
                    self.deleteAjax($element, true, $('.set-new-select').val());
                });

            } else {
                self.open('.modal-content-error').find('.message').html(response.responseJSON.message);
            }
        });
    }
};

export default Modal;
