Dropzone.autoDiscover = false;

$(document).ready(function() {
    // initialize ReferenceList with js-target
    var referenceList = new ReferenceList($('.js-reference-list'));
    console.log(referenceList);
    // initialise dropzone
    initializeDropzone(referenceList);

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
});

/**
 * @param {ReferenceList} referenceList
*/
class ReferenceList
{
    constructor($element) {
        this.$element = $element;
        // make element sortable
        this.sortable = Sortable.create(this.$element[0], {
            handle: '.drag-handle',
            animation: 150,
        });
        this.references = [];
        this.render();
        // This is called a delegate event handler. 
        // It's handy because it allows us to attach a listener to any .js-reference-delete elements,
        // even if they're added to the HTML after this line is executed. 
        this.$element.on('click', '.js-reference-delete', (event) => {
            // the callBack
            this.handleReferenceDelete(event);
        });
        // 
        this.$element.on('blur', '.js-edit-filename', (event) => {
            this.handleReferenceEditFilename(event);
        });

        $.ajax({
            url: this.$element.data('url')
        }).then(data => {
            this.references = data;
            this.render();
        })
    }

    addReference(reference) {
        this.references.push(reference);
        this.render();
    }

    render() {
        const itemsHtml = this.references.map(reference => {
            return `
<li class="list-group-item d-flex justify-content-between align-items-center" data-id="${reference.id}">
    <span class="drag-handle fa fa-reorder"></span>
    <input type="text" value="${reference.originalFilename}" class="form-control js-edit-filename" style="width: auto;">
    <span>
        <a href="/admin/article/references/${reference.id}/download" class="btn btn-link btn-sm"><span class="fa fa-download" style="vertical-align: middle"></span></a>
        <button class="js-reference-delete btn btn-link btn-sm"><span class="fa fa-trash"></span></button>
    </span>
</li>
`
        });
        this.$element.html(itemsHtml.join(''));
    }

    handleReferenceDelete(event) {
        // Start with const $li =. I'm going to use the button that was just clicked to find the <li> element that's around everything - you'll see why in a second. 
        // So, const $li = $(event.currentTarget) to get the button that was clicked, then .closest('.list-group-item').
        const $li = $(event.currentTarget).closest('.list-group-item');
        // the id from li
        const id = $li.data('id');

        $li.addClass('disabled');

        $.ajax({
            url: '/admin/article/references/'+id,
            method: 'DELETE'
        }).then(() => {
            // This callback function will be called once for each item in the array. 
            // If the function returns true, that item will be put into the new references variable. 
            // If it returns false, it won't be.
            // The end effect is that we get an identical array, except without the reference that was just deleted.
            this.references = this.references.filter(reference => {
                return reference.id !== id;
            });
            this.render();
        });
    }

    // find the reference that's being updated from inside this.
    // references, change the originalFilename data on it, 
    // JSON-encode that entire object, 
    // and send it to the endpoint.
    handleReferenceEditFilename(event) {
        const $li = $(event.currentTarget).closest('.list-group-item');
        const id = $li.data('id');

        const reference = this.references.find(reference => {
            return reference.id === id;
        });

        reference.originalFilename = $(event.currentTarget).val();

        // console.log(reference);
        // Don't forget to retrun a data string
        $.ajax({
            url: '/admin/article/references/'+id,
            method: 'PUT',
            data: JSON.stringify(reference)
        });
    }
}

/**
 * @param {ReferenceList} referenceList
 */
function initializeDropzone(referenceList) {
    var formElement = document.querySelector('.js-reference-dropzone');
    if (!formElement) {
        return;
    }
    var dropzone = new Dropzone(formElement, {
        paramName: 'reference',
        init: function() {
            this.on('error', function(file, data) {
                if (data.detail) {
                    this.emit('error', file, data.detail);
                }
            });
            this.on('success', function(file, data) {
                referenceList.addReference(data);
            });
        }
    });
};





