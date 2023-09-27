<?php

use JuanchoSL\SimpleCache\Repositories\RedisCache as MyCache;

include_once '..'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

$obj = new MyCache('host.docker.internal:6379');
$obj->set('key',-1.5,60);
echo $obj->get('key').PHP_EOL;
echo $obj->decrement('key',1.5);