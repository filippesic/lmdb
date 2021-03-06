<p align="center"><img src="https://res.cloudinary.com/dtfbvvkyp/image/upload/v1566331377/laravel-logolockup-cmyk-red.svg" width="400"></p>

## About this project

This is my second Laravel project, but it's the first REST API based so it's bound to have some bugs and general mistakes.

## Routes and initialization

You need to make (L)`lmdb_db`(InnoDB) database and run `migrate`, `seed`, `passport install`, artisan commands first. Password for all users is `321`.
There are two roles for users which defaults to `role_id` `1`, but you can manually set it to `2`, which is administrator which has basically all privileges.

For the routes, standard CRUD routes apply for all(most) models with some custom ones.

`/api/` prefix applies to all routes

* `POST /login` generates a token for the registered user using Passport
* `POST /logout` deletes all tokens for the user
* `POST /register` registers a user
* * *
* `/artists` CRUD (policy)
* `/videos` CRUD (policy) *season episodes are just numbers* (_see test branch_)
* `/genres` CRUD (policy)
* `/users` CRUD (policy)
* * *
* `GET /videos/top` lists top videos
* `GET /videos/search` typical search
* `GET /actors` lists only artists that are actors(artist_type_id) 
* `GET /directors` lists only artists that are directors(artist_type_id)
* `GET /user` shows user data(`auth:api`)
* `GET /user/watchlistId` shows video ids for user watchlist(`auth:api`)
* `GET /user/list2` returns user's watchlist with rating_average(`auth:api`)
* `GET /user/rates` returns rates for the user(`auth:api`)
* * *
* `POST /user/rate` rates a movie(`auth:api`), rates and updates
* `POST /user/rates2` takes `video_id` and returns any rates that user has given(`auth:api`)
* `POST /user/unrate` 'unrates' the movie(`auth:api`)
* `POST /user/addToList` accepts `video_id` and adds a movie to user's watchlist(`auth:api`)


## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
