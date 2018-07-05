# Get List of all choice types

Request list of all choice types already "sorted".

## HTTP Request

GET `/api/choice-types`


### HTTP Response Status-Codes

| Code   | Description |
|:-------|:------------|
|200     |Success, user received list of all texts|
|401     |Error, user is not authenticated|
|500     |Unknown error|


### Example

#### Request

##### URL

GET `/api/choice-types`

#### Response

##### Body
```json
[
    {
        "id": "UUID",
        'type': 'yesNo',
        'multiple': false,
        'tiles': false,
        'options': [
            {
                'id': 'UUID',
                'text': 'option.text.key',
                'icon': 'icon.key',
                'value': 'string',
            }
        ]
    }
]
```
