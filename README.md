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
* /lang
* /views
    * /home
        * index.php
    * _layout.php
* config.json
* index.php


How Do I Setup My Webserver?
----------------------------

Same way as other PHP MVC framworks:  

### Apache

In your site-configurtaion file add the following:  

    <Directory />
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule .* index.php [QSA,L]
    </Directory>

## IIS

In the Web.config add the following:  

    ﻿<?xml version="1.0" encoding="UTF-8"?>
    <configuration>
        <system.webServer>
            <directoryBrowse enabled="false" />
            <rewrite>
                <rules>
                    <rule name="Rewrite Rule" stopProcessing="true">
                        <match url="^(.*)$" ignoreCase="true" />
                        <conditions>
                            <add input="{REQUEST_FILENAME}" matchType="IsFile" ignoreCase="false" negate="true" />
                        </conditions>
                        <action type="Rewrite" url="index.php" appendQueryString="true" />
                    </rule>
                </rules>
            </rewrite>
        </system.webServer>
    </configuration>

Internationalization / Localization
-----------------------------------

For applications requiring language specific pages, language files for each supported language can be created. For example, create the following file:

* /lang/en.json


    {  
        "title": "one-php-mvc",  
        "tag_line": "Most Fastest-Bestest PHP Micro-Framework"  
    }


Now in your view you can specify:

    <h1><?php echo $this->get_lang('title'); ?></h1>
    <p><?php echo $this->get_lang('tag_line'); ?></p>

For additional languages, simply add the language file to the `/lang` directory, e.g. `/lang/fr.json` and add the text `fr` to the `languages` array in the configuration. When using links, route the url with the `Controller`'s `route_url` function and the language will automatically be prepended to the URL.


Other Frequently Asked Questions
--------------------------------

* **Using URL segments for file paths is a huge security flaw.**  
    That is not a question, and if you can prove it and I will change it.
* **The sample projects have the `index.php` file, controllers and views in the web directory. That's a security flaw. A user could run the file.**  
    Again not a question. Then do not put code in the PHP files that isn't encapsulated in a class (seriously). If you really cannot live with it, you can configure the *one* PHP file to look in the previous directory.
* **Why does one-php-mvc do [insert feature]?**  
    one-php-mvc is designed to be lightweight, fast, and efficient. So fast, that it can run on a Raspberry PI. Your WordPress site can't run on a Raspberry PI, can it?
* **Why does it not do [insert feature]?**  
    If you need a feature, it's just *one* PHP file. Developers have all the freedom to add their own features for use in their projects.
