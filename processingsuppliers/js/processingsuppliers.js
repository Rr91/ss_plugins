$(function() {

	Plugin.Vars.FilesTab = $('#files-tab');
	Plugin.Vars.SuppliersTab = $('#suppliers-tab');

	Plugin.Helpers.template = function(id) {
        return _.template( $('#' + id).html() );
    };

    Plugin.Routers.TabsRouter = Backbone.Router.extend({
    	routes: {
    		'processingsuppliers/': 'showFilesTab',
    		'processingsuppliers/files/': 'showFilesTab',
    		'processingsuppliers/files/add/': 'showFileAdd',
            'processingsuppliers/files/delete/:id': 'showFileDelete',
            'processingsuppliers/files/upload/:id': 'showFileUpload',
    		'processingsuppliers/suppliers/': 'showSuppliersTab',
    		'processingsuppliers/suppliers/add/': 'showSupplierAdd',
            'processingsuppliers/suppliers/edit/:id': 'showSupplierEdit',
            'processingsuppliers/suppliers/delete/:id': 'showSupplierDelete'
    	},

    	showFilesTab: function() {
            // TODO: плохо - вкладки нужно переделать
    		Plugin.Vars.FilesTab.addClass('selected');
            Plugin.Vars.SuppliersTab.removeClass('selected');

    		if (!Plugin.Vars.FileRows) {
                // предзагрузка моделей реализована в шаблоне BackendSetup
                Plugin.Vars.FileRows = new Plugin.Collections.FileRows( Plugin.Vars.PreloadFileRows );
    		}
            if (!Plugin.Vars.SupplierRows) {
                // предзагрузка моделей реализована в шаблоне BackendSetup
                Plugin.Vars.SupplierRows = new Plugin.Collections.SupplierRows( Plugin.Vars.PreloadSupplierRows );
            }
    		if (!Plugin.Vars.FilesTable) {
                Plugin.Vars.FilesTable = new Plugin.Views.FilesTable({ collection: Plugin.Vars.FileRows });
    		}
    		if (!Plugin.Vars.FilesSidebar) {
                Plugin.Vars.FilesSidebar = new Plugin.Views.FilesSidebar({ model: new Plugin.Models.FilesSidebar });
    		}

            Plugin.Vars.FilesTable.render();
            Plugin.Vars.FilesSidebar.render();
    	},
    	showFileAdd: function() {
    		this.showFilesTab();

    		if (!Plugin.Vars.FileAdd) {
                Plugin.Vars.FileAdd = new Plugin.Views.FileAdd({ collection: Plugin.Vars.FileRows });
				$(document.body).append(Plugin.Vars.FileAdd.render().el);
			} else {
                Plugin.Vars.FileAdd.show();
			}
    	},
        showFileDelete: function(id) {
            this.showFilesTab();

            Plugin.Vars.FileDelete = new Plugin.Views.FileDelete({ model: Plugin.Vars.FileRows.get(id), collection: Plugin.Vars.FileRows });
            $(document.body).append(Plugin.Vars.FileDelete.render().el);
        },
        showFileUpload: function(id) {
            this.showFilesTab();

            Plugin.Vars.FileUpload = new Plugin.Views.FileUpload({ model: Plugin.Vars.FileRows.get(id) });
            $(document.body).append(Plugin.Vars.FileUpload.render().el);
        },
    	showSuppliersTab: function() {
            // TODO: плохо - вкладки нужно переделать
            Plugin.Vars.SuppliersTab.addClass('selected');
            Plugin.Vars.FilesTab.removeClass('selected');

    		if (!Plugin.Vars.SupplierRows) {
                // предзагрузка моделей реализована в шаблоне BackendSetup
                Plugin.Vars.SupplierRows = new Plugin.Collections.SupplierRows( Plugin.Vars.PreloadSupplierRows );
    		}
            if (!Plugin.Vars.FileRows) {
                // предзагрузка моделей реализована в шаблоне BackendSetup
                Plugin.Vars.FileRows = new Plugin.Collections.FileRows( Plugin.Vars.PreloadFileRows );
            }
    		if (!Plugin.Vars.SuppliersTable) {
                Plugin.Vars.SuppliersTable = new Plugin.Views.SuppliersTable({ collection: Plugin.Vars.SupplierRows });
    		}
    		if (!Plugin.Vars.SuppliersSidebar) {
                Plugin.Vars.SuppliersSidebar = new Plugin.Views.SuppliersSidebar({ model: new Plugin.Models.SuppliersSidebar });
    		}

            Plugin.Vars.SuppliersTable.render();
            Plugin.Vars.SuppliersSidebar.render();
    	},
    	showSupplierAdd: function() {
    		this.showSuppliersTab();

    		if (!Plugin.Vars.SupplierAdd) {
                Plugin.Vars.SupplierAdd = new Plugin.Views.SupplierAdd({ collection: Plugin.Vars.SupplierRows });
				$(document.body).append(Plugin.Vars.SupplierAdd.render().el);
			} else {
                Plugin.Vars.SupplierAdd.show();
			}
    	},
        showSupplierEdit: function(id) {
            this.showSuppliersTab();

            if (!Plugin.Vars.SupplierEdit) {
                Plugin.Vars.SupplierEdit = new Plugin.Views.SupplierEdit({ model: Plugin.Vars.SupplierRows.get(id), collection: Plugin.Vars.SupplierRows });
                $(document.body).append(Plugin.Vars.SupplierEdit.render().el);
            } else {
                Plugin.Vars.SupplierEdit.show();
            }
        },
        showSupplierDelete: function(id) {
            this.showSuppliersTab();
            
            Plugin.Vars.SupplierDelete = new Plugin.Views.SupplierDelete({ model: Plugin.Vars.SupplierRows.get(id), collection: Plugin.Vars.SupplierRows });
            $(document.body).append(Plugin.Vars.SupplierDelete.render().el);
        }
    });

    Plugin.Models.FileRow = Backbone.Model.extend({
        initialize: function() {
            if (!Plugin.Vars.SupplierRows) {
                // предзагрузка моделей реализована в шаблоне BackendSetup
                Plugin.Vars.SupplierRows = new Plugin.Collections.SupplierRows( Plugin.Vars.PreloadSupplierRows );
            }
            this.supplier = Plugin.Vars.SupplierRows.get(this.get('supplier_id'));
        },
        url: function() {
            return Plugin.Vars.FileRows.url;
        },
    	validate: function(attrs) {
            if (!attrs.supplier_id) {
                return 'Не выбран поставщик для файла';
            }
        },
        parse: function(res) {
            if (res.data) {
                return res.data;
            }
            return res;
        }
    });

    Plugin.Models.SupplierRow = Backbone.Model.extend({
        url: function() {
            return Plugin.Vars.SupplierRows.url;
        },
        validate: function(attrs) {
            if (!attrs.name) {
                return 'Не заполнено поле Имя';
            }
            if (!attrs.column_sku) {
                return 'Не заполнено поле Номер или буква столбца с артикулом';
            }
            if (!this.validateColumn(attrs.column_sku)) {
                return 'Неверный символ в поле Номер или буква столбца с артикулом';
            }
            if (!attrs.column_name) {
                return 'Не заполнено поле Номер или буква столбца с названием';
            }
            if (!this.validateColumn(attrs.column_name)) {
                return 'Неверный символ в поле Номер или буква столбца с названием';
            }
            if (!attrs.column_price) {
                return 'Не заполнено поле Номер или буква столбца с ценой';
            }
            if (!this.validateColumn(attrs.column_price)) {
                return 'Неверный символ в поле Номер или буква столбца с ценой';
            }
            if (!attrs.column_stock) {
                return 'Не заполнено поле Номер или буква столбца с остатком';
            }
            if (!this.validateColumn(attrs.column_stock)) {
                return 'Неверный символ в поле Номер или буква столбца с остатком';
            }
            if (!attrs.first_row) {
                return 'Не заполнено поле Номер первой строки';
            }
            if (!parseInt(attrs.first_row)) {
                return 'Неверно заполнено поле Номер первой строки';
            }

            // v1.1 validations
            if (typeof attrs.limit == 'string') {
                if (!parseInt(attrs.limit) && attrs.limit.length > 0) {
                    return 'Неверно заполнено поле Кол-во обрабатываемых товаров за итерацию';
                }
            }
        },
        validateColumn: function(column) {
            column = column.toUpperCase();

            if (!parseInt(column)) {
                var charCode = column.charCodeAt(0);

                if (charCode >= 65 && charCode <= 90) {
                    return true;
                }

                return false;
            }

            return true;
        },
        parse: function(res) {
            if (res.data) {
                return res.data;
            }
            return res;
        }
    });

	Plugin.Views.FileRow = Backbone.View.extend({
		tagName: 'tr',
		template: Plugin.Helpers.template('FileRow'),

		render: function() {
			var compiledHTML = this.template(this.model);

			this.$el.html(compiledHTML);
			return this;
		}
	});

    Plugin.Views.SupplierRow = Backbone.View.extend({
        tagName: 'tr',
        template: Plugin.Helpers.template('SupplierRow'),

        render: function() {
            var compiledHTML = this.template(this.model.attributes);

            this.$el.html(compiledHTML);
            return this;
        }
    });

	Plugin.Collections.FileRows = Backbone.Collection.extend({
		model: Plugin.Models.FileRow,
        url: '?plugin=processingsuppliers&action=fileRows',

        parse: function(res) {
            if (res.data) {
                return res.data;
            }
            return res;
        }
	});

    Plugin.Collections.SupplierRows = Backbone.Collection.extend({
        model: Plugin.Models.SupplierRow,
        url: '?plugin=processingsuppliers&action=supplierRows',

        parse: function(res) {
            if (res.data) {
                return res.data;
            }
            return res;
        }
    });

	Plugin.Views.FilesTable = Backbone.View.extend({
		el: '.table.processing-suppliers',
		template: Plugin.Helpers.template('FilesTable'),

        initialize: function() {
            this.listenTo(this.collection, 'sync', _.debounce(this.render, 128));
            this.listenTo(this.collection, 'add', _.debounce(this.render, 128));
            this.listenTo(this.collection, 'change', _.debounce(this.render, 128));
            this.listenTo(this.collection, 'destroy', _.debounce(this.render, 128));
        },
		addRow: function(row) {
			var FileRow = new Plugin.Views.FileRow({ model: row });

			this.$el.find('table').append(FileRow.render().el);
		},
		render: function() {
			var compiledHTML = this.template();

			this.$el.html(compiledHTML);
			this.collection.each(this.addRow, this);
			return this;
		}
	});

    Plugin.Views.SuppliersTable = Backbone.View.extend({
        el: '.table.processing-suppliers',
        template: Plugin.Helpers.template('SuppliersTable'),

        initialize: function() {
            this.listenTo(this.collection, 'sync', _.debounce(this.render, 128));
            this.listenTo(this.collection, 'add', _.debounce(this.render, 128));
            this.listenTo(this.collection, 'change', _.debounce(this.render, 128));
            this.listenTo(this.collection, 'destroy', _.debounce(this.render, 128));
        },
        addRow: function(row) {
            var SupplierRow = new Plugin.Views.SupplierRow({ model: row });

            this.$el.find('table').append(SupplierRow.render().el);
        },
        render: function() {
            var compiledHTML = this.template();

            this.$el.html(compiledHTML);
            this.collection.each(this.addRow, this);
            return this;
        }
    });

    // TODO: реализовать
	Plugin.Models.FilesSidebar = Backbone.Model.extend({});
    Plugin.Models.SuppliersSidebar = Backbone.Model.extend({});

    // TODO: сделать общий класс сайдбара, от него наследовать все остальные
	Plugin.Views.FilesSidebar = Backbone.View.extend({
		el: '.sidebar.processing-suppliers',
		template: Plugin.Helpers.template('FilesSidebar'),
		events: {
			'click input.add-file': 'addFile'
		},

		addFile: function() {
            Plugin.Vars.TabsRouter.navigate('/processingsuppliers/files/add/', {trigger: true});
		},
		render: function() {
			var compiledHTML = this.template(this.model.attributes);
			this.$el.html(compiledHTML);
			return this;
		}
	});

	Plugin.Views.SuppliersSidebar = Backbone.View.extend({
		el: '.sidebar.processing-suppliers',
		template: Plugin.Helpers.template('SuppliersSidebar'),
		events: {
			'click input.add-supplier': 'addSupplier'
		},

		addSupplier: function() {
            Plugin.Vars.TabsRouter.navigate('/processingsuppliers/suppliers/add/', {trigger: true});
		},
		render: function() {
			var compiledHTML = this.template(this.model.attributes);
			this.$el.html(compiledHTML);
			return this;
		}
	});

	Plugin.Views.Dialog = Backbone.View.extend({
		tagName: 'div',
		className: 'dialog processing-suppliers',
		events: {
			'click .minimize': 'hide',
			'click .cancel': '_remove',
            'click .add': 'add'
		},

		show: function() {
			this.$el.show();
		},
		hide: function() {
			this.$el.hide();
            Plugin.Vars.TabsRouter.navigate(this.returnLink);
		},
		_remove: function() {
			this.remove();
            Plugin.Vars.TabsRouter.navigate(this.returnLink);
		},
        add: function () {
            var fields = this.$el.find('form').serializeArray();
            var row = {};

            $.each(fields, function(i, field) {
                row[field.name] = field.value;
            });

            if (this.model) {
                this.model.set(row);

                if (!this.model.isValid()) {
                    this.showError(this.model.validationError);
                    this.model.set(this.model.previousAttributes());
                    return;
                }

                if (this.model.hasChanged()) {
                    this.model.save();
                }
            } else {
                var newModel = this.collection.create(row, {wait: true, at: 0});

                if (!newModel.isValid()) {
                    this.showError(newModel.validationError);
                    return;
                }
            }

            this._remove();
        },
        showError: function(errorMsg) {
            this.$el.find('.errormsg').removeClass('hidden').find('span').text(errorMsg); // TODO: плохо - нужно переделать
            // v1.1 scroll to error
            this.$el.find('.dialog-content').animate({ scrollTop: 0 }, 400);
        },
		render: function() {
            var compiledHTML;

            if (this.model) {
                compiledHTML = this.template(this.model.attributes);
            } else {
                compiledHTML = this.template();
            }

			this.$el.html(compiledHTML);
			return this;
		}
	});

	Plugin.Views.FileAdd = Plugin.Views.Dialog.extend({
		template: Plugin.Helpers.template('FileAdd'),
		returnLink: '/processingsuppliers/files/',

        initialize: function() {
            this.progressBar = new Plugin.Views.ProgressBar({
                model: new Plugin.Models.ProgressBar({
                    message: 'Загрузка файла на сайт...',
                    progress: 0
                })
            });
        },
		_remove: function() {
			Plugin.Views.Dialog.prototype._remove.apply(this);
            Plugin.Vars.FileAdd = null;
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
                    url: '?plugin=processingsuppliers&action=FileRows',
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
                            this._remove();
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
            var compiledHTML = this.template(Plugin.Vars.SupplierRows);

            this.$el.html(compiledHTML);
            return this;
        }
	});

	Plugin.Views.SupplierAdd = Plugin.Views.Dialog.extend({
		template: Plugin.Helpers.template('SupplierAdd'),
		returnLink: '/processingsuppliers/suppliers/',
        events: {
            'click .minimize': 'hide',
            'click .cancel': '_remove',
            'click .add': 'add',
            'click .add-hint': 'hint'
        },

        hint: function() {
            this.hint = new Plugin.Views.SupplierAddHint;
            this.$el.find('.add-hint').before(this.hint.render().el).remove();
        },
		_remove: function() {
			Plugin.Views.Dialog.prototype._remove.apply(this);
            Plugin.Vars.SupplierAdd = null;
		}
	});

    Plugin.Views.SupplierAddHint = Backbone.View.extend({
        tagName: 'div',
        template: Plugin.Helpers.template('SupplierAddHint'),

        render: function() {
            var compiledHTML = this.template();

            this.$el.html(compiledHTML);
            return this;
        }
    });
    Plugin.Views.SupplierAddHint2 = Backbone.View.extend({
        tagName: 'div',
        template: Plugin.Helpers.template('SupplierAddHint2'),

        render: function() {
            var compiledHTML = this.template();

            this.$el.html(compiledHTML);
            return this;
        }
    });

    Plugin.Views.SupplierEdit = Plugin.Views.Dialog.extend({
        template: Plugin.Helpers.template('SupplierEdit'),
        returnLink: '/processingsuppliers/suppliers/',
        events: {
            'click .minimize': 'hide',
            'click .cancel': '_remove',
            'click .add': 'add',
            'click .add-hint': 'hint'
            'click .add-hint2': 'hint2'
        },

        hint: function() {
            this.hint = new Plugin.Views.SupplierAddHint;
            this.$el.find('.add-hint').before(this.hint.render().el).remove();
        },

        hint2: function() {
            this.hint2 = new Plugin.Views.SupplierAddHint2;
            this.$el.find('.add-hint2').before(this.hint2.render().el).remove();
        },

        _remove: function() {
            Plugin.Views.Dialog.prototype._remove.apply(this);
            Plugin.Vars.SupplierEdit = null;
        }
    });

    Plugin.Views.FileDelete = Plugin.Views.Dialog.extend({
        template: Plugin.Helpers.template('FileDelete'),
        returnLink: '/processingsuppliers/files/',
        events: {
            'click .delete': 'fileDelete',
            'click .cancel': '_remove'
        },

        fileDelete: function() {
            this.model.destroy({
                wait: true,
                data: {
                    model: JSON.stringify(this.model.toJSON()),
                    _method: 'DELETE'
                }
            });

            this._remove();
        },
        render: function() {
            var compiledHTML = this.template(this.model);

            this.$el.html(compiledHTML);
            return this;
        }
    });

    Plugin.Views.SupplierDelete = Plugin.Views.Dialog.extend({
        template: Plugin.Helpers.template('SupplierDelete'),
        returnLink: 'processingsuppliers/suppliers/',
        events: {
            'click .delete': 'supplierDelete',
            'click .cancel': '_remove'
        },

        supplierDelete: function() {
            this.model.destroy({
                wait: true,
                data: {
                    model: JSON.stringify(this.model.toJSON()),
                    _method: 'DELETE'
                }
            });

            this._remove();
        }
    });

    Plugin.Models.FileDataRow = Backbone.Model.extend({
        url: function() {
            return this.collection.url;
        },
        parse: function(res) {
            if (res.data) {
                return res.data;
            }
            return res;
        }
    });

    Plugin.Views.FileDataRow = Backbone.View.extend({
        tagName: 'tr',
        template: Plugin.Helpers.template('FileDataRow'),

        render: function() {
            var compiledHTML = this.template(this.model.attributes);

            this.$el.html(compiledHTML);
            return this;
        }
    });

    Plugin.Collections.FileDataRows = Backbone.Collection.extend({
        model: Plugin.Models.FileDataRow,
        url: '?plugin=processingsuppliers&action=fileDataRows',

        parse: function(res) {
            if (res.data) {
                return res.data;
            }
            return res;
        }
    });
    
    Plugin.Views.FileDataTable = Backbone.View.extend({
        tagName: 'table',
        className: 'zebra',
        template: Plugin.Helpers.template('FileDataTable'),

        initialize: function() {
            this.listenTo(this.collection, 'add', _.debounce(this.render, 128));
        },
        addRow: function(row) {
            var FileDataRow = new Plugin.Views.FileDataRow({ model: row });

            this.$el.append(FileDataRow.render().el);
        },
        render: function() {
            var compiledHTML = this.template();

            this.$el.html(compiledHTML);
            this.collection.each(this.addRow, this);
            return this;
        }
    });

    Plugin.Views.FileUpload = Plugin.Views.Dialog.extend({
        template: Plugin.Helpers.template('FileUpload'),
        returnLink: 'processingsuppliers/files/',
        limit: Plugin.Vars.FileDataTableLimit,
        offset: 0,
        events: {
            'click .more': 'showMore',
            'click .upload': 'uploadFile',
            'click .cancel': '_remove'
        },

        initialize: function() {
            this.model.fileDataRows = new Plugin.Collections.FileDataRows;

            // v1.1 fetching with offset and limit
            this.model.fileDataRows.fetch({
                data: { id: this.model.id, offset: this.offset, limit: this.limit },
                context: this,
                
                success: function() {
                    var rowsPart = this.getRowsPart();

                    this.fileDataTable = new Plugin.Views.FileDataTable({ collection: new Plugin.Collections.FileDataRows(rowsPart) });
                    this.listenTo(this.fileDataTable.collection, 'add', _.debounce(this.hideLoading, 200));
                    this.renderTable();
                }
            });

            this.progressBar = new Plugin.Views.ProgressBar({
                model: new Plugin.Models.ProgressBar({
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
                                    this._remove();
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

    Plugin.Models.ProgressBar = Backbone.Model.extend({
        defaults: {
            progress: 0,
            message: 'Загрузка...'
        },

        initialize: function(attrs) {
            this.validate(attrs);
        },
        validate: function(attrs) {
            if (attrs.progress !== parseInt(attrs.progress, 10)) {
                this.set('progress', parseInt(attrs.progress, 10));
            }

            if (attrs.progress < 0) {
                return 'Прогресс не может быть меньше 0%';
            }

            if (attrs.progress > 100) {
                return 'Прогресс не может быть больше 100%';
            }
        }
    });

    Plugin.Views.ProgressBar = Backbone.View.extend({
        tagName: 'div',
        className: 'progressbar processing-suppliers',
        template: Plugin.Helpers.template('ProgressBar'),

        initialize: function() {
            this.listenTo(this.model, 'change', _.debounce(this.render, 128));
        },
        render: function() {
            var compiledHTML = this.template(this.model.attributes);

            this.$el.html(compiledHTML);
            return this;
        }
    });

    Plugin.Vars.TabsRouter = new Plugin.Routers.TabsRouter();
    Backbone.emulateHTTP = true;
    Backbone.emulateJSON = true;
    Backbone.history.start();
});