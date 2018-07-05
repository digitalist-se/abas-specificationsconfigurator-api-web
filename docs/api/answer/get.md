# Get one answer of user of specific element


## HTTP Request

GET `/api/answers/{elementId}`


### HTTP Response Status-Codes

| Code   | Description |
|:-------|:------------|
|200     |Success, user received list of all answers|
|401     |Error, user is not authenticated|
|500     |Unknown error|


### Example

#### Request

##### URL

GET `/api/answers/UUID-OF-ELEMENT`

#### Response

##### Body
```json
{
    "elementId": "UUID-VOM-ELEMENT",
    "value":  { "text": "Das ist eine Antwort" }
}
```
