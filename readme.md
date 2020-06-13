# Api CRUD Laravel

### Installation

```sh
$ composer require iamx/api-crud-generator
```

### Database 

Set your database properties in .env file

### Create a new Api CRUD

```sh
$ php artisan make:crud Test
```
Now you can edit

| Folder | File |
| ------ | ------ |
| app/Models | Test.php |
| app/Transformers | UserTransformer.php |
| app/Http/Controller/Api/Test | TestController.php |
| app/Http/Requests | TestRequest.php |
| app/database/migrations | 2014_10_12_000000_create_tests_table.php |

### Migrate 

```sh
$ php artisan migrate
```

Then you can see the routes in routes/api.php file

License
----

MIT
