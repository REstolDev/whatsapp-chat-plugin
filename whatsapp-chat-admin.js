jQuery(document).ready(function($) {
    var frame;

    $('#whatsapp_icon_button').on('click', function(e) {
        e.preventDefault();

        if (frame) {
            frame.open();
            return;
        }

        frame = wp.media({
            title: 'Select or Upload Media',
            button: {
                text: 'Use this media'
            },
            multiple: false
        });

        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $('#whatsapp_icon').val(attachment.url);
            $('#whatsapp_icon_preview').attr('src', attachment.url);
        });

        frame.open();
    });
});
