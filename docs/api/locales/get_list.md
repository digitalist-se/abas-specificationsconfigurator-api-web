# Get List of all supported locales

## HTTP Request

GET `/api/locales`


### HTTP Response Status-Codes

| Code   | Description |
|:-------|:------------|
|200     |Success, user received list of all supported locales|
|401     |Error, user is not authenticated|
|500     |Unknown error|


### Example

#### Request

##### URL

GET `/api/locales`

#### Response

##### Body
```json
[
    "de",
    "en"
]
```
