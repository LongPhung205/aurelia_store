<?php

namespace App\Services;

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
        // Sử dụng cấu hình từ .env
        $this->apiUrl = env('GHN_API_URL', 'https://dev-online-gateway.ghn.vn/shiip/public-api');
        $this->token = env('GHN_TOKEN');
        $this->shopId = env('GHN_SHOP_ID');
        $this->fromDistrictId = env('GHN_FROM_DISTRICT_ID');
        $this->fromWardCode = env('GHN_FROM_WARD_CODE');
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
        ])->baseUrl($this->apiUrl);
    }

    /**
     * Lấy danh sách Tỉnh/Thành phố
     */
    public function getProvinces()
    {
        try {
            $response = $this->client()->get('/master-data/province');
            if ($response->successful()) {
                return $response->json('data');
            }
            Log::error('GHN getProvinces Error: ' . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error('GHN Exception: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy danh sách Quận/Huyện theo Tỉnh
     */
    public function getDistricts($provinceId)
    {
        try {
            $response = $this->client()->get('/master-data/district', [
                'province_id' => $provinceId
            ]);
            if ($response->successful()) {
                return $response->json('data');
            }
            Log::error('GHN getDistricts Error: ' . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error('GHN Exception: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy danh sách Phường/Xã theo Quận/Huyện
     */
    public function getWards($districtId)
    {
        try {
            $response = $this->client()->get('/master-data/ward', [
                'district_id' => $districtId
            ]);
            if ($response->successful()) {
                return $response->json('data');
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
            $response = $this->client()->post('/v2/shipping-order/fee', [
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
            $response = $this->client()->post('/v2/shipping-order/create', $orderData);
            
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
            $response = $this->client()->post('/v2/shipping-order/detail', [
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
