<?php


/**
 * @throws Exception
 */
function time_unix(): int
{
    $tz = new DateTimeZone('UTC');
    $datetime = new DateTime(timezone: $tz);
    $timestamp = date_timestamp_get($datetime);  // ms
    return $timestamp * 1000;  // ns
}

function datetime_from_timestamp(int $timestamp): string
{
    $ms = $timestamp / 1000;  // ns -> ms
    $datetime = new DateTime('@'.$ms);
    return $datetime->format('Y-m-d H:i:s');
}

function datetime_to_timestamp(string $date): int
{
    $tz = new DateTimeZone('UTC');
    $datetime = date_create_from_format('Y-m-d H:i:s', $date, timezone: $tz);
    $timestamp = date_timestamp_get($datetime);  // ms
    return $timestamp * 1000;  // ns
}