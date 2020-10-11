# JWT Register and Authenticate by Coldsnake Digital
[![MIT license](https://img.shields.io/badge/license-MIT-blue.svg)](https://github.com/maicol07/flarum-jwt-auth/blob/master/LICENSE.md)

**NOTE: MOVED TO [FLARUM SSO EXTENSION](https://github.com/maicol07/flarum-ext-sso)**

A fork of [rattler72/flarum-jwt-auth](https://github.com/rattler72/flarum-jwt-auth).

This extension registers and authenticates a user based on a JWT.

## Installation

Use Composer:

```bash
composer require maicol07/flarum-jwt-auth
```

## Updating

```bash
composer update maicol07/flarum-jwt-auth
php flarum cache:clear
```

## Usage

Pass the JWT as a param to the following URL: `http://yourfloarumdomain.com/api/auth/jwt?token=[token]`.

This will register the user with the username being the same as the prefix of the email address and then log them into that account. The password is randomly generated. This is meant to work in conjunction with SSO extensions to handle ongoing authentication.
