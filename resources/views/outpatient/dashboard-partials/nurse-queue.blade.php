<div class="bg-white shadow-2xl rounded-xl p-6">
    <h3 class="text-2xl font-semibold mb-4 text-gray-800 border-b pb-2">
        <i class="fas fa-clock text-yellow-500 mr-2"></i> Patients Waiting for Triage
    </h3>

    <!-- Triage Queue Table with Grid Lines -->
    <div class="overflow-x-auto border border-gray-300 rounded-lg">
        <table class="min-w-full border-collapse">
            <thead class="bg-gray-50">
                <tr>
                    <!-- Vertical line added to all but the last column -->
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider border-r border-gray-300">Token</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider border-r border-gray-300">Patient Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider border-r border-gray-300">Time Registered</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider border-r border-gray-300">Wait Time</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <!-- Horizontal lines handled by divide-y divide-gray-300 -->
            <tbody class="bg-white divide-y divide-gray-300">
                @forelse ($queue as $visit)
                
                @php
                    // Assuming $visit->triage_queue_time is a Carbon instance or a valid time string
                    $queueTime = \Carbon\Carbon::parse($visit->triage_queue_time ?? $visit->created_at);
                    $waitTimeMinutes = $queueTime->diffInMinutes(\Carbon\Carbon::now());
                    $waitTimeDisplay = $queueTime->diffForHumans(null, true, false, 2); // e.g., '15 minutes'
                    
                    // Priority visual: over 30 minutes in the queue is a warning
                    $waitColor = ($waitTimeMinutes > 30) ? 'text-red-600 font-extrabold' : 'text-gray-900';
                @endphp

                <tr class="hover:bg-gray-50 transition duration-100">
                    <!-- Vertical line added to all but the last column -->
                    <td class="px-6 py-4 whitespace-nowrap font-bold text-blue-600 border-r border-gray-300">{{ $visit->visit_token }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-900 border-r border-gray-300">{{ $visit->patient->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-r border-gray-300">{{ $visit->registration_date->format('H:i A') }}</td>
                    
                    <!-- New Wait Time Column -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm border-r border-gray-300 {{ $waitColor }}">
                        {{ $waitTimeDisplay }}
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="{{ route('triage.start', $visit->visit_token) }}" 
                           class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 transition transform hover:scale-[1.05]">
                            <i class="fas fa-clipboard-list mr-1"></i> Start Triage
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 bg-gray-50">
                        <i class="fas fa-hand-peace mr-2"></i> No patients currently waiting for triage. The queue is empty!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
