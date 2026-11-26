<?php

declare(strict_types=1);

namespace KlinikaID\Data;

use InvalidArgumentException;
use KlinikaID\Storage\DocumentStorage;

final class Storage
{
    public static function root(bool $create): string
    {
        $root = (string) getenv('STORAGE_ROOT');
        if ($root === '' || $root[0] !== '/' || rtrim($root, '/') === '' || str_contains($root, "\0")) {
            throw new DataException('STORAGE_ROOT_INVALID', 'STORAGE_ROOT harus path absolut selain "/"');
        }
        if ($create && !is_dir($root) && !mkdir($root, 0750, true) && !is_dir($root)) {
            throw new DataException('STORAGE_ROOT_INVALID', 'STORAGE_ROOT tidak dapat dibuat');
        }
        $real = realpath($root);
        if ($real === false || !is_dir($real) || $real === '/') {
            throw new DataException('STORAGE_ROOT_INVALID', 'STORAGE_ROOT bukan direktori yang valid');
        }
        return $real;
    }

    public static function fromEnv(bool $create): DocumentStorage
    {
        try {
            return new DocumentStorage(self::root($create));
        } catch (InvalidArgumentException) {
            throw new DataException('STORAGE_ROOT_INVALID', 'STORAGE_ROOT bukan direktori yang valid');
        }
    }
}
