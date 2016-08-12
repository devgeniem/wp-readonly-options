# WP Plugin: Must Use Options
Have you ever wanted to set all your options as `define('PLUGIN_OPTION','some_value')` in wp_config.php?
Sometimes you need to use 3rd party plugin which only gives you those options in admin pages and GUI.
And now you can't use git to version control your settings as you would love to do.

This plugin helps you to set all your settings in your code and doesn't force you to go over to admin pages.

It works by allowing you to force the results of `get_option()` to your predefined values.
