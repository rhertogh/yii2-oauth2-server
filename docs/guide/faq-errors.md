F.A.Q - Error Messages
======================

This F.A.Q. describes common error messages and provides possible solutions for them.

### Error: Unable to read key from file -----BEGIN RSA PRIVATE KEY----- ...  
This error could appear if the private key is set as string containing a private key with 
a 'passphrase' but the `$privateKeyPassphrase` is incorrect or not set.

### Nginx returns 403 Forbidden for /.well-known/openid-configuration
Nginx might be configured to protect hidden files and folder from being read from the web.  
Check your nginx configuration file for:  
```nginxconf
location ~* /\. {
    deny all;
}
```
And change it to:
```nginxconf
location ~ /\.(?!well-known).* {
    deny all;
}
```
This will deny access to all files and folders starting with `.` except `.well-known`.
