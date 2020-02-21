<p align="center"><img src="https://res.cloudinary.com/dtfbvvkyp/image/upload/v1566331377/laravel-logolockup-cmyk-red.svg" width="400"></p>

## About this project

This is my second Laravel project, but it's the first REST API based so it's bound to have some bugs and general mistakes with lots of spaghetti code so be aware. :)

## Routes and initialization

You need to make (L)`lmdb_db`(InnoDB) database and run `migrate`, `seed`, `passport install`, artisan commands first. Password for all users is `321`. 

For the routes, standard CRUD routes apply for all(most) models with some custom ones.

`/api/` prefix applies to all routes

* `POST /login` generates a token for the registered user using Passport
* `POST /logout` deletes all tokens for the user
* `POST /register` registers a user
* * *
* `/artists` CRUD (policy)
* `/videos` CRUD (policy)
* `/genres` CRUD **(incomplete)**
* `/users` CRUD (policy)(fixed a token deletion bug with admin)
* * *
* `GET /videos/search` typical search
* `GET /videos/top` lists top videos
* `GET /actors` lists only artists that are actors(artist_type_id) 
* `GET /directors` lists only artists that are directors(artist_type_id)
* `POST /user` shows some data about the user(`auth:api`)
* `POST /user/rate` rates a movie(`auth:api`) ~~needs update rate method~~, rates and updates now
* `POST /user/rates` returns rates for the user(`auth:api`)
* `POST /user/rates2` takes `video_id` and returns any rates that user has given(`auth:api`)
* `POST /user/unrate` unrates the movie(`auth:api`)
* `POST /user/addToList` takes `video_id` and adds a movie to user's watchlist(`auth:api`)


## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
