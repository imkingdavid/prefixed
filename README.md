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

**NOTE** Until https://github.com/phpbb/phpbb/pull/4371 is merged into phpBB, you will need to apply the changes it adds in order to use this extension. The change involves simply adding a template event inside styles/prosilver/posting_editor.html.

**Manual:**

1. If there is not yet an `./ext/imkingdavid/prefixed/` folder tree starting from your board root, create one.
2. Copy the entire contents of this repo into that folder you just created (You can leave out the *.md files, .gitignore, and the .git folder).
3. Navigate in the ACP to `Customise -> Manage extensions -> Extensions`.
4. Click Enable.

**Composer:**

1. Add the following requirement in the base phpBB composer.json file inside `require-dev`, if it doesn't already exist:
```
	"composer/installers": "~1.0",
```
2. Next, add this extension as a requirement inside `require-dev`:
```
	"imkingdavid/prefixed": "develop-dev",
```
3. Run `composer update` to install the new dependencies.
4. Double check that this extension has been installed to the `./ext/imkingdavid/prefixed/` directory of your forum

**Git:**

1. From the board root run the following git command:
`git clone https://github.com/imkingdavid/prefixed.git ext/imkingdavid/prefixed`
2. Go to `ACP -> Customise -> Manage extensions -> Extensions`
3. Click Enable next to the [pre]fixed extension.

##Usage
**To add a new prefix in the ACP**
After installing the extension, navigate in the ACP to Posting -> Topic Prefix Management -> Manage Prefixes and click on New Prefix. Fill in the form and hit submit.

**To apply a prefix to a new or existing topic**

1. Go to the desired forum and click New Topic, OR edit the first post of an existing topic.
2. Click on the section just before the Topic Title text box. A drop-down should appear with all prefixes available for your user account inside that forum.
3. Click and drag the prefix from that dropdown into the section you clicked in order to show the prefix dropdown, and drop it there.
4. You can drag the prefixes in that section into any order, and they will be saved in that order.
5. Submit the topic.

##Uninstallation
In the ACP -> Customise -> Manage Extensions -> Extensions module, you can click one of the following:
- **Disable:** This keeps the Extension data and schema intact but prevents it from running. Prefixes will remain in the database but will not appear with topic titles, and the administration area will be unavailable. When you re-enable the extension, all functionality will become active again.
- **Delete data:** This destroys any data added by the extension, and reverts any schema changes it made. You can re-enable the extension, but all prefixes will be gone. After you choose this option, if you wish to completely remove the extension from your board, delete the directory ./ext/imkingdavid/prefixed/ and all files and folders it contains.