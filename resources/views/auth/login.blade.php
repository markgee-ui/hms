@extends('layouts.guests')

@section('title', 'Login')

@section('content')
<div class="flex items-center justify-center min-h-screen -mt-20">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-2xl rounded-xl p-8 sm:p-10 border border-gray-100">
            <div class="text-center">
                <h1 class="text-3xl font-extrabold text-gray-900 mb-2">
                    Hospital Management System
                </h1>
                <p class="text-sm text-gray-500">
                    Outpatient Module Access
                </p>
            </div>
            
            <form class="mt-8 space-y-6" method="POST" action="{{ route('login') }}">
                @csrf
                
                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required autofocus
                           class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           placeholder="you@hospital.com" value="{{ old('email') }}">
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                           class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           placeholder="••••••••">
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password (optional) -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox"
                               class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <label for="remember" class="ml-2 block text-sm text-gray-900">
                            Remember me
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500 transition duration-150">
                            Forgot your password?
                        </a>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-md text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 transform hover:scale-[1.01]">
                        Sign in to Dashboard
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
