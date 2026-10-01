<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\AuditLog;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Hiển thị giao diện Đăng Nhập
     */
    public function showLoginForm()
    {
        $this->ensureDemoAccountsExist();

        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Xử lý đăng nhập hệ thống (BẮT BUỘC MẬT KHẨU PHẢI ĐÚNG)
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $this->ensureDemoAccountsExist();

        $email = strtolower(trim($request->email));
        $password = $request->password;
        $remember = $request->has('remember');

        // Tìm kiếm người dùng theo email
        $user = User::where('email', $email)->first();

        // 1. Nếu không tìm thấy người dùng
        if (!$user) {
            return back()->withInput($request->only('email', 'remember'))
                         ->with('error', 'Tài khoản không tồn tại trong hệ thống. Vui lòng kiểm tra lại!');
        }

        // 2. Nếu tài khoản đã bị khóa
        if ($user->status === 'locked') {
            return back()->withInput($request->only('email', 'remember'))
                         ->with('error', 'Tài khoản của bạn đang bị khóa tạm thời. Vui lòng liên hệ Quản trị viên để mở khóa.');
        }

        // 3. KIỂM TRA MẬT KHẨU CHÍNH XÁC
        // Hỗ trợ kiểm tra hash Bcrypt hoặc nếu mật khẩu demo là 'password'/'123456'
        $passwordMatches = Hash::check($password, $user->password) 
                        || ($user->password === 'password' && $password === 'password')
                        || ($user->password === '123456' && $password === '123456');

        if (!$passwordMatches) {
            // MẬT KHẨU SAI -> TỪ CHỐI ĐĂNG NHẬP NGAY LẬP TỨC!
            return back()->withInput($request->only('email', 'remember'))
                         ->with('error', 'Mật khẩu không chính xác. Vui lòng thử lại!');
        }

        // 4. Mật khẩu đúng -> Đăng nhập thành công
        Auth::login($user, $remember);
        $request->session()->regenerate();

        try {
            if (class_exists(AuditLog::class)) {
                AuditLog::create([
                    'operator_name' => $user->name,
                    'operator_role' => $user->role,
                    'action' => 'LOGIN_SUCCESS',
                    'target_id' => (string)$user->id,
                    'details' => "Người dùng {$user->name} ({$user->role}) đăng nhập thành công vào hệ thống.",
                    'ip_address' => $request->ip()
                ]);
            }
        } catch (\Throwable $e) {}

        return $this->redirectBasedOnRole($user);
    }

    /**
     * Chuyển hướng người dùng dựa vào vai trò
     */
    private function redirectBasedOnRole($user)
    {
        if ($user->role === 'admin') {
            return redirect()->route('admin.index')->with('success', 'Chào mừng Quản trị viên ' . $user->name . ' quay trở lại!');
        } elseif ($user->role === 'librarian') {
            return redirect()->route('librarian.index')->with('success', 'Chào mừng Thủ thư ' . $user->name . ' đã vào ca làm việc!');
        } else {
            return redirect()->route('reader.index')->with('success', 'Chào mừng độc giả ' . $user->name . ' đến với Thư viện LibraNova!');
        }
    }

    /**
     * Tự động tạo sẵn 3 tài khoản mặc định nếu database trống
     */
    private function ensureDemoAccountsExist()
    {
        try {
            // 1. Độc giả mẫu đã có thẻ
            User::firstOrCreate(
                ['email' => 'an.nguyen@libranova.vn'],
                [
                    'name' => 'Nguyễn Văn An',
                    'password' => Hash::make('password'),
                    'role' => 'reader',
                    'card_number' => 'LIB-2026-8899',
                    'card_expiry_date' => Carbon::now()->addYear(),
                    'status' => 'active',
                    'phone' => '0912345678',
                    'address' => 'Hải Châu, Đà Nẵng'
                ]
            );

            // 2. Thủ thư
            User::firstOrCreate(
                ['email' => 'thuthu@libranova.vn'],
                [
                    'name' => 'Trần Thu Thư',
                    'password' => Hash::make('password'),
                    'role' => 'librarian',
                    'card_number' => 'STAFF-LIB-01',
                    'card_expiry_date' => Carbon::now()->addYears(3),
                    'status' => 'active',
                    'phone' => '0988776655',
                    'address' => 'Khu Văn Phòng Thư Viện'
                ]
            );

            // 3. Quản trị viên
            User::firstOrCreate(
                ['email' => 'admin@libranova.vn'],
                [
                    'name' => 'Phạm Quang Admin',
                    'password' => Hash::make('password'),
                    'role' => 'admin',
                    'card_number' => 'ADMIN-ROOT',
                    'card_expiry_date' => Carbon::now()->addYears(5),
                    'status' => 'active',
                    'phone' => '0909999999',
                    'address' => 'Ban Giám Hiệu'
                ]
            );
        } catch (\Throwable $e) {}
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Đăng ký tài khoản khách hàng mới (chưa có thẻ)
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => strtolower(trim($request->email)),
            'phone' => trim($request->phone),
            'password' => Hash::make($request->password),
            'role' => 'reader',
            'status' => 'active',
            'card_number' => null, // Chưa cấp thẻ khi đăng ký online
            'card_expiry_date' => null,
            'address' => $request->address ?? 'Khách hàng đăng ký trực tuyến'
        ]);

        Auth::login($user);

        return redirect()->route('reader.index')->with('success', "🎉 Đăng ký tài khoản thành công! Bạn có thể tra cứu sách. Vui lòng đến quầy thư viện để được cấp thẻ mượn sách.");
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Đã đăng xuất tài khoản an toàn khỏi hệ thống!');
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function resetPasswordDirect(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:6|confirmed'
        ]);

        $user = User::where('email', strtolower(trim($request->email)))->first();
        if ($user) {
            $user->password = Hash::make($request->password);
            $user->status = 'active'; // Mở khóa nếu tài khoản bị khóa
            $user->save();

            return redirect()->route('login')->with('success', 'Đổi mật khẩu thành công! Bạn có thể đăng nhập bằng mật khẩu mới.');
        }

        return back()->with('error', 'Không tìm thấy tài khoản tương ứng.');
    }

    /**
     * Báo cáo sự cố cho Quản Trị Viên
     */
    public function reportAdmin(Request $request)
    {
        $request->validate([
            'reporter_info' => 'required|string|max:255',
            'issue_type' => 'required|string',
        ]);

        try {
            if (class_exists(AuditLog::class)) {
                $detail = "Sự cố: " . $request->issue_type;
                if ($request->filled('custom_reason')) {
                    $detail .= " | Chi tiết: " . $request->custom_reason;
                }
                AuditLog::create([
                    'operator_name' => $request->reporter_info,
                    'operator_role' => 'guest',
                    'action' => 'REPORT_ISSUE',
                    'target_id' => 'ADMIN',
                    'details' => $detail,
                    'ip_address' => $request->ip()
                ]);
            }
        } catch (\Throwable $e) {}

        return back()->with('success', 'Báo cáo sự cố của bạn đã được gửi tới Ban Quản Trị thành công!');
    }
}