$.products.categoryimagesAction = function () {
    this.load('?plugin=categoryimages&action=filebrowser', function () {
        $("#s-sidebar li.selected").removeClass('selected');
        $("#s-categoryimages").addClass('selected');
    });
};