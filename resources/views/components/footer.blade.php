<footer class="bg-white border-t border-gray-100 pt-16 pb-8 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
            <!-- Brand Info -->
            <div>
                <a href="/" class="flex items-center gap-2 mb-6">
                    <img src="{{ asset('images/nenlogoaureliawwhite.png') }}" alt="Aurelia Logo" class="h-[45px] w-auto object-contain">
                    <span class="text-brand font-playfair font-bold text-2xl tracking-[2px]">AURELIA</span>
                </a>
                <p class="text-gray-500 text-sm leading-relaxed mb-6">
                    Thương hiệu thời trang thiết kế cao cấp dành cho phái đẹp. Chúng tôi mang đến những bộ trang phục thanh lịch, hiện đại và tôn vinh vẻ đẹp tự nhiên của người phụ nữ.
                </p>
                <div class="flex gap-4">
                    <a href="#" class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-600 hover:bg-brand hover:text-white transition-colors">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-600 hover:bg-brand hover:text-white transition-colors">
                        <i class="bi bi-instagram"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-600 hover:bg-brand hover:text-white transition-colors">
                        <i class="bi bi-tiktok"></i>
                    </a>
                </div>
            </div>

            <!-- Customer Service -->
            <div>
                <h4 class="font-bold text-brand mb-6 uppercase tracking-wider text-sm">Chăm sóc khách hàng</h4>
                <ul class="space-y-3">
                    <li><a href="#" class="text-gray-500 hover:text-brand text-sm transition-colors">Chính sách vận chuyển</a></li>
                    <li><a href="#" class="text-gray-500 hover:text-brand text-sm transition-colors">Chính sách đổi trả</a></li>
                    <li><a href="#" class="text-gray-500 hover:text-brand text-sm transition-colors">Hướng dẫn mua hàng</a></li>
                    <li><a href="#" class="text-gray-500 hover:text-brand text-sm transition-colors">Bảo mật thông tin</a></li>
                    <li><a href="#" class="text-gray-500 hover:text-brand text-sm transition-colors">Liên hệ hỗ trợ</a></li>
                </ul>
            </div>

            <!-- About Us -->
            <div>
                <h4 class="font-bold text-brand mb-6 uppercase tracking-wider text-sm">Về Aurelia</h4>
                <ul class="space-y-3">
                    <li><a href="#" class="text-gray-500 hover:text-brand text-sm transition-colors">Câu chuyện thương hiệu</a></li>
                    <li><a href="#" class="text-gray-500 hover:text-brand text-sm transition-colors">Hệ thống cửa hàng</a></li>
                    <li><a href="#" class="text-gray-500 hover:text-brand text-sm transition-colors">Tuyển dụng</a></li>
                    <li><a href="#" class="text-gray-500 hover:text-brand text-sm transition-colors">Góc báo chí</a></li>
                    <li><a href="#" class="text-gray-500 hover:text-brand text-sm transition-colors">Khách hàng thân thiết</a></li>
                </ul>
            </div>

            <!-- Newsletter -->
            <div>
                <h4 class="font-bold text-brand mb-6 uppercase tracking-wider text-sm">Đăng ký nhận tin</h4>
                <p class="text-gray-500 text-sm mb-4">
                    Nhận ngay ưu đãi 10% cho đơn hàng đầu tiên và cập nhật những bộ sưu tập mới nhất.
                </p>
                <form class="flex flex-col gap-3">
                    <input type="email" placeholder="Nhập email của bạn" class="w-full px-4 py-3 bg-gray-50 border border-transparent rounded-lg focus:border-brand focus:ring-1 focus:ring-brand outline-none transition-all text-sm">
                    <button type="button" class="w-full bg-brand hover:bg-brand-hover text-white font-medium py-3 rounded-lg transition-colors text-sm">
                        ĐĂNG KÝ
                    </button>
                </form>
            </div>
        </div>

        <!-- Bottom Footer -->
        <div class="border-t border-gray-100 pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-gray-400 text-sm">
                &copy; {{ date('Y') }} AURELIA BOUTIQUE. Tất cả quyền được bảo lưu.
            </p>
            <div class="flex items-center gap-4">
                <!-- Payment Methods Mockup -->
                <i class="bi bi-credit-card text-2xl text-gray-400"></i>
                <i class="bi bi-paypal text-2xl text-gray-400"></i>
                <i class="bi bi-wallet2 text-2xl text-gray-400"></i>
            </div>
        </div>
    </div>
</footer>
