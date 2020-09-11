# CakePHP 4


## Instructions

1. Install composer `composer install`
2. Fill `.env`
3. Run migration `bin/cake migrations migrate`
4. Run seed `bin/cake migrations seed`


## Endpoints

1. GET User's List
Method `GET`, `baseurl/users.json?token=token`

2. Register User
Method `POST`, `baseurl/users.json`

3. Login
Method `POST`, `baseurl/login.json`