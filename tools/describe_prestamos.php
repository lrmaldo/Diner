<?php

require __DIR__.'/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Boot Laravel's application to use DB facade
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cols = DB::select('DESCRIBE prestamos');
foreach ($cols as $c) {
    echo $c->Field.' '.$c->Type.PHP_EOL;
}
