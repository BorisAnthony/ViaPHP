<?php

declare(strict_types=1);

if (!function_exists('via')) {
    function via(string $dotPath, ?string $additionalPath = null): string
    {
        return \Via\Via::get($dotPath, $additionalPath);
    }
}
if (!function_exists('via_local')) {
    function via_local(): ?string
    {
        return \Via\Via::getLocal();
    }
}
if (!function_exists('via_host')) {
    function via_host(): ?string
    {
        return \Via\Via::getHost();
    }
}
