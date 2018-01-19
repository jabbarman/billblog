# billblog

## Introduction
A simple Laravel based api to allow basic blogging functions

## Overview
This is for testing Laravel as a basis for developing a RESTful web service

## Authentication
Authentication is JSON Web Token based and a user account is required in order to gt hold of a token. The token is required for adding, editing or removing actual data

## Error Codes
400, 404, 200, 201, 500 etc

## Rate limit
60 per minute

## RESTful endpoints

#### list all blog posts
**GET** `api/v1/blog/`

#### create a blog post
**POST** `api/v1/blog?token={token}`
[_title_,_body_]

#### upload an image
**POST** `api/v1/blog/{blog}/upload?token={token}`
[_image_]

#### edit a blog post
**PATCH** `api/v1/blog/{blog}?token={token}`
[_title_],[_body_]

#### show a blog post
**GET** `api/v1/blog/{blog}`

#### delete a blog post
**DEL** `api/v1/blog/{blog}?token={token}`

#### add a label to a post
**POST** `api/v1/blog/{blog}/label?token={token}`
[_name_]

#### delete a label from a post
**DEL** `api/v1/blog/{blog}/label/{label}?token={token}`

#### authenticate a user
**POST** `api/v1/user/authenticate`
[_email_,_password_]

#### create a user
**POST** `api/v1/user`
[_name_,_email_,_password_]


