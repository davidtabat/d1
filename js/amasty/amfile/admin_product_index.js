document.observe("dom:loaded", function()
{
    function initDragDropGrid() {
        $$('#productGrid_table tr').each(function(item) {
            if(item.down('.drop')) {
                initDragAndDrop(item.down('.drop').up('td'), 'index');
            }
        });
    }

    varienGrid.prototype.initGridAjax = function () {
        this.initGrid();
        this.initGridRows();

        function initDragDropGrid() {
            $$('#productGrid_table tr').each(function(item) {
                if(item.down('.drop')) {
                    initDragAndDrop(item.down('.drop').up('td'), 'index');
                }
            });
        }
        initDragDropGrid();
    }

    initDragDropGrid();

});

function attachLoadMore(productId){
    var showDialog = function (html){
        var title = 'Order Detailed Information';
        var dialog = Dialog.info(html, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: title,
            width: 300,
            height: 200,
            zIndex: 1000,
            recenterAuto: true,
            hideEffect: Element.hide,
            showEffect: Element.show,
            id: 'viewDialog'
        });

    }


    var container = document.createElement('div');
    container.addClassName('attach-file-container');

    $$('.attach-name-'+productId).each(function(item) {

        var link = document.createElement('a');
        link.href = item.down('a').readAttribute('href');
        link.addClassName('attach-file-link');

        var icon = document.createElement('img');
        icon.src = item.readAttribute('data-icon');
        link.appendChild(icon);

        var fileName = document.createElement('span');
        fileName.innerHTML = item.down('a').innerHTML;
        link.appendChild(fileName);

        container.appendChild(link);

    });
    showDialog(container.outerHTML);
    return false;
}
