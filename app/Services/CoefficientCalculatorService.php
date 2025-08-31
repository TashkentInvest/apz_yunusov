<?php

namespace App\Services;

use App\Models\Objectt;
use App\Models\BaseCalculationAmount;

class CoefficientCalculatorService
{
    /**
     * Calculate the total amount using the formula:
     * Ti = Bh * ((Hb + Hyu) - (Ha + Ht + Hu)) * Kt * Ko * Kz * Kj
     */
    public function calculateTotalAmount(Objectt $object, BaseCalculationAmount $baseAmount): float
    {
        // Get base calculation amount (Bh)
        $bh = $baseAmount->amount;

        // Get object volumes
        $hb = $object->construction_volume; // General construction volume
        $hyu = $object->above_permit_volume; // Volume above permitted floors
        $ha = $object->parking_volume; // Parking volume
        $ht = $object->technical_rooms_volume; // Technical rooms volume
        $hu = $object->common_area_volume; // Common areas volume

        // Calculate effective volume: (Hb + Hyu) - (Ha + Ht + Hu)
        $effectiveVolume = ($hb + $hyu) - ($ha + $ht + $hu);

        // Get coefficients
        $kt = $this->getConstructionTypeCoefficient($object->construction_type_id);
        $ko = $this->getObjectTypeCoefficient($object->object_type_id);
        $kz = $this->getTerritorialZoneCoefficient($object->territorial_zone_id);
        $kj = $this->getLocationCoefficient($object->location_type);

        // Calculate total coefficient
        $totalCoefficient = $kt * $ko * $kz * $kj;

        // Apply coefficient limits (0.50 - 2.00)
        $totalCoefficient = max(0.50, min(2.00, $totalCoefficient));

        // Calculate final amount
        $totalAmount = $bh * $effectiveVolume * $totalCoefficient;

        return max(0, $totalAmount); // Ensure non-negative result
    }

    /**
     * Get construction type coefficient (Kt)
     */
    public function getConstructionTypeCoefficient(?int $constructionTypeId): float
    {
        if (!$constructionTypeId) {
            return 1.0;
        }

        $coefficients = [
            1 => 1.0, // New capital construction
            2 => 1.0, // Object reconstruction (coefficient applies to added construction volume)
            3 => 0.0, // Reconstruction not requiring project expertise
            4 => 0.0, // Reconstruction without changing construction volume
        ];

        return $coefficients[$constructionTypeId] ?? 1.0;
    }

    /**
     * Get object type coefficient (Ko)
     */
    public function getObjectTypeCoefficient(?int $objectTypeId): float
    {
        if (!$objectTypeId) {
            return 1.0;
        }

        $coefficients = [
            1 => 0.5, // Separate private social infrastructure and tourism objects
            2 => 0.5, // Investment projects with state share >50%
            3 => 0.5, // Production enterprise facilities
            4 => 0.5, // Warehouses with height ≤2m per floor (excluding administrative buildings)
            5 => 1.0, // Other objects not listed in rows 1-4
        ];

        return $coefficients[$objectTypeId] ?? 1.0;
    }

    /**
     * Get territorial zone coefficient (Kz)
     */
    public function getTerritorialZoneCoefficient(?int $territorialZoneId): float
    {
        if (!$territorialZoneId) {
            return 1.0;
        }

        // Load from database or use default values
        $territorialZone = \App\Models\TerritorialZone::find($territorialZoneId);
        return $territorialZone ? $territorialZone->coefficient : 1.0;
    }

    /**
     * Get location coefficient (Kj)
     */
    public function getLocationCoefficient(?string $locationType): float
    {
        if (!$locationType) {
            return 1.0;
        }

        // Define location coefficients based on proximity to metro stations
        $coefficients = [
            'metro_radius_200m' => 0.6, // Outside 200m radius from metro exit
            'other_locations' => 1.0,   // Other locations
        ];

        return $coefficients[$locationType] ?? 1.0;
    }

    /**
     * Build formula string for display
     */
    public function buildFormulaString(Objectt $object, BaseCalculationAmount $baseAmount): string
    {
        $bh = $baseAmount->amount;
        $hb = $object->construction_volume;
        $hyu = $object->above_permit_volume;
        $ha = $object->parking_volume;
        $ht = $object->technical_rooms_volume;
        $hu = $object->common_area_volume;

        $effectiveVolume = ($hb + $hyu) - ($ha + $ht + $hu);

        $kt = $this->getConstructionTypeCoefficient($object->construction_type_id);
        $ko = $this->getObjectTypeCoefficient($object->object_type_id);
        $kz = $this->getTerritorialZoneCoefficient($object->territorial_zone_id);
        $kj = $this->getLocationCoefficient($object->location_type);

        $totalCoefficient = max(0.50, min(2.00, $kt * $ko * $kz * $kj));

        return "Ti = {$bh} × (({$hb} + {$hyu}) - ({$ha} + {$ht} + {$hu})) × {$totalCoefficient} = " .
               number_format($bh * $effectiveVolume * $totalCoefficient, 2) . " сум";
    }

