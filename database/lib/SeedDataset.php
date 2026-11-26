<?php

declare(strict_types=1);

namespace KlinikaID\Data;

use DateTimeImmutable;
use DateTimeZone;
use KlinikaID\Storage\DocumentStorage;

final class SeedDataset
{
    public const ANCHOR_DATE = '2026-09-01';
    public const TZ = '+07:00';
    public const PATIENTS = 40;
    public const VISITS = 150;
    public const EXPANDED_PATIENTS = 120;
    public const EXPANDED_VISITS = 1440;
    public const LAB_ACCOUNTS = ['pasien_a', 'pasien_b', 'dokter_a', 'dokter_b', 'frontdesk', 'admin'];

    public const UID_PASIEN_A = 1;
    public const UID_PASIEN_B = 2;
    public const UID_DOKTER_A = 3;
    public const UID_DOKTER_B = 4;
    public const UID_FRONTDESK = 5;
    public const UID_ADMIN = 6;

    public const OTHER_DOCTOR_IDS = [7, 8, 9, 10];

    private const EXPANDED_DOCTOR_NAMES = [
        129 => '[DUMMY] dr. Kevin Aditya',
        130 => '[DUMMY] dr. Laras Puspita, Sp.PD',
        131 => '[DUMMY] dr. Maya Paramita, Sp.DV',
        132 => '[DUMMY] dr. Naufal Ramadhan, Sp.N',
    ];
    private const DEPARTMENT_DOCTORS = [
        'Umum' => [4, 129], 'Penyakit Dalam' => [3, 130], 'Anak' => [7],
        'Kebidanan' => [10], 'Gigi' => [8], 'Mata' => [9],
        'Kulit dan Kelamin' => [131], 'Saraf' => [132],
    ];
    private const DEPARTMENT_COMPLAINTS = [
        'Umum' => ['batuk pilek', 'pemeriksaan kesehatan berkala', 'demam dua hari'],
        'Penyakit Dalam' => ['kontrol tekanan darah', 'kontrol gula darah', 'kontrol keluhan lambung'],
        'Anak' => ['kontrol tumbuh kembang', 'batuk pilek pada anak', 'pemeriksaan kesehatan anak'],
        'Kebidanan' => ['konsultasi kesehatan reproduksi', 'kontrol kebidanan', 'pemeriksaan berkala kebidanan'],
        'Gigi' => ['kontrol kesehatan gigi', 'gusi mudah berdarah', 'gigi sensitif'],
        'Mata' => ['mata terasa kering', 'kontrol ketajaman penglihatan', 'mata merah'],
        'Kulit dan Kelamin' => ['gatal pada kulit', 'kontrol ruam kulit', 'konsultasi jerawat'],
        'Saraf' => ['nyeri kepala berulang', 'kesemutan pada tangan', 'kontrol pusing berputar'],
    ];

    private const VISIT_OFFSETS = [-300, -270, -240, -210, -180, -150, -120, -90, -60, 85, 99, 113];

    public const VISIT_A = 1001;
    public const VISIT_B = 1002;
    public const DOCUMENT_A = 5001;
    public const DOCUMENT_B = 5002;

