<?php

namespace haydenk\SansThreatLevel;

use GuzzleHttp\Client;

class SansClient extends Client
{
    const BASE_URI = 'https://isc.sans.edu';

    public function __construct(array $config = [])
    {
        if (false === array_key_exists('base_uri', $config)) {
            $config['base_uri'] = self::BASE_URI;
        }

        parent::__construct($config);
    }
}
