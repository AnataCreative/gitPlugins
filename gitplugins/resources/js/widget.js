var gitplugins = gitplugins || {};

gitplugins.app = function(undefined) {

    var exports = this.app;

    // Select
    var select = function() {
        $('#gitplugins-url').selectize({
            valueField: 'url',
            labelField: 'name',
            searchField: 'name',
            create: false,
            render: {
                option: function(item, escape) {
                    return '<div>' +
                        '<span class="title">' +
                            '<span class="name"><i class="icon ' + (item.fork ? 'fork' : 'source') + '"></i>' + escape(item.name) + '</span>' +
                            '<span class="by">' + escape(item.username) + '</span>' +
                        '</span>' +
                        '<span class="description">' + escape(item.description) + '</span>' +
                        '<ul class="meta">' +
                            (item.language ? '<li class="language">' + escape(item.language) + '</li>' : '') +
                            '<li class="watchers"><span>' + escape(item.watchers) + '</span> watchers</li>' +
                            '<li class="forks"><span>' + escape(item.forks) + '</span> forks</li>' +
                        '</ul>' +
                    '</div>';
                }
            },
            score: function(search) {
                var score = this.getScoreFunction(search);
                return function(item) {
                    return score(item) * (1 + Math.min(item.watchers / 100, 1));
                };
            },
            load: function(query, callback) {
                if (!query.length) return callback();
                $.getJSON('https://api.github.com/search/repositories/?q=' + encodeURIComponent(query), function() {
                    callback(res.repositories.slice(0, 10));
                }).fail(function() {
                    callback();
                });
            }
        });
    };


    // Init
    var init = function() {
        select();
    }();
};


var ready = function(fn) {
    // Sanity check
    if (typeof(fn) !== 'function') return;

    // If document is already loaded, run method
    if (document.readyState === 'complete') {
        return fn();
    }

    // Otherwise, wait until document is loaded
    document.addEventListener('DOMContentLoaded', fn, false);
};

ready(function() {
    gitplugins.app();
});
