define([
    "jquery"
], function ($) {
    "use strict";
    var module = {
        _create: function () {
            if (this.options.disabled) {
                return;
            }
            this._initState();
            var self = this;
            $('#layered-filter-block, .pages-items').off('click', 'a').on('click', 'a', function (e) {
                e.preventDefault();
                var url = $(this).attr('href');
                if (url) {
                    self.updateContent(url, true);
                }
            });
            $(window).unbind('popstate').bind('popstate', this.updateContent.bind(this));
        },

        _initState: function () {
            var self = this;
            if (!window.history.state) {
                $(document).ready(function () {
                    self._saveState(document.location.href);
                });
            }

        },

        _saveState: function (url) {
            window.history.pushState({url: url}, '', url);
        },

        updateContent: function (url, updateState) {
            if (updateState) {
                this._saveState(url);
            }
            if (url instanceof Object) {
                url = url.originalEvent.state.url;
            }
            $('body').loader('show');
            var self = this;
            $.ajax({
                url: url,
                cache: true,
                type: 'GET',
                data: {niksAjax: true},
                success: function (resp) {
                    if (resp instanceof Object) {
                        $(self.options.filtersContainer).replaceWith(resp.leftnav);
                        $(self.options.productsContainer).replaceWith(resp.products);
                        $.mage.init();
                        $('html, body').animate({
                            scrollTop: $('#maincontent').offset().top
                        }, 400);
                        self._create();
                        $('body').loader('hide');
                    }
                }
            });
        },

        init: function (options) {
            if (!module.options) {
                module.options = options;
                module._create();
            }
            return {
                updateContent: module.updateContent.bind(module)
            };
        }
    };

    return module.init;
});
