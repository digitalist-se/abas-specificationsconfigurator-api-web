# PUT User

Update a user.

## HTTP Request

PUT `/api/user`

### Request body

| Parameter             |  Type  | Description |
|:------------          |:-------|:------------|
|name                   |string  |The users name|
|email                  |string  |The users email|

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
PUT `/api/user`

##### Headers
```json
{
    "Authentication": "Bearer abcDEFghi"
}
```

##### Body
```json
{
    "name": "Max Muster",
    "email": "max.muster@company.com",
    "sex": "m or w",
    "company_name": "company",
    "phone": "0000000000",
    "website": "www.example.de",
    "street": "street",
    "additional_street_info": "",
    "zipcode": "55555",
    "city": "Stadt",
    "contact": "Max Muster",
    "contact_function": "Geschäftsführer"
}
```

#### Response

##### Body

No response body has to be provided.
