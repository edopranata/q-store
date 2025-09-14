<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\User;
use App\Services\UserService;
use App\Services\BaseResponseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ApiResponseTrait;

    protected UserService $userService;
    protected BaseResponseService $responseService;

    public function __construct(UserService $userService, BaseResponseService $responseService)
    {
        $this->userService = $userService;
        $this->responseService = $responseService;
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request): JsonResponse
    {
        return $this->handleRequest(function () use ($request) {
            // Validate request parameters
            $validated = $request->validate([
                'page' => 'sometimes|integer|min:1',
                'per_page' => 'sometimes|integer|min:1|max:100',
                'search' => 'sometimes|string|max:255',
                'status' => 'sometimes|in:true,false,1,0,active,inactive',
                'role' => 'sometimes|nullable|string|max:255',
                'created_from' => 'sometimes|date',
                'created_to' => 'sometimes|date|after_or_equal:created_from',
                'last_login_from' => 'sometimes|date',
                'last_login_to' => 'sometimes|date|after_or_equal:last_login_from',
                'sort_by' => 'sometimes|string|in:id,name,email,username,full_name,is_active,created_at,updated_at,last_login',
                'sort_order' => 'sometimes|string'
            ]);
            
            // Normalize sort_order to lowercase and validate
            if (isset($validated['sort_order'])) {
                $validated['sort_order'] = strtolower($validated['sort_order']);
                if (!in_array($validated['sort_order'], ['asc', 'desc'])) {
                    throw ValidationException::withMessages([
                        'sort_order' => ['The selected sort order is invalid.']
                    ]);
                }
            }

            // Extract filters from validated request
            $filters = [
                'status' => $validated['status'] ?? null,
                'role' => isset($validated['role']) && trim($validated['role']) !== '' ? $validated['role'] : null,
                'search' => $validated['search'] ?? null,
                'created_from' => $validated['created_from'] ?? null,
                'created_to' => $validated['created_to'] ?? null,
                'last_login_from' => $validated['last_login_from'] ?? null,
                'last_login_to' => $validated['last_login_to'] ?? null,
                'sort_by' => $validated['sort_by'] ?? 'created_at',
                'sort_order' => $validated['sort_order'] ?? 'desc'
            ];

            // Remove null values (but keep empty strings for role to allow filtering)
            $filters = array_filter($filters, fn($value) => $value !== null);

            // Set relationships to load
            $with = ['roles'];
            $perPage = min($validated['per_page'] ?? 15, 100);
            
            // Get paginated users
            $users = $this->userService->getPaginatedUsers($filters, $with, $perPage);
            
            return $this->paginatedResponse(
                $users,
                'Users retrieved successfully'
            );
        });
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): JsonResponse
    {
        return $this->handleRequest(function () use ($request) {
            // Validate request data
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:100|unique:users,email',
                'username' => 'required|string|max:50|alpha_dash|unique:users,username',
                'password' => 'required|string|min:8|confirmed',
                'full_name' => 'required|string|max:100',
                'is_active' => 'boolean',
                'roles' => 'array',
                'roles.*' => 'string|exists:roles,name'
            ]);

            // Create user through service
            $user = $this->userService->createUser($validated);

            return $this->successResponse(
                $user,
                'User created successfully',
                201
            );
        });
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        return $this->handleRequest(function () use ($user) {
            // Load relationships
            $user->load(['roles']);
            
            return $this->successResponse(
                $user,
                'User retrieved successfully'
            );
        });
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        return $this->handleRequest(function () use ($request, $user) {
            // Validate request data
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|max:100|unique:users,email,' . $user->id,
                'full_name' => 'sometimes|required|string|max:100',
                'is_active' => 'boolean',
                'roles' => 'array',
                'roles.*' => 'string|exists:roles,name'
            ]);

            // Update user through service
            $updatedUser = $this->userService->updateUser($user, $validated);

            return $this->successResponse(
                $updatedUser,
                'User updated successfully'
            );
        });
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): JsonResponse
    {
        return $this->handleRequest(function () use ($user) {
            // Prevent deleting current user
            if ($user->id === Auth::id()) {
                return $this->forbiddenResponse(
                    'You cannot delete your own account'
                );
            }

            // Delete user through service
            $this->userService->deleteUser($user);

            return $this->successResponse(
                null,
                'User deleted successfully'
            );
        });
    }

    /**
     * Assign role to user.
     */
    public function assignRole(Request $request, User $user): JsonResponse
    {
        return $this->handleRequest(function () use ($request, $user) {
            // Validate request data
            $validated = $request->validate([
                'role' => 'required|string|exists:roles,name'
            ]);

            // Assign role through service
            $updatedUser = $this->userService->assignRoleToUser($user, $validated['role']);

            return $this->successResponse(
                $updatedUser,
                'Role assigned successfully'
            );
        });
    }

    /**
     * Remove role from user.
     */
    public function removeRole(Request $request, User $user): JsonResponse
    {
        return $this->handleRequest(function () use ($request, $user) {
            // Validate request data
            $validated = $request->validate([
                'role' => 'required|string|exists:roles,name'
            ]);

            // Remove role through service
            $updatedUser = $this->userService->removeRoleFromUser($user, $validated['role']);

            return $this->successResponse(
                $updatedUser,
                'Role removed successfully'
            );
        });
    }

    /**
     * Sync user roles.
     */
    public function syncRoles(Request $request, User $user): JsonResponse
    {
        return $this->handleRequest(function () use ($request, $user) {
            // Validate request data
            $validated = $request->validate([
                'roles' => 'required|array',
                'roles.*' => 'string|exists:roles,name'
            ]);

            // Sync roles through service
            $updatedUser = $this->userService->syncUserRoles($user, $validated['roles']);

            return $this->successResponse(
                $updatedUser,
                'Roles synchronized successfully'
            );
        });
    }

    /**
     * Toggle user status.
     */
    public function toggleStatus(User $user): JsonResponse
    {
        return $this->handleRequest(function () use ($user) {
            // Prevent deactivating current user
            if ($user->id === Auth::id() && $user->is_active) {
                return $this->forbiddenResponse(
                    'You cannot deactivate your own account'
                );
            }

            // Toggle status through service
            $updatedUser = $this->userService->toggleUserStatus($user);

            return $this->successResponse(
                $updatedUser,
                'User status updated successfully'
            );
        });
    }

    /**
     * Search users.
     */
    public function search(Request $request): JsonResponse
    {
        return $this->handleRequest(function () use ($request) {
            // Validate request data
            $validated = $request->validate([
                'query' => 'required|string|min:1|max:255'
            ]);

            // Search users through service
            $users = $this->userService->searchUsers($validated['query'], ['roles']);

            return $this->successResponse(
                $users,
                'Users found successfully'
            );
        });
    }

    /**
     * Get user statistics.
     */
    public function statistics(): JsonResponse
    {
        return $this->handleRequest(function () {
            // Get statistics through service
            $statistics = $this->userService->getUserStatistics();

            return $this->successResponse(
                $statistics,
                'User statistics retrieved successfully'
            );
        });
    }

    /**
     * Get available roles.
     */
    public function roles(): JsonResponse
    {
        return $this->handleRequest(function () {
            // Get available roles through service
            $roles = $this->userService->getAvailableRoles();

            return $this->successResponse(
                $roles,
                'Available roles retrieved successfully'
            );
        });
    }

    /**
     * Change user password.
     */
    public function changePassword(Request $request, User $user): JsonResponse
    {
        return $this->handleRequest(function () use ($request, $user) {
            // Validate request data
            $validated = $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed'
            ]);

            // Verify current password
            if (!Hash::check($validated['current_password'], $user->password)) {
                return $this->errorResponse(
                    'Current password is incorrect',
                    422
                );
            }

            // Change password through service
            $this->userService->changePassword($user, $validated['new_password']);

            return $this->successResponse(
                null,
                'Password changed successfully'
            );
        });
    }

    /**
     * Get users with transaction statistics.
     */
    public function getUsersWithStats(Request $request): JsonResponse
    {
        return $this->handleRequest(function () use ($request) {
            $validated = $request->validate([
                'page' => 'sometimes|integer|min:1',
                'per_page' => 'sometimes|integer|min:1|max:100',
                'date_from' => 'sometimes|date',
                'date_to' => 'sometimes|date|after_or_equal:date_from'
            ]);

            $filters = [
                'date_from' => $validated['date_from'] ?? null,
                'date_to' => $validated['date_to'] ?? null
            ];
            $perPage = $validated['per_page'] ?? 15;

            $users = $this->userService->getUsersWithTransactionStats($filters, $perPage);

            return $this->successResponse(
                $users,
                'Users with transaction statistics retrieved successfully'
            );
        });
    }

    /**
     * Get user performance metrics.
     */
    public function getPerformanceMetrics(Request $request): JsonResponse
    {
        return $this->handleRequest(function () use ($request) {
            $validated = $request->validate([
                'user_ids' => 'sometimes|array',
                'user_ids.*' => 'integer|exists:users,id',
                'date_from' => 'sometimes|date',
                'date_to' => 'sometimes|date|after_or_equal:date_from',
                'limit' => 'sometimes|integer|min:1|max:50'
            ]);

            $userIds = $validated['user_ids'] ?? [];
            $dateFrom = $validated['date_from'] ?? null;
            $dateTo = $validated['date_to'] ?? null;
            $limit = $validated['limit'] ?? 10;

            $metrics = $this->userService->getUserPerformanceMetrics($userIds, $dateFrom, $dateTo, $limit);

            return $this->successResponse(
                $metrics,
                'User performance metrics retrieved successfully'
            );
        });
    }

    /**
     * Get dashboard data for user.
     */
    public function getDashboardData(User $user): JsonResponse
    {
        return $this->handleRequest(function () use ($user) {
            $dashboardData = $this->userService->getUserDashboardData($user->id);

            return $this->successResponse(
                $dashboardData,
                'User dashboard data retrieved successfully'
            );
        });
    }

    /**
     * Get user activity timeline.
     */
    public function getActivityTimeline(Request $request, User $user): JsonResponse
    {
        return $this->handleRequest(function () use ($request, $user) {
            $validated = $request->validate([
                'days' => 'sometimes|integer|min:1|max:365',
                'limit' => 'sometimes|integer|min:1|max:100'
            ]);

            $days = $validated['days'] ?? 30;
            $limit = $validated['limit'] ?? 50;

            $timeline = $this->userService->getUserActivityTimeline($user->id, $days, $limit);

            return $this->successResponse(
                $timeline,
                'User activity timeline retrieved successfully'
            );
        });
    }

    /**
     * Get users with recent activity.
     */
    public function getRecentActivity(Request $request): JsonResponse
    {
        return $this->handleRequest(function () use ($request) {
            $validated = $request->validate([
                'hours' => 'sometimes|integer|min:1|max:168',
                'limit' => 'sometimes|integer|min:1|max:50'
            ]);

            $hours = $validated['hours'] ?? 24;
            $limit = $validated['limit'] ?? 20;

            $users = $this->userService->getUsersWithRecentActivity($hours, $limit);

            return $this->successResponse(
                $users,
                'Users with recent activity retrieved successfully'
            );
        });
    }

    /**
     * Bulk update user status.
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        return $this->handleRequest(function () use ($request) {
            $validated = $request->validate([
                'user_ids' => 'required|array|min:1',
                'user_ids.*' => 'integer|exists:users,id',
                'is_active' => 'required|boolean'
            ]);

            // Prevent deactivating current user
            if (!$validated['is_active'] && in_array(Auth::id(), $validated['user_ids'])) {
                return $this->forbiddenResponse(
                    'You cannot deactivate your own account'
                );
            }

            $result = $this->userService->bulkUpdateUserStatus(
                $validated['user_ids'],
                $validated['is_active']
            );

            return $this->successResponse(
                $result,
                'User status updated successfully'
            );
        });
    }

    /**
     * Get users eligible for deletion.
     */
    public function getUsersForDeletion(): JsonResponse
    {
        return $this->handleRequest(function () {
            $users = $this->userService->getUsersForDeletion();

            return $this->successResponse(
                $users,
                'Users eligible for deletion retrieved successfully'
            );
        });
    }

    /**
     * Check if user can be deleted.
     */
    public function canBeDeleted(User $user): JsonResponse
    {
        return $this->handleRequest(function () use ($user) {
            $canDelete = $this->userService->canUserBeDeleted($user->id);

            return $this->successResponse(
                ['can_delete' => $canDelete['can_delete'], 'reasons' => $canDelete['reasons']],
                $canDelete['can_delete'] ? 'User can be deleted' : 'User cannot be deleted'
            );
        });
    }

    /**
     * Get enhanced user statistics.
     */
    public function getEnhancedStatistics(): JsonResponse
    {
        return $this->handleRequest(function () {
            $statistics = $this->userService->getEnhancedUserStatistics();

            return $this->successResponse(
                $statistics,
                'Enhanced user statistics retrieved successfully'
            );
        });
    }
}