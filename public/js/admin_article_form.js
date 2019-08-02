// customize dropzone: configure Dropzone to use the reference key.
// Tells Dropzone to not automatically configure itself 
// on any form that has the dropzone class: we're going to do it manually.
Dropzone.autoDiscover = false;

$(document).ready(function() {
    // initialize ReferenceList with js-target
    var referenceList = new ReferenceList($('.js-reference-list'));

    // initialise dropzone
    initializeDropzone(referenceList);
    var $locationSelect = $('.js-article-form-location');
    var $specificLocationTarget = $('.js-specific-location-target');

    var referenceList = new ReferenceList($('.js-reference-list'));

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
});

/**
 * @param {ReferenceList} referenceList
*/
class ReferenceList
{
    // the constructor 
    constructor($element) {
        this.$element = $element;
        this.references = [];
        // this.render(): fill the ul element
        this.render();
        $.ajax({
            url: this.$element.data('url')
        }).then(data => {
            this.references = data;
            this.render();
        })
    }
    //this.references.map is a fancy way to loop over the references array,
    //create a string of html
    //this.references.map is a fancy way to loop over the references array, which is empty at the start, but won't be forever. 
    //For each reference, it creates a string of HTML that is basically a copy of what we had in our template before. 
    //This uses a feature called template literals that allows us to create a multi-line string with variables inside - 
    //like reference.originalFilename and referenced.id.
    //Finally, at the bottom, we take all that HTML and stick it into the element. 
    render() {
        const itemsHtml = this.references.map(reference => { 
            return ` 
<li class="list-group-item d-flex justify-content-between align-items-center"> 
    ${reference.originalFilename}
    <span>
        <a href="/admin/article/references/${reference.id}/download"><span class="fa fa-download"></span></a>
    </span>
</li>
`
        });
        this.$element.html(itemsHtml.join(''));
    }
};


// initializeDropzone(): find the form element and initialize Dropzone on it.
function initializeDropzone() {
    var formElement = document.querySelector('.js-reference-dropzone');
    if (!formElement) {
        return;
    }
    // Finally, initialize things with var dropzone = new Dropzone(formElement). 
    // And now we can pass an array of options. The one we need now is paramName. Set it to reference.
    var dropzone = new Dropzone(formElement, {
        paramName: 'reference',
        // return validation error
        // Need to customize something in dropzone use init function
        // mofify error
        //      Because the real validation message lives on the detail key, 
        //      we can say: if data.detail, this.emit('error') passing file and the actual error message string: data.detail.
        init: function() {
            this.on('error', function(file, data) {
                if (data.detail) {
                    this.emit('error', file, data.detail);
                }
            });
            this.on('success', function(file, data) {
                this.references.push(reference);
                this.render();
            });
        }
    });
};





