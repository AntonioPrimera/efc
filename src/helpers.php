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

function isRegCom($regCom): bool
{
    if (!$regCom || !is_string($regCom)) {
        return false;
    }
    
    // Old format: [JFC] followed by 2 digits, followed by "/"
    // followed by 1-7 digits, followed by "/"
    // followed by 4 digits (the year) or the date (dd.mm.yyyy)
    $oldPattern = '/^[JFC]\d{2}\/\d{1,7}\/(?:\d{4}|\d{2}\.\d{2}\.\d{4})$/';
    
    // New format (since 2024): [JFC] followed by 4 digits (year)
    // followed by 6 digits (sequential number)
    // followed by 2 digits (county code or 00)
    // followed by 1 digit (control digit)
    // Format: YAAAAXXXXXXJJC (e.g., J2004001180235)
    $newPattern = '/^[JFC]\d{4}\d{6}\d{2}\d{1}$/';
    
    return preg_match($oldPattern, $regCom) === 1 || preg_match($newPattern, $regCom) === 1;
}
