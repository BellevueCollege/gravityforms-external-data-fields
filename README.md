gravityforms-external-data-fields
=================================

Gravity Forms enhancements for Bellevue College

## Project setup

This plugin was developed with the following:

+   PHP 5.3
+   WordPress 3.8
+   Gravity Forms
+   MS SQL Server 2008 R2

### SQL Server drivers on Windows

The following instructions were taken from [Accessing SQL Server Databases from
PHP](http://social.technet.microsoft.com/wiki/contents/articles/1258.accessing-sql-server-databases-from-php.aspx)
and assume PHP ver 5.3, non-thread-safe. For more detailed information, please see the full article.

1.  Download the [SQLSRV](http://msdn.microsoft.com/en-us/sqlserver/ff657782.aspx) PHP Data Objects
    driver from Microsoft, unpacking the files into a temporary folder.
2.  Copy the following files into your PHP extensions folder (e.g. `C:\php\ext`)
    +   php_pdo_sqlsrv_53_nts.dll
    +   php_sqlsrv_53_nts.dll
3.  Add the following lines to your `php.ini` file:

```ini
extension=php_sqlsrv_53_nts.dll
extension=php_pdo_sqlsrv_53_nts.dll
```

### SQL Server drivers on Linux

(TODO)

### Plugin configuration

#### Database settings for Windows

```
sqlsrv:Server=SERVER_NAME;Database=DB_NAME;
```

#### Database settings for Linux

**NOTE:** The following setting has not yet been confirmed.

```
mssql:Server=SERVER_NAME;Database=DB_NAME;
```
