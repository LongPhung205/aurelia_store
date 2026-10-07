<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>In Tem Hàng Loạt</title>
    @vite(['resources/css/app.css'])
    <style>
        body {
            background-color: white;
            margin: 0;
            padding: 0;
        }
        @media print {
            @page {
                margin: 0;
                size: 100mm 150mm;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .page-break {
                page-break-after: always;
                page-break-inside: avoid;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    @foreach($orders as $order)
    <div class="page-break">
        <div class="w-[100mm] bg-white text-black p-3 font-sans border-none mx-auto print:mx-0 overflow-hidden" style="width: 100mm; height: 148mm; box-sizing: border-box;">
            <!-- Phần 1: Header / Logo GHN -->
            <div class="flex border-b-2 border-black pb-2 mb-2 items-center">
                <div class="w-1/2 border-r-2 border-black pr-2">
                    <h1 class="font-black text-lg leading-none mb-1 tracking-tighter uppercase">AURELIA</h1>
                    <p class="text-[10px] font-bold mb-0.5">Hotline: 1900 6969</p>
                    <p class="text-[9px] font-medium leading-tight">Đại Học Tài Nguyên và Môi Trường Hà Nội</p>
                </div>
                <div class="w-1/2 pl-2 text-center flex flex-col justify-center items-center">
                    <div class="font-black text-xl border-2 border-black px-2 py-0.5 inline-block mb-1 uppercase tracking-widest">
                        GHN
                    </div>
                    @if($order->shipping_order_code)
                       <p class="text-sm font-black font-mono tracking-widest">{{$order->shipping_order_code}}</p>
                    @else
                       <p class="text-[10px] font-bold italic text-rose-500">Chưa tạo vận đơn</p>
                    @endif
                </div>
            </div>
            
            <!-- Phần 2: Người nhận -->
            <div class="border-b-2 border-black pb-2 mb-2">
                <div class="text-[10px] font-bold mb-1 uppercase">Đến (Người nhận):</div>
                <div class="font-black text-xl uppercase leading-none mb-1">{{ $order->customer_name }}</div>
                <div class="font-black text-lg tracking-widest mt-1.5"><span class="text-[10px] font-bold uppercase tracking-normal mr-1">SĐT:</span>{{ $order->customer_phone }}</div>
                @if($order->ghnAddressStr)
                    <div class="text-[11px] font-bold mt-1.5 leading-snug">{{ $order->ghnAddressStr }}</div>
                @endif
                <div class="text-[10px] font-medium mt-0.5 leading-snug italic">{{ $order->address }}</div>
            </div>
            
            <!-- Phần 3: Mã đơn & Tiền thu hộ (COD) -->
            <div class="flex border-b-2 border-black pb-2 mb-2 gap-2">
                <div class="w-1/2 border-r-2 border-black pr-2">
                    <div class="text-[10px] font-bold uppercase">Mã đơn hàng:</div>
                    <div class="font-black text-sm uppercase">ORD-{{ $order->id }}</div>
                    <div class="text-[9px] mt-1 font-bold">Ngày: {{ $order->created_at->format('d/m/Y H:i') }}</div>
                </div>
                <div class="w-1/2 text-center flex flex-col justify-center">
                    <div class="text-[10px] font-bold uppercase">Tổng thu hộ (COD)</div>
                    @if($order->payment_status == 'paid')
                        <div class="font-black text-3xl mt-1 tracking-tighter">0 đ</div>
                        <div class="text-[10px] font-black border border-black inline-block px-1 mx-auto mt-1 uppercase">Đã thanh toán trước</div>
                    @else
                        <div class="font-black text-2xl mt-1 tracking-tighter">{{ number_format($order->total_amount, 0, ',', '.') }} đ</div>
                        <div class="text-[9px] font-bold mt-1">Người nhận trả tiền</div>
                    @endif
                </div>
            </div>
            
            <!-- Phần 4: Danh sách sản phẩm -->
            <div class="border-b-2 border-black pb-2 mb-2 min-h-[80px]">
                <div class="text-[10px] font-bold mb-1 uppercase">Nội dung hàng (Tổng SL: {{ $order->items->sum('quantity') }}):</div>
                <table class="w-full text-xs font-bold">
                    @foreach($order->items as $index => $item)
                    <tr class="border-b border-dashed border-gray-300 last:border-0">
                        <td class="pr-1 align-top py-1 w-4">{{$index+1}}.</td>
                        <td class="align-top py-1">{{ $item->product_name }} 
                            @if($item->variant_attributes) <span class="text-[10px] font-normal">({{ $item->variant_attributes }})</span> @endif
                        </td>
                        <td class="text-right align-top py-1 whitespace-nowrap">SL: <span class="text-sm font-black">{{ $item->quantity }}</span></td>
                    </tr>
                    @endforeach
                </table>
            </div>

            <!-- Phần 5: Ghi chú -->
            <div class="text-[11px] font-bold text-center border-2 border-black p-1 uppercase">
                Lưu ý: {{ $order->note ?: 'Cho khách xem hàng, không thử. Quay video khi bóc.' }}
            </div>
            
            <!-- Phần 6: Chữ ký -->
            <div class="flex justify-between mt-2 px-4">
                <div class="text-[9px] font-bold text-center">
                    Chữ ký bưu tá
                    <div class="mt-8 text-[8px] italic text-gray-500">(Ký, ghi rõ họ tên)</div>
                </div>
                <div class="text-[9px] font-bold text-center">
                    Chữ ký người nhận
                    <div class="mt-8 text-[8px] italic text-gray-500">(Ký, ghi rõ họ tên)</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    <script>
        // Tự động mở hộp thoại in sau khi tải trang 1 giây để load font kịp
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
