define([
    "jquery",
    "niksNavigation",
    "jquery/ui"
], function ($, nav) {
    "use strict";

    $.widget('niks.priceSlider', {
        _create: function () {
            var self = this,
                slider = $('#slider-' + this.options.code + '-range'),
                fromInput = $('#' + self.options.code + '-from'),
                toInput = $('#' + self.options.code + '-to');
            this.options.urlTemplate = decodeURI(this.options.urlTemplate);
            slider.slider({
                range: true,
                min: this.options.min,
                max: this.options.max,
                values: [this.options.from, this.options.to],
                slide: function (event, ui) {
                    fromInput.val(ui.values[0]);
                    toInput.val(ui.values[1]);
                },
                stop: function () {
                    self.processPrice(slider, fromInput, toInput);
                }
            });

            fromInput.val(slider.slider('values', 0));
            toInput.val(slider.slider('values', 1));

            fromInput.change(function () {
                slider.slider('values', 0, $(this).val());
                self.processPrice(slider, fromInput, toInput);
            });

            toInput.change(function () {
                slider.slider('values', 1, $(this).val());
                self.processPrice(slider, fromInput, toInput);
            });
        },

        processPrice: function (slider, fromInput, toInput) {
            var from = slider.slider('values', 0),
                to = slider.slider('values', 1),
                url = this.options.urlTemplate.replace('{{from}}', from).replace('{{to}}', to);

            fromInput.val(slider.slider('values', 0));
            toInput.val(slider.slider('values', 1));

            nav().updateContent(url, true);
        }
    });
    return $.niks.priceSlider;
});
