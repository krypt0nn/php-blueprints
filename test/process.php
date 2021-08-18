<?php

require '../blueprints.php';

use Blueprints\Blueprints;

Blueprints::processDir (__DIR__ .'/input', __DIR__ .'/output');
