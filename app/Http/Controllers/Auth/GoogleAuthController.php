<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $clientId = config('services.google.client_id');

        if (!$clientId) {
            return redirect()
                ->route('login')
                ->with('error', 'Chưa cấu hình GOOGLE_CLIENT_ID cho đăng nhập Google.');
        }

        $state = Str::random(40);
        $request->session()->put('google_oauth_state', $state);

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account',
        ]);

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->filled('error')) {
            return redirect()
                ->route('login')
                ->with('error', 'Bạn đã hủy hoặc Google từ chối đăng nhập.');
        }

        $state = $request->query('state');
        $sessionState = $request->session()->pull('google_oauth_state');

        if (!$state || !$sessionState || !hash_equals($sessionState, $state)) {
            return redirect()
                ->route('login')
                ->with('error', 'Phiên đăng nhập Google không hợp lệ. Vui lòng thử lại.');
        }

        if (!$request->filled('code')) {
            return redirect()
                ->route('login')
                ->with('error', 'Google không trả về mã xác thực. Vui lòng thử lại.');
        }

        try {
            $googleUser = $this->fetchGoogleUser($request->query('code'));
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('login')
                ->with('error', 'Không thể kết nối Google. Vui lòng thử lại sau.');
        }

        if (empty($googleUser['email'])) {
            return redirect()
                ->route('login')
                ->with('error', 'Tài khoản Google không cung cấp email hợp lệ.');
        }

        $user = $this->findOrCreateUser($googleUser);

        if ($user->status !== 'active') {
            return redirect()
                ->route('login')
                ->with('error', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.');
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended($this->redirectByRole($user));
    }

    private function fetchGoogleUser(string $code): array
    {
        $tokenResponse = Http::asForm()
            ->timeout(15)
            ->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri' => $this->redirectUri(),
                'grant_type' => 'authorization_code',
                'code' => $code,
            ]);

        if (!$tokenResponse->successful()) {
            throw new \RuntimeException('Google token exchange failed: ' . $tokenResponse->body());
        }

        $accessToken = $tokenResponse->json('access_token');

        if (!$accessToken) {
            throw new \RuntimeException('Google access token is missing.');
        }

        $userResponse = Http::withToken($accessToken)
            ->timeout(15)
            ->get('https://www.googleapis.com/oauth2/v3/userinfo');

        if (!$userResponse->successful()) {
            throw new \RuntimeException('Google userinfo failed: ' . $userResponse->body());
        }

        return $userResponse->json();
    }

    private function findOrCreateUser(array $googleUser): User
    {
        $email = strtolower($googleUser['email']);
        $googleId = $googleUser['sub'] ?? null;

        $user = User::where('email', $email)
            ->when($googleId, function ($query) use ($googleId) {
                $query->orWhere('google_id', $googleId);
            })
            ->first();

        if ($user) {
            $user->forceFill([
                'google_id' => $user->google_id ?: $googleId,
                'email_verified_at' => $user->email_verified_at ?: now(),
                'avatar' => $user->avatar ?: ($googleUser['picture'] ?? null),
            ])->save();

            return $user;
        }

        return User::create([
            'name' => $googleUser['name'] ?? ($googleUser['given_name'] ?? 'Khách hàng Google'),
            'email' => $email,
            'email_verified_at' => now(),
            'password' => Hash::make(Str::random(40)),
            'avatar' => $googleUser['picture'] ?? null,
            'google_id' => $googleId,
            'role' => 'customer',
            'status' => 'active',
        ]);
    }

    private function redirectByRole(User $user): string
    {
        return match ($user->role) {
            'admin' => route('admin.dashboard'),
            'owner' => route('owner.dashboard'),
            default => route('customer.home'),
        };
    }

    private function redirectUri(): string
    {
        return config('services.google.redirect') ?: route('auth.google.callback');
    }
}
