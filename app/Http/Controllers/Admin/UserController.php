<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     * Akun superadmin adalah otoritas tertinggi sistem dan tidak ditampilkan
     * di halaman pengguna yang dikelola.
     */
    public function index(Request $request): Response
    {
        $currentUser = $request->user();

        // Hanya tampilkan akun admin dan tim (superadmin disembunyikan dari tabel)
        $users = User::select('id', 'name', 'email', 'role', 'created_at')
            ->where('role', '!=', 'superadmin')
            ->orderByRaw("CASE WHEN role = 'admin' THEN 1 ELSE 2 END")
            ->orderBy('name', 'asc')
            ->get();

        // Informasi status Super Admin hanya dikirim jika yang membuka adalah Super Admin sendiri
        $superAdminInfo = null;
        if ($currentUser->role === 'superadmin') {
            $superAdmin = User::where('role', 'superadmin')->first();
            if ($superAdmin) {
                $superAdminInfo = [
                    'name' => $superAdmin->name,
                    'email' => $superAdmin->email,
                    'role' => $superAdmin->role,
                ];
            }
        }

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'superAdminInfo' => $superAdminInfo,
        ]);
    }

    /**
     * Store a newly created user in storage.
     * Hanya boleh membuat role 'admin' atau 'tim'. Superadmin tidak bisa dibuat dari dashboard.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'role' => ['required', 'string', 'in:admin,tim'],
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers()],
        ], [
            'role.in' => 'Peran pengguna hanya dapat dipilih sebagai Admin atau Tim Redaksi.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'password.min' => 'Password minimal 8 karakter dengan huruf besar, kecil, dan angka.',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.users.index')->with('success', "Akun pengguna {$validated['name']} berhasil ditambahkan!");
    }

    /**
     * Update the specified user in storage.
     * Akun superadmin tidak dapat diubah oleh siapapun melalui dashboard.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        // Proteksi absolut: Akun Super Admin tidak bisa diubah apapun
        if ($user->role === 'superadmin') {
            return back()->with('error', 'Akun Super Admin adalah otoritas tertinggi sistem dan tidak dapat diubah.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'string', 'in:admin,tim'],
            'password' => ['nullable', 'string', Password::min(8)->mixedCase()->numbers()],
        ], [
            'role.in' => 'Peran pengguna hanya dapat dipilih sebagai Admin atau Tim Redaksi.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'password.min' => 'Password minimal 8 karakter dengan huruf besar, kecil, dan angka.',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', "Data akun {$user->name} berhasil diperbarui!");
    }

    /**
     * Remove the specified user from storage.
     * Akun superadmin tidak dapat dihapus oleh siapapun melalui dashboard.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        $currentUser = $request->user();

        // Proteksi absolut: Akun Super Admin tidak dapat dihapus
        if ($user->role === 'superadmin') {
            return back()->with('error', 'Akun Super Admin adalah otoritas tertinggi sistem dan tidak dapat dihapus.');
        }

        // Cegah menghapus akun sendiri yang sedang aktif
        if ($user->id === $currentUser->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan.');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', "Akun pengguna {$userName} berhasil dihapus.");
    }
}
