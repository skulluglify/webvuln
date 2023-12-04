<?php


/**
 * @throws Exception
 */
function time_unix(): int
{
    $tz = new DateTimeZone('UTC');
    $date = new DateTime(timezone: $tz);
    $timestamp = date_timestamp_get($date);  // ms
    return $timestamp * 1000;  // ns
}