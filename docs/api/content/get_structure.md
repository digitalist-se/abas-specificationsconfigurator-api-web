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
        "name": "text.key"
        "slug_name": "foo-bar",
        "sections": [
            {
                "id": "UUID2",
                "headline": "text.key1",
                "description": "text.key2",
                "elements": [
                    {
                    
                        "id": "UUID",
                        "content": "text.key3"
                        "type": "choice",
                        "choice_type": "yesNo"
                    }
                ]
            }
        ]
    },
    {
        "id": "UUID3",
        "name": "text.key4"
        "slug_name": "foo-bar2",
        "sections": [
            {
                "id": "UUID4",
                "headline": "text.key5",
                "description": "text.key",
                "elements": [
                    {
                    
                        "id": "UUID5",
                        "content": "text.key6"
                        "type": "choice",
                        "choice_type": "yesNo"
                    }
                ]
            }
        ]
    }
]
```
