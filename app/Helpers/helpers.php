<?php

use Illuminate\Support\Str;

if (! function_exists('flash_success')) {
    function flash_success($message)
    {
        session()->flash('success', [
            'message' => $message,
            'id' => Str::uuid()->toString(),
        ]);
    }
}

if (! function_exists('flash_error')) {
    function flash_error($message)
    {
        session()->flash('error', [
            'message' => $message,
            'id' => Str::uuid()->toString(),
        ]);
    }
}
