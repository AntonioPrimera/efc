<?php
use Spatie\LaravelData\Data;

function data(mixed $data, string $dataClass): null|Data
{
    return is_null($data) ? null : call_user_func([$dataClass, 'from'], $data);
}
