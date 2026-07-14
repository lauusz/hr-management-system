<?php

it('disables browser autocomplete on visible search inputs', function () {
    $viewsPath = getcwd().DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'views';
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsPath));
    $missing = [];

    foreach ($files as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        preg_match_all('/<input\b[^>]*>/i', file_get_contents($file->getPathname()), $matches);

        foreach ($matches[0] as $input) {
            $isHidden = preg_match('/type\s*=\s*["\']hidden["\']/i', $input);
            $isSearch = preg_match('/type\s*=\s*["\']search["\']/i', $input)
                || preg_match('/name\s*=\s*["\'](?:q|search|keyword)["\']/i', $input)
                || preg_match('/placeholder\s*=\s*["\'][^"\']*(?:Cari|Ketik nama)/i', $input);

            if (! $isHidden && $isSearch && ! preg_match('/autocomplete\s*=\s*["\']off["\']/i', $input)) {
                $missing[] = str_replace($viewsPath.DIRECTORY_SEPARATOR, '', $file->getPathname()).' => '.$input;
            }
        }
    }

    expect($missing)->toBe([]);
});
