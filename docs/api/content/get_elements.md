# Get List of Elements

Request list of all elements already "sorted" from section.

## HTTP Request

GET `/api/elements/{sectionId}`


### HTTP Response Status-Codes

| Code   | Description |
|:-------|:------------|
|200     |Success, user received list of all texts|
|401     |Error, user is not authenticated|
|404     |Error, section not found|
|500     |Unknown error|


### Example

#### Request

##### URL

GET `/api/elements/UUID-VON-SECTION`

#### Response

##### Body
```json
[
    {
        "id": "UUID",
        "content": "text.key"
        "type": "text",
    },
    {
        "id": "UUID",
        "content": "text.key"
        "type": "choice",
        "choice_type": "yesNo"
    }
]
```
