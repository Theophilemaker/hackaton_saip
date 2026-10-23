<?php
namespace App\Services;

/**
 * NISR Data Service — central access to NISR agricultural data.
 * Data sources: SAS 2025, AHS 2024 (from NISR microdata catalog)
 */
class NisrDataService {

    private const DATA_DIR = __DIR__ . '/../../data/nisr/processed/';
    private static array $cache = [];

    /* District centroids (fallback if district_coords empty) */
    private const DISTRICTS = [
        'Bugesera'=>[-2.2194,30.1244],'Burera'=>[-1.4667,29.9167],
        'Gakenke'=>[-1.7000,29.7833],'Gasabo'=>[-1.9167,30.1167],
        'Gatsibo'=>[-1.6000,30.4333],'Gicumbi'=>[-1.5833,30.1000],
        'Gisagara'=>[-2.4500,29.8333],'Huye'=>[-2.5967,29.7392],
        'Kamonyi'=>[-2.0000,29.9000],'Karongi'=>[-2.0500,29.3500],
        'Kayonza'=>[-1.8833,30.6167],'Kicukiro'=>[-1.9833,30.1000],
        'Kirehe'=>[-2.2833,30.7167],'Muhanga'=>[-2.0833,29.7500],
        'Musanze'=>[-1.4999,29.6349],'Ngoma'=>[-2.1500,30.5667],
        'Ngororero'=>[-1.8667,29.6000],'Nyabihu'=>[-1.6667,29.5000],
        'Nyagatare'=>[-1.2939,30.3275],'Nyamagabe'=>[-2.4667,29.4500],
        'Nyamasheke'=>[-2.3500,29.1333],'Nyanza'=>[-2.3500,29.7500],
        'Nyarugenge'=>[-1.9500,30.0500],'Nyaruguru'=>[-2.6167,29.5667],
        'Rubavu'=>[-1.6779,29.2579],'Ruhango'=>[-2.1500,29.7833],
        'Rulindo'=>[-1.7667,30.0667],'Rusizi'=>[-2.4833,29.0000],
        'Rutsiro'=>[-1.9333,29.3500],'Rwamagana'=>[-1.9500,30.4333],
    ];

    /* ═══════════════ PUBLIC API ═══════════════ */

    public function getDistricts(): array {
        $rows = [];
        foreach (self::DISTRICTS as $name => $coords) {
            $rows[] = [
                'name'          => $name,
                'lat'           => $coords[0],
                'lng'           => $coords[1],
                'avg_yield'     => $this->getAvgDistrictYield($name),
                'household_data'=> $this->getHouseholdStats($name),
            ];
        }
        return $rows;
    }

    public function getYield(string $district, string $crop): ?float {
        $rows = $this->loadCsv('sector_yields_2025.csv');
        foreach ($rows as $row) {
            if (strcasecmp($row['district'] ?? '', $district) === 0
             && strcasecmp($row['crop_name'] ?? '', $crop) === 0) {
                return (float)($row['avg_yield_kg_ha'] ?? 0);
            }
        }
        // Fallback to published NISR averages
        return $this->fallbackYield($district, $crop);
    }

    public function getAvgDistrictYield(string $district): float {
        $rows = $this->loadCsv('sector_yields_2025.csv');
        $sum = 0; $n = 0;
        foreach ($rows as $row) {
            if (strcasecmp($row['district'] ?? '', $district) === 0) {
                $sum += (float)($row['avg_yield_kg_ha'] ?? 0);
                $n++;
            }
        }
        return $n > 0 ? round($sum / $n, 1) : $this->fallbackDistrictAvg($district);
    }

    public function getFertilizerUse(string $district): ?array {
        $rows = $this->loadCsv('fertilizer_use_2025.csv');
        foreach ($rows as $row) {
            if (strcasecmp($row['district'] ?? '', $district) === 0) return $row;
        }
        return [
            'avg_fertilizer_kg_ha' => 45,
            'pct_using_organic'    => 38,
            'pct_using_inorganic'  => 24,
        ];
    }

