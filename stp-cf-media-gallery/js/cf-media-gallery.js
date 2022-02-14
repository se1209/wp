jQuery(function ($) {
    var gallery_frame;
    var $gallery_image_ids = $('#gallery-image');
    var $images = $('#images-container ul.images');

    jQuery('.add-images').on( 'click', 'a', function ( event ) {
        var $el = $(this);
        var attachment_ids = $gallery_image_ids.val();

        event.preventDefault();

        if (gallery_frame) {
            gallery_frame.open();
            return;
        }

        gallery_frame = wp.media.frames.gallery = wp.media({
            title: $el.data('choose'),
            button: {
                text: $el.data('update'),
            },
            states: [
                new wp.media.controller.Library({
                    title: $el.data('choose'),
                    filterable: 'all',
                    multiple: true,
                })
            ]
        });

        gallery_frame.on('select', function () {
            var selection = gallery_frame.state().get('selection');

            selection.map(function (attachment) {
                attachment = attachment.toJSON();

                if (attachment.id) {
                    attachment_ids = attachment_ids ? attachment_ids + "," + attachment.id : attachment.id;
                    attachment_image = attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;

                    $images.append('\
                            <li class="image" data-attachment_id="' + attachment.id + '">\
                                <img src="' + attachment_image + '">\
                                <ul class="actions">\
                                    <li>\
                                        <a href="#" class="delete" title="' + $el.data('delete') + '">' + $el.data('text') + '</a>\
                                    </li>\
                                </ul>\
                            </li>');
                }
            });

            $gallery_image_ids.val(attachment_ids);
        });

        gallery_frame.open();
    })

    $images.sortable({
        items: 'li.image',
        cursor: 'move',
        scrollSensitivity: 40,
        forcePlaceholderSize: true,
        forceHelperSize: false,
        helper: 'clone',
        opacity: 0.65,
        placeholder: 'wc-metabox-sortable-placeholder',
        start: function (event, ui) {
            ui.item.css('background-color', '#f6f6f6');
        },
        stop: function (event, ui) {
            ui.item.removeAttr('style');
        },
        update: function (event, ui) {
            var attachment_ids = '';

            $('#images-container ul li.image').css('cursor', 'default').each(function () {
                var attachment_id = jQuery(this).attr('data-attachment_id');
                attachment_ids = attachment_ids + attachment_id + ',';
            });

            $gallery_image_ids.val(attachment_ids);
        }
    });

    // Remove images
    $('#images-container').on('click', 'a.delete', function () {
        $(this).closest('li.image').remove();

        var attachment_ids = '';

        $('#images-container ul li.image').css('cursor', 'default').each(function () {
            var attachment_id = jQuery(this).attr('data-attachment_id');
            attachment_ids = attachment_ids + attachment_id + ',';
        });

        $gallery_image_ids.val(attachment_ids);

        // remove any lingering tooltips
        $('#tiptip_holder').removeAttr('style');
        $('#tiptip_arrow').removeAttr('style');

        return false;
    });
})