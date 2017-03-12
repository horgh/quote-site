# Summary
This is a website for displaying quotes. The quotes come from IRC. It's sort of
a version of [bash.org](http://bash.org) for a few friends.

It is not the prettiest code but it does its job. I started it as a quick hack
and it has grown to encompass more functionality. Typically I add features
quickly and sometimes go back to clean up the code.

The site is really a counterpart to an IRC bot that displays and searches for
quotes within a channel. The database backend is PostgreSQL and its schema is
[in
sqlquote.pl](https://github.com/horgh/irssi-scripts/blob/master/sqlquote.pl).


## Features
The site has several features:

  * Add a new quote
  * Show who added quotes and how many each added
  * Basic statistics about quote frequency
  * View popular quotes (as voted by a search function in sqlquote.pl)
  * View quotes missing information (the database has gone through a few
    iterations and I've gradually collected more information about each quote)
  * View random quotes
  * View latest quotes
  * Seach for quotes
  * Notify to an IRC channel when someone adds a quote
  * Ability to download the entire database (a database dump must be done
    outside of the site)


## Gotchas
Some things to keep in mind:

  * Error handling is pretty raw. For example if you have a database query
    problem, it's likely the page will get pretty ugly.


## Requirements
  * php-imagick ([ImageMagick](http://php.net/manual/en/book.imagick.php))
