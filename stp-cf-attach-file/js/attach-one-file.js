jQuery(function ($) {

    var frame;

    // Attach file
    $(document).on('click', '.js-add-file', function (event) {

        event.preventDefault();
        // If upload window available, simple open her.
        if (frame) {
            frame.open();
            return;
        }

        // If don't,else create new.
        frame = wp.media({
            title: "Выберите файл",
            button: {
                text: "Использовать этот файл"
            },
            multiple: false // single or many files need
        });

        // View info attach file.
        frame.on('select', function() {
            // Get object with all information about attach file.
            var attachment = frame.state().get('selection').first().toJSON();

            $('.js-add-wrap').html('<div class="add_file js-add_file_itm">' +
            '<input type="hidden" name="add_file_id" value="' + attachment.id + '">' +
            '<div class="add_file_icon"><img src="' + attachment.icon + '" alt=""></div>' +
            '<p class="add_file_name">' + attachment.title + '</p>' +
            '<a href="#" class="button button-primary button-large js-add-file-remove">Открепить файл</a>' +
            '</div>');
        });

        // Open the file.
        frame.open();
    });

    // Unpin the file.
    $(document).on('click', '.js-add-file-remove', function (event) {
        event.preventDefault();
        $(this).closest('.js-add_file_itm').remove();
    });
});