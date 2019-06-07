export class CookieService {
    set(key, value, expires) {
        let cookieValue = `${key}=${value}`;
        if (expires) cookieValue += `;expires='${expires.toUTCString()}'`;
        document.cookie = cookieValue;
    };

    setWithExpiryInYears(key, value, expires) {
        this.setWithExpiryInDays(key, value, expires * 365);
    };

    setWithExpiryInDays(key, value, expires) {
        this.setWithExpiryInHours(key, value, expires * 24);
    };

    setWithExpiryInHours(key, value, expires) {
        this.setWithExpiryInMinutes(key, value, expires * 60);
    };

    setWithExpiryInMinutes(key, value, expires) {
        this.setWithExpiryInSeconds(key, value, expires * 60);
    };

    setWithExpiryInSeconds(key, value, expires) {
        this.setWithExpiryInMiliseconds(key, value, expires * 1000);
    };

    setWithExpiryInMiliseconds(key, value, expires) {
        const expireDate = new Date();
        const time = expireDate.getTime() + expires;
        expireDate.setTime(time);

        this.set(key, value, expireDate);
    };

    get(key) {
        const decodedCookie = decodeURIComponent(document.cookie);
        const pairs = decodedCookie.split(/;\s*/);

        const prefix = `${key}=`;
        for (const pair of pairs) {
            if (pair.indexOf(prefix) === 0) {
                return pair.substring(prefix.length);
            }
        }
        return '';
    }
}
