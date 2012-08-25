![[pre]fixed](http://www.thedavidking.com/mods/prefixed/prefixed_logo_small.png "[pre]fixed")

© 2012 - David King ([imkingdavid](http://www.thedavidking.com))

This is an extension for phpBB 3.1 that will add a topic prefix functionality to your board.

##Installation
Until the full extensions architecture is completed in phpBB, installation is manual. Here are the steps to follow:

1. Drop the `imkingdavid/` directory and all contents into the `ext/` directory of your phpBB installation (create it if it does not already exist).
2. Open a new shell/cmd/terminal window and navigate to the /develop/ directory within the root phpBB installation directory.
3. To ensure that the files were properly uploaded, type `php extensions.php list`. You should see imkingdavid/prefixed in the list. If not, you may need to clear the cache and try again; othrwise double check that the files are in the proper location.
4. To enable the extension, type `php extensions.php enable imkingdavid/prefixed`
5. Now, clear the cache once again.
6. Run the queries located in schema.sql

There is currently no frontend interface for managing topic prefixes, so you will have to manually create them.

##Usage
Coming soon...

##Uninstallation
To disable the Extension, open the shell/cmd/terminal window and run `php extensions.php disable imkingdavid/prefixed`.
To delete the Extension, open the shell/cmd/terminal window and run `php extensions.php purge imkingdavid/prefixed` *and* delete the /ext/imkingdavid/prefixed/ directory and everything contained within.
