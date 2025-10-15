<!DOCTYPE html>

<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HMS - @yield('title', 'Career Portal')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])  

<!-- Google Fonts - Inter -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
<script src="https://cdn.tailwindcss.com"></script>
<!-- Custom Styles -->
<style>
    body {
        font-family: 'Poppins', sans-serif;
        
    }
</style>

</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

<!-- Top bar for the guest layout -->
  <!-- Header for Guest Layout (Login, Register) -->
    <header class="bg-white shadow p-4 flex justify-between items-center sticky top-0 z-10">
        <div class="flex items-center">
            <img src="{{ asset('images/hms-logo.png') }}"  alt="hms Logo"  class="h-10">
        </div>
    </header>

<!-- Main Content Area -->
<main class="flex-1 p-6">
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    @yield('content')
</main>

<!-- Footer -->
<footer class="bg-gray-200 text-center py-4 text-gray-600">
    &copy; {{ date('Y') }} Taison Group Limited. All rights reserved.
</footer>

</body>
</html>