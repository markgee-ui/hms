@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="max-w-3xl mx-auto bg-white shadow-xl rounded-lg p-6">
        
        <!-- Header with Back Button -->
        <div class="flex items-center justify-between border-b pb-3 mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Edit Staff Member: {{ $user->name }}</h1>
            <a href="{{ route('admin.users.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg shadow-sm text-gray-700 bg-white hover:bg-gray-50 transition duration-150 transform hover:scale-[1.02]">
                <i class="fas fa-arrow-left mr-2"></i> Back to User List
            </a>
        </div>
        
        <!-- Update User Details Form -->
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="mb-10">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div class="col-span-1">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                           class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="col-span-1">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                           class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role -->
                <div class="col-span-2">
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select name="role" id="role" required
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('role') border-red-500 @enderror">
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" {{ old('role', $user->role) == $role ? 'selected' : '' }}>
                                {{ ucfirst($role) }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-8 pt-6 border-t flex justify-end">
                <button type="submit"
                        class="px-6 py-2 bg-green-600 text-white font-semibold rounded-lg shadow-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition duration-150 ease-in-out">
                    Update User Details
                </button>
            </div>
        </form>
        
        <!-- Password Reset Section (Separate form is necessary for correct HTTP method/validation) -->
        <h2 class="text-xl font-bold text-gray-800 border-b pb-3 mb-6 mt-10">Reset Password</h2>
        <p class="text-sm text-gray-600 mb-4">Set a new password for this user. Requires confirmation.</p>
        
        <form action="{{ route('admin.users.password.update', $user->id) }}" method="POST">
             @csrf
             @method('PATCH')
             
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-1">
                    <label for="password_new" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <input type="password" name="password_new" id="password_new" required
                           class="w-full border-gray-300 rounded-md shadow-sm focus:border-yellow-500 focus:ring-yellow-500 @error('password_new') border-red-500 @enderror">
                    @error('password_new')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="col-span-1">
                    <label for="password_new_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                    <input type="password" name="password_new_confirmation" id="password_new_confirmation" required
                           class="w-full border-gray-300 rounded-md shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                </div>
            </div>

            <div class="mt-6 pt-6 border-t flex justify-end">
                 <button type="submit"
                        class="px-6 py-2 bg-yellow-600 text-white font-semibold rounded-lg shadow-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition duration-150 ease-in-out">
                    Reset Password
                </button>
            </div>
        </form>

    </div>
</div>
@endsection