{{-- Assumes $data is passed, containing staff data (e.g., $data['staff']) --}}

<div class="space-y-8">
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- System Metrics Card (Example) -->
        <div class="bg-white p-6 rounded-xl shadow-2xl border-l-4 border-indigo-500">
            <p class="text-sm font-medium text-gray-500">Total Visits Today</p>
            <p class="text-3xl font-extrabold text-gray-900 mt-1">{{ $data['totalVisits'] ?? 'N/A' }}</p>
        </div>

        <!-- Average Consultation Time Card (Example) -->
        <div class="bg-white p-6 rounded-xl shadow-2xl border-l-4 border-green-500">
            <p class="text-sm font-medium text-gray-500">Avg. Consultation Time</p>
            <p class="text-3xl font-extrabold text-gray-900 mt-1">{{ $data['avgConsultTime'] ?? 'N/A' }} min</p>
        </div>

        <!-- Pending Billing Card (Example) -->
        <div class="bg-white p-6 rounded-xl shadow-2xl border-l-4 border-red-500">
            <p class="text-sm font-medium text-gray-500">Unpaid Bills Count</p>
            <p class="text-3xl font-extrabold text-gray-900 mt-1">{{ $data['unpaidBills'] ?? 'N/A' }}</p>
        </div>
    </div>

    <!-- User Management Section -->
    <div class="bg-white shadow-2xl rounded-xl p-6">
        <h3 class="text-2xl font-semibold mb-4 text-gray-800 border-b pb-2 flex justify-between items-center">
            Staff & User Management
            <a href="{{ route('admin.users.create') }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-md text-white bg-blue-600 hover:bg-blue-700 transition duration-150 transform hover:scale-[1.02]">
                <i class="fas fa-user-plus mr-2"></i> Add New Staff
            </a>
        </h3>

        <!-- Staff List Table -->
        <div class="overflow-x-auto border border-gray-300 rounded-lg">
            <table class="min-w-full border-collapse">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r">Role</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-300">
                    {{-- Loop through the staff data --}}
                    @forelse ($data['staff'] ?? [] as $user)
                    <tr class="hover:bg-gray-50 transition duration-100">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 border-r border-gray-300">{{ $user->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-500 border-r border-gray-300">{{ $user->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap border-r border-gray-300">
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $user->role == 'doctor' ? 'bg-indigo-100 text-indigo-800' : ($user->role == 'nurse' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                            <div class="flex flex-wrap justify-center gap-2">
                                <a href="{{ route('admin.users.edit', $user->id) }}" 
                                   class="inline-flex items-center px-3 py-1.5 bg-purple-500 text-white text-xs font-semibold rounded-lg hover:bg-purple-600 transition">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </a>
                                <form action="{{ route('admin.users.delete', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="inline-flex items-center px-3 py-1.5 bg-red-500 text-white text-xs font-semibold rounded-lg hover:bg-red-600 transition">
                                        <i class="fas fa-trash-alt mr-1"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500 text-lg border-t border-gray-300">
                            <i class="fas fa-users-slash mr-2"></i> No staff users found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>