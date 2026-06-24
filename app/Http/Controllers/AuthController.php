<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use App\Http\Controllers\CartController;
use App\Models\DocumentVerification;
use App\Models\AdminLoginLog;

class AuthController extends Controller
{
    // REGISTER — Show Form
    public function showRegister()
    {
        return view('main_page.register.register');
    }

    // REGISTER — Process
    public function register(Request $request)
    {
        // ----------------------------------------------------------
        // 1. VALIDASI (semua pengguna)
        // ----------------------------------------------------------
        $commonRules = [
            'role'             => 'required|in:buyer,seller',
            'account_category' => 'required|in:company,personal',
            'password'         => ['required', 'min:8'],
            'confirm_password' => 'required|same:password',
            'address'          => 'required|string|max:500',
            'phone'            => 'required|string|max:20',
            'email'            => 'required|email|unique:users,email',
        ];

        // ----------------------------------------------------------
        // 2. VALIDASI ROLE — Company vs Personal
        // ----------------------------------------------------------
        $category = $request->input('account_category');

        if ($category === 'company') {
            $categoryRules = [
                'company_name'  => 'required|string|max:255',

                'full_name'     => 'required|string|max:50',
                'position'      => 'required|string|max:50',
                'industry'      => 'required|string|max:50',
                // Dokumen wajib company
                'akta'          => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'npwp'          => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'nib'           => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
                // optional
                'iso'           => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ];
        } else {
            // personal
            $categoryRules = [
                'name'  => 'required|string|max:50',
                // 'email' => 'required|email|unique:users,email',
                'npwp'  => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'job_title' => 'required|string|max:50',
            ];
        }

        // ----------------------------------------------------------
        // 3. VALIDASI DOKUMEN — Seller
        // ----------------------------------------------------------
        $role = $request->input('role');

        $sellerRules = [];
        if ($role === 'seller') {
            $sellerRules = [
                'gold_standard' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'vcs'           => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ];
        }

        // Gabungkan semua rules dan validasi
        $request->validate(array_merge($commonRules, $categoryRules, $sellerRules));

        // Validasi seller: minimal 1 sertifikat
        if ($role === 'seller') {
            $hasGold = $request->hasFile('gold_standard') && $request->file('gold_standard')->isValid();
            $hasVcs  = $request->hasFile('vcs') && $request->file('vcs')->isValid();

            if (!$hasGold && !$hasVcs) {
                return back()
                    ->withInput()
                    ->withErrors(['gold_standard' => 'Seller wajib upload minimal 1 sertifikat (Gold Standard atau VCS).']);
            }
        }

        // ----------------------------------------------------------
        // 4. SIMPAN USER
        // ----------------------------------------------------------
        $user = new User();

        // Field berdasarkan category
        if ($category === 'company') {
            $user->name         = $request->full_name;
            // $user->email        = $request->company_email;
            $user->company_name = $request->company_name;
            $user->position     = $request->position;
            $user->industry     = $request->industry;
        } else {
            $user->name      = $request->name;
            $user->job_title = $request->job_title;
        }

        // Field bersama
        $user->email     = $request->email;
        $user->password         = Hash::make($request->password);
        $user->role             = $role;
        $user->account_category = $category;
        $user->phone            = $request->phone;
        $user->address          = $request->address;
        $user->status           = 'pending'; // Akun menunggu verifikasi admin

        // ----------------------------------------------------------
        // 5. FOTO PROFIL (base64 dari cropper, optional)
        // ----------------------------------------------------------
        if ($request->filled('profile_photo')) {
            $photoPath = $this->saveBase64Image(
                $request->input('profile_photo'),
                'profile_photos'
            );
            $user->profile_photo = $photoPath;
        }

        $user->save();

        // ----------------------------------------------------------
        // 6. UPLOAD DOKUMEN
        // ----------------------------------------------------------
        $documents = [];

        // NPWP — semua pengguna
        if ($request->hasFile('npwp')) {
            $documents['npwp'] = $this->uploadDocument($request->file('npwp'), $user->id, 'npwp');
        }

        // Company-only documents
        if ($category === 'company') {
            if ($request->hasFile('akta')) {
                $documents['akta'] = $this->uploadDocument($request->file('akta'), $user->id, 'akta');
            }
            if ($request->hasFile('nib')) {
                $documents['nib'] = $this->uploadDocument($request->file('nib'), $user->id, 'nib');
            }
            if ($request->hasFile('iso')) {
                $documents['iso'] = $this->uploadDocument($request->file('iso'), $user->id, 'iso');
            }
        }

        // Seller-only documents
        if ($role === 'seller') {
            if ($request->hasFile('gold_standard')) {
                $documents['gold_standard'] = $this->uploadDocument($request->file('gold_standard'), $user->id, 'gold_standard');
            }
            if ($request->hasFile('vcs')) {
                $documents['vcs'] = $this->uploadDocument($request->file('vcs'), $user->id, 'vcs');
            }
        }

        // Simpan path dokumen sebagai JSON di kolom documents
        if (!empty($documents)) {
            $user->documents = $documents;
            $user->save();

            foreach ($documents as $type => $path) {
                DocumentVerification::updateOrCreate(
                    ['user_id' => $user->id, 'document_type' => $type],
                    ['document_path' => $path, 'status' => 'pending']
                );
            }
        }

        // ----------------------------------------------------------
        // 7. LOGIN & REDIRECT
        // ----------------------------------------------------------
        return redirect()->route('login')->with('success', 'Akun berhasil dibuat! Akun Anda sedang menunggu verifikasi admin dalam 1-2 hari kerja.');
    }

