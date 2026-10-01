<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GhnService
{
    protected $apiUrl;
    protected $token;
    protected $shopId;
    protected $fromDistrictId;
    protected $fromWardCode;

    public function __construct()
    {
        $rawUrl = config('services.ghn.api_url', 'https://dev-online-gateway.ghn.vn/shiip/public-api');
        $this->apiUrl = rtrim($rawUrl, '/') . '/';
        $this->token = config('services.ghn.token');
        $this->shopId = config('services.ghn.shop_id');
        $this->fromDistrictId = config('services.ghn.from_district_id');
        $this->fromWardCode = config('services.ghn.from_ward_code');
    }

    /**
     * Tạo URL chuẩn, tránh lỗi RFC 3986 của Guzzle khi base_uri có path component
     */
    protected function requestUrl(string $path): string
    {
        return $this->apiUrl . ltrim($path, '/');
    }

    /**
     * Khởi tạo HTTP Client với headers chuẩn của GHN
     */
    protected function client()
    {
        return Http::withHeaders([
            'token' => $this->token,
            'ShopId' => $this->shopId,
            'Content-Type' => 'application/json'
        ])->timeout(10);
    }

    /**
     * Lấy danh sách Tỉnh/Thành phố (hỗ trợ Cache 7 ngày)
     */
    public function getProvinces()
    {
        try {
            $cached = Cache::get('ghn_provinces_list');
            if (!empty($cached) && is_array($cached)) {
                return $cached;
            }

            $response = $this->client()->get($this->requestUrl('master-data/province'));
            if ($response->successful()) {
                $data = $response->json('data') ?? [];
                if (!empty($data) && is_array($data)) {
                    usort($data, fn($a, $b) => strcmp($a['ProvinceName'], $b['ProvinceName']));
                    Cache::put('ghn_provinces_list', $data, now()->addDays(7));
                    return $data;
                }
            }
            Log::error('GHN getProvinces Error: ' . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error('GHN Exception: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy danh sách Quận/Huyện theo Tỉnh (hỗ trợ Cache 3 ngày)
     */
    public function getDistricts($provinceId)
    {
        try {
            $cacheKey = 'ghn_districts_province_' . $provinceId;
            $cached = Cache::get($cacheKey);
            if (!empty($cached) && is_array($cached)) {
                return $cached;
            }

            $response = $this->client()->get($this->requestUrl('master-data/district'), [
                'province_id' => (int) $provinceId
            ]);
            if ($response->successful()) {
                $data = $response->json('data') ?? [];
                if (!empty($data)) {
                    Cache::put($cacheKey, $data, now()->addDays(3));
                }
                return $data;
            }
            Log::error('GHN getDistricts Error: ' . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error('GHN Exception: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy danh sách Phường/Xã theo Quận/Huyện (hỗ trợ Cache 3 ngày)
     */
    public function getWards($districtId)
    {
        try {
            $cacheKey = 'ghn_wards_district_' . $districtId;
            $cached = Cache::get($cacheKey);
            if (!empty($cached) && is_array($cached)) {
                return $cached;
            }

            $response = $this->client()->get($this->requestUrl('master-data/ward'), [
                'district_id' => (int) $districtId
            ]);
            if ($response->successful()) {
                $data = $response->json('data') ?? [];
                if (!empty($data)) {
                    Cache::put($cacheKey, $data, now()->addDays(3));
                }
                return $data;
            }
            Log::error('GHN getWards Error: ' . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error('GHN Exception: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Tính phí giao hàng dự kiến
     */
    public function calculateFee($toDistrictId, $toWardCode, $weight = 200, $length = 10, $width = 10, $height = 10)
    {
        try {
            $response = $this->client()->post($this->requestUrl('v2/shipping-order/fee'), [
                'from_district_id' => (int)$this->fromDistrictId,
                'from_ward_code' => (string)$this->fromWardCode,
                'service_id' => 53320, // 53320 là id dịch vụ Chuẩn
                'service_type_id' => 2, // 2: Chuyển phát chuẩn
                'to_district_id' => (int)$toDistrictId,
                'to_ward_code' => (string)$toWardCode,
                'height' => (int)$height,
                'length' => (int)$length,
                'weight' => (int)$weight,
                'width' => (int)$width,
                'insurance_value' => 0,
                'coupon' => null
            ]);

            if ($response->successful()) {
                return $response->json('data.total');
            }
            
            Log::error('GHN calculateFee Error: ' . $response->body());
            return 30000; // Giá mặc định nếu lỗi
        } catch (\Exception $e) {
            Log::error('GHN Exception: ' . $e->getMessage());
            return 30000;
        }
    }

    /**
     * Tạo đơn hàng sang GHN
     */
    public function createOrder($orderData)
    {
        try {
            $response = $this->client()->post($this->requestUrl('v2/shipping-order/create'), $orderData);
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'order_code' => $response->json('data.order_code'),
                    'fee' => $response->json('data.total_fee'),
                    'expected_delivery_time' => $response->json('data.expected_delivery_time'),
                ];
            }
            
            Log::error('GHN createOrder Error: ' . $response->body());
            return [
                'success' => false,
                'message' => $response->json('message') ?? 'Lỗi tạo đơn GHN'
            ];
        } catch (\Exception $e) {
            Log::error('GHN Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi kết nối GHN'
            ];
        }
    }

    /**
     * Lấy thông tin/trạng thái đơn hàng
     */
    public function getOrderInfo($orderCode)
    {
        try {
            $response = $this->client()->post($this->requestUrl('v2/shipping-order/detail'), [
                'order_code' => $orderCode
            ]);
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $response->json('data.status'),
                    'data' => $response->json('data')
                ];
            }
            
            return ['success' => false];
        } catch (\Exception $e) {
            Log::error('GHN Exception: ' . $e->getMessage());
            return ['success' => false];
        }
    }
}
