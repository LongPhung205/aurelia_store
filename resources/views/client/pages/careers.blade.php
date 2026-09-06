@extends('client.pages.layout')

@section('title', 'Tuyển dụng - Aurelia Store')
@section('page_title', 'Gia nhập đội ngũ Aurelia')
@section('page_subtitle', 'Cùng chúng tôi kiến tạo những giá trị thanh lịch và bền vững.')

@section('page_content')
<p>Tại Aurelia, chúng tôi luôn tìm kiếm những tài năng trẻ, những con người đam mê cái đẹp và có tinh thần cầu tiến. Môi trường làm việc tại Aurelia đề cao sự sáng tạo, chuyên nghiệp và tôn trọng bản sắc cá nhân.</p>

<h2>Vị trí đang tuyển dụng</h2>

<div class="mt-6 space-y-4">
    <!-- Job 1 -->
    <div class="p-6 border border-gray-200 rounded-xl hover:border-brand transition-colors">
        <div class="flex justify-between items-start md:items-center flex-col md:flex-row mb-4">
            <div>
                <h3 class="!mt-0 mb-1 text-lg font-bold text-gray-900">Chuyên viên Tư vấn Thời trang (Sales Assistant)</h3>
                <p class="text-gray-500 text-sm">Địa điểm: TP. Hồ Chí Minh | Toàn thời gian</p>
            </div>
            <span class="mt-2 md:mt-0 px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">Đang tuyển</span>
        </div>
        <p class="text-gray-600 mb-4">Đại diện cho hình ảnh thương hiệu tại cửa hàng, tư vấn phong cách và mang lại trải nghiệm mua sắm xuất sắc cho khách hàng cao cấp.</p>
        <button class="text-brand font-semibold hover:underline">Xem chi tiết & Ứng tuyển &rarr;</button>
    </div>

    <!-- Job 2 -->
    <div class="p-6 border border-gray-200 rounded-xl hover:border-brand transition-colors">
        <div class="flex justify-between items-start md:items-center flex-col md:flex-row mb-4">
            <div>
                <h3 class="!mt-0 mb-1 text-lg font-bold text-gray-900">Chuyên viên Sáng tạo Nội dung (Content Creator)</h3>
                <p class="text-gray-500 text-sm">Địa điểm: Văn phòng Quận 1, TP.HCM | Toàn thời gian</p>
            </div>
            <span class="mt-2 md:mt-0 px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">Đang tuyển</span>
        </div>
        <p class="text-gray-600 mb-4">Lên ý tưởng, viết bài, sản xuất hình ảnh/video kịch bản lookbook mang đậm dấu ấn thanh lịch của Aurelia trên các nền tảng mạng xã hội.</p>
        <button class="text-brand font-semibold hover:underline">Xem chi tiết & Ứng tuyển &rarr;</button>
    </div>
</div>

<h2>Chế độ đãi ngộ</h2>
<ul>
    <li>Thu nhập hấp dẫn, xứng đáng với năng lực (Lương cơ bản + Thưởng hiệu suất + Hoa hồng).</li>
    <li>Môi trường làm việc trẻ trung, năng động, văn phòng hạng A sang trọng.</li>
    <li>Cơ hội thăng tiến rõ ràng trong ngành bán lẻ thời trang cao cấp.</li>
    <li>Được tài trợ trang phục làm việc từ thương hiệu Aurelia, hưởng ưu đãi chiết khấu nội bộ lên đến 40%.</li>
</ul>

<p class="mt-8 text-center text-gray-500 italic">Vui lòng gửi CV và Portfolio của bạn về địa chỉ email: <strong>hr@aureliastore.com</strong> với tiêu đề [Vị trí ứng tuyển] - [Họ Tên].</p>
@endsection
