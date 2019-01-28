import 'cookieconsent';
import 'slick-carousel';
import axios from 'axios';
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

axios.get('/api/cookieconsent').then(({ data }) => {
    $(() => {
        window.cookieconsent.initialise(data);
    });
});
