<?php

return [
    'rate_limit' => env('API_RATE_LIMIT', 60),
    'rate_limit_decay' => env('API_RATE_LIMIT_DECAY', 1),
];
