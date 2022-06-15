/**
 * @composeBy Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-06-14 00:18:47
 * @modify date 2022-06-15 21:59:24
 * @desc [description]
 */


function createEditor(uniqueId, callback, requestToolbar = ['bold','italic','link']) 
{
    DecoupledEditor.create(document.querySelector(`#ckeditor-content${uniqueId}`), {
        toolbar: requestToolbar
    })
    .then( editor => {
        const toolbarContainer = document.querySelector(`#ckeditor-toolbar${uniqueId}`); 
        toolbarContainer.appendChild( editor.ui.view.toolbar.element );

        callback(editor);
    })
    .catch( error => { 
        console.log(error);
    })
}

function createMultiEditor(numberOfEditor, formSelector, requestToolbar = ['bold','italic','link'])
{
    $(document).ready(function(){
        let editorInstance = [];
        for (let i = 0; i < numberOfEditor; i++) 
        {
            createEditor(i, function(editor){
                editorInstance.push(editor);
            }, requestToolbar);
        }

        $(formSelector).submit(function(){
            for (let i = 0; i < numberOfEditor; i++) 
            {
                let container = $(`#container${i}`);
                let field = container.data('field');

                container.append(`<textarea name="${field}" class="d-none" style="display: block">${editorInstance[i].getData()}</textarea>`);
            }
        });
    });

    
}