<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class MarketBasketMiningService
{
    /**
     * Extracts multi-item shopping baskets from completed orders.
     */
    public function extractTransactions(): array
    {
        $rows = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->where(function($q) {
                $q->where('orders.payment_status', 'paid')
                  ->orWhere('orders.status', 'completed');
            })
            ->where('orders.status', '!=', 'cancelled')
            ->select('orders.id as order_id', 'product_variants.product_id')
            ->get();

        $transactions = [];
        foreach ($rows as $row) {
            $transactions[$row->order_id][] = (int) $row->product_id;
        }

        // Filter baskets with at least 2 distinct products
        $validTransactions = [];
        foreach ($transactions as $orderId => $items) {
            $unique = array_unique($items);
            if (count($unique) >= 2) {
                $validTransactions[] = array_values($unique);
            }
        }

        return $validTransactions;
    }

    /**
     * Mines pairwise association rules ({A} => {B}) and computes Support, Confidence, Lift.
     */
    public function calculateRulesFromTransactions(array $transactions, float $minConfidence = 15.0, float $minLift = 1.05): array
    {
        $totalTransactions = count($transactions);
        if ($totalTransactions === 0) {
            return [];
        }

        $itemCounts = [];
        $pairCounts = [];

        foreach ($transactions as $items) {
            $n = count($items);
            for ($i = 0; $i < $n; $i++) {
                $itemA = $items[$i];
                $itemCounts[$itemA] = ($itemCounts[$itemA] ?? 0) + 1;

                for ($j = 0; $j < $n; $j++) {
                    if ($i === $j) continue;
                    $itemB = $items[$j];
                    $pairKey = "{$itemA}_{$itemB}";
                    $pairCounts[$pairKey] = ($pairCounts[$pairKey] ?? 0) + 1;
                }
            }
        }

        $rules = [];

        foreach ($pairCounts as $key => $coCount) {
            [$itemA, $itemB] = array_map('intval', explode('_', $key));

            $countA = $itemCounts[$itemA];
            $countB = $itemCounts[$itemB];

            $supportAB = round(($coCount / $totalTransactions) * 100, 1);
            $supportB = $countB / $totalTransactions;
            $confidence = round(($coCount / $countA) * 100, 1);
            $lift = $supportB > 0 ? round(($coCount / $countA) / $supportB, 2) : 0.0;

            if ($confidence >= $minConfidence && $lift >= $minLift) {
                $rules[] = [
                    'antecedent_id' => $itemA,
                    'consequent_id' => $itemB,
                    'co_count' => $coCount,
                    'support' => $supportAB,
                    'confidence' => $confidence,
                    'lift' => $lift,
                ];
            }
        }

        usort($rules, fn($a, $b) => $b['lift'] <=> $a['lift']);

        return $rules;
    }

    /**
     * Enriches association rules with product details, thumbnails and metadata.
     */
    public function mineAssociationRules(float $minConfidence = 10.0, float $minLift = 1.05): array
    {
        $transactions = $this->extractTransactions();
        $rawRules = $this->calculateRulesFromTransactions($transactions, $minConfidence, $minLift);

        $productIds = [];
        foreach ($rawRules as $r) {
            $productIds[] = $r['antecedent_id'];
            $productIds[] = $r['consequent_id'];
        }
        $productIds = array_unique($productIds);

        $products = Product::whereIn('id', $productIds)
            ->with(['variants', 'images', 'categories'])
            ->get()
            ->keyBy('id');

        $enrichedRules = [];

        foreach ($rawRules as $r) {
            $prodA = $products->get($r['antecedent_id']);
            $prodB = $products->get($r['consequent_id']);

            if (!$prodA || !$prodB) continue;

            $formatImg = function($p) {
                $img = $p->primary_image_url;
                if (!$img) return null;
                return (str_starts_with($img, 'http://') || str_starts_with($img, 'https://'))
                    ? $img
                    : asset('storage/' . ltrim($img, '/'));
            };

            $enrichedRules[] = [
                'antecedent' => [
                    'id' => $prodA->id,
                    'name' => $prodA->name,
                    'sku' => $prodA->variants->first()?->sku ?? 'SP-' . $prodA->id,
                    'category' => $prodA->categories->first()?->name ?? 'N/A',
                    'thumbnail' => $formatImg($prodA),
                ],
                'consequent' => [
                    'id' => $prodB->id,
                    'name' => $prodB->name,
                    'sku' => $prodB->variants->first()?->sku ?? 'SP-' . $prodB->id,
                    'category' => $prodB->categories->first()?->name ?? 'N/A',
                    'thumbnail' => $formatImg($prodB),
                ],
                'co_count' => $r['co_count'],
                'support' => $r['support'],
                'confidence' => $r['confidence'],
                'lift' => $r['lift'],
            ];
        }

        return [
            'total_transactions' => count($transactions),
            'rules' => $enrichedRules,
            'top_rules' => array_slice($enrichedRules, 0, 3),
        ];
    }

    /**
     * Finds the best product to bundle with a given product using mined association rules.
     */
    public function getFrequentlyBoughtTogether(int $productId, int $limit = 1): ?array
    {
        $transactions = $this->extractTransactions();
        if (empty($transactions)) {
            return null;
        }

        // Mine with inclusive threshold for cross-sell discovery (minConfidence 1%, minLift 0.0)
        $rawRules = $this->calculateRulesFromTransactions($transactions, 1.0, 0.0);
        if (empty($rawRules)) {
            return null;
        }

        // Gather all rules involving $productId
        $candidates = [];
        foreach ($rawRules as $rule) {
            if ($rule['antecedent_id'] === $productId) {
                $candidates[] = [
                    'paired_id' => $rule['consequent_id'],
                    'co_count' => $rule['co_count'],
                    'lift' => $rule['lift'],
                    'confidence' => $rule['confidence'],
                    'support' => $rule['support'],
                ];
            } elseif ($rule['consequent_id'] === $productId) {
                $candidates[] = [
                    'paired_id' => $rule['antecedent_id'],
                    'co_count' => $rule['co_count'],
                    'lift' => $rule['lift'],
                    'confidence' => $rule['confidence'],
                    'support' => $rule['support'],
                ];
            }
        }

        if (empty($candidates)) {
            return null;
        }

        // Sort by co_count DESC, then lift DESC
        usort($candidates, function($a, $b) {
            if ($b['co_count'] === $a['co_count']) {
                return $b['lift'] <=> $a['lift'];
            }
            return $b['co_count'] <=> $a['co_count'];
        });

        $matchedRule = $candidates[0];

        $mainProduct = Product::with(['variants.color', 'variants.size', 'images'])->find($productId);
        $pairedProduct = Product::with(['variants.color', 'variants.size', 'images'])->find($matchedRule['paired_id']);

        if (!$mainProduct || !$pairedProduct || $pairedProduct->status !== 'active') {
            return null;
        }

        $mainVariant = $mainProduct->variants->where('stock_quantity', '>', 0)->first() ?? $mainProduct->variants->first();
        $pairedVariant = $pairedProduct->variants->where('stock_quantity', '>', 0)->first() ?? $pairedProduct->variants->first();

        if (!$mainVariant || !$pairedVariant) {
            return null;
        }

        $mainPrice = (float) ($mainVariant->price ?: $mainProduct->base_price);
        $pairedPrice = (float) ($pairedVariant->price ?: $pairedProduct->base_price);
        $totalOriginal = $mainPrice + $pairedPrice;
        $discountPercent = 5; // 5% combo discount
        $bundlePrice = round($totalOriginal * (1 - $discountPercent / 100), -3);
        $savings = $totalOriginal - $bundlePrice;

        $formatImg = function($p) {
            $img = $p->primary_image_url;
            if (!$img) return null;
            return (str_starts_with($img, 'http://') || str_starts_with($img, 'https://'))
                ? $img
                : asset('storage/' . ltrim($img, '/'));
        };

        return [
            'main_product' => [
                'id' => $mainProduct->id,
                'name' => $mainProduct->name,
                'slug' => $mainProduct->slug,
                'price' => $mainPrice,
                'variant_id' => $mainVariant->id,
                'sku' => $mainVariant->sku,
                'thumbnail' => $formatImg($mainProduct),
            ],
            'paired_product' => [
                'id' => $pairedProduct->id,
                'name' => $pairedProduct->name,
                'slug' => $pairedProduct->slug,
                'price' => $pairedPrice,
                'variant_id' => $pairedVariant->id,
                'sku' => $pairedVariant->sku,
                'variants' => $pairedProduct->variants->where('stock_quantity', '>', 0)->values()->map(function($v) {
                    return [
                        'id' => $v->id,
                        'sku' => $v->sku,
                        'price' => (float) $v->price,
                        'color' => $v->color?->name,
                        'size' => $v->size?->name,
                        'stock' => $v->stock_quantity,
                    ];
                })->toArray(),
                'thumbnail' => $formatImg($pairedProduct),
            ],
            'lift' => $matchedRule['lift'],
            'confidence' => $matchedRule['confidence'],
            'total_original' => $totalOriginal,
            'bundle_price' => $bundlePrice,
            'savings' => $savings,
            'discount_percent' => $discountPercent,
        ];
    }
}
