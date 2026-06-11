<?php
if (!function_exists('asset')) {
    function asset($path) {
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        $path = ltrim($path, '/');
        
        return $basePath . '/' . $path . '?v=1.0';
    }
}
?>