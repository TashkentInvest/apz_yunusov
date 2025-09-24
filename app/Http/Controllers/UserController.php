<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request): View
    {
        $query = User::query();

        // Search functionality
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->role && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        // Status filter
        if ($request->status && $request->status !== 'all') {
            $query->where('is_active', $request->status === 'active');
        }

        // Department filter
        if ($request->department && $request->department !== 'all') {
            $query->where('department', $request->department);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15)->appends($request->query());

        // Get filter options
        $departments = User::whereNotNull('department')
            ->distinct()
            ->pluck('department')
            ->sort();

        $roles = [
            'admin' => 'Администратор',
            'manager' => 'Менеджер',
            'employee' => 'Сотрудник'
        ];

        return view('users.index', compact('users', 'departments', 'roles'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create(): View
    {
        return view('users.create');
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,manager,employee',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'phone' => $request->phone,
                'department' => $request->department,
                'is_active' => $request->has('is_active'),
                'email_verified_at' => now(),
            ]);

            return redirect()->route('users.index')
                ->with('success', 'Пользователь успешно создан');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Ошибка при создании пользователя: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the user
     */
    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin,manager,employee',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
                'phone' => $request->phone,
                'department' => $request->department,
                'is_active' => $request->has('is_active'),
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            return redirect()->route('users.index')
                ->with('success', 'Пользователь успешно обновлен');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Ошибка при обновлении пользователя: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user): RedirectResponse
    {
        try {
            // Prevent deleting yourself
            if ($user->id === auth()->id()) {
                return back()->with('error', 'Нельзя удалить самого себя');
            }

            $user->delete();

            return redirect()->route('users.index')
                ->with('success', 'Пользователь успешно удален');
        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка при удалении пользователя: ' . $e->getMessage());
        }
    }

    /**
     * Toggle user status (active/inactive)
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        try {
            // Prevent deactivating yourself
            if ($user->id === auth()->id()) {
                return back()->with('error', 'Нельзя деактивировать самого себя');
            }

            $user->update(['is_active' => !$user->is_active]);

            $status = $user->is_active ? 'активирован' : 'деактивирован';
            return back()->with('success', "Пользователь {$status}");
        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка при изменении статуса: ' . $e->getMessage());
        }
    }
}
