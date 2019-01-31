document.observe("dom:loaded", function(){
    window.amfile_dragndrop = 'draggable' in $('amfile-uploads');
    window.amfile_new_upload_template = $$('#amfile-uploads .box:last-child')[0].clone(true);
    window.amfile_new_upload_id = -1;

    $('amfile-uploads').down('button.add').observe('click', addNewFile);

    if (amfile_dragndrop)
    {
        var drop = $('amfile-uploads').down('.main.drop');
        drop.show();

        drop.observe('drop', function(e){
            e.stopPropagation();
            e.preventDefault();

            updateDrag(e);

            dropMultipleFiles(e.dataTransfer.files);
        });

        drop.observe('dragover', updateDrag);
        drop.observe('dragenter', updateDrag);
        drop.observe('dragleave', updateDrag);

        var input = new Element('input', {type: 'file', multiple: true, style:'display:none'});

        drop.observe('click', function(){
            input.click();
        });

        input.observe('change', function(){
            dropMultipleFiles(this.files);
        });

        drop.insert({after: input});
    }

    updateFileBoxes();
});