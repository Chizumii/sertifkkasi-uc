<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Controller ini buat admin nerima atau nolak pendaftar penyewa. Keputusannya final, nggak bisa diubah lagi.
class UserVerificationController extends Controller
{
    // Function ini buat nampilin daftar pendaftar. Awalnya yang menunggu dulu, bisa difilter per status atau semua.
    public function index(Request $request): View
    {
        $status = UserStatus::tryFrom((string) $request->query('status', 'pending'));

        $users = User::query()
            ->where('role', UserRole::Tenant)
            ->when($status, fn ($query) => $query->where('verification_status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Jumlah penyewa per status, contoh: ['pending' => 2, 'rejected' => 1]. Status tanpa penyewa tidak muncul.
        $statusCounts = [];
        foreach (UserStatus::cases() as $case) {
            $count = User::query()->where('role', UserRole::Tenant)->where('verification_status', $case)->count();
            if ($count > 0) {
                $statusCounts[$case->value] = $count;
            }
        }

        return view('admin.users.index', [
            'users' => $users,
            'status' => $status?->value ?? 'all',
            'statusCounts' => $statusCounts,
        ]);
    }

    // Function ini buat nerima pendaftaran penyewa.
    public function approve(User $user): RedirectResponse
    {
        return $this->changeStatus($user, UserStatus::Approved, "Pendaftaran {$user->full_name} diterima.");
    }

    // Function ini buat nolak pendaftaran penyewa.
    public function reject(User $user): RedirectResponse
    {
        return $this->changeStatus($user, UserStatus::Rejected, "Pendaftaran {$user->full_name} ditolak.");
    }

    // Function ini buat ngubah status pendaftar. Akun admin nggak boleh diubah, dan cuma pendaftar yang masih menunggu yang bisa diputuskan.
    private function changeStatus(User $user, UserStatus $newStatus, string $message): RedirectResponse
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'Status akun admin tidak dapat diubah.');
        }

        if ($user->verification_status !== UserStatus::Pending) {
            return back()->with('error', 'Pendaftaran '.$user->full_name.' sudah '.strtolower($user->verification_status->label()).' dan tidak bisa diubah lagi.');
        }

        $user->verification_status = $newStatus;
        $user->save();

        return back()->with('success', $message);
    }
}
