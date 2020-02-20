<p align="center"><img src="https://res.cloudinary.com/dtfbvvkyp/image/upload/v1566331377/laravel-logolockup-cmyk-red.svg" width="400"></p>

## About this project

This is my second Laravel project, but it's the first REST API based so it's bound to have some bugs and general mistakes with lots of spaghetti code so be aware. :)

## Routes and initialization

You need to make `lmdb_db`(InnoDB) database and run `migrate`, `seed`, `passport install`, artisan commands first.

For the routes, standard CRUD routes apply for all models with some custom ones.

`/api/` prefix applies to all routes

* `/login` generates a token for the registered user using Passport
* `/register` registers a user
* * *
* `/artists` CRUD
* `/videos` CRUD
* `/genres` CRUD **(incomplete)**
* `/users` CRUD (fixed a token deletion bug with admin)
* * *
* `/videos/search` typical search
* `/videos/top` lists top videos
* `/actors` lists only artists that are actors(artist_type_id) 
* `/directors` lists only artists that are directors(artist_type_id)
* `/user` shows some data about the logged user
* `/user/rate` rates a movie(if the user is logged in) **needs update rate method**
* `/user/rates` returns rates for authenticate user
* `/user/rates2` takes `video_id` and returns any rates that user has given
* `/user/unrate` unrates the movie


## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
