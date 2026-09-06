@extends('client.pages.layout')

@section('title', 'Hướng dẫn mua hàng - Aurelia Store')
@section('page_title', 'Hướng dẫn mua hàng')
@section('page_subtitle', 'Các bước đơn giản để sở hữu những thiết kế yêu thích.')

@section('page_content')
<h2>1. Tìm kiếm và chọn sản phẩm</h2>
<p>Bạn có thể dễ dàng tìm kiếm sản phẩm tại website của Aurelia qua các cách sau:</p>
<ul>
    <li>Sử dụng thanh công cụ tìm kiếm ở góc trên cùng trang web.</li>
    <li>Duyệt qua các danh mục chính trên thanh Menu (Ví dụ: Váy công sở, Áo Blazer, Bộ sưu tập mới).</li>
    <li>Khám phá các gợi ý trên Trang chủ.</li>
</ul>

<h2>2. Đưa sản phẩm vào giỏ hàng</h2>
<p>Sau khi chọn được sản phẩm ưng ý, bạn tiến hành chọn <strong>Màu sắc</strong> và <strong>Kích cỡ (Size)</strong>. Nếu phân vân về size, hãy bấm vào <em>"Bảng quy đổi kích cỡ"</em> kế bên để tìm được thông số chuẩn nhất cho cơ thể.</p>
<p>Bấm <strong>"Thêm vào giỏ hàng"</strong> để tiếp tục mua sắm hoặc bấm <strong>"Mua ngay"</strong> để chuyển thẳng tới trang thanh toán.</p>

<h2>3. Tiến hành thanh toán</h2>
<p>Tại trang Giỏ hàng, bạn có thể kiểm tra lại số lượng, thuộc tính sản phẩm và nhập <strong>Mã giảm giá (Coupon)</strong> nếu có.</p>
<p>Chuyển sang bước Thanh toán, bạn vui lòng điền đầy đủ và chính xác các thông tin giao hàng bao gồm: Họ tên, Số điện thoại, Địa chỉ chi tiết. Phí vận chuyển sẽ được hệ thống tính toán tự động dựa trên địa chỉ này.</p>

<h2>4. Chọn phương thức thanh toán</h2>
<p>Aurelia hỗ trợ đa dạng phương thức thanh toán để tạo sự thuận tiện tối đa:</p>
<ul>
    <li>Thanh toán khi nhận hàng (COD).</li>
    <li>Thanh toán chuyển khoản ngân hàng.</li>
    <li>Thanh toán qua cổng VNPay / MoMo (Thẻ ATM nội địa, Thẻ tín dụng Visa/MasterCard).</li>
</ul>

<h2>5. Xác nhận đơn hàng</h2>
<p>Sau khi nhấn "Hoàn tất đặt hàng", màn hình sẽ hiển thị thông báo thành công kèm <strong>Mã đơn hàng</strong>. Đồng thời, một email xác nhận sẽ được gửi đến hộp thư của bạn. Bạn đã hoàn thành việc mua sắm, nhân viên của Aurelia sẽ gọi điện xác nhận và chuẩn bị gửi hàng cho bạn sớm nhất!</p>
@endsection
