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

Using Composer is the quickest way to install everything. For this, make sure you have [Composer](https://getcomposer.org) installed.

Now, in order to run the login-script on your local machine or your own server, follow these installation instructions:

First of all, execute this command in your terminal or command prompt:

```
composer create-project jr-cologne/login-script
```

This is basically the same as doing a `git clone` followed by a `composer install`.

This means that you are almost finished already. Executing the one command above should have downloaded the entire project and installed all Composer dependencies as well.

Now, just continue with the [Installation of Dependencies](https://github.com/jr-cologne/login-script#installation_of_dependencies), but keep in mind that you can skip the part with Composer. Just move on to npm.

### Manual Installation

In order to run the login-script on your local machine or your own server, follow these installation instructions:

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
- Set up the connection to an SMTP server for Email Delivery
- Deploy!

Let's get started!

### Database Setup

If you take a look in the `database` folder, you can see that the login-script provides you with a SQL file (`db-setup.sql`) for setting up your database. Just load this into your database client and everything should be fine.

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

First of all, go ahead and sign up for a Bugsnag account at [bugsnag.com](https://app.bugsnag.com/user/new/).

After that, Bugsnag will guide you through a small setup tour for your specific project.

In the case of the login-script, you need to select a server application, the language PHP and no framework (other) when you are asked for this kind of information.

Once you successfully set up your project, a quick installation guide is shown. In there, you can also find your API key.
In our case, you can find this API key inside the line of code which basically looks like that:

```php
$bugsnag = Bugsnag\Client::make('your-api-key');
```

Now, just copy that API key. In case you are not working with environment variables to store secret information like API credentials, the login-script supports grabbing your API key from a JSON config file. For Bugsnag, this config file should look like this:

```json
{
  "api_key": "your-api-key"
}
```

So, just paste your API key from Bugsnag in there and then save your JSON file to some directory which is ignored by git since you don't want your API keys to be publically available on GitHub or something like that.

Finally, to make sure the login-script knows where it can find your file, you need to specify the file path inside `app/config.php`.

Inside the file `config.php`, look for something looking like this and modify it for your own needs:

```php
'error_handling' => [
  'bugsnag' => [
    'config_file' => Env::get('BUGSNAG_CONFIG') ?? 'storage/error_handling/bugsnag/bugsnag-api-credentials.json',
  ]
]
```

For instance, your own config could look like the following:

```php
'error_handling' => [
  'bugsnag' => [
    'config_file' => 'storage/error_handling/bugsnag/bugsnag-api-credentials.json',
  ]
]
```

That's all. You should now be ready for handling your errors with Bugsnag.

#### Google Sign-In for Websites

Setting up Google Sign-In for Websites is relatively simple since Google provides you with a tone of information and an easy setup tour.

Just go to the [Getting Started Guide for Google-Sign for Websites](https://developers.google.com/identity/sign-in/web/sign-in) and click on the button "Configure a Project".

You are then asked to enter a project and a product name. Next, you need to configure your OAuth client.

For your application environment, choose "Web Server". Furthermore, your authorized Redirect URIs need to look similar to this:

```
http://example.com/google_login.php
http://example.com/google_register.php

For Localhost:
http://localhost/google_login.php
http://localhost/google_register.php
```

Very important is that the filenames stay the same. The rest can be adapted.

Next, download the client configuration of your created project. This is a JSON file which can be used for the login-script to define your credentials for the Google API. For this, again just use a directory which is ignored by git and make sure you put the file path inside the config file of the login-script. By default, the section of interest inside the file `config.php` looks like this:

```php
'social_auth' => [
  'google' => [
    'config_file' => json_decode(Env::get('GOOGLE_CONFIG'), true) ?? 'storage/social_auth/google/client_secret_374519720876-f0vvtnsi6prh6oepehtj9e2vgif8u2fd.apps.googleusercontent.com.json',
    'scopes' => 'email',
    'redirect_uri' => [
      'login' => Env::get('GOOGLE_REDIRECT_URI_LOGIN') ?? 'http://localhost:8080/GitHub/login-script/google_login.php',
      'register' => Env::get('GOOGLE_REDIRECT_URI_REGISTER') ?? 'http://localhost:8080/GitHub/login-script/google_register.php'
    ],
    'registration' => [
      'username_length' => 8,
      'password_length' => 16
    ]
  ],
```

The path to your Google client configuration file needs to be specified at `config_file`. In addition, go ahead and change the redirect URIs as well. The rest can stay the same.

Here's a final example of how your configuration could look like after you entered everything mentioned above:

```php
'social_auth' => [
  'google' => [
    'config_file' => 'storage/social_auth/google/client_secret_374519720876-f0vvtnsi6prh6oepehtj9e2vgif8u2fd.apps.googleusercontent.com.json',
    'scopes' => 'email',
    'redirect_uri' => [
      'login' => 'http://localhost/google_login.php',
      'register' => 'http://localhost/google_register.php'
    ],
    'registration' => [
      'username_length' => 8,
      'password_length' => 16
    ]
  ],
```

That's it. You can now move on to Twitter.

#### Twitter API

Setting up everything for accessing the Twitter API is a bit more complex compared to Google.

First of all, you need to create a Twitter App over at [apps.twitter.com](https://apps.twitter.com/). In order to be able to do this, you might need to [apply for a Twitter developer account](https://developer.twitter.com/en/apply/user).

During this process, you are asked for a project name, a description, the website of your application and a Callback URL.

This is basically the same as the Redirect URI what Google calls it. Again, this needs to look similar to this:

```
http://example.com/twitter_login.php
http://example.com/twitter_register.php

For Localhost:
http://localhost/twitter_login.php
http://localhost/twitter_register.php
```

Like already mentioned, it is especially important that the filename stays the same. The rest can be edited.

Once you then successfully created your app, you need to change the following settings for your Twitter App:

- Go to the settings page and activate the checkbox "Allow this application to be used to Sign in with Twitter".
- Go to the permissions page. Under "Additional Permissions", activate the checkbox "Request email addresses from users".

Next, go to the page "Keys and Access Tokens". There you can find your API key and your API secret key.

Now, you need to copy and paste them into another JSON config file responsible for the Twitter API credentials.

Here is the required format:

```json
{
  "api_key": "your-api-key",
  "api_secret": "your-api-secret-key"
}
```

Save this file including your credentials inside a folder which is ignored by git and specify the file path inside the file `app/config.php`. You can find the needed part directly under the Google configurations inside the array with the key `social_auth`:

```php
'twitter' => [
  'config_file' => Env::get('TWITTER_CONFIG') ?? 'storage/social_auth/twitter/twitter-api-credentials.json',
  'redirect_uri' => [
    'login' => Env::get('TWITTER_REDIRECT_URI_LOGIN') ?? 'http://localhost:8080/GitHub/login-script/twitter_login.php',
    'register' => Env::get('TWITTER_REDIRECT_URI_REGISTER') ?? 'http://localhost:8080/GitHub/login-script/twitter_register.php'
  ],
  'registration' => [
    'username_length' => 8,
    'password_length' => 16
  ]
]
```

Like with Google, you need to change the `config_file` options as well as the Redirect URIs. This is how it could look like:

```php
'twitter' => [
  'config_file' => 'storage/social_auth/twitter/twitter-api-credentials.json',
  'redirect_uri' => [
    'login' => 'http://localhost/twitter_login.php',
    'register' => 'http://localhost/twitter_register.php'
  ],
  'registration' => [
    'username_length' => 8,
    'password_length' => 16
  ]
]
```

That's all. You should now have access to the Twitter API.

#### Setting up Email Delivery through an SMTP server

Finally, let's set up email delivery by configuring the login-script to use an SMTP server for this.

If you don't have an SMTP server, you might be interested in using [Gmail's SMTP server](https://support.google.com/a/answer/176600?hl=en).

Once you have the credentials to some kind of SMTP server, go ahead and create another JSON config file inside a directory which is ignored by git. The format of your JSON file is as follows:

```json
{
  "smtp_server": "your-smtp-server-address",
  "smtp_port": "your-smtp-port",
  "smtp_encryption": "tls",
  "smtp_username": "your-smtp-username",
  "smtp_password": "your-smtp-password"
}
```

After entering your credentials, save the JSON file and then open up the login-script config file over at `app/config.php`.

Now, search for this entry:

```php
'mail' => [
  'smtp_config_file' => Env::get('SMTP_CONFIG') ?? 'storage/mail/smtp/smtp-server-credentials.json'
],
```

Everything you need to specify is the file path to your file containing the SMTP server credentials. Finally, this could look like that:

```php
'mail' => [
  'smtp_config_file' => 'storage/mail/smtp/smtp-server-credentials.json'
],
```

After doing all of this, everything should be ready to go. Go ahead, deploy your application and have fun!
If you want to, you can, of course, customize the login-script according to your own needs.

## Contributing

Feel free to contribute to this project! Any kinds of contributions are highly appreciated!

## License

This project is licensed under the [MIT License](https://github.com/jr-cologne/login-script/blob/master/LICENSE).
