<?php
// autoload any script on directory! (modules)
function __MODULES_LOADER(string $cwd, string $model): void
{
    $files = scandir($cwd . DIRECTORY_SEPARATOR . $model);
    foreach ($files as $file)
    {
        if (preg_match('/^\w.+?\.php$/i', $file))
        {
            $src = $cwd . DIRECTORY_SEPARATOR . $model . DIRECTORY_SEPARATOR . $file;
            if (is_file($src)) require_once $src;
        }
    }
}

__MODULES_LOADER(__DIR__, 'middlewares');
