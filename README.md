# WP Plugin: Must Use Options
Have you ever wanted to set all your options as `define('PLUGIN_OPTION','some_value')` in wp_config.php?
Sometimes you need to use 3rd party plugin which only gives you those options in admin pages and GUI.
And now you can't use git to version control your settings as you would love to do.

This plugin helps you to set all your settings in your code and doesn't force you to go over to admin pages.


## How it works
It works by allowing you to force the results of `get_option()` to your predefined values.

This also adds tiny amount if javascript into admin pages so that it can set readonly attributes to your options: `<input readonly>`.
This makes it easier for the users to understand that these options shouldn't be changed.

This only works in php7.0 because we use arrays in defining constants. Sorry legacy projects :(

## Installation
Prefered installation is with composer:

```json
{
    "require": {
        "devgeniem/wp-must-use-options": "^1.0"
    },
    "extra": {
        "installer-paths": {
          "web/app/mu-plugins/{$name}/": ["type:wordpress-muplugin"],
        },
    }
}
```

## Code Example:

Following line in your `wp-config.php` will force `upload_path` to be `wp-content/files/` always.

```php
define( 'WP_MUST_USE_OPTIONS', array(
    'upload_path' => 'files'
));
```


## Configuration
Disable JS include from admin pages.
```php
define('WP_MUST_USE_OPTIONS_NO_JS',true);
```
