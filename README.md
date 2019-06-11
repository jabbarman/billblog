# billblog

## Introduction
A simple Laravel based RESTful api to allow basic blogging functions

## Overview
This is for testing Laravel as a basis for developing a RESTful web service

## Authentication
Authentication is JSON Web Token based and a user account is required in order to gt hold of a token. The token is required for adding, editing or removing actual data

## Error Codes
400, 404, 403, 200, 201, 500 etc

## Rate limit
60 per minute

## RESTful API

##### List all blog posts
**GET** `api/v1/blog/`
```
{
    "message": "posts found",
    "posts": [
        {
            "id": 1,
            "title": "Hello, World!",
            "creator": "Joe Bloggs",
            "href": "/api/v1/blog/1",
            "method": "GET"
        }
        .
        .
        .
    ]
}
```

##### Create a blog post
**POST** `api/v1/blog?token={token}`
_[``title``,``body``]_
```
{
    "message": "post created",
    "id": 8,
    "title": "OctoPosty",
    "creator": "Joe Bloggs",
    "href": "/api/v1/blog/8",
    "method": "GET"
}
```

##### Upload an image
**POST** `api/v1/blog/{blog}/upload?token={token}`
_[``image``]_
```
{
    "message": "file accepted",
    "blog_id": "7",
    "upload_id": 2
}
```

##### Edit a blog post
**PATCH** `api/v1/blog/{blog}?token={token}`
_[``title``] [``body``]_
```
{
    "message": "post edited",
    "id": 8,
    "title": "OctoPosty (edited)",
    "user_id": 8,
    "creator": "Joe Bloggs",
    "href": "/api/v1/blog/8",
    "method": "GET"
}
```

##### Show a blog post
**GET** `api/v1/blog/{blog}`
```
{
    "message": "post found",
    "post": {
        "id": 7,
        "title": "Lucky for some",
        "body": "Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.",
        "images": [
            {
                "id": 2,
                "href": "/storage/1zgBLRlHPdNkw0kDJ0Z9AjEHie4AmmA90iRPpI42.jpeg",
                "method": "GET"
            }
        ],
        "labels": [
            {
                "id": 8,
                "name": "honey"
            }
        ],
        "user_id": 2,
        "creator": "Joe 2 ",
        "created_at": {
            "date": "2018-01-20 12:51:10.000000",
            "timezone_type": 3,
            "timezone": "UTC"
        },
        "updated_at": {
            "date": "2018-01-20 12:51:10.000000",
            "timezone_type": 3,
            "timezone": "UTC"
        },
        "href": "/api/v1/blog/7",
        "method": "GET"
    }
}
```

##### Delete a blog post
**DEL** `api/v1/blog/{blog}?token={token}`
```
{
    "message": "post deleted",
    "id": 9,
    "title": "Not long for this world!"
}
```

##### Add a label to a post
**POST** `api/v1/blog/{blog}/label?token={token}`
_[``name``]_
```
{
    "message": "label created",
    "id": 8,
    "name": "honey",
    "href": "/api/v1/blog/7",
    "method": "GET"
}
```

##### Delete a label from a post
**DEL** `api/v1/blog/{blog}/label/{label}?token={token}`
```
{
    "message": "label deleted",
    "id": 8,
    "name": "honey",
    "href": "/api/v1/blog/7",
    "method": "GET"
}
```

##### Authenticate a user
**POST** `api/v1/user/authenticate`
_[``email``,``password``]_
```
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGci...dUjWQYVOwiRSo"
}
```

##### Create a user
**POST** `api/v1/user`
_[``name``,``email``,``password``]_
```
{
    "message": "user created"
}
```