    private const FIRST = ['Ayu', 'Budi', 'Sari', 'Rizki', 'Dewi', 'Agus', 'Putri', 'Hendra', 'Wulan', 'Yoga', 'Intan', 'Bayu',
        'Ratna', 'Joko', 'Nur', 'Fitri', 'Arif', 'Lina', 'Teguh', 'Maya', 'Bambang', 'Siti', 'Andi', 'Rina', 'Dodi', 'Indah'];
    private const LAST = ['Lestari', 'Santoso', 'Wijaya', 'Saputra', 'Hidayat', 'Kurniawan', 'Rahayu', 'Siregar', 'Nasution',
        'Simanjuntak', 'Halim', 'Gunawan', 'Permata', 'Utami', 'Setiawan', 'Pangaribuan', 'Sihombing', 'Wibowo', 'Purnama', 'Hakim'];
    private const STREETS = ['Melati', 'Kenanga', 'Merdeka', 'Sudirman', 'Diponegoro', 'Gajah Mada', 'Pemuda', 'Cendana', 'Flamboyan', 'Anggrek'];
    private const CITIES = ['Kota Dummy Jakarta', 'Kota Dummy Bandung', 'Kota Dummy Surabaya', 'Kota Dummy Medan', 'Kota Dummy Semarang',
        'Kota Dummy Yogyakarta', 'Kota Dummy Makassar', 'Kota Dummy Denpasar'];
    private const DEPARTMENTS = ['Umum', 'Penyakit Dalam', 'Anak', 'Kebidanan', 'Gigi', 'Mata', 'Kulit dan Kelamin', 'Saraf'];
    private const COMPLAINTS = ['demam dua hari', 'batuk pilek', 'nyeri kepala ringan', 'kontrol tekanan darah', 'nyeri perut',
        'kontrol gula darah', 'gatal pada kulit', 'sakit gigi', 'mata merah', 'pemeriksaan rutin', 'nyeri sendi', 'pusing berputar'];
    private const ACTIONS = ['observasi dan edukasi', 'pemeriksaan fisik lengkap', 'rujukan laboratorium dummy', 'kontrol satu minggu',
        'terapi simptomatik dummy', 'edukasi pola makan', 'jadwal kontrol ulang'];
    private const DOC_TYPES = ['Resume Medis', 'Hasil Laboratorium', 'Surat Rujukan', 'Hasil Radiologi', 'Surat Keterangan Sehat'];
    private const DOCTOR_NAMES = [
        self::UID_DOKTER_A => '[DUMMY] dr. Citra Wulandari, Sp.PD',
        self::UID_DOKTER_B => '[DUMMY] dr. Dimas Pratama',
        7 => '[DUMMY] dr. Galih Prasetyo, Sp.A',
        8 => '[DUMMY] drg. Hana Kartika',
        9 => '[DUMMY] dr. Irfan Maulana, Sp.M',
        10 => '[DUMMY] dr. Juwita Anggraini, Sp.OG',
    ];

    private array $tables = [];

    private array $files = [];

    public function __construct(public readonly string $seedVersion)
    {
        $this->build();
    }

    public function tables(): array
    {
        return $this->tables;
    }

    public function files(): array
    {
        $out = [];
        foreach ($this->tables['documents'] as $d) {
            $out[$d['storage_key']] = $this->files[$d['id']];
        }
        return $out;
    }

    private static function anchor(): DateTimeImmutable
    {
        return new DateTimeImmutable(self::ANCHOR_DATE . ' 00:00:00', new DateTimeZone(self::TZ));
    }

    private static function day(int $offsetDays): DateTimeImmutable
    {
        return self::anchor()->modify(sprintf('%+d days', $offsetDays));
    }

    private static function ts(DateTimeImmutable $d, string $time): string
    {
        return $d->format('Y-m-d') . ' ' . $time . self::TZ;
    }

