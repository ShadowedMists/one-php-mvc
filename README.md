one-php-mvc
===========

one-php-mvc is a true PHP microframework that is contained in one PHP file with internationalization support. No libraries, extensions or dependencies.  

It has everything you need to write an MVC application in PHP and nothing you don't need. Since one-php-mvc contains only one file to initialize and execute server requests, it contains no autoloader, no ORM/DCI, and no additional packages or frameworks.  Controllers and View pages are dynamically loaded based on the requested URL. Developers interested in using one-php-mvc can add anything else they need.  

Because the one-php-mvc microframework exists in one PHP file and only loads the files required to complete the request, this makes one-php-mvc faster than other PHP microframeworks and certainly faster than other larger PHP frameworks with built in functionalities. The application is also highly portable. Compatible with PHP 5.3 and up, one-php-mvc will run on almost any machine with any PHP configuration.

What Files are Included?
------------------------

index.php


What Other Files are Included?
------------------------------

Optionally, the user can can create a configuration file which is expected to contan a JSON object. When the Router is constructed, the user can pass in the file path for this configuration object, and it's properties will be added to the `Configuration` class.  

This repository also contains sample projects for setting up one-php-mvc for a variety of uses.  

How Should I Structure My Project?
----------------------------------

* /controllers
    * home.php
* /includes
* /views
    * /home
        * index.php
    * _layout.php
* config.json
* index.php
