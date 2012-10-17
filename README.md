# KoApp
## Base classes for the Kohana applications
### Usage:

```php
<?php

// Register the Error shutdown function in bootstrap.php before Kohana::init();

if (Kohana::$environment === Kohana::PRODUCTION)
{
    register_shutdown_function(array('Error', 'shutdown_handler'));
}
```