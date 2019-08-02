// customize dropzone: configure Dropzone to use the reference key.
// Tells Dropzone to not automatically configure itself 
// on any form that has the dropzone class: we're going to do it manually.
Dropzone.autoDiscover = false;

$(document).ready(function() {
    // initialise dropzone
    initializeDropzone();
    var $locationSelect = $('.js-article-form-location');
    var $specificLocationTarget = $('.js-specific-location-target');

    $locationSelect.on('change', function(e) {
        $.ajax({
            url: $locationSelect.data('specific-location-url'),
            data: {
                location: $locationSelect.val()
            },
            success: function (html) {
                if (!html) {
                    $specificLocationTarget.find('select').remove();
                    $specificLocationTarget.addClass('d-none');

                    return;
                }

                // Replace the current field and show
                $specificLocationTarget
                    .html(html)
                    .removeClass('d-none')
            }
        });
    });

    // initializeDropzone(): find the form element and initialize Dropzone on it.
    function initializeDropzone() {
        var formElement = document.querySelector('.js-reference-dropzone');

        if (!formElement) {
            return;
        }
        // Finally, initialize things with var dropzone = new Dropzone(formElement). 
        // And now we can pass an array of options. The one we need now is paramName. Set it to reference.
        var dropzone = new Dropzone(formElement, {
            paramName: 'reference'
        });
    }
});
