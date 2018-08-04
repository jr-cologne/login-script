# Login Script

A simple object-oriented and database-based login script with PHP.

## Requirements/Dependencies

- [PHP](http://php.net) (version 7.0 or higher)
- Database, which supports PDO (e.g. MySQL)
- [jr-cologne/db-class](https://github.com/jr-cologne/db-class) (version ^2.1)
- [Google APIs Client Library for PHP](https://github.com/google/google-api-php-client) (version ^2.2)
- [Codebird Twitter API Library](https://github.com/jublonet/codebird-php) (version ^3.1)
- [Bugsnag PHP Library](https://github.com/bugsnag/bugsnag-php) (version ^3.14)
- [SwiftMailer](https://github.com/swiftmailer/swiftmailer) (version ^6.1)
- [Bootstrap](https://getbootstrap.com/) (version ^4.1.3)

## Installation

### Using Composer (recommended)

Coming soon...

### Manual Installation

In order to run the login script on your local machine or your own server, follow these installation instructions:

1. Download one of the archive files from the latest (stable) release of this project, either *zip* or *tar.gz*.
2. Unpack the archive of your choice and put the files into a folder where you project should be located.
3. Install dependencies, see [Installation of Dependencies](https://github.com/jr-cologne/login-script#installation_of_dependencies) for more details.

## Installation of Dependencies

You have got two options to install all dependencies for this project:

### Using Composer & npm (recommended)

First of all, make sure you have [Composer](https://getcomposer.org) installed. Then execute this command in your terminal or command prompt:

```
composer install
```

This should install all dependencies listed in the file `composer.json`.

Secondly, you also need to have [Node.js](https://nodejs.org/en/) and [npm](https://www.npmjs.com/) installed on your computer.

Then, go ahead and run the following command:

```
npm install
```

## Getting Started

Before following this short Getting Started Guide, please ensure that you have successfully installed everything outlined in the above Installation Guide.

In order to achieve getting the login-script working as expected, you need to do the following things:

- Set up your database and the connection to it
- Set up your access to the API of Google and Twitter for the Social Authentication feature as well as Bugsnag for Error Handling
- Set up the connection to a SMTP server for Email Delivery
- Deploy!

Let's get started!

### Database Setup

If you take a look in the `database` folder, you can see that the login-script provides you with a sql file (`db-setup.sql`) for setting up your database. Just load this into your database client and everything should be fine.

After you have successfully set up the database, you only need to establish your database connection.

For this, open the file `config.php` which is located in the `app` folder.

For now, you just need to worry about changing the values inside the array with the key `database`:

```php
'database' => [
    'type' => 'mysql',
    'name' => substr($db_url['path'], 1) ?: 'login-script',
    'host' => $db_url['host'] ?? '127.0.0.1',
    'user' => $db_url['user'] ?? 'root',
    'password' => $db_url['pass'] ?? 'root',
    'table' => 'users'
]
```

After your modifications, your array might look like this for example:

```php
'database' => [
    'type' => 'mysql',
    'name' => 'login-script',
    'host' => '127.0.0.1',
    'user' => 'root',
    'password' => 'password',
    'table' => 'users'
]
```

That's it. Your database should now be ready to go.

### Setting up Access to APIs for Social Auth and Error Handling

As I already mentioned shortly, you need access to a total of three APIs in order to make everything work properly without really touching any code.

These are the three APIs/Services of interest:

- [Bugsnag](https://www.bugsnag.com/)
- [Google Sign-In for Websites](https://developers.google.com/identity/sign-in/web/)
- [Twitter API](https://developer.twitter.com/)

Let's start setting them up.

#### Bugsnag



#### Google Sign-In for Websites



#### Twitter API



#### Setting up Email Delivery through a SMTP server



#### Deployment



## Contributing

Feel free to contribute to this project! Any kinds of contributions are highly appreciated!

## License

This project is licensed under the [MIT License](https://github.com/jr-cologne/login-script/blob/master/LICENSE).
