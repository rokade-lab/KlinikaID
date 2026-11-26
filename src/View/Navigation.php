<?php

declare(strict_types=1);

namespace KlinikaID\View;

final class Navigation
{
    public static function forRole(string $role): array
    {
        $items = [['label' => 'Dasbor', 'href' => '/dasbor']];
        $later = match ($role) {
            'patient' => ['Jadwal Saya', 'Kunjungan Saya', 'Dokumen', 'Profil'],
            'doctor' => ['Jadwal Praktik', 'Kunjungan Pasien', 'Profil'],
            'frontdesk' => ['Jadwal Klinik', 'Profil'],
            'admin' => ['Jadwal Klinik', 'Assignment Dokter', 'Profil'],
            default => [],
        };
        foreach ($later as $label) {
            $items[] = ['label' => $label, 'href' => null];
        }
        return $items;
    }
}
