Development - Frequently Asked Questions
========================================

This FAQ describes common questions and errors regarding development and provides possible solutions for them.

PhpStorm
--------

* ### Codeception code coverage fails with Docker compose
  * Cause:  
    The wrong coverage report path is set in the —coverage-xml command line argument.
  * Workaround:  
    Add the following code at the beginning of `/opt/.phpstorm_helpers/codeception.php` inside your docker container:
    ```php
    <?php

    // Workaround for bug https://youtrack.jetbrains.com/issue/WI-61914 ///
    if (isset($_SERVER['argv'])) {
        foreach ($_SERVER['argv'] as &$arg) {
            $arg = str_replace('$$', '$', $arg);
        }
    }
    // End workaround ///
    ```
  * More details:
    https://youtrack.jetbrains.com/issue/WI-60198
