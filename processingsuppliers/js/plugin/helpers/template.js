define(function(require) {
    var _ = require('underscore');
    
    return function template(id) {
        return _.template($('#'+id).html());
    }
});