# files_external_dropbox
Flysystem based dropbox backend for Nextcloud

Requires Nextcloud 22.0 to 25.0

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
- Connecting Dropbox is a little more work because you have to create a Dropbox app.
- Log into [Dropbox Developers](http://www.dropbox.com/developers) page and click Create Your App
- Then choose which folders to share, or to share everything in your Dropbox.
- Name Your App and then click Create App
- Under the section **OAuth2** Redirect URIs add a new URL
  - For adding global storages:<br>
    ```https://mynextcloud.example.com/index.php/settings/admin/externalstorages```
  - For user storages:<br>
    ```https://mynextcloud.example.com/index.php/settings/user/externalstorages```

  _Replace `https://mynextcloud.example.com/` with your valid Nextcloud installation path._<br>
  _If you have set `htaccess.RewriteBase` in your `config.php` you may need to remove `/index.php` and may need to add a subdirectory_
- Then go to nextcloud ```/settings/admin/externalstorages``` and add a new storage **Dropbox V2**
  > :warning: **Warning:** There is a bug introduced in Nextcloud v24.0.0 but only fixed in v27.0.0 and up.
  > 
  > This bug prevents any interactive authorisation for external storages like its required for OAuth2,<br>
  > but any existing authorization remains in place as long as it is not revoked.<br>
  > If you know what you are doing, you may fix it yourself, like it has been done in core version 27.0.0:<br>
  > https://github.com/nextcloud/server/commit/da83464459d4fc2fd4965b805354933f8e6fbdf6
  > You need to reapply this patch after any update of the files_external app and the Nextcloud core until core version 27.0.0.
- Fill the details Client Id, Client Secrets from your Dropbox App page (search for "App key" and "App secret")
- Click Grant Access and then you will be redirected to the OAuth login
- After completing the OAuth you will be redirect back to Storage Section and you should see **green** mark along your storage configuration
### Global configuration
- To allow any user to configure his own dropbox storage, you need to ensure that relevant checkbox is setting under "Allow users to mount external storage"

## Dependencies
This app depends on the flysystem adapter for dropbox which can be found here:<br>
[https://github.com/Hemant-Mann/flysystem-dropbox](https://github.com/Hemant-Mann/flysystem-dropbox)
