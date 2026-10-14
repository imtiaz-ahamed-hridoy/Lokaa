<?php

namespace App\Http\Controllers\Web\Admin\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\FileManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    use FileManager;

    public function index(Request $request): Response
    {
        $users = User::where('role', 'User')
            ->select([
                'id',
                'name',
                'email',
                'phone',
                'avatar',
                'status',
                'created_at',
            ])->search(['name', 'email', 'phone'])->paginateData();

        return Inertia::render('user/index', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        return Inertia::render('user/create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|mimes:jpeg,png,jpg,webp,svg|max:10120',
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $paths = $this->uploadToPublic($request->file('avatar'), 'uploads/users');
            $avatarPath = $paths[0] ?? null;
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'avatar' => $avatarPath,
            'role' => 'User',
            'status' => 'Active',
        ]);

        return redirect()->route('user.index')->with('success', 'User created successfully!');
    }

    public function show(User $user)
    {
        return redirect()->route('user.edit', $user);
    }

    public function edit(User $user)
    {
        return Inertia::render('user/edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|mimes:jpeg,png,jpg,webp,svg|max:10120',
        ]);

        $avatarPath = $user->avatar;
        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                $this->deleteFromPublic($user, 'avatar');
            }
            $paths = $this->uploadToPublic($request->file('avatar'), 'uploads/users');
            $avatarPath = $paths[0] ?? null;
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'avatar' => $avatarPath,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('user.index')->with('success', 'User updated successfully!');
    }

    public function updateStatus(User $user)
    {
        $user->update([
            'status' => $user->status === 'Active' ? 'Inactive' : 'Active',
        ]);

        return redirect()->back()->with('success', 'User status updated successfully!');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully!');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $users = User::where('role', 'User')
            ->select(['id', 'name', 'email', 'phone', 'status', 'created_at'])
            ->get();

        $response = new StreamedResponse(function () use ($users) {
            $handle = fopen('php://output', 'w');

            // CSV header
            fputcsv($handle, ['ID', 'Name', 'Email', 'Phone', 'Status', 'Created At']);

            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->phone,
                    $user->status,
                    $user->created_at?->format('Y-m-d H:i:s') ?? '',
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="users-export-'.now()->format('Y-m-d').'.csv"');

        return $response;
    }
}