    // LOGIN — Show Form
    public function showLogin()
    {
        return view('main_page.login.login');
    }

    // LOGIN — Process
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);
        
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (Auth::user()->isSuspended()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'loginError' => 'Akun Anda sedang dinonaktifkan. Hubungi super admin.',
                ])->onlyInput('email');
            }

            if (Auth::user()->isAdministrator()) {
                $loginLog = AdminLoginLog::create([
                    'admin_id' => Auth::id(),
                    'session_id' => $request->session()->getId(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'logged_in_at' => now(),
                ]);
                $request->session()->put('admin_login_log_id', $loginLog->id);
            }

            // FIX #2: Merge cart dari session (guest) ke DB setelah login
            // Sehingga produk yang ditambahkan sebelum login tidak hilang.
            //app(CartController::class)->mergeSessionCart();

            return redirect()->intended(match (Auth::user()->role) {
                'admin', 'super_admin' => route('admin.dashboard'),
                'seller' => route('seller.dashboard'),
                default => route('dashboard'),
            });
        }
        return back()->withErrors(['loginError' => 'Email atau password tidak sesuai.',])->onlyInput('email');
    }

    // LOGOUT
    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user?->isAdministrator()) {
            AdminLoginLog::query()
                ->where('admin_id', $user->id)
                ->whereKey($request->session()->get('admin_login_log_id'))
                ->whereNull('logged_out_at')
                ->first()
                ?->update(['logged_out_at' => now()]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Berhasil logout.');
    }

    // HELPER: Upload dokumen ke storage
    private function uploadDocument($file, int $userId, string $docType): string
    {
        $extension = $file->getClientOriginalExtension();
        $fileName  = "{$docType}_{$userId}_" . time() . ".{$extension}";
        $path      = $file->storeAs("documents/{$userId}", $fileName, 'private');

        return $path;
    }

    // HELPER: Simpan foto profil dari base64 (Cropper.js output)
    private function saveBase64Image(string $base64String, string $folder): string
    {
        if (!str_contains($base64String, ',')) {
            return '';
        }

        [$meta, $data] = explode(',', $base64String, 2);

        // Tentukan ekstensi dari meta
        $extension = 'jpg';
        if (str_contains($meta, 'png')) {
            $extension = 'png';
        } elseif (str_contains($meta, 'webp')) {
            $extension = 'webp';
        }

        $decoded  = base64_decode($data);
        $fileName = uniqid('photo_', true) . '.' . $extension;
        $path     = "{$folder}/{$fileName}";

        Storage::disk('public')->put($path, $decoded);

        return $path;
    }
}
