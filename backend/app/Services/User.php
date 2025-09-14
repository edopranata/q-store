<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class User
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Get all Users.
     */
    public function getAllUsers(array $with = []): Collection
    {
        return $this->userRepository->getAll($with);
    }

    /**
     * Get paginated Users with filters.
     */
    public function getPaginatedUsers(array $filters = [], array $with = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->getPaginated($filters, $with, $perPage);
    }

    /**
     * Get User by ID.
     */
    public function getUserById(int $id, array $with = []): ?User
    {
        return $this->userRepository->findById($id, $with);
    }

    /**
     * Get active Users for options.
     */
    public function getActiveUsersForOptions(): Collection
    {
        return $this->userRepository->getActiveUsers()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                // Add other fields as needed
            ];
        });
    }

    /**
     * Create a new User.
     */
    public function createUser(array $data): User
    {
        $this->validateUserData($data);
        
        return $this->userRepository->create($data);
    }

    /**
     * Update an existing User.
     */
    public function updateUser(int $id, array $data): User
    {
        $user = $this->getUserById($id);
        
        if (!$user) {
            throw new \Exception('User not found');
        }

        $this->validateUserData($data, $id);
        
        return $this->userRepository->update($user, $data);
    }

    /**
     * Delete a User.
     */
    public function deleteUser(int $id): bool
    {
        $user = $this->getUserById($id);
        
        if (!$user) {
            throw new \Exception('User not found');
        }

        // Check if User is being used
        if ($this->isUserInUse($id)) {
            throw new \Exception('Cannot delete User that is being used');
        }

        return $this->userRepository->delete($user);
    }

    /**
     * Search Users.
     */
    public function searchUsers(string $query, array $with = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->search($query, $with, $perPage);
    }

    /**
     * Get User statistics.
     */
    public function getUserStatistics(): array
    {
        return $this->userRepository->getStatistics();
    }

    /**
     * Check if User is in use.
     */
    public function isUserInUse(int $userId): bool
    {
        // Implement your business logic to check if the User is being used
        // Example: return $this->userRepository->hasRelatedRecords($userId);
        return false;
    }

    /**
     * Validate User data.
     */
    protected function validateUserData(array $data, ?int $excludeId = null): void
    {
        // Implement your validation logic here
        // Example:
        // if (isset($data['name']) && $this->userRepository->nameExists($data['name'], $excludeId)) {
        //     throw ValidationException::withMessages([
        //         'name' => ['The User name has already been taken.']
        //     ]);
        // }
    }

    /**
     * Get Users by status.
     */
    public function getUsersByStatus(string $status, array $with = []): Collection
    {
        return $this->userRepository->getByStatus($status, $with);
    }

    /**
     * Get active Users.
     */
    public function getActiveUsers(array $with = []): Collection
    {
        return $this->userRepository->getActiveUsers($with);
    }
}