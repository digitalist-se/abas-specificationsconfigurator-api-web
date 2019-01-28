# Get config for cookieconsent

provide config for cookie consent popup.
see also config docs: https://cookieconsent.insites.com/documentation/

## HTTP Request

GET `/api/cookieconsent`


### HTTP Response Status-Codes

| Code   | Description |
|:-------|:------------|
|200     |Success, received config of cookie consent|
|500     |Unknown error|


### Example

#### Request

##### URL

GET `/api/cookieconsent`

#### Response

##### Body
```json
{
    "palette": {
        "popup": {
            "background": "#2a3539"
        },
        "button": {
            "background": "#008bd0"
        }
    },
    "position": "bottom-right",
    "cookie": {
        "domain": "app.domain"
    },
    "content": {
        "message": "cookieconsent.message",
        "dismiss": "cookieconsent.dismiss",
        "link":    "cookieconsent.link",
        "href":    "route to data-privacy "
    }
}
```
