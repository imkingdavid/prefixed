![[pre]fixed](http://www.thedavidking.com/mods/prefixed/prefixed_logo_small.png "[pre]fixed")

© 2012 - David King ([imkingdavid](http://www.thedavidking.com))

This is an extension for phpBB 3.1 that will add a topic prefix functionality to your board.

##Features
- Topics can have multiple prefixes
- Prefixes can be limited per user, per group, and per forum (whitelist)
- Prefixes can be styled using BBCode (in progress)
- Prefixes may contain "tokens", which represent data that can be different per instance. For instance, {USERNAME} is replaced with the username of the user that applied the prefix. New tokens may be added via extensions.

##Requirements
- >= phpBB 3.1-dev
- >= PHP 5.4

##Installation
You can install this on the latest copy of the develop branch (phpBB 3.1-dev) by following the steps below.

**Manual:**

1. If there is not yet an `./ext/imkingdavid/prefixed/` folder tree starting from your board root, create one.
2. Copy the entire contents of this repo into that folder you just created (You can leave out the *.md files, .gitignore, and the .git folder).
3. Navigate in the ACP to `Customise -> Manage extensions -> Extensions`.
4. Click Enable.

**Git:**

1. From the board root run the following git command:
`git clone https://github.com/imkingdavid/prefixed.git ext/imkingdavid/prefixed`
2. Go to `ACP -> Customise -> Manage extensions -> Extensions`
3. Click Enable next to the [pre]fixed extension.

##Usage
There is currently no frontend interface for managing topic prefixes, neither in the ACP or on the viewtopic page, so you will have to manually create them and apply them to topics by creating new rows on the appropriate tables added by this extension. (Ask me on IRC if you need help.)

##Uninstallation
In the ACP -> Customise -> Manage Extensions -> Extensions module, you can click one of the following:
- **Disable:** This keeps the Extension data and schema intact but prevents it from running. Prefixes will remain in the database but will not appear with topic titles, and the administration area will be unavailable. When you re-enable the extension, all functionality will become active again.
- **Delete data:** This destroys any data added by the extension, and reverts any schema changes it made. You can re-enable the extension, but all prefixes will be gone.
