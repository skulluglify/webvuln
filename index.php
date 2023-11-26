<?php

include "skfw/autoload.php";
include "app/autoload.php";

function main(): void
{
    $stream = fopen("skfw/extras/http_status_codes.csv", "rb");

    $temp = "";
    while (!feof($stream)) {
        $data = fread($stream, 512);
        $temp .= $data;
    }

    $temp = unpack_csv_file($temp);
    var_dump($temp);
}

main();