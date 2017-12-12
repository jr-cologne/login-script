# Login Script

Welcome to this project, a simple database-based login script with PHP.

Before you get started, I would like to give you a few information about it. So please take a minute and read this small documentation carefully.

## Requirements/Dependencies

- [PHP](http://php.net) (version 7.0 or higher)
- Database, which supports PDO (e.g. MySQL)
- [jr-cologne/db-class](https://github.com/jr-cologne/db-class) (version ^2.1)
- [Google APIs Client Library for PHP](https://github.com/google/google-api-php-client) (version ^2.2)

You don't have any clue, what the symbols next to the versions mean? [Read this](https://getcomposer.org/doc/articles/versions.md)!

## Installation

If you want to run the login script on your local machine or your own server, do the following things.

1. Download the ZIP file of this project.
2. Unzip it and also install the required dependencies for this project. ([More information on that here](https://github.com/jr-cologne/login-script#installation_of_dependencies))
3. Upload the files to your server via FTP or if you want to have it on your local machine, put the files inside your corresponding directory of your local server. Mostly the directory is called `htdocs`, `www` or something like that.
4. Create a new database and then import the table `users`, by the SQL file inside the `php` directory, into the database.
5. Open the file `php/config.php` in your editor and change the login settings for your database connection. Also, you can make some changes to the other settings, if you want to.
6. Now you should be ready to start!

### Installation of Dependencies

You have got two options to install all dependencies for this project:

#### Using Composer (recommended)

Once you have installed [Composer](https://getcomposer.org), execute this command:

`composer install`

That should install all dependencies listed in [`composer.json`](https://github.com/jr-cologne/login-script/blob/master/composer.json).

#### Manual Installation

Follow the instructions for the manual installation from the own documentations of the dependencies, but if you are not using Composer, always make sure to remove this line out of the file [`includes/init.php`](https://github.com/jr-cologne/login-script/blob/master/includes/init.php):

```php
require_once('vendor/autoload.php');
```

For the *Google APIs Client Library* you can [read through this](https://github.com/google/google-api-php-client#download-the-release) and for the Database Class you can [follow this small documentation](https://github.com/jr-cologne/db-class#manual-installation).

After you followed the instructions over there and downloaded it, you just have to put the `google-api-php-client` folder with all files into your `vendor` folder. Finally, you must include the autoloader in the file [`includes/google/google.php`](https://github.com/jr-cologne/login-script/blob/master/includes/google/google.php):

```php
require_once('vendor/google-api-php-client/vendor/autoload.php');
```

The same applies for the Database Class. You have to include the class into the file [`includes/db.php`](https://github.com/jr-cologne/login-script/blob/master/includes/db.php) like so:

```php
require_once('vendor/db-class/DB.php');
```


If you have any trouble installing the login script and the dependencies, you found a bug or you have got a question, open up an Issue and I will try to help you.

## Contributions

Feel free to contribute to this project! Although it is a private project, it would be awesome for me if somebody contributes to it.

So don't be shy and start coding! If you want to make sure that I like your idea, you can contact me by an Issue.

But if you decide to contribute to this project, keep in mind that at last, it is my choice to merge your Pull Request or not, so also be prepared for a negative decision.

## License

This work is licensed under the Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.

To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-sa/4.0/.


Ok, that's it. Have fun with this login script and I thank you for your time that you spend on the project!

Also, I must excuse me for my bad English. I hope you can understand it.
