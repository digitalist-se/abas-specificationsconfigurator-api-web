# Update or create an Answer

## HTTP Request

PUT `/api/answers/{elementId}`

### Request body

| Parameter |  Type  | Description |
|:----------|:-------|:------------|
|value      |string  |new value of answer|

### HTTP Response Status-Codes

| Code   | Description |
|:-------|:------------|
|204     |Update was successfully|
|500     |Unknown error|

### Example

#### Request

##### URL
PUT `/api/answers/UUID-OF-ELEMENT`

##### Body
```json
{
    "value": { "text": "Das ist eine Antwort" }
}
```

#### Response

No response body has to be provided.
