var DropObservers = {
    "index" : {
        "drop": dropFileGrid,
        "submit": submitFileGrid
    },
    "edit": {
        "drop": dropFileForm,
        "submit": submitFile
    }
}

function updateFileBoxes(blink)
{
   $$('#amfile-uploads .box:not(.ready)').each(function(element) {
        bindActions(element, blink);
    });
}

function bindActions(box, blink)
{
    box.down('.delete').observe('click', removeFile);

    box.select('.default-value').each(function(element) {
        element.observe('click', useDefault);
    });

    box.down('input[name*=file_link]').observe('change', function(){
        box.down('input[name*=use][value=url]').checked = true;
    }.bind(box))

    box.down('input[name*=\[file\]]').observe('change', function(){
        box.down('input[name*=use][value=file]').checked = true;
    }.bind(box))

    if (amfile_dragndrop)
    {
        initDragAndDrop(box, 'edit');
    }

    if (blink)
    {
        box.setStyle({'background':'#5c5'});

        new Effect.Morph(box, {
            style: {'background-color':'#E7EFEF'},
            duration: 3
        });
    }

    box.addClassName('ready');
}

function initDragAndDrop(box, page)
{

    var drop = box.down('.drop');
    drop.show();

    box.down('input[type=file]').observe('change', function(){
        DropObservers[page]['submit'](box);
    }.bind(box));

    drop.observe('click', function(event){
        event.stopPropagation();
        event.preventDefault();
        box.down('input[type=file]').click();
    }.bind(box));

    drop.observe('drop', DropObservers[page]['drop']);

    drop.observe('dragover', updateDrag);
    drop.observe('dragenter', updateDrag);
    drop.observe('dragleave', updateDrag);

    box.down('input[type=file]').hide();
}

function submitFile(box, file)
{
    showPreloader(box);

    var fd = new FormData;

    var elements = box.select('input:not([type=file]), select');

    elements.each(function(element){
        fd.append(element.name, element.getValue());
    });

    var fileInput = box.down('input[type=file]');

    fd.append(fileInput.name, file ? file : fileInput.files[0]);

    fd.append('id', $('amfile_product_id').value);
    fd.append('store', $('amfile_store_id').value);
    fd.append('form_key', FORM_KEY);


    var xhr = new XMLHttpRequest();

    xhr.addEventListener('load', function(e){
        var response = e.target.response.evalJSON();
        if (response.errors.length > 0)
        {
            removePreloader(box);
            Effect.Shake(box)
            alert(response.errors[0])
        }
        else
        {
            box.replace(response.content);
            updateFileBoxes(true);
        }
    }, false);

    xhr.open('POST', $('amfile_ajax_action').value);
    xhr.send(fd);
}

function submitFileGrid(box, file)
{
    if(!box.down('.preloader')) {
        new Effect.Opacity(box.down('span'), { from: 1.0, to: 0.0, duration:0});
        showPreloader(box.down('.drop'));
    }
    var fd = new FormData;

    var elements = box.select('input:not([type=file])');

    elements.each(function(element){
        fd.append(element.name, element.getValue());
    });

    var fileInput = box.down('input[type=file]');

    fd.append(fileInput.name, file ? file : fileInput.files[0]);
    fd.append('form_key', FORM_KEY);

    var xhr = new XMLHttpRequest();

    xhr.addEventListener('load', function(e){
        var response = e.target.response.evalJSON();
        if (response.errors.length > 0)
        {
            if(box.down('.preloader')) {
                removePreloader(box.down('.drop'));
            }
            Effect.Shake(box);
            box.down('.drop').show();
            alert(response.errors[0]);
        }
        else
        {
            if(box.down('.preloader')) {
                removePreloader(box.down('.drop'));
                blinkSuccessIcon(box.down('.drop'));
                $('attach-files-box-'+response.content.product_id).up().innerHTML = response.content.content;
            }
            box.down('.drop').show();
            fileInput.value = '';
        }
    }, false);

    xhr.open('POST', box.down('input[name=amfile_ajax_action]').value);
    xhr.send(fd);
}

function blinkSuccessIcon(box, callback) {
    var success = new Element('div', {class: 'success-upload-image'});
    box.appendChild(success);
    Effect.Fade(success, {duration: 1.2, afterFinish: function () {box.removeChild(success); new Effect.Opacity(box.down('span'), { from: 0.0, to: 1.0, duration:0 }); }});
}

function showPreloader(box)
{
    var preloader = new Element('div', {class: 'preloader'});
    preloader.appendChild(new Element('img', {src: $('loading-mask').down('img').readAttribute('src')}));

    box.appendChild(preloader);
}

function removePreloader(box)
{
    box.down('.preloader').remove();
}

function updateDrag(e)
{
    e.stopPropagation();
    e.preventDefault();

    if (e.target.tagName == 'DIV')
    {
        if (e.type == 'dragover')
            e.target.addClassName('hover');
        else
            e.target.removeClassName('hover');
    }
}

function dropFile(e, box, page)
{
    e.stopPropagation();
    e.preventDefault();

    updateDrag(e);

    if (e.dataTransfer.files.length > 0)
    {
        DropObservers[page]['submit'](box, e.dataTransfer.files[0]);
    }
    return e.dataTransfer.files.length > 0;
}

function dropFileGrid(e, box)
{
    e.stopPropagation();
    e.preventDefault();

    updateDrag(e);

    for (var i = 0; i < e.dataTransfer.files.length; i++) {
        submitFileGrid(this.up('td'), e.dataTransfer.files[i]);
    }
}

function dropFileForm(e) {
    if(dropFile(e, this.up('.box'), 'edit') == true) {
        this.up('.box').down('input[name*=use][value=file]').checked = true;
    }
}

function dropMultipleFiles(files)
{
    for (var i = 0; i < files.length; i++)
    {
        addNewFile();
        submitFile($('amfile-uploads').down('.box:last'), files[i]);
    }
}

function useDefault()
{
    var input = $(this).up('td').down('input[type=text],select');

    if (this.checked)
        input.disable();
    else
        input.enable();
}

function removeFile()
{
    if (confirm("Are you sure ?"))
    {
        var box = this.up('.box');

        box.down('.delete-input').value = 1;
        box.setStyle({display: 'none'});
    }
}

function addNewFile()
{
    var block = amfile_new_upload_template.clone(true);
    var id = --amfile_new_upload_id;

    block = block.outerHTML.replace(/\[-\d\]/g, '['+id+']');
    $('amfile-uploads').down('.container').insert({bottom: block});

    updateFileBoxes();
}
