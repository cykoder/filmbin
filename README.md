FilmBin
===
FilmBin is a free, open-source project created as a personal alternative to services such as Netflix, PrimeWire (letmewatchthis), LoveFilm etc. FilmBin crawls websites for film/tv show information and, if available, links to stream the title in question. I support anyone who wants to clone this repository and make any changes they see fit to the API, crawler or various apps. Just submit a pull request, and your wish is my command!

The crawler
===
At the heart is FilmBin is the crawler. The crawler allows any machine that can run PHP and has an internet connection (even a Raspberry Pi can make a great crawler) to scrape data from film/tv show websites. Without the crawler storing all of this data in a local database, the service would be extremely slow. It is my intention to have many crawlers running across multiple machines throughout the world so that FilmBin can have the largest, most up-to-date library of films and tv shows available.

Usage
---
The crawler is best used from a command line interface. It's as simple as:
> /path/to/php crawler.php

It can also take command line arguments

> /path/to/php crawler.php primewire 1 10

*	"primewire" being the service to crawl
*	"1" being the page to start crawling on
*	"10" being the page to end crawling on

The API
===
FilmBin uses a RESTful api service, written in PHP. We use Phil Sturgeon's [CI rest-server](https://github.com/philsturgeon/codeigniter-restserver) project to help us along with this. But hey, if you have a better method, why not code it and submit a pull request? :D

Setup
---
Setting up the API is relatively simple. Simply just pull/download the repository. You'll want to edit these files and configure them as needed:
*	.htaccess
*	application/config/config.php.template (save as config.php)
*	application/config/database.php.template (save as database.php)

You'll then need to import the sample database (coming soon)

Contact
===
Any issues or queries, the best way to contact me is to email me at [sshellawell@gmail.com](mailto:sshellawell@gmail.com), tweet me [@Beckiwoosh](http://twitter.com/Beckiwoosh) or add me on Skype: *becki.ness*