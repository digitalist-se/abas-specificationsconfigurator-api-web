# Create new Text

## HTTP Request

POST `/api/texts`

### Request body

| Parameter |  Type  | Description |
|:----------|:-------|:------------|
|key        |string  |unique key of new value|
|value      |string  |new value of text|
|public     |boolean |is text public to api. or only visible in word export|

### HTTP Response Status-Codes

| Code   | Description |
|:-------|:------------|
|204     |Update was successfully|
|500     |Unknown error|

### Example

#### Request

##### URL
POST `/api/texts`

##### Body
```json
{
    "key: "key1",
    "value": "Neuer Value"
}
```

#### Response

No response body has to be provided.
