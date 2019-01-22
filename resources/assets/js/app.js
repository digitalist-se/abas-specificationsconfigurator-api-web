require('cookieconsent');
require('slick-carousel');

global.$ = global.jquery = global.jQuery = require('jquery');
$(() => {
    $('.slider').slick({
        rows: 0,
        dots: true,
        infinite: true,
        slide: '.slide',
        appendArrows: '.nav-arrows'
    });
});

