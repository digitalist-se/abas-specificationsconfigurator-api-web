# POST Reset password

Password reset request.

## HTTP Request

POST `/api/password/reset`

### Request body

| Parameter |  Type  | Description |
|:----------|:-------|:------------|
|token      |string  |The token from the email link|
|email      |string  |The users email|
|password      |string  |The new password|
|password_confirmation      |string  |Confirmation of the new password|

### HTTP Response Status-Codes

| Code   | Description |
|:-------|:------------|
|204     |Success, password was resetted |
|401     |Unauthorized, no matching user to token was found.   |
|422     |Error, request data is not correct. Failed by validation|
|500     |Unknown error|

### Example

#### Request

##### URL
POST `/api/password/reset`

##### Body
```json
{
    "token": "37afe89506ff9b6a255a25ad7de20af3c6deee8b28230dacbe6b059",
    "email": "max.muster@company.com",
    "password": "newpw",
    "password_confirmation": "newpw"
}
```

#### Response

Success Response:
No response body has to be provided.


Error Response

```json
{
    "error": "This password reset token is invalid."
}
```
