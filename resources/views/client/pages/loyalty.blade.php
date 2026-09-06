@extends('client.pages.layout')

@section('title', 'Khách hàng thân thiết - Aurelia Store')
@section('page_title', 'Aurelia Loyalty Program')
@section('page_subtitle', 'Đặc quyền đẳng cấp dành riêng cho những quý cô của Aurelia.')

@section('page_content')
<p>Chúng tôi luôn trân trọng sự đồng hành của bạn. Chương trình Khách hàng thân thiết (Loyalty Program) được thiết kế như một lời tri ân sâu sắc, với 3 hạng thẻ mang đến những đặc quyền hấp dẫn không thể chối từ.</p>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-10 mb-12">
    <!-- Thẻ Silver -->
    <div class="rounded-2xl p-8 bg-gradient-to-br from-gray-200 to-gray-400 text-gray-900 relative overflow-hidden shadow-lg transform hover:-translate-y-2 transition-transform duration-300">
        <div class="absolute right-0 top-0 w-32 h-32 bg-white/20 rounded-full blur-2xl"></div>
        <h3 class="!mt-0 text-2xl font-bold font-serif mb-1 relative z-10">Silver Member</h3>
        <p class="text-sm font-medium mb-6 relative z-10">Tổng chi tiêu: Tới 5.000.000đ</p>
        <ul class="space-y-3 text-sm relative z-10">
            <li class="flex items-start"><i class="bi bi-check-circle-fill mr-2 mt-0.5"></i> Chiết khấu cố định 5% cho mọi hóa đơn.</li>
            <li class="flex items-start"><i class="bi bi-check-circle-fill mr-2 mt-0.5"></i> Quà tặng Voucher 200K nhân dịp Sinh nhật.</li>
            <li class="flex items-start"><i class="bi bi-check-circle-fill mr-2 mt-0.5"></i> Tích điểm đổi quà thưởng trên app.</li>
        </ul>
    </div>

    <!-- Thẻ Gold -->
    <div class="rounded-2xl p-8 bg-gradient-to-br from-yellow-300 to-yellow-600 text-gray-900 relative overflow-hidden shadow-lg transform hover:-translate-y-2 transition-transform duration-300">
        <div class="absolute right-0 top-0 w-32 h-32 bg-white/30 rounded-full blur-2xl"></div>
        <h3 class="!mt-0 text-2xl font-bold font-serif mb-1 relative z-10">Gold Member</h3>
        <p class="text-sm font-medium mb-6 relative z-10">Tổng chi tiêu: 5.000.000đ - 20.000.000đ</p>
        <ul class="space-y-3 text-sm relative z-10">
            <li class="flex items-start"><i class="bi bi-check-circle-fill mr-2 mt-0.5"></i> Chiết khấu cố định 10% cho mọi hóa đơn.</li>
            <li class="flex items-start"><i class="bi bi-check-circle-fill mr-2 mt-0.5"></i> Miễn phí vận chuyển toàn quốc không giới hạn.</li>
            <li class="flex items-start"><i class="bi bi-check-circle-fill mr-2 mt-0.5"></i> Quà tặng Sinh nhật cao cấp (Trị giá 500K).</li>
            <li class="flex items-start"><i class="bi bi-check-circle-fill mr-2 mt-0.5"></i> Quyền mua sớm các BST giới hạn (Early Access).</li>
        </ul>
    </div>

    <!-- Thẻ Diamond -->
    <div class="rounded-2xl p-8 bg-gradient-to-br from-gray-800 to-black text-white relative overflow-hidden shadow-lg transform hover:-translate-y-2 transition-transform duration-300">
        <div class="absolute right-0 top-0 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
        <h3 class="!mt-0 text-2xl font-bold font-serif mb-1 relative z-10 text-white">Diamond Elite</h3>
        <p class="text-sm font-medium text-gray-400 mb-6 relative z-10">Tổng chi tiêu: Trên 20.000.000đ</p>
        <ul class="space-y-3 text-sm relative z-10">
            <li class="flex items-start"><i class="bi bi-star-fill text-yellow-400 mr-2 mt-0.5"></i> Chiết khấu cố định 15% VIP.</li>
            <li class="flex items-start"><i class="bi bi-star-fill text-yellow-400 mr-2 mt-0.5"></i> Ưu đãi giảm 30% nguyên tháng Sinh nhật.</li>
            <li class="flex items-start"><i class="bi bi-star-fill text-yellow-400 mr-2 mt-0.5"></i> Chuyên viên tư vấn phong cách riêng (Personal Stylist).</li>
            <li class="flex items-start"><i class="bi bi-star-fill text-yellow-400 mr-2 mt-0.5"></i> Tham dự Private Event & Fashion Show của Aurelia.</li>
        </ul>
    </div>
</div>

<h2>Làm thế nào để trở thành thành viên?</h2>
<p>Rất đơn giản! Ngay khi bạn tạo tài khoản và thực hiện đơn hàng đầu tiên thành công tại website Aurelia Store, hệ thống sẽ tự động ghi nhận bạn là thành viên và bắt đầu tích lũy chi tiêu. Bạn có thể theo dõi hạng thẻ và số điểm của mình trong mục <a href="{{ route('profile.index') }}" class="text-brand font-bold">Tài khoản cá nhân</a>.</p>
@endsection
