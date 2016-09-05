<?php

namespace TomPHP\ConfigServiceProvider\FileReader;

final class FileLocator
{
    /**
     * @param array $patterns
     *
     * @return string[]
     */
    public function locate(array $patterns)
    {
        $files = [];

        foreach ($patterns as $pattern) {
            $files = array_merge($files, glob($pattern, GLOB_BRACE));
        }

        return $files;
    }
}