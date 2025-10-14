<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>HMS Outpatient Module - @yield('title', 'Dashboard')</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Inter Font Family -->
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
    <!-- Font Awesome for Icons (Essential for Dashboard Flow) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" 
          xintegrity="sha512-SnH5WK+bZxgPHs44uWIX+LLMD/cd2f7gYtJd8Ym+8gYp4H5U6fH9M1S6iUa8b/eP+X+G5z9x/u2k5z5t5+g==" 
          crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
</head>
<body class="bg-gray-50 antialiased">

    <div id="app" class="min-h-screen flex">
        
        <!-- Sidebar Navigation (Fixed width for desktop, hidden on mobile) -->
        <aside class="w-64 bg-gray-800 text-white p-4 hidden md:block shadow-2xl">
            <div class="text-2xl font-extrabold mb-8 border-b border-gray-700 pb-4 text-white">
                HMS OP Module
            </div>
            
            <!-- Navigation Links -->
            <nav class="space-y-2">
                <a href="/dashboard/outpatient" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-gray-700 transition duration-150 @if(request()->is('dashboard/*')) bg-gray-700 @endif">
                    <i class="fas fa-chart-line text-lg"></i>
                    <span>Dashboard</span>
                </a>
                
                @if(Auth::check() && Auth::user()->role === 'receptionist')
                <a href="/outpatient/register" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-gray-700 transition duration-150 @if(request()->is('outpatient/register')) bg-gray-700 @endif">
                    <i class="fas fa-user-plus text-lg"></i>
                    <span>Registration</span>
                </a>
                @endif
                
                @if(Auth::check() && Auth::user()->role === 'doctor')
                <a href="{{ route('consultation.laboratory_queue') }}" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-gray-700 transition duration-150 @if(request()->is('outpatient/consulation/labs*')) bg-gray-700 @endif">
                <i class="fas fa-flask text-lg"></i>
                <span>Labs</span>
                </a>
                @endif

                @if(Auth::check() && Auth::user()->role === 'doctor')
                <a href="{{ route('consultation.history') }}" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-gray-700 transition duration-150 @if(request()->is('outpatient/consultation/prescriptions')) bg-gray-700 @endif">
                <i class="fas fa-prescription-bottle-alt text-lg"></i>
                <span>Prescriptions</span>
                </a>
                @endif  
                <!-- Lab Queue Link (Lab Technician) -->
                @if(Auth::check() && Auth::user()->role === 'labtech')
                <a href="{{ route('lab.dashboard') }}" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-gray-700 transition duration-150 @if(request()->is('lab/queue*') || request()->is('lab/requests/*')) bg-gray-700 @endif">
                <i class="fas fa-microscope text-lg"></i>
                <span>Lab Queue</span>
                </a>
                @endif          
                <a href="#" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-gray-700 transition duration-150">
                    <i class="fas fa-cog text-lg"></i>
                    <span>Settings</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto">
            <!-- Top Bar/Header (Mobile & User Info) -->
        <header class="bg-white shadow-md p-4 flex items-center justify-between sticky top-0 z-10">


        <button id="sidebarToggle" class="text-gray-600 md:hidden p-2 rounded-lg hover:bg-gray-100">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Search bar -->
        <div class="relative w-full max-w-xs md:max-w-md ml-4">
            <input
                type="text"
                id="globalSearch"
                placeholder="Search patient name or visit token..."
                class="w-full border border-gray-300 rounded-lg py-2 pl-10 pr-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400"></i>

            <!-- Search results dropdown -->
            <div id="searchResults"
                class="hidden absolute mt-2 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto z-50">
                <p class="text-gray-400 text-sm text-center p-3">Start typing to search...</p>
            </div>
        </div>

        <!-- Notifications + Profile -->
        <div class="flex items-center space-x-4">
            <!-- Notifications -->
            <div class="relative">
                <button id="notifBtn" class="relative p-2 rounded-full hover:bg-gray-100">
                    <i class="fas fa-bell text-gray-600 text-lg"></i>
                    <span id="notifCount"
                        class="hidden absolute -top-1 -right-1 bg-red-600 text-white text-xs font-bold px-1.5 py-0.5 rounded-full">
                        0
                    </span>
                </button>

                <div id="notifDropdown"
                    class="hidden absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-lg shadow-lg z-50">
                    <div class="p-3 border-b font-semibold text-gray-700 flex justify-between items-center">
                        <span>Notifications</span>
                        <button id="clearNotif"
                            class="text-xs text-blue-600 hover:underline">Mark all read</button>
                    </div>
                    <ul id="notifList" class="max-h-64 overflow-y-auto text-sm text-gray-700">
                        <li class="p-3 text-gray-500 text-sm text-center">Loading...</li>
                    </ul>
                    <div class="p-3 text-center text-sm text-blue-600 hover:underline cursor-pointer">View all</div>
                </div>
            </div>

            <!-- Profile -->
            <div class="flex items-center space-x-3">
                @if(Auth::check())
                    <span class="text-sm text-gray-600 font-medium hidden sm:block">
                        Welcome, {{ Auth::user()->name }} ({{ ucfirst(Auth::user()->role) }})
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="p-2 text-sm text-red-600 hover:text-red-800 rounded-lg hover:bg-red-50 transition duration-150">
                            <i class="fas fa-sign-out-alt mr-1"></i> Logout
                        </button>
                    </form>
                @endif
            </div>
        </div>
        </header>


            <!-- Session Messages (Success, Error) -->
            <div class="p-6">
                @if (session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-4 shadow-sm" role="alert">
                        <p class="font-bold">Success!</p>
                        <p>{{ session('success') }}</p>
                    </div>
                @endif

                @if (session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-4 shadow-sm" role="alert">
                        <p class="font-bold">Error!</p>
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-4 shadow-sm">
                        <p class="font-bold">Whoops! There were some problems with your input.</p>
                        <ul class="mt-2 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <!-- Content Section Yield -->
            <div class="px-6 pb-6">
                @yield('content')
            </div>

        </main>
    </div>

    <!-- Simple JS for Mobile Sidebar Toggle (Vanilla JS) -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.querySelector('aside');
            const toggleButton = document.getElementById('sidebarToggle');
            
            if (toggleButton) {
                toggleButton.addEventListener('click', function () {
                    // Toggles 'hidden' class on mobile for the sidebar
                    sidebar.classList.toggle('hidden');
                    sidebar.classList.toggle('absolute'); // Overlay effect on mobile
                    sidebar.classList.toggle('z-20');
                });
            }
        });
    </script>
    <script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('globalSearch');
    const searchResults = document.getElementById('searchResults');
    let timeout = null;
    
    // Helper function to generate Laravel URL using the route name
    // This assumes the routes are defined in web.php and accessible via route().
    function getRouteUrl(name, params = {}) {
        let url = `{{ url('/') }}`;
        
        // This is a minimal way to replicate Laravel's route() helper in JS.
        // For 'patient.view', we replace {id} with the patient ID.
        if (name === 'patient.view') {
            url += `/outpatient/patient/view/${params.id}`;
        } 
        // For other links (like triage.start, etc.), you'd add more cases here.
        // Example: else if (name === 'triage.start') { url += `/triage/start/${params.visit_token}`; }

        return url;
    }

    searchInput.addEventListener('input', function () {
        clearTimeout(timeout);
        const query = this.value.trim();

        if (query.length < 2) {
            searchResults.innerHTML = '<p class="text-gray-400 text-sm text-center p-3">Type at least 2 characters...</p>';
            searchResults.classList.remove('hidden');
            return;
        }
        
        // Show loading state while waiting for API
        searchResults.innerHTML = '<li class="p-3 text-gray-500 text-center flex justify-center items-center"><i class="fas fa-spinner fa-spin mr-2"></i> Searching...</li>';
        searchResults.classList.remove('hidden');


        timeout = setTimeout(async () => {
            try {
                // Ensure correct route access using blade helper
                const searchUrl = `{{ route('search.index') }}?q=${encodeURIComponent(query)}`;
                const res = await fetch(searchUrl);
                
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }

                const data = await res.json();
                searchResults.innerHTML = '';
                
                if (data.patients.length === 0 && data.visits.length === 0) {
                    searchResults.innerHTML = '<p class="text-gray-500 text-center p-3">No results found.</p>';
                } else {
                    if (data.patients.length > 0) {
                        searchResults.innerHTML += `<li class="px-4 py-2 text-xs uppercase text-gray-400">Patients</li>`;
                        data.patients.forEach(p => {
                            // FIX: Added <a> tag with dynamic URL to make result clickable
                            const patientUrl = getRouteUrl('patient.view', { id: p.id });
                            searchResults.innerHTML += `
                                <a href="${patientUrl}" class="block">
                                    <li class="p-3 border-b hover:bg-blue-50 cursor-pointer transition">
                                        <div class="text-sm font-semibold text-gray-800">${p.name}</div>
                                        <div class="text-xs text-gray-500">ID: ${p.id} | DOB: ${p.dob || 'N/A'}</div>
                                    </li>
                                </a>`;
                        });
                    }

                    if (data.visits.length > 0) {
                        searchResults.innerHTML += `<li class="px-4 py-2 text-xs uppercase text-gray-400">Visits</li>`;
                        data.visits.forEach(v => {
                            // FIX: Added <a> tag with dynamic URL to make result clickable
                            // Since a visit is linked to a patient, we can link to the patient view if patient_id is available (must be retrieved by controller)
                            // Assuming the SearchController was fixed to include patient data in the visit object if possible, 
                            // but linking to a workflow action (like Triage) using visit_token is often better.
                            
                            // Let's link to the triage start route if the status is Registered
                            let visitUrl;
                            let actionText;
                            
                            if (v.status === 'Registered') {
                                // Assuming v.visit_token is available
                                visitUrl = `{{ url('/triage/start') }}/${v.visit_token}`; 
                                actionText = 'Start Triage';
                            } else if (v.status === 'Triage') {
                                visitUrl = `{{ url('/consultation/start') }}/${v.visit_token}`;
                                actionText = 'Start Consultation';
                            } else {
                                // Fallback: Link to the general search patient view (Requires patient_id in $visits array, which the controller should provide)
                                // NOTE: If patient_id is NOT available in the $visits array from the controller, this fallback will fail.
                                // It is recommended to eagerly load patient data in the SearchController for visits.
                                visitUrl = '#'; // Fallback to non-clickable for now to prevent errors
                                actionText = `Status: ${v.status}`;
                            }
                            
                            searchResults.innerHTML += `
                                <a href="${visitUrl}" class="block">
                                    <li class="p-3 border-b hover:bg-green-50 cursor-pointer transition">
                                        <div class="text-sm font-semibold text-gray-800">Token: ${v.visit_token}</div>
                                        <div class="text-xs text-gray-500">Action: ${actionText} | Reg Date: ${v.registration_date}</div>
                                    </li>
                                </a>`;
                        });
                    }
                }

                searchResults.classList.remove('hidden');

            } catch (error) {
                console.error('Search failed:', error);
                searchResults.innerHTML = '<p class="text-red-500 text-center p-3">Error fetching search results.</p>';
                searchResults.classList.remove('hidden');
            }
        }, 300);
    });

    // Hide dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!searchResults.contains(e.target) && e.target !== searchInput) {
            searchResults.classList.add('hidden');
        }
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const notifBtn = document.getElementById('notifBtn');
    const notifDropdown = document.getElementById('notifDropdown');
    const notifList = document.getElementById('notifList');
    const notifCount = document.getElementById('notifCount');
    const clearNotif = document.getElementById('clearNotif');

    // Fetch notifications dynamically
    async function loadNotifications() {
        try {
            // FIX: Ensure the API key is being sent if required, and explicitly handle network/JSON errors
            const res = await fetch('{{ route("notifications.fetch") }}');
            
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            
            const data = await res.json();
            
            notifList.innerHTML = '';
            if (data.notifications.length === 0) {
                notifList.innerHTML = '<li class="p-3 text-gray-500 text-center">No new notifications</li>';
            } else {
                data.notifications.forEach(n => {
                    // FIX: Ensure 'is_read' check is used to change background color
                    const bgColor = n.is_read ? 'hover:bg-gray-100' : 'bg-blue-50 hover:bg-blue-100';
                    const fontColor = n.is_read ? 'text-gray-600' : 'text-gray-800 font-semibold';
                    
                    // FIX: Added <a> tag for navigation
                    const link = n.link || '#';
                    
                    notifList.innerHTML += `
                        <a href="${link}" class="block ${bgColor} transition duration-100 border-b">
                            <li class="p-3">
                                <div class="flex items-start space-x-2">
                                    <i class="fas fa-${n.icon} text-blue-500 mt-1"></i>
                                    <div>
                                        <p class="${fontColor}">${n.title}</p>
                                        <p class="text-gray-600 text-xs">${n.message || ''}</p>
                                        <span class="text-gray-400 text-xs">${new Date(n.created_at).toLocaleString()}</span>
                                    </div>
                                </div>
                            </li>
                        </a>`;
                });
            }

            if (data.unreadCount > 0) {
                notifCount.classList.remove('hidden');
                notifCount.textContent = data.unreadCount;
            } else {
                notifCount.classList.add('hidden');
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
            // Show a friendly error message in the dropdown
            notifList.innerHTML = '<li class="p-3 text-red-500 text-center text-sm">Error fetching notifications. Check console.</li>';
        }
    }

    // Auto-load on page load
    loadNotifications();

    // Refresh every 60 seconds
    setInterval(loadNotifications, 60000);

    // Toggle dropdown
    notifBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        notifDropdown.classList.toggle('hidden');
    });

    // Close when clicked outside
    document.addEventListener('click', (e) => {
        if (!notifDropdown.contains(e.target) && !notifBtn.contains(e.target)) {
            notifDropdown.classList.add('hidden');
        }
    });

    // Clear all (mark as read)
    clearNotif.addEventListener('click', async () => {
        try {
            const res = await fetch('{{ route("notifications.fetch") }}?mark_read=1');
            if (res.ok) {
                // If successful, update the UI without reloading
                notifList.innerHTML = '<li class="p-3 text-gray-500 text-center">No new notifications</li>';
                notifCount.classList.add('hidden');
            } else {
                 console.error('Failed to mark notifications as read:', res.status);
            }
        } catch (error) {
            console.error('Error marking notifications as read:', error);
        }
    });
});
</script>

<!-- Footer -->
<footer class="bg-gray-200 text-center py-4 text-gray-600">
    &copy; {{ date('Y') }} Taison Group Limited. All rights reserved.
</footer>
</body>
</html>
