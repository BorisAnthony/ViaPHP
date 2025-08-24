<?php

declare(strict_types=1);

if (!function_exists('via')) {
    function via(string $dotPath, ?string $additionalPath = null): string
    {
        return \Via\Via::get($dotPath, $additionalPath);
    }
}