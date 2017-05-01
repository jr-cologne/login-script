# db-class
This project is a simple database class with php and pdo.

## Table of Contents
1. [Installation/Setup](#installationsetup)
2. [Basic Usage](#basic-usage)
	1. [Require Database Class](#require-database-class)
	2. [Instantiate Class / Connect to Database (`__construct()`)](#instantiate-class--connect-to-database-__construct)
	3. [Check Connection to Database (`connected()`)](#check-connection-to-database-connected)
	4. [Select/Get Data from Database (`select()`)](#selectget-data-from-database-select)
	5. [Insert Data into Database (`insert()`)](#insert-data-into-database-insert)
	6. [Delete Data/Rows from Database (`delete()`)](#delete-datarows-from-database-delete)
	7. [Update Data in Database (`update()`)](#update-data-in-database-update)
	8. [Configure Error Handling (`initErrorHandler()`)](#configure-error-handling-initerrorhandler)
	9. [Check for Errors (`error()`)](#check-for-errors-error)
	10. [Get Errors (`getError()`)](#get-errors-geterror)
3. [Further Examples / Stuff for Testing](#further-examples--stuff-for-testing)
4. [Contributing](#further-examples--stuff-for-testing)
5. [Versioning](#versioning)
6. [License](#license)

## Installation/Setup
If you want to use the database class for your own project, you can simply follow these instructions:

1. Download the ZIP file of this project
2. Unzip it and save the file `DB.php` to your own project directory.
3. If all requirements, like PHP, PDO and a database that fits to that setup, are fulfilled, you should now be ready to start!

## Basic Usage
If you have successfully "installed" everything, you can use the class like that:

### Require Database Class
```php
require_once('your_path/DB.php');
```

### Instantiate Class / Connect to Database (`__construct()`)
To be able to use the class and connect to the database, you have to instantiate it.

To do that, follow that format:

```php
new DB(
  string $dbname,
  string $user,
  string $password,
  string $db_type='mysql',
  string $host='localhost',
  int $pdo_err_mode=PDO::ERRMODE_EXCEPTION
)
```

It's providing these options:

#### Database Name (`$dbname`)
The name of your database you want to connect with. (required)

#### Database User (`$user`)
The user of the database that should interact with it. (required)

#### Password of the Database User (`$password`)
The password of the database user you are using to interact with the database. (required)

#### Database Type (`$db_type`)
The type of the database you are using. (optional)

Default: `mysql`

#### Host (`$host`)
The host of your database. (optional)

Default: `localhost`

#### PDO Error Mode (`$pdo_err_mode`)
The [error mode of pdo](http://php.net/manual/en/pdo.error-handling.php) you want to use. (optional)

Default: `PDO::ERRMODE_EXCEPTION`

Simple example for instantiating the class:
```php
$db = new DB('db-class-example', 'root', '');
```

### Check Connection to Database (`connected()`)
The method `connected()` gives you the ability to check if the connection to the database was established successfully.

The method requires no arguments at all, so you can just call it and then it will give you a return value of `true` or `false` based on the fact if the connection was established or not.

Example:
```php
if ($db->connected()) {
  echo 'Successfully connected!';
} else {
  echo 'Connection failed';
}
```

### Select/Get Data from Database (`select()`)
In order to get data from the database you can use the method `select()` in the following format:

```php
select(string $sql, array $where=null, int $fetch_mode=PDO::FETCH_ASSOC)
```

The following arguments exist:

#### SQL Query (`$sql`)
The sql to perform the query to the database. (required)

For example:

```sql
SELECT * FROM `users` WHERE `username` = :username
```

#### Values for Where Clause (`$where`)
An array of the values to use in the where clause. (optional)

Default: `null`

Example:
```php
[ 'username' => 'Jack' ]
```

Please note that you have to provide an associative array with keys that match to the placeholders in the sql query, unless you are not using the named placeholders in the query. In case you are just using the question marks as the placeholder, you can get rid of the keys.

#### PDO Fetch Mode (`$fetch_mode`)
The [pdo fetch mode](http://php.net/manual/en/pdostatement.fetch.php). Defines in which format the data is returned from the database. (optional)

Default: `PDO::FETCH_ASSOC`

Example:

```php
PDO::FETCH_NUM
```

An simple example for using everything together:

```php
$data = $db->select("SELECT * FROM `users` WHERE `username` = :username", [ 'username' => 'Jack' ], PDO::FETCH_NUM);
```

### Insert Data into Database (`insert()`)
To insert any data into your database, you can use the `insert()` method with the following format:

```php
insert(string $sql, array $values)
```

The following arguments are required when calling the method:

#### SQL Query (`$sql`)
The sql query to insert the data into your database. (required)

For example that could be:

```sql
INSERT INTO `users` (`username`, `password`) VALUES (:username, :password)
```

#### Values to Insert (`$values`)
An array of the values to be inserted into the database. (required)

Example:

```php
[
  'username' => 'test',
  'password' => 'hello'
]
```

Please note that you have to provide an associative array with keys that match to the placeholders in the sql query, unless you are not using the named placeholders in the query. In case you are just using the question marks as the placeholder, you can get rid of the keys.

Finally, here is an example for using the `insert()` method to insert data into your database:

```php
$inserted = $db->insert(
  "INSERT INTO `users` (`username`, `password`) VALUES (:username, :password)",
  [
    'username' => 'test',
    'password' => 'hello'
  ]
);
```

### Delete Data/Rows from Database (`delete()`)
The method `delete()` provides the ability to delete data from the database.

You have to follow this format:

```php
delete(string $sql, array $where=null)
```

These are the existing arguments:

#### SQL Query (`$sql`)
The sql query to delete the data from the database. (required)

For example:

```sql
DELETE FROM `users` WHERE `id` = :id
```

#### Values for Where Clause (`$where`)
An array of the values to use in the where clause. (optional)

Default: `null`

Example:

```php
[ 'id' => 3 ]
```

Please note that you have to provide an associative array with keys that match to the placeholders in the sql query, unless you are not using the named placeholders in the query. In case you are just using the question marks as the placeholder, you can get rid of the keys.

Simple example for deleting data with this method:

```php
$deleted = $db->delete("DELETE FROM `users` WHERE `id` = :id", [ 'id' => 3 ]);
```

### Update Data in Database (`update()`)
You can update data in your database by the method `update()`, which has this format:

```php
update(string $sql, array $values)
```

That leads to the following arguments:

#### SQL Query (`$sql`)
The sql query to update the data in your database. (required)

Example:

```sql
UPDATE `users` SET `username` = :username WHERE `id` = :id
```

#### New Values and Values for Where Clause (`values`)
An array of the new values to which it should be updated and the values for the where clause. (required)

For example that could be the following:

```php
[
  'username' => 'test',
  'id' => 3
]
```

Please note that you have to provide an associative array with keys that match to the placeholders in the sql query, unless you are not using the named placeholders in the query. In case you are just using the question marks as the placeholder, you can get rid of the keys.

Example for using everything this method has to offer together:

```php
$updated = $db->update(
  "UPDATE `users` SET `username` = :username WHERE `id` = :id",
  [
    'username' => 'test',
    'id' => 3
  ]
);
```

### Configure Error Handling (`initErrorHandler()`)

If you want to, you can create your own error handling setup before you instantiate the class.

Important to know is that every method returns the array `$error` on failure with some basic information about the error that is occured.

The following options exist:

#### Environment (`$env`)
`production` or `development`/`dev`

Production: return simple error code and the related error message (default)

Development: return simple error code, the related error message and the [`PDOException Object`](http://php.net/manual/en/class.pdoexception.php)

#### Error Types / Error Messages (`$error_types`)
An array of the error messages with the error code as the key.

Default:
```php
[
      0 => 'success',
      1 => 'Connection to database failed',
      2 => 'Selecting/Getting data from database failed',
      3 => 'Inserting data into database failed',
      4 => 'Deleting data from database failed',
      5 => 'Updating data in database failed',
]
```

**Attention**: Do not change the error codes/keys as long as you don't modify the class according to that! When you change the error code of an error and then just use the database class as normal, it will not work as expected!

Besides from that you can freely change the error messages to your own liking.

To change the config of the error handling, you must call the static method `initErrorHandler(array $error_types=[], string $env='production')`, which will basically set your specified configs, **before** you are instantiating the class.

Example:
```php
DB::initErrorHandler(
    [
      0 => 'success',
      1 => 'Sorry, the connection to the database is failed!',
      2 => 'Sorry, we are currently not able to receive data from the database!',
      3 => 'Sorry, we are currently not able to insert your data to the database!',
      4 => 'Sorry, we are currently not able to delete your data from the database!',
      5 => 'Sorry, we are currently not able to update your data in the database!',
    ]
);
```

Always make sure to pass in the whole array, e.g. not just error 2 and 5, because then only error 2 and 5 will exist.

In case you don't want to change the messages, but you would like to switch the environment, you have to pass in an empty array as the first argument like that:

```php
DB::initErrorHandler(
    [],
    'development'
);
```

### Check for Errors (`error()`)
In case you want to know if an error occured, you can simply call the method `error()` and it will return you `true` or `false` based on the fact whether there is an error or not.

Example for using that:

```php
if ($db->error()) {
  echo 'There is an error!';
} else {
  echo 'Everything is fine!';
}
```

### Get Errors (`getError()`)
You can get the array `$error`, which contains all errors, by calling this method.

If there are no errors, it will just return `null`.

Example for getting the whole array:

```php
$error = $db->getError();
```

Example for displaying the error message:

```php
echo $db->getError()['msg'];
```

## Further Examples / Stuff for Testing
You want to see further examples for using the database class or you just want to play around with it a little bit?

- You can find a further examples in the file [`example.php`](https://github.com/jr-cologne/db-class/blob/master/example.php).
- To play around with the database class, you can use the database provided in the file [`db-class-example.sql`](https://github.com/jr-cologne/db-class/blob/master/db-class-example.sql). Just import that in your database client and you are ready to start!

## Contributing
Feel free to contribute to this project! It would be awesome for me if somebody contributes to it.

So don't be shy and start coding! If you want to make sure that I like your idea, you can contact me by a Issue.

But if you decide to contribute to this project, keep in mind that finally it is my choice to merge your Pull Request or not, so also be prepared for a negative decision.

## Versioning
I try to follow the rules of Semantic Versioning as good as I can.

For more information about it and if you want to check out the rules, visit http://semver.org/.

## License
This project is licensed under the [MIT License](https://github.com/jr-cologne/db-class/blob/master/LICENSE).