    private function build(): void
    {
        $expanded = $this->seedVersion === '2';
        $patientCount = $expanded ? self::EXPANDED_PATIENTS : self::PATIENTS;
        $visitCount = $expanded ? self::EXPANDED_VISITS : self::VISITS;
        $rng = new Rng('klinikaid:seed:v' . $this->seedVersion);

        $patients = [];
        for ($id = 1; $id <= $patientCount; $id++) {
            $name = match ($id) {
                1 => 'Ayu Lestari',
                2 => 'Budi Santoso',
                default => $rng->pick(self::FIRST) . ' ' . $rng->pick(self::LAST),
            };
            $patients[$id] = [
                'id' => $id,
                'medical_record_no' => sprintf('RM-DUMMY-%04d', $id),
                'full_name' => '[DUMMY] ' . $name,
                'birth_date' => self::day(-$rng->int(365 * 2, 365 * 80))->format('Y-m-d'),
                'phone' => sprintf('0800-0000-%04d', $id),
                'address' => sprintf('[DUMMY] Jl. %s No. %d, %s', $rng->pick(self::STREETS), $rng->int(1, 120), $rng->pick(self::CITIES)),
                'created_at' => self::ts(self::day(($expanded ? -730 : -400) + $id), '09:00:00'),
            ];
        }
        if ($expanded) {
            $patients[1]['birth_date'] = '1992-04-15';
            $patients[2]['birth_date'] = '1985-08-22';
        }
        $this->tables['patients'] = array_values($patients);

        $users = [];
        $addUser = static function (int $id, string $username, string $role, string $display, ?int $patientId) use (&$users, $expanded): void {
            $users[] = [
                'id' => $id, 'username' => $username, 'password_hash' => null, 'role' => $role,
                'display_name' => $display, 'patient_id' => $patientId, 'active' => true,
                'created_at' => self::ts(self::day(($expanded ? -700 : -380) + $id), '08:00:00'),
            ];
        };
        $addUser(self::UID_PASIEN_A, 'pasien_a', 'patient', $patients[1]['full_name'], 1);
        $addUser(self::UID_PASIEN_B, 'pasien_b', 'patient', $patients[2]['full_name'], 2);
        $addUser(self::UID_DOKTER_A, 'dokter_a', 'doctor', self::DOCTOR_NAMES[self::UID_DOKTER_A], null);
        $addUser(self::UID_DOKTER_B, 'dokter_b', 'doctor', self::DOCTOR_NAMES[self::UID_DOKTER_B], null);
        $addUser(self::UID_FRONTDESK, 'frontdesk', 'frontdesk', '[DUMMY] Eka Putri (Front Desk)', null);
        $addUser(self::UID_ADMIN, 'admin', 'admin', '[DUMMY] Fajar Nugroho (Admin)', null);
        foreach (self::OTHER_DOCTOR_IDS as $n => $id) {
            $addUser($id, sprintf('dokter_%02d', $n + 3), 'doctor', self::DOCTOR_NAMES[$id], null);
        }
        for ($pid = 3; $pid <= $patientCount; $pid++) {
            $addUser(8 + $pid, sprintf('pasien_%03d', $pid), 'patient', $patients[$pid]['full_name'], $pid);
        }
        if ($expanded) {
            foreach (self::EXPANDED_DOCTOR_NAMES as $id => $name) {
                $addUser($id, sprintf('dokter_%02d', $id - 122), 'doctor', $name, null);
            }
        }
        $this->tables['users'] = $users;

        $otherDoctors = [...self::OTHER_DOCTOR_IDS, ...($expanded ? array_keys(self::EXPANDED_DOCTOR_NAMES) : [])];
        $allDoctors = [self::UID_DOKTER_A, self::UID_DOKTER_B, ...$otherDoctors];
        $visits = $assignments = $documents = $schedules = [];
        $docId = 5001;
        for ($vid = 1001; $vid < 1001 + $visitCount; $vid++) {
            if ($vid === self::VISIT_A) {
                [$pid, $offset, $dept] = [1, -7, 'Penyakit Dalam'];
            } elseif ($vid === self::VISIT_B) {
                [$pid, $offset, $dept] = [2, -6, 'Umum'];
            } else {
                $pid = (($vid - 1003) % $patientCount) + 1;
                $offset = $expanded ? self::VISIT_OFFSETS[intdiv($vid - 1003, $patientCount)] + $rng->int(0, 6) : $rng->int(-330, 21);
                $departments = self::DEPARTMENTS;
                if ($expanded) {
                    $age = (new DateTimeImmutable($patients[$pid]['birth_date'], new DateTimeZone(self::TZ)))->diff(self::day($offset))->y;
                    $departments = $age < 18 ? ['Anak', 'Gigi', 'Mata', 'Umum'] : array_values(array_diff($departments, ['Anak']));
                }
                $dept = $rng->pick($departments);
            }
            $date = self::day($offset);
            $status = $offset < 0 ? ($vid % 17 === 0 ? 'dibatalkan' : 'selesai') : 'terjadwal';
            $complaint = $rng->pick($expanded ? self::DEPARTMENT_COMPLAINTS[$dept] : self::COMPLAINTS);
            $action = $rng->pick(self::ACTIONS);
            $note = sprintf('[DUMMY] Keluhan: %s. Tindakan: %s. Catatan fiktif untuk latihan; bukan data medis riil.', $complaint, $action);
            if ($expanded && $status !== 'selesai') {
                $note = $status === 'terjadwal'
                    ? sprintf('[DUMMY] Rencana kunjungan: %s. Pemeriksaan belum dilakukan. Data fiktif untuk latihan.', $complaint)
                    : '[DUMMY] Kunjungan dibatalkan. Tidak ada hasil pemeriksaan. Data fiktif untuk latihan.';
            }
            $visits[] = [
                'id' => $vid, 'patient_id' => $pid, 'visit_date' => $date->format('Y-m-d'), 'department' => $dept,
                'medical_note_dummy' => $note,
                'created_at' => self::ts($date->modify('-3 days'), '10:00:00'),
            ];

            $pool = in_array($pid, [1, 2], true) ? $otherDoctors : $allDoctors;
            $specialists = $expanded ? array_values(array_intersect($pool, self::DEPARTMENT_DOCTORS[$dept])) : $pool;
            $active = match ($vid) {
                self::VISIT_A => self::UID_DOKTER_A,
                self::VISIT_B => self::OTHER_DOCTOR_IDS[0],
                default => $rng->pick($specialists),
            };

            if ($vid % 10 === 0) {
                $prev = $pool[($rng->int(0, count($pool) - 2) + array_search($active, $pool, true) + 1) % count($pool)];
                $assignments[] = [
                    'id' => count($assignments) + 1, 'doctor_id' => $prev, 'visit_id' => $vid, 'active' => false,
                    'assigned_at' => self::ts($date->modify('-2 days'), '09:00:00'),
                    'revoked_at' => self::ts($date->modify('-1 days'), '12:00:00'),
                ];
            }
            $assignments[] = [
                'id' => count($assignments) + 1, 'doctor_id' => $active, 'visit_id' => $vid, 'active' => true,
                'assigned_at' => self::ts($date->modify('-1 days'), '13:00:00'), 'revoked_at' => null,
            ];

            $nDocs = ($vid !== self::VISIT_A && $vid !== self::VISIT_B && $vid % 4 === 0) ? 2 : 1;
            if ($expanded && !in_array($vid, [self::VISIT_A, self::VISIT_B], true)) {
                $nDocs = $status === 'selesai' ? 1 + ($vid % 3) : 1;
            }
            for ($k = 0; $k < $nDocs; $k++) {
                $type = $k === 0 ? ($vid === self::VISIT_A || $vid === self::VISIT_B ? 'Resume Medis' : $rng->pick(self::DOC_TYPES)) : 'Hasil Laboratorium';
                if ($expanded) {
                    $type = match ($status) {
                        'terjadwal' => 'Ringkasan Janji Temu',
                        'dibatalkan' => 'Bukti Pembatalan Jadwal',
                        default => ['Resume Medis', 'Hasil Laboratorium', 'Surat Rujukan'][$k],
                    };
                }
                $content = DummyPdf::render([
                    '[DUMMY] KlinikaID - dokumen fiktif untuk latihan keamanan',
                    'Jenis dokumen: ' . $type,
                    'ID dokumen: ' . $docId,
                    'ID kunjungan: ' . $vid . ' (' . $date->format('Y-m-d') . ', poli ' . $dept . ')',
                    'Pasien: ' . $patients[$pid]['full_name'] . ' / ' . $patients[$pid]['medical_record_no'],
                    'Isi: data fiktif. Bukan data pasien riil.',
                    'seed_version: ' . $this->seedVersion,
                ]);
                $documents[] = [
                    'id' => $docId, 'visit_id' => $vid,
                    'storage_key' => DocumentStorage::keyFromHash(hash('sha256', 'klinikaid:seed:v' . $this->seedVersion . ':document:' . $docId)),
                    'display_name' => sprintf('[DUMMY] %s Kunjungan %d.pdf', $type, $vid),
                    'mime_type' => 'application/pdf', 'size_bytes' => strlen($content), 'sha256' => hash('sha256', $content),
                    'created_at' => $expanded ? self::ts($status === 'selesai' ? $date : $date->modify('-1 day'), '17:00:00') : self::ts($date, '15:00:00'),
                ];
                $this->files[$docId] = $content;
                $docId++;
            }

            $schedules[] = [
                'id' => count($schedules) + 1, 'visit_id' => $vid,
                'operational_time' => self::ts($date, sprintf('%02d:%02d:00', $rng->int(8, 15), $rng->pick([0, 30]))),
                'room' => sprintf('Ruang Poli %s %d', $dept, $rng->int(1, 3)), 'status' => $status,
            ];
        }
        $this->tables['visits'] = $visits;
        $this->tables['doctor_assignments'] = $assignments;
        $this->tables['documents'] = $documents;
        $this->tables['schedules'] = $schedules;
    }
}
