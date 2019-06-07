global.$ = global.jquery = global.jQuery = require('jquery');
import 'cookieconsent';
import 'slick-carousel';
import axios from 'axios';
import './mobile-screen-notification';
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
