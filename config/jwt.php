<?php
    return [
        'secret_key' => getenv('JWT_SECRET_KEY'),
        'issuer' => getenv('JWT_ISSUER'),
        'audience' => getenv('JWT_AUDIENCE'),
        'expiration_time' => getenv('JWT_EXPIRATION_TIME', 3600),
    ];
