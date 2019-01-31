document.observe("dom:loaded", function(){
    var drop = $("ftp_import_drag_n_drop");
    var input = new Element('input', {type: 'file', multiple: true, style:'display:none', name:'file'});

    drop.show();


    drop.observe('drop', function(e){
        e.stopPropagation();
        e.preventDefault();

        updateDrag(e);

        dropMultipleFilesFtp(e.dataTransfer.files);
    });

    drop.observe('dragover', updateDrag);
    drop.observe('dragenter', updateDrag);
    drop.observe('dragleave', updateDrag);


    drop.observe('click', function(){
        input.click();
    });

    input.observe('change', function(){
        dropMultipleFilesFtp(this.files);
    });

    drop.insert({after: input});
});

function dropMultipleFilesFtp(files)
{
    for (var i = 0; i < files.length; i++)
    {
        submitFileFtp($('preloader'), files[i]);
    }
}


function submitFileFtp(box, file)
{
    showPreloader(box);

    var fd = new FormData;

    var fileInput = $('files_box').down('input[type=file]');

    fd.append(fileInput.name, file ? file : fileInput.files[0]);

    fd.append('form_key', FORM_KEY);


    var xhr = new XMLHttpRequest();

    xhr.addEventListener('load', function(e){
        var response = e.target.response.evalJSON();
        removePreloader(box);
        if (response.errors.length > 0)
        {
            Effect.Shake(box)
            alert(response.errors[0])
        }
        else
        {
            //box.replace(response.content);
            box.insert(response.content + "<br>");
            files_list_gridJsObject.reload();
        }
    }, false);

    xhr.open('POST', $('amfile_ajax_action').value);
    xhr.send(fd);
}