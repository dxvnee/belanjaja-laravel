<?php

namespace App\Services;

class ShippingService
{
    public const SERVICE_HEMAT = 'hemat';
    public const SERVICE_REGULER = 'reguler';
    public const SERVICE_EXPRESS = 'express';

    // Regional keyword clusters for distance approximation in Indonesia
    protected static array $clusters = [
        'jawa' => [
            'jakarta', 'bogor', 'depok', 'tangerang', 'bekasi', 'bandung', 'cimahi',
            'semarang', 'surakarta', 'solo', 'yogyakarta', 'jogja', 'surabaya', 'malang',
            'cirebon', 'serang', 'cilegon', 'sukabumi', 'tasikmalaya', 'magelang',
            'pekalongan', 'tegal', 'salatiga', 'kediri', 'blitar', 'madiun', 'pasuruan',
            'probolinggo', 'batu', 'banten', 'jawa barat', 'jawa tengah', 'jawa timur', 'diy'
        ],
        'sumatera' => [
            'medan', 'palembang', 'pekanbaru', 'padang', 'bandar lampung', 'jambi',
            'bengkulu', 'batam', 'tanjung pinang', 'pangkal pinang', 'aceh', 'lampung',
            'riau', 'sumatera'
        ],
        'bali_nusra' => [
            'denpasar', 'bali', 'badung', 'mataram', 'lombok', 'kupang', 'bima',
            'nusa tenggara', 'ntb', 'ntt'
        ],
        'kalimantan' => [
            'pontianak', 'banjarmasin', 'samarinda', 'balikpapan', 'palangkaraya',
            'tarakan', 'singkawang', 'kalimantan'
        ],
        'sulawesi' => [
            'makassar', 'manado', 'palu', 'kendari', 'gorontalo', 'kotamobagu',
            'parepare', 'palopo', 'bau-bau', 'sulawesi'
        ],
        'maluku_papua' => [
            'ambon', 'jayapura', 'sorong', 'ternate', 'tidore', 'merauke', 'mimika',
            'manokwari', 'maluku', 'papua'
        ]
    ];

    /**
     * Determine the distance zone: 'local' (same city), 'regional' (same cluster), or 'inter_region' (different).
     */
    public static function getDistanceZone(?string $sellerLocation, ?string $buyerLocation): string
    {
        if (empty($sellerLocation) || empty($buyerLocation)) {
            return 'regional'; // default fallback
        }

        $sellerNorm = static::normalizeLocation($sellerLocation);
        $buyerNorm = static::normalizeLocation($buyerLocation);

        // 1. Same City check
        if ($sellerNorm === $buyerNorm || stripos($sellerLocation, $buyerLocation) !== false || stripos($buyerLocation, $sellerLocation) !== false) {
            return 'local';
        }

        // Substring / partial match on words (e.g. "Jakarta Selatan" vs "Jakarta Pusat")
        $sellerWords = array_filter(explode(' ', $sellerNorm), fn($w) => strlen($w) > 3);
        $buyerWords = array_filter(explode(' ', $buyerNorm), fn($w) => strlen($w) > 3);
        $sharedWords = array_intersect($sellerWords, $buyerWords);
        if (!empty($sharedWords)) {
            return 'local';
        }

        // 2. Same Region cluster check
        $sellerCluster = static::findCluster($sellerNorm);
        $buyerCluster = static::findCluster($buyerNorm);

        if ($sellerCluster !== null && $buyerCluster !== null && $sellerCluster === $buyerCluster) {
            return 'regional';
        }

        return 'inter_region';
    }

    /**
     * Get available shipping options with rates and ETD based on locations.
     */
    public static function getOptions(?string $sellerLocation, ?string $buyerLocation): array
    {
        $zone = static::getDistanceZone($sellerLocation, $buyerLocation);

        $rates = [
            'local' => [
                self::SERVICE_HEMAT => [
                    'cost' => 6000,
                    'etd' => '2-3 hari',
                    'name' => 'Hemat (Ekonomis)',
                    'description' => 'Ongkos kirim paling hemat dalam kota',
                ],
                self::SERVICE_REGULER => [
                    'cost' => 10000,
                    'etd' => '1-2 hari',
                    'name' => 'Reguler (Standar)',
                    'description' => 'Layanan standar cepat & aman',
                ],
                self::SERVICE_EXPRESS => [
                    'cost' => 18000,
                    'etd' => '1 hari (Same Day/Next Day)',
                    'name' => 'Kilat (Express)',
                    'description' => 'Prioritas sampai hari ini atau besok',
                ],
            ],
            'regional' => [
                self::SERVICE_HEMAT => [
                    'cost' => 14000,
                    'etd' => '3-4 hari',
                    'name' => 'Hemat (Ekonomis)',
                    'description' => 'Pengiriman ekonomis antar kota',
                ],
                self::SERVICE_REGULER => [
                    'cost' => 22000,
                    'etd' => '2-3 hari',
                    'name' => 'Reguler (Standar)',
                    'description' => 'Pengiriman reguler terpercaya',
                ],
                self::SERVICE_EXPRESS => [
                    'cost' => 35000,
                    'etd' => '1-2 hari',
                    'name' => 'Kilat (Express)',
                    'description' => 'Pengiriman kilat prioritas tinggi',
                ],
            ],
            'inter_region' => [
                self::SERVICE_HEMAT => [
                    'cost' => 25000,
                    'etd' => '4-6 hari',
                    'name' => 'Hemat (Ekonomis)',
                    'description' => 'Pengiriman kargo hemat jarak jauh',
                ],
                self::SERVICE_REGULER => [
                    'cost' => 38000,
                    'etd' => '3-4 hari',
                    'name' => 'Reguler (Standar)',
                    'description' => 'Pengiriman reguler lintas wilayah',
                ],
                self::SERVICE_EXPRESS => [
                    'cost' => 60000,
                    'etd' => '1-2 hari',
                    'name' => 'Kilat (Express)',
                    'description' => 'Pengiriman kilat pesawat udara',
                ],
            ],
        ];

        $zoneRates = $rates[$zone];
        $result = [];

        foreach ($zoneRates as $key => $info) {
            $result[] = [
                'service'     => $key,
                'name'        => $info['name'],
                'cost'        => $info['cost'],
                'etd'         => $info['etd'],
                'description' => $info['description'],
                'zone'        => $zone,
            ];
        }

        return $result;
    }

    /**
     * Calculate cost for a specific service.
     */
    public static function calculateCost(?string $sellerLocation, ?string $buyerLocation, string $service = self::SERVICE_REGULER): int
    {
        $options = static::getOptions($sellerLocation, $buyerLocation);
        foreach ($options as $opt) {
            if ($opt['service'] === $service) {
                return (int) $opt['cost'];
            }
        }
        return 10000;
    }

    protected static function normalizeLocation(string $loc): string
    {
        $loc = strtolower(trim($loc));
        $prefixes = ['kota adm. ', 'kota adm ', 'kota ', 'kabupaten ', 'kab. ', 'kab '];
        foreach ($prefixes as $p) {
            if (str_starts_with($loc, $p)) {
                $loc = substr($loc, strlen($p));
                break;
            }
        }
        return trim($loc);
    }

    protected static function findCluster(string $normalizedLoc): ?string
    {
        foreach (static::$clusters as $cluster => $keywords) {
            foreach ($keywords as $kw) {
                if (stripos($normalizedLoc, $kw) !== false) {
                    return $cluster;
                }
            }
        }
        return null;
    }
}
