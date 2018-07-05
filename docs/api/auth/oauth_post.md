# POST OAuth

Login for users.

## HTTP Request

POST `/oauth/token`

### Request body

| Parameter    |  Type  | Description |
|:-------------|:-------|:------------|
|client_id     |number  |The backend APIs client ID|
|client_secret |string  |The backend APIs client secret|
|email         |string  |The users email|
|grant_type    |string  |The backend APIs grant type (currently "password")|
|password      |string  |The users password|
|scope         |string  |The backend APIs scope (currently "*")|

### HTTP Response Status-Codes

| Code   | Description |
|:-------|:------------|
|200     |Success, user logged in, token is sent|
|401     |Error, user is not authenticated|
|500     |Unknown error|

### Example

#### Request

##### URL
POST `/oauth/token`

##### Body
```json
{
    "client_id": 1,
    "client_secret": "abcDEFghi",
    "username": "max.muster@company.com",
    "grant_type": "password",
    "password": "12345",
    "scope": "*"
}
```

#### Response

##### Body
```json
{
    "token_type": "Bearer",
    "expires_in": 86399,
    "access_token": "abcDEFghi",
    "refresh_token":"DEFghiJKL"
}
```
