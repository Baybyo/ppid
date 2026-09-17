<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class SecureHeaders extends BaseConfig
{
    public bool $enabled = true;

    public string $referrerPolicy = 'no-referrer';

    public array $headers = [
        'X-Frame-Options'           => 'SAMEORIGIN',
        'X-Content-Type-Options'    => 'nosniff',
        'X-XSS-Protection'          => '1; mode=block',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
    ];
}
