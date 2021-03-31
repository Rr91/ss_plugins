define([
    'storage',
    'template',
    'views/Dialog',
    'collections/FileDataRows',
    'views/FileDataTable',
    'models/ProgressBar',
    'views/ProgressBar'
], function(
    Storage,
    Template,
    DialogView,
    FileDataRowsCollection,
    FileDataTableView,
    ProgressBarModel,
    ProgressBarView
) {
    return DialogView.extend({
        template: Template('FileUpload'),
        limit: Storage.FileDataTableLimit,
        offset: 0,

        events: function() {
            return Object.assign(DialogView.prototype.events, {
                'click .more': 'showMore',
                'click .upload': 'uploadFile'
            });
        },
        initialize: function() {
            this.model.fileDataRows = new FileDataRowsCollection;

            // v1.1 fetching with offset and limit
            this.model.fileDataRows.fetch({
                data: { id: this.model.id, offset: this.offset, limit: this.limit },
                context: this,

                success: function() {
                    var rowsPart = this.getRowsPart();

                    this.fileDataTable = new FileDataTableView({ collection: new FileDataRowsCollection(rowsPart) });
                    this.listenTo(this.fileDataTable.collection, 'add', _.debounce(this.hideLoading, 200));
                    this.renderTable();
                }
            });
            
            this.progressBar = new ProgressBarView({
                model: new ProgressBarModel({
                    message: 'Выгрузка файла на сайт...',
                    progress: 0
                })
            });
        },
        showMore: function() {
            this.showLoading();

            var rowsPart = this.getRowsPart();

            this.fileDataTable.collection.add(rowsPart);
        },
        getRowsPart: function() {
            var begin = this.offset;
            var end = this.limit + this.offset;
            var slicedRows;

            // v1.1 fetching with offset and limit
            this.model.fileDataRows.fetch({
                data: { id: this.model.id, offset: begin, limit: end },
                async: false,

                success: function(res) {
                    slicedRows = res.models;
                }
            });

            if (slicedRows.length) {
                this.offset += this.limit;
            } else {
                this.$el.find('.more').hide();
                this.hideLoading();
            }

            return slicedRows;
        },
        uploadFile: function() {
            $.ajax({
                url: '?plugin=processingsuppliers&action=fileUpload',
                data: { id: this.model.id },
                dataType: 'json',
                context: this,

                success: function(res) {
                    this.getProcessingStatus(this.model.id, res.processId);
                }
            });

            this.$el.find('.progressbar-field').append(this.progressBar.render().el);
        },
        getProcessingStatus: function(id, processId) {
            var getStatus = function(id, processId) {
                $.ajax({
                    url: "?plugin=processingsuppliers&action=fileUpload",
                    data: { id: id, processId: processId },
                    dataType: 'json',
                    context: this,

                    success: function(res){
                        if (res.ready) {
                            var uploads = 1 + Number(this.model.supplier.get('uploads'));
                            
                            this.model.supplier.save({uploads: uploads}, {
                                context: this,

                                success: function(res) {
                                    // TODO: если нет ошибок
                                    this.model.save({status: 1});
                                    this.remove();
                                }
                            });
                        } else {
                            var progress = parseInt(res.progress);

                            this.progressBar.model.set({progress: progress}, {validate: true});
                            this.getProcessingStatus(id, processId);
                        }
                    }
                });
            };

            getStatus = _.bind(getStatus, this, id, processId);
            _.delay(getStatus, 1000);
        },
        render: function() {
            var compiledHTML = this.template(this.model);

            this.$el.html(compiledHTML);
            return this;
        },
        renderTable: function() {
            var compiledHTML = this.fileDataTable.render().el;

            this.$el.find('.upload-table').html(compiledHTML);
            this.hideLoading();
            return this.fileDataTable;
        },
        showLoading: function() {
            this.$el.find('.loading.processing-suppliers').show();
        },
        hideLoading: function() {
            this.$el.find('.loading.processing-suppliers').hide();
        }
    });
});
