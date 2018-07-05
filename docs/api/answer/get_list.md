# Get List of all answers of user


## HTTP Request

GET `/api/answers`


### HTTP Response Status-Codes

| Code   | Description |
|:-------|:------------|
|200     |Success, user received list of all answers|
|401     |Error, user is not authenticated|
|500     |Unknown error|


### Example

#### Request

##### URL

GET `/api/answers`

#### Response

##### Body
```json
[
{
    "elementId": "UUID-VOM-ELEMENT",
    "value": { "text": "Das ist eine Antwort" }
},
{
    "elementId": "UUID-VOM-ELEMENT2",
    "value": { "text": "Das ist eine Antwort" }
},
]
```
