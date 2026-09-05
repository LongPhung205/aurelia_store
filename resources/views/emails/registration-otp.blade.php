<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Mã xác thực đăng ký tài khoản</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f9fafb; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; border: 1px solid #e5e7eb; text-align: center;">
        <h2 style="color: #111827; margin-bottom: 20px;">Xin chào!</h2>
        <p style="color: #4b5563; font-size: 16px; margin-bottom: 30px;">
            Cảm ơn bạn đã đăng ký tài khoản tại Aurelia Store. Dưới đây là mã xác thực của bạn:
        </p>
        
        <div style="background-color: #f3f4f6; padding: 15px; border-radius: 6px; display: inline-block; margin-bottom: 30px;">
            <span style="font-size: 32px; font-weight: bold; color: #fd817a; letter-spacing: 5px;">{{ $otp }}</span>
        </div>
        
        <p style="color: #6b7280; font-size: 14px; margin-bottom: 10px;">
            Mã xác thực này sẽ hết hạn sau 5 phút. Vui lòng không chia sẻ mã này cho bất kỳ ai.
        </p>
        <p style="color: #6b7280; font-size: 14px;">
            Nếu bạn không yêu cầu mã này, vui lòng bỏ qua email này.
        </p>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #9ca3af; font-size: 12px;">
            &copy; {{ date('Y') }} Aurelia Store. Đã đăng ký bản quyền.
        </div>
    </div>
</body>
</html>
