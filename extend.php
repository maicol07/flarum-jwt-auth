<?php

use Flarum\Extend;
use Maicol07\JwtAuth\JwtAuthController;

return [
    (new Extend\Frontend('admin'))
            ->js(__DIR__.'/js/dist/admin.js'),
    (new Extend\Routes('api'))
        ->get('/jwt-auth', 'maicol07.jwt-auth', JwtAuthController::class)
];