    /**
     * Calculate coefficient breakdown for display
     */
    public function getCoefficientBreakdown(Objectt $object): array
    {
        $kt = $this->getConstructionTypeCoefficient($object->construction_type_id);
        $ko = $this->getObjectTypeCoefficient($object->object_type_id);
        $kz = $this->getTerritorialZoneCoefficient($object->territorial_zone_id);
        $kj = $this->getLocationCoefficient($object->location_type);

        $totalBeforeLimit = $kt * $ko * $kz * $kj;
        $totalAfterLimit = max(0.50, min(2.00, $totalBeforeLimit));

        return [
            'kt' => $kt,
            'ko' => $ko,
            'kz' => $kz,
            'kj' => $kj,
            'total_before_limit' => $totalBeforeLimit,
            'total_after_limit' => $totalAfterLimit,
            'is_limited' => $totalBeforeLimit !== $totalAfterLimit
        ];
    }

    /**
     * Calculate payment schedule
     */
    public function calculatePaymentSchedule(float $totalAmount, int $initialPaymentPercent, int $periodsCount): array
    {
        $initialPayment = $totalAmount * ($initialPaymentPercent / 100);
        $remainingAmount = $totalAmount - $initialPayment;
        $quarterlyPayment = $periodsCount > 0 ? $remainingAmount / $periodsCount : 0;

        return [
            'total_amount' => $totalAmount,
            'initial_payment' => $initialPayment,
            'remaining_amount' => $remainingAmount,
            'quarterly_payment' => $quarterlyPayment,
            'periods_count' => $periodsCount
        ];
    }

    /**
     * Detect zone from KML file by coordinates
     */
    public function detectZoneFromKML(float $lat, float $lng): ?array
    {
        $kmlPath = public_path('zona.kml');

        if (!file_exists($kmlPath)) {
            return null;
        }

        try {
            $kmlContent = file_get_contents($kmlPath);
            $xml = simplexml_load_string($kmlContent);
            $xml->registerXPathNamespace('kml', 'http://www.opengis.net/kml/2.2');

            $point = [$lng, $lat];

            foreach ($xml->xpath('//kml:Placemark') as $placemark) {
                $extendedData = $placemark->ExtendedData;
                if ($extendedData) {
                    $schemaData = $extendedData->SchemaData;
                    $zoneName = null;

                    foreach ($schemaData->SimpleData as $simpleData) {
                        if ((string)$simpleData['name'] === 'SONI') {
                            $zoneName = (string)$simpleData;
                            break;
                        }
                    }

                    if ($zoneName) {
                        $coordinates = (string)$placemark->MultiGeometry->Polygon->outerBoundaryIs->LinearRing->coordinates;
                        $polygon = $this->parseKMLCoordinates($coordinates);

                        if ($this->pointInPolygon($point, $polygon)) {
                            return [
                                'name' => $zoneName,
                                'coefficient' => $this->getZoneCoefficient($zoneName)
                            ];
                        }
                    }
                }
            }

            return null;

        } catch (\Exception $e) {
            \Log::error('KML parsing error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Parse KML coordinates string
     */
    private function parseKMLCoordinates(string $coordinatesString): array
    {
        $coordinates = [];
        $points = explode(' ', trim($coordinatesString));

        foreach ($points as $point) {
            $coords = explode(',', trim($point));
            if (count($coords) >= 2) {
                $coordinates[] = [floatval($coords[0]), floatval($coords[1])];
            }
        }

        return $coordinates;
    }

    /**
     * Check if point is inside polygon using ray casting algorithm
     */
    private function pointInPolygon(array $point, array $polygon): bool
    {
        $x = $point[0];
        $y = $point[1];
        $inside = false;

        for ($i = 0, $j = count($polygon) - 1; $i < count($polygon); $j = $i++) {
            $xi = $polygon[$i][0];
            $yi = $polygon[$i][1];
            $xj = $polygon[$j][0];
            $yj = $polygon[$j][1];

            if ((($yi > $y) !== ($yj > $y)) && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi)) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    /**
     * Get zone coefficient by zone name
     */
    private function getZoneCoefficient(string $zoneName): float
    {
        $coefficients = [
            'ЗОНА-1' => 1.40,
            'ЗОНА-2' => 1.25,
            'ЗОНА-3' => 1.00,
            'ЗОНА-4' => 0.75,
            'ЗОНА-5' => 0.50
        ];

        return $coefficients[$zoneName] ?? 1.00;
    }

    /**
     * Validate object volumes
     */
    public function validateObjectVolumes(array $volumes): array
    {
        $errors = [];

        $hb = floatval($volumes['construction_volume'] ?? 0);
        $hyu = floatval($volumes['above_permit_volume'] ?? 0);
        $ha = floatval($volumes['parking_volume'] ?? 0);
        $ht = floatval($volumes['technical_rooms_volume'] ?? 0);
        $hu = floatval($volumes['common_area_volume'] ?? 0);

        if ($hb <= 0) {
            $errors[] = 'Общий объем строительства должен быть больше 0';
        }

        $effectiveVolume = ($hb + $hyu) - ($ha + $ht + $hu);
        if ($effectiveVolume <= 0) {
            $errors[] = 'Расчетный объем не может быть отрицательным или равным нулю';
        }

        if ($ha + $ht + $hu > $hb + $hyu) {
            $errors[] = 'Сумма исключаемых объемов не может превышать общий объем';
        }

        return $errors;
    }
}
