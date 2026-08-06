# Gravity Forms External Data Fields Plugin


This plugin is used by authenticated forms that pre-populate field data. It connects to our DataAPI to provide student or employee information for the logged in user.

### Requirements

+   PHP 5.5 or PHP 7.x
+   WordPress 4.75, 4.98
+   Gravity Forms
+   BC Data API

### Configuration

Add Data API credentials (and any optional overrides) as constants in `wp-config.php`, **above** the line `/* That's all, stop editing! */`.

#### Required

```php
define( 'GFEDF_DATA_API_URL', 'https://example.com/api/' ); // base URL with trailing slash
define( 'GFEDF_DATA_API_CLIENT_ID', 'your-client-id' );
define( 'GFEDF_DATA_API_CLIENT_KEY', 'your-client-key' );
```

#### Optional overrides

| Constant | Default | Description |
|---|---|---|
| `GFEDF_RESTRICT_ATTR` | `allow` | Restriction attribute used in the Gravity Forms shortcode |
| `GFEDF_RESTRICT_FAIL_REDIRECT_ATTR` | `failure_redirect_url` | Shortcode attribute for redirect URL when restriction fails (must be an on-site URL; uses `wp_safe_redirect`) |
| `GFEDF_DEFAULT_REDIRECT_URL` | `""` | Default redirect URL if none is provided in the shortcode (must be on-site) |
| `GFEDF_DATA_API_STUB_EMP` | `employee/%s` | Employee endpoint path stub |
| `GFEDF_DATA_API_STUB_STU` | `student/%s` | Student endpoint path stub |
| `GFEDF_DATA_API_STUB_AUTH` | `auth/login` | Auth/login endpoint path stub |
| `GFEDF_DATA_API_TOKEN_OPTION_NAME` | `gfedf_dataapi_token` | Transient key for the encrypted API token |
| `GFEDF_DATA_API_TOKEN_TTL` | `HOUR_IN_SECONDS` | TTL in seconds for the encrypted API token transient |
| `GFEDF_DATA_API_SSLVERIFY` | `true` | Whether SSL is verified on Data API requests |

SSL verification defaults to `true`. Set `GFEDF_DATA_API_SSLVERIFY` to `false` only for broken local/dev certificates:

```php
define( 'GFEDF_DATA_API_SSLVERIFY', false );
```

The Data API bearer token is stored encrypted (OpenSSL AES-256-CBC, key from `wp_salt( 'auth' )`) in a WordPress transient that expires after the configured TTL.
#### Migrating from `config.php`

If you previously used a plugin-root `config.php` copied from `config-sample.php`, move those values into the corresponding `GFEDF_*` constants in `wp-config.php`, then delete the plugin `config.php` file.

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

#### Autofill parameters

To pre-populate a Gravity Forms field, open the field in the Form Editor, go to the **Advanced** tab, enable **Allow field to be populated dynamically**, and enter one of the parameter names below.

| Parameter | Description | Student | Employee |
|---|---|---|---|
| `bc_sid` | Student/employee ID (EMPLID) | ✓ | ✓ |
| `bc_first_name` | First name | ✓ | ✓ |
| `bc_last_name` | Last name | ✓ | ✓ |
| `bc_email` | Email address | ✓ | ✓ |
| `bc_dayphone` | Daytime phone | ✓ | ✓ (uses employee phone) |
| `bc_evephone` | Evening phone | ✓ | — |

Student data is preferred when the logged-in user has a student record; otherwise employee data is used (except `bc_evephone`, which is student-only).

#### Additional functionality

[Visit the wiki](https://github.com/BellevueCollege/gravityforms-external-data-fields/wiki/Plugin-Functionality) for additional info on restricting the login to only employees.


### Updates

- May 2017 - The plugin was updated to change from getting data directly from ODS/connection to MSSQL to using the new BC DataAPI. The plugin was massively refactored with this update.