# PUT password

Update password of a user.

## HTTP Request

PUT `/api/password`

### Request body

| Parameter             |  Type  | Description      |
|:------------          |:-------|:------------     |
|password_old           |string  |The users old/current password|
|password               |string  |The users new password|
|password_confirmation  |string  |The users new password|

### HTTP Response Status-Codes

| Code   | Description |
|:-------|:------------|
|204     |Success, user was updated|
|400     |Error, request data not correct|
|401     |Error, user is not authenticated|
|422     |Error, request data is not correct. Failed by validation|
|500     |Unknown error|

### Example

#### Request

##### URLmake up
PUT `/api/password`

##### Headers
```json
{
    "Authentication": "Bearer abcDEFghi"
}
```

##### Body
```json
{
    "password_old": "aktuellesPassword",
    "password": "neuesSicheresPassword",
    "password_confirmation": "neuesSicheresPassword",
}
```

#### Response

##### Body

No response body has to be provided.
