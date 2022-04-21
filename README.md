# iragu
Badminton Player and Court Management

The title says it all.  I just have some ideas and will try it out.  This is
experimental and not sure if it will ever be useful.  Just for fun.  Planning
to use GNU/Linux, Apache, MySQL, PHP (LAMP) stack to create this webapp.

## Development Environment

I am using an old PC with Ubuntu 20.04.0 LTS (focal) operating system for this
development work.

### Generating autoload.php

I am using PHP Autoload Builder (phpab) to generate the autoload.php file.
Refer to https://github.com/theseer/Autoload for more details.

phpab -o www/autoload.php www

### Unit Testing

Using PHPUnit for unit testing PHP classes. For more details refer to
https://phpunit.de.
