define([
    'storage',
    'template',
    'views/Dialog',
    'models/ProgressBar',
    'views/ProgressBar'
], function (
    Storage,
    Template,
    DialogView,
    ProgressBarModel,
    ProgressBarView
) {
    return DialogView.extend({
        template: Template('FileAdd'),

        initialize: function() {
            this.progressBar = new ProgressBarView({
                model: new ProgressBarModel({
                    message: 'Загрузка файла на сайт...',
                    progress: 0
                })
            });
        },
        add: function() {
            var file = this.$el.find('input[type=file]').prop('files')[0];
            var form = this.$el.find('form').get().shift();
            var formData = new FormData(form);
            var supplier_id = formData.get('supplier_id');
            var error;

            if (!file) {
                error = 'Не выбран файл для загрузки';
            }

            if (supplier_id < 1) {
                error = 'Не выбран поставщик для файла';
            }

            if (error) {
                this.showError(error);
            } else {
                formData.append('file', file);
                $.ajax({
                    url: '?plugin=processingsuppliers&action=fileRows',
                    type: 'post',
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    data: formData,
                    context: this,

                    success: function(res) {
                        if (res.data.error) {
                            this.showError(res.data.error);
                        } else {
                            var row = res.data;

                            this.collection.add(row, {at: 0});
                            this.fileRead(row.id);
                            this.$el.find('.progressbar-field').append(this.progressBar.render().el);
                        }
                    }
                });
            }
        },
        fileRead: function(id) {
            $.ajax({
                url: "?plugin=processingsuppliers&action=fileRead",
                data: { id: id },
                dataType: "json",
                context: this,

                success: function(res){
                    this.getProcessingStatus(id, res.processId);
                }
            });
        },
        getProcessingStatus: function(id, processId) {
            var getStatus = function(id, processId) {
                $.ajax({
                    url: "?plugin=processingsuppliers&action=fileRead",
                    data: { id: id, processId: processId },
                    dataType: "json",
                    context: this,

                    success: function(res){
                        if (res.ready) {
                            this.remove();
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
            var compiledHTML = this.template(Storage.SupplierRows);

            this.$el.html(compiledHTML);
            return this;
        }
    });
});
