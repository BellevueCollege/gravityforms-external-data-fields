# Gravity Forms External Data Fields Plugin


This plugin is used by authenticated forms that pre-populate field data. It connects to our DataAPI to provide student or employee information for the logged in user.

### Requirements

+   PHP 5.5 or PHP 7.x
+   WordPress 4.75, 4.98
+   Gravity Forms
+   BC Data API

### Configuration

After installation, create a `config.php` file in the main directory using the sample config file (`config-sample.php`) as an example.

- `$restrict_attr` -  The restriction attribute used in the Gravity Forms shortcode used to include the form in a page or post.
- `$restrict_fail_redirect_attr` - The name of the optional attribute for specifying a redirect URL if the user fails the restriction. Use in the Gravity Form shortcode that includes the form in a page or post.
- `$default_redirect_url` - The default redirect url (for those that fail the user restriction) in case another is not provided in the shortcode
- `$data_api_url` - The base URL for the Data API api endpoints (with trailing slash)
- `$data_api_stub_emp` - The stub path for the api end point to get student info (config sample is correct unless DataAPI changes).
- `$data_api_stub_stu` - The stub path for the api end point to get student info (config sample is correct unless DataAPI changes).
- `$data_api_stub_auth` - The stub path for the api login/authentication end point (config sample is correct unless DataAPI changes).
- `$data_api_clientid`, `$data_api_clientkey` - The client ID and key for the plugin that will authenticate it to the DataAPI.
- `$data_api_token_option_name` - Name to use for WP option when saving token. (Preset value should not need changed unless there is a naming conflict for some reason.)
- `$data_api_sslverify` - Specify whether SSL is verified in call to DataAPI. Should be `true` for production environment.

### Usage

This plugin consists of a few pieces. First, the Gravity Forms form you wish to prepopulate data in must require the user to be logged in. This can be done in the Form Settings. Gravity Forms also provides a space to specify a login message.  Here is where you can use a short code to add a link to the default WordPress login (which will in turn redirect to the current login method specified for the WordPress install, i.e. SAML).  This shortcode also allows you to specify a class attribute with value to style the generated link (e.g. style it as a button).

#### Shortcode examples

##### Unstyled login link
```
Log in to continue. [gfedf_redirect_to_login]
```

##### Styled login link

```
Log in to continue. [gfedf_redirect_to_login class="btn btn-primary"]
```

#### Additional functionality

[Visit the wiki](https://github.com/BellevueCollege/gravityforms-external-data-fields/wiki/Plugin-Functionality) for additional info on prepopulating fields or restricting the login to only employees.


### Updates

- May 2017 - The plugin was updated to change from getting data directly from ODS/connection to MSSQL to using the new BC DataAPI. The plugin was massively refactored with this update.