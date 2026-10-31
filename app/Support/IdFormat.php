<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Format tanggal Indonesia + konversi batas hari/bulan Asia/Jakarta ke UTC.
 * Database menyimpan UTC (config/app.php), tampilan memakai Asia/Jakarta.
 */
final class IdFormat
{
    public const TZ = 'Asia/Jakarta';

    public static function date(?CarbonInterface $value): string
    {
        return $value ? $value->copy()->timezone(self::TZ)->locale('id')->translatedFormat('d M Y') : '—';
    }

    public static function dateTime(?CarbonInterface $value): string
    {
        return $value ? $value->copy()->timezone(self::TZ)->locale('id')->translatedFormat('d M Y, H:i') : '—';
    }

    public static function startOfDayUtc(string $date): Carbon
    {
        return Carbon::parse($date, self::TZ)->startOfDay()->utc();
    }

    public static function endOfDayUtc(string $date): Carbon
    {
        return Carbon::parse($date, self::TZ)->endOfDay()->utc();
    }

    /**
     * Tanggal kegiatan disimpan pukul 12:00 WIB (05:00 UTC) agar tanggalnya sama di WIB maupun UTC,
     * sehingga batas bulan perhitungan SAP (UTC) tidak bergeser.
     */
    public static function noonUtc(string $date): Carbon
    {
        return Carbon::parse($date, self::TZ)->setTime(12, 0)->utc();
    }

    /** @return array{0: Carbon, 1: Carbon} awal & akhir bulan berjalan (WIB) dalam UTC */
    public static function currentMonthRangeUtc(): array
    {
        $now = Carbon::now(self::TZ);

        return [$now->copy()->startOfMonth()->utc(), $now->copy()->endOfMonth()->utc()];
    }

    public static function today(): string
    {
        return Carbon::now(self::TZ)->toDateString();
    }

    /** Hindari karakter wildcard LIKE dari input pengguna. */
    public static function escapeLike(string $term): string
    {
        return addcslashes($term, '%_\\');
    }
}
