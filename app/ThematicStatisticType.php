<?php

namespace App;

enum ThematicStatisticType: string
{
    case LaborForceParticipation = 'apak';
    case LaborForceParticipationByAge = 'apak_kelompok_umur';
    case LaborForceParticipationByEducation = 'apak_pendidikan';
    case LaborForceParticipationByFamilyRelationship = 'apak_status_hubungan_keluarga';
    case EducationBySingleAge = 'pendidikan_umur_tunggal';
    case BirthCertificateAgeZeroToOne = 'akta_kelahiran_usia_0_1';
    case BirthCertificateAgeZeroToFive = 'akta_kelahiran_usia_0_5';
    case BirthCertificateAgeZeroToEighteen = 'akta_kelahiran_usia_0_18';
    case BirthCertificateAllAges = 'akta_kelahiran_seluruh_umur';
    case DeathCertificate = 'akta_kematian';
    case ChildIdentityCard = 'kia';

    public static function fromSlug(string $slug): ?self
    {
        foreach (self::cases() as $type) {
            if ($type->slug() === $slug) {
                return $type;
            }
        }

        return null;
    }

    public function slug(): string
    {
        return str_replace('_', '-', $this->value);
    }

    public function title(): string
    {
        return match ($this) {
            self::LaborForceParticipation => 'APAK',
            self::LaborForceParticipationByAge => 'APAK menurut kelompok umur',
            self::LaborForceParticipationByEducation => 'APAK menurut pendidikan',
            self::LaborForceParticipationByFamilyRelationship => 'APAK menurut status hubungan keluarga',
            self::EducationBySingleAge => 'Pendidikan menurut umur tunggal',
            self::BirthCertificateAgeZeroToOne => 'Akta Kelahiran usia 0–1',
            self::BirthCertificateAgeZeroToFive => 'Akta Kelahiran usia 0–5',
            self::BirthCertificateAgeZeroToEighteen => 'Akta Kelahiran usia 0–18',
            self::BirthCertificateAllAges => 'Akta Kelahiran seluruh umur',
            self::DeathCertificate => 'Akta Kematian',
            self::ChildIdentityCard => 'Kartu Identitas Anak',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::LaborForceParticipation => 'Persentase partisipasi angkatan kerja laki-laki, perempuan, dan keseluruhan.',
            self::LaborForceParticipationByAge => 'APAK untuk kelompok umur 15–64 tahun.',
            self::LaborForceParticipationByEducation => 'APAK menurut jenjang pendidikan yang tercatat.',
            self::LaborForceParticipationByFamilyRelationship => 'APAK menurut status hubungan dalam keluarga.',
            self::EducationBySingleAge => 'Komposisi pendidikan penduduk untuk setiap umur tunggal.',
            self::BirthCertificateAgeZeroToOne => 'Kepemilikan Akta Kelahiran dinamis untuk penduduk usia 0–1 tahun.',
            self::BirthCertificateAgeZeroToFive => 'Kepemilikan Akta Kelahiran dinamis untuk penduduk usia 0–5 tahun.',
            self::BirthCertificateAgeZeroToEighteen => 'Kepemilikan Akta Kelahiran dinamis untuk penduduk usia 0–18 tahun.',
            self::BirthCertificateAllAges => 'Kepemilikan Akta Kelahiran dinamis untuk seluruh umur.',
            self::DeathCertificate => 'Jumlah Akta Kematian terbit, batal, dan penduduk meninggal yang tercatat.',
            self::ChildIdentityCard => 'Kepemilikan Kartu Identitas Anak dinamis.',
        };
    }

    public function group(): string
    {
        return match ($this) {
            self::LaborForceParticipation,
            self::LaborForceParticipationByAge,
            self::LaborForceParticipationByEducation,
            self::LaborForceParticipationByFamilyRelationship => 'APAK',
            self::EducationBySingleAge => 'Pendidikan',
            default => 'Kepemilikan dokumen',
        };
    }

    public function defaultFilename(): string
    {
        return match ($this) {
            self::LaborForceParticipation => 'AGR_APAK_202601_7501_GORONTALO.xlsx',
            self::LaborForceParticipationByAge => 'AGR_APAK_KELOMPOK_UMUR_202601_7501_GORONTALO.xlsx',
            self::LaborForceParticipationByEducation => 'AGR_APAK_PENDIDIKAN_202601_7501_GORONTALO.xlsx',
            self::LaborForceParticipationByFamilyRelationship => 'AGR_APAK_SHBKEL_202601_7501_GORONTALO.xlsx',
            self::EducationBySingleAge => 'AGR_PENDIDIKAN_UMUR_TUNGGAL_202601_7501_GORONTALO.xlsx',
            self::BirthCertificateAgeZeroToOne => 'Akta Kelahiran_Usia 0-1.xls',
            self::BirthCertificateAgeZeroToFive => 'Akta Kelahiran_Usia 0-5.xls',
            self::BirthCertificateAgeZeroToEighteen => 'Akta Kelahiran_Usia 0-18.xls',
            self::BirthCertificateAllAges => 'Akta Kelahiran_seluruh umur.xls',
            self::DeathCertificate => 'Akta Kematian_2026 sem 1.xls',
            self::ChildIdentityCard => 'KIA_2026 sem 1.xls',
        };
    }

    public function isHtmlSpreadsheet(): bool
    {
        return match ($this) {
            self::BirthCertificateAgeZeroToOne,
            self::BirthCertificateAgeZeroToFive,
            self::BirthCertificateAgeZeroToEighteen,
            self::BirthCertificateAllAges,
            self::DeathCertificate,
            self::ChildIdentityCard => true,
            default => false,
        };
    }

    public function isBirthCertificate(): bool
    {
        return match ($this) {
            self::BirthCertificateAgeZeroToOne,
            self::BirthCertificateAgeZeroToFive,
            self::BirthCertificateAgeZeroToEighteen,
            self::BirthCertificateAllAges => true,
            default => false,
        };
    }
}
