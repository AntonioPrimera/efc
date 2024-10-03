<?php
use Carbon\Carbon;
use Spatie\LaravelData\Data;

function data(mixed $data, string $dataClass): null|Data
{
    return is_null($data) ? null : call_user_func([$dataClass, 'from'], $data);
}

function carbonDateString(Carbon|null $date, string $format = 'Y-m-d'): string|null
{
    return $date?->format($format);
}

function isRegCom(string $regCom): bool
{
    // [F,F or C] followed by 2 digits, followed by "/"
    // followed by 1-7 digits, followed by "/"
    // followed by 4 digits (the year) or the date (dd.mm.yyyy)
    $pattern = '/^[JFC]\d{2}\/\d{1,7}\/(?:\d{4}|\d{2}\.\d{2}\.\d{4})$/';
    return preg_match($pattern, $regCom) === 1;
}
