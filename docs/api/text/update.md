# Update one Text

## HTTP Request

PUT `/api/texts/{textKey}`

### Request body

| Parameter |  Type  | Description |
|:----------|:-------|:------------|
|value      |string  |new value of text|

### HTTP Response Status-Codes

| Code   | Description |
|:-------|:------------|
|204     |Update was successfully|
|500     |Unknown error|

### Example

#### Request

##### URL
PUT `/api/texts/key1`

##### Body
```json
{
    "value": "Neuer Value"
}
```

#### Response

No response body has to be provided.
