# KoApp
## Base classes for building web applications with Kohana
### Usage:

```php
<?php

// Register the Error shutdown function in bootstrap.php before Kohana::init();

if (Kohana::$environment === Kohana::PRODUCTION)
{
    register_shutdown_function(array('Error', 'shutdown_handler'));
}
```