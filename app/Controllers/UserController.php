<?php

namespace App\Controllers;

use App\Models\User;

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
} 