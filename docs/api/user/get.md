# GET User

Retrieves the own user data.

## Prerequisites

The user has to be authenticated by JWT.

## HTTP Request

GET `/api/user`

### Request parameters / body

None, the user should be identified through authentication.

### HTTP Response Status-Codes

| Code   | Description |
|:-------|:------------|
|200     |Success, user data will be sent|
|401     |Error, user is not authenticated|
|500     |Unknown error|

### Example

#### Request

##### URL
GET `/api/user`

##### Headers
```json
{
    "Authentication": "Bearer abcDEFghi"
}
```

#### Response

##### Body
```json
{
    "name": "Max Muster",
    "email": "max.muster@company.com",
    "email_verified": true,
    "role": 1,
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
