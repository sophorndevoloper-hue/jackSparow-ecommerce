<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductCompatibility;
use Illuminate\Support\Collection;

class HardwareCompatibilityService
{
    /**
     * Check compatibility between two individual hardware components.
     *
     * @return array{
     *     is_compatible: bool,
     *     issues: list<string>,
     *     recommendations: list<string>,
     *     details: array<string, mixed>
     * }
     */
    public function checkPairCompatibility(Product $componentA, Product $componentB): array
    {
        $issues = [];
        $recommendations = [];

        // Check explicit database compatibility rule if present
        $explicitRule = ProductCompatibility::where(function ($q) use ($componentA, $componentB) {
            $q->where('product_id', $componentA->id)->where('compatible_product_id', $componentB->id);
        })->orWhere(function ($q) use ($componentA, $componentB) {
            $q->where('product_id', $componentB->id)->where('compatible_product_id', $componentA->id);
        })->first();

        if ($explicitRule) {
            if ($explicitRule->is_verified) {
                return [
                    'is_compatible' => true,
                    'issues' => [],
                    'recommendations' => $explicitRule->notes ? [$explicitRule->notes] : [],
                    'details' => ['rule_type' => $explicitRule->compatibility_type],
                ];
            }
        }

        // Socket Compatibility (CPU <-> Motherboard)
        if ($componentA->socket && $componentB->socket) {
            if (strcasecmp(trim($componentA->socket), trim($componentB->socket)) !== 0) {
                $issues[] = "Socket Incompatibility: '{$componentA->name}' requires socket {$componentA->socket}, but '{$componentB->name}' is socket {$componentB->socket}.";
            }
        }

        // RAM Generation Check (via specs or description)
        $ramGenA = $this->extractSpec($componentA, 'ram_generation') ?: $this->extractSpec($componentA, 'memory_type');
        $ramGenB = $this->extractSpec($componentB, 'ram_generation') ?: $this->extractSpec($componentB, 'memory_type');
        if ($ramGenA && $ramGenB && strcasecmp(trim($ramGenA), trim($ramGenB)) !== 0) {
            $issues[] = "Memory Standard Mismatch: '{$componentA->name}' uses {$ramGenA} while '{$componentB->name}' requires {$ramGenB}.";
        }

        // Form Factor check (Motherboard <-> Case)
        if ($componentA->form_factor && $componentB->form_factor) {
            $ffA = strtoupper(trim($componentA->form_factor));
            $ffB = strtoupper(trim($componentB->form_factor));
            if ($ffA !== $ffB && ! $this->isFormFactorBackwardCompatible($ffA, $ffB)) {
                $recommendations[] = "Notice: Form factor difference between {$ffA} and {$ffB}. Please verify chassis mounting support.";
            }
        }

        return [
            'is_compatible' => count($issues) === 0,
            'issues' => $issues,
            'recommendations' => $recommendations,
            'details' => [
                'socket_a' => $componentA->socket,
                'socket_b' => $componentB->socket,
                'tdp_a' => $componentA->tdp_watts,
                'tdp_b' => $componentB->tdp_watts,
            ],
        ];
    }

    /**
     * Validate an entire Bill of Materials (BOM) / system build / cart for hardware compatibility and power requirements.
     *
     * @param  Collection<int, Product>|array<int, Product>  $products
     * @return array{
     *     is_valid: bool,
     *     total_tdp_watts: int,
     *     recommended_psu_watts: int,
     *     psu_wattage_supplied: int|null,
     *     is_power_sufficient: bool,
     *     issues: list<string>,
     *     warnings: list<string>,
     *     socket: string|null,
     *     chipset: string|null
     * }
     */
    public function validateSystemBuild(Collection|array $products): array
    {
        $collection = $products instanceof Collection ? $products : collect($products);

        $issues = [];
        $warnings = [];
        $totalTdp = 0;
        $psuWattsSupplied = null;
        $primarySocket = null;
        $primaryRamGen = null;

        foreach ($collection as $product) {
            // Tally TDP
            if ($product->tdp_watts) {
                $totalTdp += (int) $product->tdp_watts;
            }

            // Detect PSU
            if ($product->category?->slug === 'power-supplies' || str_contains(strtolower($product->name), 'power supply') || str_contains(strtolower($product->name), 'psu')) {
                $psuWattsSupplied = (int) ($product->power_requirement_watts ?: $this->extractSpec($product, 'wattage') ?: 0);
            }

            // Track Socket Consistency
            if ($product->socket) {
                if ($primarySocket === null) {
                    $primarySocket = $product->socket;
                } elseif (strcasecmp($primarySocket, $product->socket) !== 0) {
                    $issues[] = "Conflicting Sockets in Build: Found {$primarySocket} on previous component, but '{$product->name}' uses {$product->socket}.";
                }
            }

            // Track RAM Generation Consistency
            $ramGen = $this->extractSpec($product, 'ram_generation') ?: $this->extractSpec($product, 'memory_type');
            if ($ramGen) {
                if ($primaryRamGen === null) {
                    $primaryRamGen = $ramGen;
                } elseif (strcasecmp($primaryRamGen, $ramGen) !== 0) {
                    $issues[] = "Conflicting RAM Types in Build: Found {$primaryRamGen}, but '{$product->name}' is {$ramGen}.";
                }
            }
        }

        // Add 20% safety overhead buffer + 50W base for fans/storage/RGB
        $recommendedPsuWatts = (int) ceil(($totalTdp * 1.25) + 50);

        $isPowerSufficient = true;
        if ($psuWattsSupplied !== null && $psuWattsSupplied > 0) {
            if ($psuWattsSupplied < $totalTdp) {
                $issues[] = "Critical Power Deficit: Selected PSU ({$psuWattsSupplied}W) cannot supply total component TDP ({$totalTdp}W).";
                $isPowerSufficient = false;
            } elseif ($psuWattsSupplied < $recommendedPsuWatts) {
                $warnings[] = "Warning: Selected PSU ({$psuWattsSupplied}W) is below the recommended capacity with headroom ({$recommendedPsuWatts}W).";
            }
        }

        return [
            'is_valid' => count($issues) === 0,
            'total_tdp_watts' => $totalTdp,
            'recommended_psu_watts' => $recommendedPsuWatts,
            'psu_wattage_supplied' => $psuWattsSupplied,
            'is_power_sufficient' => $isPowerSufficient,
            'issues' => $issues,
            'warnings' => $warnings,
            'socket' => $primarySocket,
            'chipset' => $collection->firstWhere('chipset', '!==', null)?->chipset,
        ];
    }

    /**
     * Extract a spec value from jsonb specs or specifications array.
     */
    protected function extractSpec(Product $product, string $key): ?string
    {
        if (! empty($product->specs[$key])) {
            return (string) $product->specs[$key];
        }

        if (! empty($product->specifications[$key])) {
            return (string) $product->specifications[$key];
        }

        return null;
    }

    /**
     * Determine if motherboards/cases are backward compatible across form factor standard.
     */
    protected function isFormFactorBackwardCompatible(string $caseFF, string $moboFF): bool
    {
        $hierarchy = ['E-ATX' => 4, 'ATX' => 3, 'MICRO-ATX' => 2, 'MINI-ITX' => 1];

        $caseLevel = $hierarchy[$caseFF] ?? 0;
        $moboLevel = $hierarchy[$moboFF] ?? 0;

        return $caseLevel >= $moboLevel && $caseLevel > 0;
    }
}
