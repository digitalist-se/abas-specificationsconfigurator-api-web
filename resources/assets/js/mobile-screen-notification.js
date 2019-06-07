global.$ = global.jquery = global.jQuery = require('jquery');
global.Snackbar = require('node-snackbar/dist/snackbar');
import './cookie-service';
import { CookieService } from "./cookie-service";
import 'node-snackbar';

const isMobile = () => {
    const MAX_MOBILE_DEVICE_WIDTH = 640;
    return window.innerWidth < MAX_MOBILE_DEVICE_WIDTH;
};

$(() => {
    const showedMobileDeviceInfoCookieKey = 'mobile-screen-size-info';
    const cookie = new CookieService();
    if (isMobile() && cookie.get(showedMobileDeviceInfoCookieKey) !== 'true') {
        Snackbar.show({
            pos: 'top-center',
            text: 'Für eine optimale Nutzung empfehlen wir Desktop-Geräte und Tablets.',
            duration: null,// null define that the snackbar needs to be dismissed,
            actionTextColor: null,// reset. use css values
            backgroundColor: null,// reset. use css values
            textColor: null,// reset. use css values
            width: 'expand',
            actionText: 'OK',
            onActionClick: () => {
                cookie.set(showedMobileDeviceInfoCookieKey, 'true');
            }
        });
    }
});
