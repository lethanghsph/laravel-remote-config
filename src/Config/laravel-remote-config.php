<?php

return [
    "host"      => env("EXTERNAL_SERVICE_HOST"),
    "user"      => env("EXTERNAL_SERVICE_USER"),
    "password"  => env("EXTERNAL_SERVICE_PASSWORD"),
    "timeout"   => env("EXTERNAL_SERVICE_TIMEOUT", 5),
    "env"       => env("EXTERNAL_SERVICE_ENV"),
    "prefix"    => env("EXTERNAL_SERVICE_URI_PREFIX"),
];