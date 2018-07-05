# POST Forgot password

Starting Password reset process. Email will be send to user with reset pw url.
Final reset Password link: '/password/reset/{token}'

> Under normal circumstances, this request will always send a success message to prevent brute force attacks.

## HTTP Request

POST `/api/password/email`

### Request body

| Parameter |  Type  | Description |
|:----------|:-------|:------------|
|email      |string  |The users email|

### HTTP Response Status-Codes

| Code   | Description |
|:-------|:------------|
|204     |Always sends success to prevent brute force attacks|
|500     |Unknown error|

### Example

#### Request

##### URL
POST `/api/password/email`

##### Body
```json
{
    "email": "max.muster@company.com"
}
```

#### Response

No response body has to be provided.
