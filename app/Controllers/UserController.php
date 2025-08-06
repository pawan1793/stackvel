<?php

namespace App\Controllers;

use App\Models\User;
use Stackvel\Request;

/**
 * User Controller
 * 
 * Handles user management functionality.
 */
class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(): string
    {
        $users = User::all();
        
        $data = [
            'title' => 'Users',
            'users' => $users
        ];

        return $this->view('users.index', $data);
    }

    /**
     * Display the specified user
     */
    public function show(string $id): string
    {
        $user = User::find($id);
        
        if (!$user) {
            $this->flash('error', 'User not found.');
            $this->redirect('/users');
            return '';
        }

        $data = [
            'title' => 'User Details',
            'user' => $user
        ];

        return $this->view('users.show', $data);
    }

    /**
     * Show the form for creating a new user
     */
    public function create(): string
    {
        $data = [
            'title' => 'Create User',
            'errors' => $this->getErrors(),
            'old' => $this->old()
        ];

        return $this->view('users.create', $data);
    }

    /**
     * Store a newly created user
     */
    public function store(): void
    {
        $data = $this->input();
        
        // Validate input
        $errors = $this->validate($data, [
            'name' => 'required|string|min:2',
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);

        if (!empty($errors)) {
            $this->redirect('/users/create');
            return;
        }

        // Check if email already exists
        $existingUser = User::whereFirst('email', $data['email']);
        if ($existingUser) {
            $this->flash('error', 'A user with this email already exists.');
            $this->redirect('/users/create');
            return;
        }

        // Create user
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $user = User::create($userData);

        if ($user) {
            $this->flash('success', 'User created successfully.');
            $this->redirect('/users');
        } else {
            $this->flash('error', 'Failed to create user.');
            $this->redirect('/users/create');
        }
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(string $id): string
    {
        $user = User::find($id);
        
        if (!$user) {
            $this->flash('error', 'User not found.');
            $this->redirect('/users');
            return '';
        }

        $data = [
            'title' => 'Edit User',
            'user' => $user,
            'errors' => $this->getErrors(),
            'old' => $this->old()
        ];

        return $this->view('users.edit', $data);
    }

    /**
     * Update the specified user
     */
    public function update(string $id): void
    {
        $user = User::find($id);
        
        if (!$user) {
            $this->flash('error', 'User not found.');
            $this->redirect('/users');
            return;
        }

        $data = $this->input();
        
        // Validate input
        $errors = $this->validate($data, [
            'name' => 'required|string|min:2',
            'email' => 'required|email'
        ]);

        if (!empty($errors)) {
            $this->redirect("/users/{$id}/edit");
            return;
        }

        // Check if email already exists (excluding current user)
        $existingUser = User::whereFirst('email', $data['email']);
        if ($existingUser && $existingUser->id != $id) {
            $this->flash('error', 'A user with this email already exists.');
            $this->redirect("/users/{$id}/edit");
            return;
        }

        // Update user
        $user->name = $data['name'];
        $user->email = $data['email'];
        
        if (!empty($data['password'])) {
            $user->password = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $user->updated_at = date('Y-m-d H:i:s');

        if ($user->save()) {
            $this->flash('success', 'User updated successfully.');
            $this->redirect('/users');
        } else {
            $this->flash('error', 'Failed to update user.');
            $this->redirect("/users/{$id}/edit");
        }
    }

    /**
     * Remove the specified user
     */
    public function destroy(string $id): void
    {
        $user = User::find($id);
        
        if (!$user) {
            $this->flash('error', 'User not found.');
            $this->redirect('/users');
            return;
        }

        if ($user->delete()) {
            $this->flash('success', 'User deleted successfully.');
        } else {
            $this->flash('error', 'Failed to delete user.');
        }

        $this->redirect('/users');
    }

    // API Methods

    /**
     * API: Display a listing of users
     */
    public function apiIndex(): array
    {
        $users = User::all();
        
        return $this->success([
            'users' => array_map(function ($user) {
                return $user->toArray();
            }, $users)
        ]);
    }

    /**
     * API: Display the specified user
     */
    public function apiShow(string $id): array
    {
        $user = User::find($id);
        
        if (!$user) {
            return $this->error('User not found.', 404);
        }

        return $this->success([
            'user' => $user->toArray()
        ]);
    }

    /**
     * API: Store a newly created user
     */
    public function apiStore(): array
    {
        $data = $this->input();
        
        // Validate input
        $errors = $this->validate($data, [
            'name' => 'required|string|min:2',
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);

        if (!empty($errors)) {
            return $this->error('Validation failed.', 422);
        }

        // Check if email already exists
        $existingUser = User::whereFirst('email', $data['email']);
        if ($existingUser) {
            return $this->error('A user with this email already exists.', 409);
        }

        // Create user
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $user = User::create($userData);

        if ($user) {
            return $this->success([
                'user' => $user->toArray()
            ], 'User created successfully.');
        } else {
            return $this->error('Failed to create user.', 500);
        }
    }

    /**
     * API: Update the specified user
     */
    public function apiUpdate(string $id): array
    {
        $user = User::find($id);
        
        if (!$user) {
            return $this->error('User not found.', 404);
        }

        $data = $this->input();
        
        // Validate input
        $errors = $this->validate($data, [
            'name' => 'required|string|min:2',
            'email' => 'required|email'
        ]);

        if (!empty($errors)) {
            return $this->error('Validation failed.', 422);
        }

        // Check if email already exists (excluding current user)
        $existingUser = User::whereFirst('email', $data['email']);
        if ($existingUser && $existingUser->id != $id) {
            return $this->error('A user with this email already exists.', 409);
        }

        // Update user
        $user->name = $data['name'];
        $user->email = $data['email'];
        
        if (!empty($data['password'])) {
            $user->password = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $user->updated_at = date('Y-m-d H:i:s');

        if ($user->save()) {
            return $this->success([
                'user' => $user->toArray()
            ], 'User updated successfully.');
        } else {
            return $this->error('Failed to update user.', 500);
        }
    }

    /**
     * API: Remove the specified user
     */
    public function apiDestroy(string $id): array
    {
        $user = User::find($id);
        
        if (!$user) {
            return $this->error('User not found.', 404);
        }

        if ($user->delete()) {
            return $this->success([], 'User deleted successfully.');
        } else {
            return $this->error('Failed to delete user.', 500);
        }
    }

    /**
     * Example method demonstrating Request parameter injection
     * This method accepts a Request object as its first parameter
     */
    public function exampleWithRequest(Request $request, string $id): array
    {
        // Access request data using the Request object
        $name = $request->input('name');
        $email = $request->input('email');
        $isAjax = $request->isAjax();
        $userAgent = $request->userAgent();
        $ip = $request->ip();
        
        // Get route parameter
        $userId = $request->parameter('id', $id);
        
        // Get query parameters
        $page = $request->query('page', 1);
        $limit = $request->query('limit', 10);
        
        // Check if request expects JSON
        if ($request->expectsJson()) {
            return $this->success([
                'name' => $name,
                'email' => $email,
                'is_ajax' => $isAjax,
                'user_agent' => $userAgent,
                'ip' => $ip,
                'user_id' => $userId,
                'page' => $page,
                'limit' => $limit
            ], 'Request processed successfully');
        }
        
        // For non-JSON requests, you could return a view
        return $this->error('This endpoint expects JSON requests.', 400);
    }

    /**
     * Another example showing different ways to use Request object
     */
    public function advancedRequestExample(Request $request): array
    {
        // Get all input data
        $allData = $request->all();
        
        // Get only specific fields
        $userData = $request->only(['name', 'email', 'password']);
        
        // Get all except password
        $safeData = $request->except(['password']);
        
        // Check if specific fields exist
        $hasRequiredFields = $request->hasAll(['name', 'email']);
        $hasAnyOptionalFields = $request->hasAny(['phone', 'address']);
        
        // Get file upload
        $avatar = $request->file('avatar');
        $hasAvatar = $request->hasFile('avatar');
        
        // Get request information
        $method = $request->method();
        $path = $request->path();
        $url = $request->url();
        $isSecure = $request->isSecure();
        
        return $this->success([
            'request_info' => [
                'method' => $method,
                'path' => $path,
                'url' => $url,
                'is_secure' => $isSecure,
                'is_ajax' => $request->isAjax(),
                'expects_json' => $request->expectsJson()
            ],
            'input_data' => [
                'all_data' => $allData,
                'user_data' => $userData,
                'safe_data' => $safeData,
                'has_required_fields' => $hasRequiredFields,
                'has_optional_fields' => $hasAnyOptionalFields
            ],
            'file_upload' => [
                'has_avatar' => $hasAvatar,
                'avatar_info' => $avatar
            ]
        ]);
    }
} 