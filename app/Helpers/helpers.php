<?php
// 2025.5.10 added
if (!function_exists('auto_asset')) {
    function auto_asset($path)
    {
        return request()->secure() ? secure_asset($path) : asset($path);
    }
}

// 2025.5.10 added
if (!function_exists('auto_url')) {
    function auto_url($path)
    {
        return request()->secure() ? secure_url($path) : url($path);
    }
}