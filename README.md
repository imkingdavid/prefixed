![[pre]fixed](http://www.thedavidking.com/mods/prefixed/prefixed_logo_small.png "[pre]fixed")

© 2012 - David King ([imkingdavid](http://www.thedavidking.com))

This is an extension for phpBB 3.1 that will add a topic prefix functionality to your board.

##Requirements
- >= phpBB 3.1-dev
- >= PHP 5.4

##Installation
You can install this on the latest copy of the develop branch (phpBB 3.1-dev) by following the steps below:

1. If there is not yet an `./ext/imkingdavid/prefixed/` folder tree starting from your board root, create one.
2. Copy the entire contents of this repo into that folder you just created (You can leave out the *.md files, .gitignore, and the .git folder).
3. Navigate in the ACP to `Customise -> Manage extensions -> Extensions`.
4. Click on the Details page to ensure that your PHP and phpBB versions are compatible with this extension (or see "Requirements" above).
5. Go back to the extensions list and click Enable.

##Usage
There is currently no frontend interface for managing topic prefixes, neither in the ACP or on the viewtopic page, so you will have to manually create them and apply them to topics by creating new rows on the appropriate tables added by this extension. (Ask me on IRC if you need help.)

##Uninstallation
In the ACP -> Customise -> Manage Extensions -> Extensions module, you can click one of the following:
- Disable: This keeps the Extension data intact but prevents it from running. Prefixes will remain in the database but will not appear with topic titles. You can re-enable the extension and the same prefixes will still be applied to the same topics as before.
- Purge: This uninstalls the extension and destroys any data added by it. You can re-enable the extension, but all prefixes will be gone.
