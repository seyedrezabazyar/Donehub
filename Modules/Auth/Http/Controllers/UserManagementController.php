<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Models\User;
use Modules\Auth\Http\Requests\UpdateUserRequest;
use Modules\Auth\Http\Requests\ResetPasswordRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Modules\Auth\Http\Resources\UserCollection;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users with pagination and search
     */
    public function index(Request $request): UserCollection
    {
        $request->validate([
            'per_page' => 'nullable|integer|min:1|max:100',
            'sort_by' => ['nullable', 'string', Rule::in(['created_at', 'name', 'last_login_at'])],
            'sort_order' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'search' => 'nullable|string|max:255',
        ]);

        $query = User::with('roles');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->input('per_page', 15);
        $users = $query->paginate($perPage);

        return new UserCollection($users);
    }

    /**
     * Display the specified user
     */
    public function show($id): JsonResponse
    {
        $user = User::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(UpdateUserRequest $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $validated = $request->validated();

        // Handle password update
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        // Update user
        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'کاربر با موفقیت به‌روزرسانی شد',
            'data' => $user
        ]);
    }

    /**
     * Remove the specified user
     */
    public function destroy($id): JsonResponse
    {
        $user = User::findOrFail($id);
        
        // Prevent deleting yourself
        if (auth()->id() === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'شما نمی‌توانید حساب کاربری خود را حذف کنید'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'کاربر با موفقیت حذف شد'
        ]);
    }

    /**
     * Get user statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_users' => User::count(),
            'verified_emails' => User::whereNotNull('email_verified_at')->count(),
            'verified_phones' => User::whereNotNull('phone_verified_at')->count(),
            'users_with_password' => User::whereNotNull('password')->count(),
            'recent_registrations' => User::where('created_at', '>=', now()->subDays(7))->count(),
            'locked_accounts' => User::where('locked_until', '>', now())->count()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Toggle user account lock status
     */
    public function toggleLock($id): JsonResponse
    {
        $user = User::findOrFail($id);
        
        // Prevent locking yourself
        if (auth()->id() === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'شما نمی‌توانید حساب کاربری خود را قفل کنید'
            ], 403);
        }

        if ($user->locked_until && $user->locked_until->isFuture()) {
            // Unlock the user
            $user->update([
                'locked_until' => null,
                'failed_attempts' => 0
            ]);
            $message = 'حساب کاربری باز شد';
        } else {
            // Lock the user for 24 hours
            $user->update([
                'locked_until' => now()->addHours(24)
            ]);
            $message = 'حساب کاربری قفل شد';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $user
        ]);
    }

    /**
     * Reset user password
     */
    public function resetPassword(ResetPasswordRequest $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $validated = $request->validated();

        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        // Revoke all tokens to force re-login
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'رمز عبور با موفقیت بازنشانی شد'
        ]);
    }

    /**
     * Verify user email manually
     */
    public function verifyEmail($id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'ایمیل قبلاً تأیید شده است'
            ], 400);
        }

        $user->update([
            'email_verified_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'ایمیل با موفقیت تأیید شد',
            'data' => $user
        ]);
    }

    /**
     * Verify user phone manually
     */
    public function verifyPhone($id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->phone_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'شماره تلفن قبلاً تأیید شده است'
            ], 400);
        }

        $user->update([
            'phone_verified_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'شماره تلفن با موفقیت تأیید شد',
            'data' => $user
        ]);
    }
}