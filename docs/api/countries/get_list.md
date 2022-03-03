# Get List of all countries

can be used for country select / dropdown

## HTTP Request

GET `/api/countries`


### HTTP Response Status-Codes

| Code   | Description                                  |
|:-------|:---------------------------------------------|
|200     | Success, user received list of all countries |
|500     | Unknown error                                |


### Example

#### Request

##### URL

GET `/api/locales`

#### Response

##### Body
```json
[
    {
        "id": "de",
        "name": "Germany"
    },
    {
        "id": "at",
        "name": "Österreich"
    }
]
```
