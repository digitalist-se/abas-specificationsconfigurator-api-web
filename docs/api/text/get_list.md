# Get List of all Texts

value of "description" will only be provided to admin users.

## HTTP Request

GET `/api/texts`


### HTTP Response Status-Codes

| Code   | Description |
|:-------|:------------|
|200     |Success, user received list of all texts|
|401     |Error, user is not authenticated|
|500     |Unknown error|


### Example

#### Request

##### URL

GET `/api/texts`

#### Response

##### Body
```json
{
    "key0": { "value": "value0", "description": "descriptions" },
    "key1": { "value": "value1", "description": "descriptions" },
    "key2": { "value": "value2", "description": "descriptions" },
    "key3": { "value": "value3", "description": "descriptions" },
    "key4": { "value": "value4", "description": "descriptions" },
}
```
