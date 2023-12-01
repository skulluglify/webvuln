<?php

include "skfw/autoload.php";

use Skfw\Virtualize\VirtStdIn;

$stdin = new VirtStdIn();  // hook php://input
$stdin->open_hook(filename: "caches/php/input");  // overlay

echo $stdin;