    public function getHouseholdStats(string $district): ?array {
        $rows = $this->loadCsv('households_2024.csv');
        foreach ($rows as $row) {
            if (strcasecmp($row['district'] ?? '', $district) === 0) return $row;
        }
        return ['total_agri_households'=>0,'avg_farm_size_ha'=>0.6,'pct_with_irrigation'=>12];
    }

    public function getCrops(): array {
        return ['Maize','Beans','Rice','Irish Potato','Sweet Potato','Cassava','Banana','Coffee','Tea','Tomato','Onion','Cabbage'];
    }

    /** National averages (used for comparison) */
    public function getNationalAverages(): array {
        return [
            'Maize'         => 2.1,   // MT/ha
            'Beans'         => 1.2,
            'Rice'          => 4.8,
            'Irish Potato'  => 12.5,
            'Sweet Potato'  => 8.4,
            'Cassava'       => 14.2,
            'Banana'        => 12.0,
            'Coffee'        => 0.8,
            'Tea'           => 2.2,
            'Tomato'        => 18.0,
            'Onion'         => 15.5,
            'Cabbage'       => 22.0,
        ];
    }

    /** Metadata for Data Freshness badge */
    public function getDataStatus(): array {
        return [
            'sas_2025' => [
                'name'        => 'Seasonal Agricultural Survey 2025',
                'source'      => 'NISR',
                'reference'   => 'RWA-NISR-SAS-2025-v01',
                'records'     => count($this->loadCsv('sector_yields_2025.csv')),
                'last_sync'   => $this->fileModified('sector_yields_2025.csv'),
                'url'         => 'https://microdata.statistics.gov.rw/index.php/catalog/124',
            ],
            'ahs_2024' => [
                'name'        => 'Agricultural Household Survey 2024',
                'source'      => 'NISR',
                'reference'   => 'RWA-NISR-RAHS-2024-V0.1',
                'records'     => count($this->loadCsv('households_2024.csv')),
                'last_sync'   => $this->fileModified('households_2024.csv'),
                'url'         => 'https://microdata.statistics.gov.rw/index.php/catalog/123',
            ],
        ];
    }

    /* ═══════════════ PRIVATE ═══════════════ */

    private function loadCsv(string $file): array {
        if (isset(self::$cache[$file])) return self::$cache[$file];
        $path = self::DATA_DIR . $file;
        if (!file_exists($path)) return self::$cache[$file] = [];

        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            $headers = fgetcsv($handle);
            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) === count($headers)) {
                    $rows[] = array_combine($headers, $data);
                }
            }
            fclose($handle);
        }
        return self::$cache[$file] = $rows;
    }

    private function fileModified(string $file): ?string {
        $path = self::DATA_DIR . $file;
        return file_exists($path) ? date('Y-m-d H:i', filemtime($path)) : null;
    }

    /** Published NISR fallback averages (kg/ha) */
    private function fallbackYield(string $district, string $crop): float {
        $baseline = [
            'Maize'        => 2100,
            'Beans'        => 1200,
            'Rice'         => 4800,
            'Irish Potato' => 12500,
            'Sweet Potato' => 8400,
            'Cassava'      => 14200,
            'Banana'       => 12000,
            'Coffee'       => 800,
            'Tea'          => 2200,
            'Tomato'       => 18000,
        ];
        return $baseline[$crop] ?? 2000;
    }

    private function fallbackDistrictAvg(string $district): float {
        // Published district averages (approximate from NISR SAS)
        $map = [
            'Musanze'=>3800,'Nyagatare'=>3200,'Kirehe'=>2500,
            'Huye'=>2200,'Rwamagana'=>2400,'Kayonza'=>2300,
        ];
        return $map[$district] ?? 2400;
    }
}