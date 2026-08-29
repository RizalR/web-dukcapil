<?php

namespace App;

enum PopulationStatisticCategory: string
{
    case Gender = 'Jenis Kelamin';
    case HouseholdHead = 'Kepala Keluarga';
    case AgeGroup = 'Kelompok Umur';
    case Religion = 'Agama';
    case Education = 'Pendidikan';
    case MaritalStatus = 'Status Perkawinan';
    case Disability = 'Disabilitas';
    case ElectronicIdentityCard = 'KTP-el';

    public static function fromSlug(string $slug): ?self
    {
        foreach (self::cases() as $category) {
            if ($category->slug() === $slug) {
                return $category;
            }
        }

        return null;
    }

    public function slug(): string
    {
        return match ($this) {
            self::Gender => 'jenis-kelamin',
            self::HouseholdHead => 'kepala-keluarga',
            self::AgeGroup => 'kelompok-umur',
            self::Religion => 'agama',
            self::Education => 'pendidikan',
            self::MaritalStatus => 'status-perkawinan',
            self::Disability => 'disabilitas',
            self::ElectronicIdentityCard => 'ktp-el',
        };
    }

    public function title(): string
    {
        return match ($this) {
            self::Gender => 'Penduduk menurut jenis kelamin',
            self::HouseholdHead => 'Kepala keluarga',
            self::AgeGroup => 'Penduduk menurut kelompok umur',
            self::Religion => 'Penduduk menurut agama',
            self::Education => 'Penduduk menurut pendidikan',
            self::MaritalStatus => 'Penduduk menurut status perkawinan',
            self::Disability => 'Penduduk penyandang disabilitas',
            self::ElectronicIdentityCard => 'Perekaman dan pencetakan KTP-el',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Gender => 'Komposisi penduduk laki-laki dan perempuan.',
            self::HouseholdHead => 'Jumlah kepala keluarga menurut jenis kelamin.',
            self::AgeGroup => 'Sebaran penduduk dalam kelompok umur lima tahunan.',
            self::Religion => 'Komposisi agama dan kepercayaan yang tercatat.',
            self::Education => 'Sebaran jenjang pendidikan terakhir yang tercatat.',
            self::MaritalStatus => 'Komposisi belum kawin, kawin, cerai hidup, dan cerai mati.',
            self::Disability => 'Jumlah penduduk menurut jenis disabilitas yang tercatat.',
            self::ElectronicIdentityCard => 'Cakupan wajib KTP, perekaman, dan pencetakan KTP-el.',
        };
    }

    public function headlineLabel(): ?string
    {
        return match ($this) {
            self::Gender => 'Jumlah Penduduk',
            self::HouseholdHead => 'Jumlah Kepala Keluarga',
            self::ElectronicIdentityCard => 'Wajib KTP',
            default => null,
        };
    }

    public function unit(): string
    {
        return $this === self::HouseholdHead ? 'KK' : 'jiwa';
    }

    /** @return list<string> */
    public function labels(): array
    {
        return match ($this) {
            self::Gender => [
                'Jumlah Penduduk Laki-laki',
                'Jumlah Penduduk Perempuan',
                'Jumlah Penduduk',
            ],
            self::HouseholdHead => [
                'Kepala Keluarga Laki-laki',
                'Kepala Keluarga Perempuan',
                'Jumlah Kepala Keluarga',
            ],
            self::AgeGroup => [
                '0–4 tahun',
                '5–9 tahun',
                '10–14 tahun',
                '15–19 tahun',
                '20–24 tahun',
                '25–29 tahun',
                '30–34 tahun',
                '35–39 tahun',
                '40–44 tahun',
                '45–49 tahun',
                '50–54 tahun',
                '55–59 tahun',
                '60–64 tahun',
                '65–69 tahun',
                '70–74 tahun',
                '75+ tahun',
            ],
            self::Religion => ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu', 'Kepercayaan'],
            self::Education => [
                'Tidak/belum sekolah',
                'Belum tamat SD/sederajat',
                'Tamat SD/sederajat',
                'SLTP/sederajat',
                'SLTA/sederajat',
                'Diploma I/II',
                'Akademi/Diploma III/Sarjana Muda',
                'Diploma IV/Strata I',
                'Strata II',
                'Strata III',
            ],
            self::MaritalStatus => ['Belum kawin', 'Kawin', 'Cerai hidup', 'Cerai mati'],
            self::Disability => [
                'Disabilitas fisik',
                'Disabilitas netra/buta',
                'Disabilitas rungu/wicara',
                'Disabilitas mental/jiwa',
                'Disabilitas fisik dan mental',
                'Disabilitas lainnya',
            ],
            self::ElectronicIdentityCard => [
                'Wajib KTP',
                'Sudah rekam',
                'Belum rekam',
                'Sudah memiliki KTP-el',
                'Belum memiliki KTP-el',
            ],
        };
    }
}
