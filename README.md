![[pre]fixed](http://www.thedavidking.com/mods/prefixed/prefixed_logo_small.png "[pre]fixed")

© 2012 - David King ([imkingdavid](http://www.thedavidking.com))

This is an extension for phpBB 3.1 that will add a topic prefix functionality to your board.

##Requirements
- >= phpBB 3.1-dev
- >= PHP 5.4

##Installation
You can install this on the latest copy of the develop branch (phpBB 3.1-dev) by following the steps below:

1. If there is not yet an `./ext/imkingdavid/prefixed/` folder tree starting from your board root, create one.
2. Copy the entire contents of this repo into that folder you just created.
3. Navigate in the ACP to `System -> Manage board extensions`.
4. Click on the Details page to ensure that your PHP and phpBB versions are compatible with this extension.
5. Go back to the extensions list and click Enable
6. Until Migrations are merged into develop, either install this over the migrations branch, or run the queries in this file: https://github.com/imkingdavid/prefixed/blob/967c6b0772f086a91457223528af9010eda04a40/schema.sql

##Usage
There is currently no frontend interface for managing topic prefixes, so you will have to manually create them and apply them to topics by creating new rows on the tables added by this extension. (Ask me on IRC if you need help.)

##Uninstallation
Currently, disabling and purging are, for the most part, the same thing. Until the migrations system is done, both disable and purge simply keep the extension from functioning. Eventally, disable will keep it from functioning but retain settings and data, whereas purge will go to the next level by removing all data added by the extension.
To do either, click the appropriate link in the ACP Extension manager you used to install it.

##Command Line
You are also welcome to use the cmd/terminal commands available via the `develop/extensions.php` file to enable/disable/purge the extension. Syntax is like so:

`/path/to/phpbb/develop/>php extensions.php enable imkingdavid/prefixed`
`/path/to/phpbb/develop/>php extensions.php disable imkingdavid/prefixed`
`/path/to/phpbb/develop/>php extensions.php purge imkingdavid/prefixed`
To view all extensions you can use:
`/path/to/phpbb/develop/>php extensions.php list`
