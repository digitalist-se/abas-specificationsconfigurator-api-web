# Get List of Sections

Request list of all already "sorted" sections of chapter.

## HTTP Request

GET `/api/sections/{chapter_id}`


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
        "headline": "text.key",
        "description": "text.key"
    },
    {
        "id": "UUID2",
        "headline": "text.key2",
        "description": "text.key2"
    }
]
```
