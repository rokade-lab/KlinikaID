<?php

declare(strict_types=1);

namespace KlinikaID\Storage;

use InvalidArgumentException;
use RuntimeException;

final class DocumentStorage
{
    public const KEY_PATTERN = '/^docs\/([0-9a-f]{2})\/([0-9a-f]{64})\.pdf$/D';

    private readonly string $root;

    public function __construct(string $root)
    {
        $real = $root === '' ? false : realpath($root);
        if ($real === false || !is_dir($real) || $real === '/' || $real === DIRECTORY_SEPARATOR) {
            throw new InvalidArgumentException('Storage root tidak valid');
        }
        $this->root = $real;
    }

    public function root(): string
    {
        return $this->root;
    }

    public static function isValidKey(string $storageKey): bool
    {
        return preg_match(self::KEY_PATTERN, $storageKey, $m) === 1 && str_starts_with($m[2], $m[1]);
    }

    public static function keyFromHash(string $sha256Hex): string
    {
        if (preg_match('/^[0-9a-f]{64}$/D', $sha256Hex) !== 1) {
            throw new InvalidArgumentException('Hash storage key tidak valid');
        }
        return 'docs/' . substr($sha256Hex, 0, 2) . '/' . $sha256Hex . '.pdf';
    }

    public function resolve(string $storageKey): string
    {
        if (!self::isValidKey($storageKey)) {
            throw new InvalidArgumentException('Storage key tidak valid');
        }
        $path = $this->root . '/' . $storageKey;

        foreach ([dirname($path, 2), dirname($path), $path] as $p) {
            if (file_exists($p) || is_link($p)) {
                $real = realpath($p);
                if ($real === false || !str_starts_with($real . '/', $this->root . '/')) {
                    throw new InvalidArgumentException('Storage key keluar dari storage root');
                }
            }
        }
        return $path;
    }

    public function exists(string $storageKey): bool
    {
        return is_file($this->resolve($storageKey));
    }

    public function read(string $storageKey): string
    {
        $path = $this->resolve($storageKey);
        $content = is_file($path) ? file_get_contents($path) : false;
        if ($content === false) {
            throw new RuntimeException('Dokumen tidak ditemukan di storage');
        }
        return $content;
    }

    public function write(string $storageKey, string $content): void
    {
        $path = $this->resolve($storageKey);
        $dir = dirname($path);
        if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) {
            throw new RuntimeException('Direktori storage tidak dapat dibuat');
        }
        $this->resolve($storageKey);
        $tmp = $dir . '/.tmp-' . bin2hex(random_bytes(8));
        if (file_put_contents($tmp, $content, LOCK_EX) !== strlen($content)) {
            @unlink($tmp);
            throw new RuntimeException('Dokumen tidak dapat ditulis ke storage');
        }
        chmod($tmp, 0640);
        if (!rename($tmp, $path)) {
            @unlink($tmp);
            throw new RuntimeException('Dokumen tidak dapat ditulis ke storage');
        }
    }

    public function delete(string $storageKey): void
    {
        $path = $this->resolve($storageKey);
        if (is_file($path) && !unlink($path)) {
            throw new RuntimeException('Dokumen tidak dapat dihapus dari storage');
        }
    }
}
