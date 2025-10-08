<div class="bg-white shadow-2xl rounded-xl p-6">
    <h3 class="text-2xl font-semibold mb-4 text-gray-800 border-b pb-2">
        Today's Patient Queue
    </h3>

    <!-- Top Actions -->
    <div class="flex flex-wrap items-center justify-between mb-4 gap-2">
        <!-- Register New Patient -->
        <a href="{{ route('outpatient.register') }}" 
           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-md text-white bg-blue-600 hover:bg-blue-700 transition duration-150 transform hover:scale-[1.01]">
            <i class="fas fa-user-plus mr-2"></i> Register New Patient
        </a>

        <!-- Filters and Search -->
        <form method="GET" action="{{ route('outpatient.dashboard') }}" class="flex flex-wrap items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Search patient name or token..." 
                   class="px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />

            <select name="status" class="px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">All Statuses</option>
                <option value="Registered" {{ request('status') == 'Registered' ? 'selected' : '' }}>Registered</option>
                <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
            </select>

            <button type="submit" class="px-3 py-2 bg-blue-500 text-white rounded-lg text-sm hover:bg-blue-600">
                <i class="fas fa-filter mr-1"></i> Apply
            </button>

            <a href="{{ route('outpatient.dashboard') }}" 
               class="px-3 py-2 bg-gray-300 rounded-lg text-sm hover:bg-gray-400">
                <i class="fas fa-times mr-1"></i> Clear
            </a>
        </form>
    </div>

    <!-- Patient Queue Table -->
    <div class="overflow-x-auto border border-gray-300 rounded-lg">
        <table class="min-w-full border-collapse">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300">Visit Token</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300">Patient Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300">Current Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300">Time Registered</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-300">
                @forelse ($queue as $visit)
                <tr class="hover:bg-gray-50 transition duration-100">
                    <td class="px-6 py-4 whitespace-nowrap font-bold text-blue-600 border-r border-gray-300">{{ $visit->visit_token }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-900 border-r border-gray-300">{{ $visit->patient->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap border-r border-gray-300">
                        @php
                            // Dynamic color based on status
                            $color = match($visit->status) {
                                'Registered' => 'yellow',
                                'Waiting for Triage' => 'blue',
                                default => 'green',
                            };
                        @endphp
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">
                            {{ $visit->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-r border-gray-300">
                        {{ $visit->registration_date->format('H:i A') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex flex-col space-y-1">
                            
                            <!-- View Info -->
                            <a href="{{ route('patient.view', $visit->patient->id) }}" 
                               class="text-indigo-600 hover:text-indigo-900 inline-flex items-center">
                                <i class="fas fa-eye mr-1 w-4"></i> View
                            </a>
                            
                            <!-- Edit Info -->
                            <a href="{{ route('patient.edit', $visit->patient->id) }}" 
                               class="text-purple-600 hover:text-purple-900 inline-flex items-center">
                                <i class="fas fa-edit mr-1 w-4"></i> Edit
                            </a>

                            <!-- Triage/Next Step Action -->
                            @if ($visit->status == 'Registered')
                                {{-- RECEPTIONIST ACTION: Send to Nurse Triage Queue (POST action) --}}
                                <form action="{{ route('triage.send_to_queue', $visit->visit_token) }}" method="POST" class="inline-block">
                                    @csrf
                                    {{-- Use a button that is styled like a link/action for consistency --}}
                                    <button type="submit" class="text-blue-500 hover:text-blue-700 inline-flex items-center p-0 m-0 border-none bg-transparent font-medium">
                                        <i class="fas fa-procedures mr-1 w-4"></i> Send to Triage
                                    </button>
                                </form>
                            @elseif ($visit->status == 'Waiting for Triage')
                                <span class="text-blue-600 inline-flex items-center font-medium">
                                    <i class="fas fa-user-clock mr-1 w-4"></i> Nurse Queue
                                </span>
                            @else
                                {{-- Patient is completed, offer next action like Follow-up/History --}}
                                <a href="{{ route('followup.view', $visit->visit_token) }}" class="text-green-500 hover:text-green-700 inline-flex items-center">
                                    <i class="fas fa-history mr-1 w-4"></i> Follow-up
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 text-lg border-t border-gray-300">
                        <i class="fas fa-check-circle mr-2"></i> No patients in queue.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $queue->links('pagination::tailwind') }}
    </div>
</div>
