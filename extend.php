<?php

use Flarum\Extend;
use Maicol07\JwtAuth\JwtAuthController;

return [
    (new Extend\Frontend('admin'))
            ->js(__DIR__.'/js/dist/admin.js'),
    (new Extend\Routes('api'))
        ->get('/jwt-auth', 'coldsnake.jwt-auth', JwtAuthController::class)
];
