# files_external_dropbox
Flysystem based Dropbox backend for Nextcloud

Requires Nextcloud 26.0 to 30.0

## Steps For Installation
### From App Store
- Install the app from [App Store](https://apps.nextcloud.com/apps/files_external_dropbox)
- Enable the app in the web interface
### From Source
- Clone the [source](https://github.com/DJaeger/files_external_dropbox.git) into the Nextcloud apps directory.
- Set the directory ownership according to your setup.
- Run `make` or `composer install` within the apps directory
- Enable the app by running `occ app:enable files_external_dropbox` from nextcloud main directory
### Configuring OAuth2
Connecting Dropbox with OAuth 2 is a little more work because you have to create a Dropbox app.
- Log into the [Dropbox Developers](http://www.dropbox.com/developers) page and click Create Your App
- Choose if you want to share a single folder or your complete Dropbox with your Nextcloud.
- Name your new app and click "Create app"
- In the tab "Settings" of your Dropbox app
  - If you have chosen to use a single folder, you may want to change the "App folder name"
  - Add new "Redirect URIs" under section "OAuth 2"
    - For adding global storages:
    
      ```https://mynextcloud.example.com/index.php/settings/admin/externalstorages```
    - For user storages:
    
      ```https://mynextcloud.example.com/index.php/settings/user/externalstorages```
    
    _Replace `https://mynextcloud.example.com/` with your valid Nextcloud installation path_
    
    _If you have set `htaccess.RewriteBase` in your config.php you may need to remove `/index.php`_
    
    _If your Nextcloud is in a subdirectory you need to add this subdirectory here too_
    
- In the tab "Permissions" of your Dropbox app
  - Check all checkboxes under section "Files and folders"
  - If there is a section "Team Scopes", you may want to uncheck all of them, if you do not want to login with a Dropbox work account

- Then go to nextcloud ```/settings/admin/externalstorages``` and add a new storage **Dropbox V2**
  > ⚠️ **Warning:** There is a bug introduced in Nextcloud v24.0.0 but only fixed in v27.0.0 and up.
  > 
  > This bug prevents any interactive authorisation for external storages like its required for OAuth2,
  > 
  > but any existing authorization remains in place as long as it is not revoked.
  > 
  > If you know what you are doing, you may fix it yourself, like it has been done in core version 27.0.0:
  > 
  > https://github.com/nextcloud/server/commit/da83464459d4fc2fd4965b805354933f8e6fbdf6
  > 
  > You need to reapply this patch after any update of the files_external app and the Nextcloud core until core version 27.0.0.
- Fill the details "Client ID" and "Client secret" from your Dropbox App page (search for "App key" and "App secret")
- Choose users the external storage should be "Available for"
- Click "Grant access" and then you will be redirected to the OAuth login
- After completing the authorization you will be redirect back to the "External storage" page of yout Nextcloud instance and should see a **green** mark along your storage configuration
### Global configuration
- To allow any user to configure his own Dropbox storage, you need to ensure that the checkbox "Allow users to mount external storage" is checked

## Dependencies
This app depends on the flysystem adapter for Dropbox which can be found here:<br>
[https://github.com/Hemant-Mann/flysystem-dropbox](https://github.com/Hemant-Mann/flysystem-dropbox)
