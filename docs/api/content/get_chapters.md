# Get List of all Chapters

Request list of all chapters already "sorted".

## HTTP Request

GET `/api/chapters`


### HTTP Response Status-Codes

| Code   | Description |
|:-------|:------------|
|200     |Success, user received list of all texts|
|401     |Error, user is not authenticated|
|500     |Unknown error|


### Example

#### Request

##### URL

GET `/api/chapters`

#### Response

##### Body
```json
[
    {
        "id": "UUID",
        "name": "text.key",
        "slug_name": "foo-bar",
    },
    {
        "id": "UUID2",
        "name": "text.key2",
        "slug_name": "foo-bar2"
    }
]
```
