<div class="min-h-screen w-full bg-gray-50 p-6">
    <div class="bg-white shadow-2xl rounded-xl p-6 w-full h-full">
        <h3 class="text-3xl font-semibold mb-6 text-gray-800 border-b pb-3 flex items-center">
            <i class="fas fa-clock text-gray-500 mr-3"></i>
            Triage Queue
        </h3>

        <form method="GET" action="{{ route('outpatient.dashboard') }}" class="mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
            <input type="hidden" name="role" value="nurse">

            <div class="relative flex-grow w-full md:w-1/3">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search by Patient Name or Token..." 
                    value="{{ request('search') }}"
                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 text-gray-800"
                >
                <i class="fas fa-search absolute left-3 top-4 text-gray-400"></i>
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto">
                <!-- NEW: Triage Priority Filter -->
                <select name="triage_priority" onchange="this.form.submit()" class="py-3 px-4 border border-gray-300 rounded-lg shadow-sm focus:ring-yellow-500 focus:border-yellow-500">
                    <option value="">All Priorities</option>
                    @foreach (['Emergency', 'Urgent', 'Non-Urgent', 'Routine'] as $priority)
                        <option value="{{ $priority }}" {{ request('triage_priority') == $priority ? 'selected' : '' }}>{{ $priority }}</option>
                    @endforeach
                </select>

                <select name="status" onchange="this.form.submit()" class="py-3 px-4 border border-gray-300 rounded-lg shadow-sm focus:ring-yellow-500 focus:border-yellow-500">
                    <option value="">All Triage Statuses</option>
                    <option value="Waiting for Triage" {{ request('status') == 'Waiting for Triage' ? 'selected' : '' }}>Waiting for Triage</option>
                    <option value="Triage Completed" {{ request('status') == 'Triage Completed' ? 'selected' : '' }}>Triage Completed</option>
                    
                </select>

                @if(request()->filled('search') || request()->filled('status') || request()->filled('triage_priority'))
                    <a href="{{ route('outpatient.dashboard', ['role' => 'nurse']) }}" class="py-3 px-4 bg-gray-200 text-gray-700 rounded-lg shadow-sm hover:bg-gray-300 flex items-center justify-center">
                        Clear
                    </a>
                @else
                    <button type="submit" class="py-3 px-4 bg-blue-600 text-white rounded-lg shadow-sm hover:bg-blue-700">
                        Filter
                    </button>
                @endif

                <a href="{{ route('outpatient.dashboard', ['role' => 'nurse', 'status' => request('status'), 'search' => request('search'), 'triage_priority' => request('triage_priority')]) }}" 
                    class="py-3 px-4 bg-purple-600 text-white rounded-lg shadow-sm hover:bg-purple-700 transition transform hover:scale-[1.05] flex items-center justify-center">
                    <i class="fas fa-sync-alt mr-2"></i> Refresh
                </a>
            </div>
        </form>

        <div class="overflow-x-auto border border-gray-300 rounded-lg">
            <table class="min-w-full border-collapse">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600 uppercase border-r border-gray-300">Token</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600 uppercase border-r border-gray-300">Patient Name</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600 uppercase border-r border-gray-300">Time Registered</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600 uppercase border-r border-gray-300">Wait Time</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600 uppercase border-r border-gray-300">Triage Priority</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600 uppercase border-r border-gray-300">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-300">
                    @forelse ($data['triageQueue'] as $visit)
                        @php
    $queueTime = \Carbon\Carbon::parse($visit->triage_queue_time ?? $visit->registration_date);
    $waitTimeMinutes = $queueTime->diffInMinutes(\Carbon\Carbon::now());
    $waitTimeDisplay = ($visit->status == 'Waiting for Triage') 
        ? $queueTime->diffForHumans(null, true, false, 2) 
        : 'Completed';
    $waitColor = ($visit->status == 'Waiting for Triage' && $waitTimeMinutes > 30) ? 'text-red-600 font-bold' : 'text-gray-900';
    
    $statusColor = [
        'Waiting for Triage' => 'bg-yellow-100 text-yellow-800',
        'Triage Completed' => 'bg-green-100 text-green-800',
    ][$visit->status] ?? 'bg-gray-100 text-gray-800';

    // Correctly fetch the category from the visit's triage relationship
    $priority = $visit->triage->triage_category ?? 'N/A';

    $priorityStyles = [
        'Emergency' => ['bg' => 'bg-red-600', 'text' => 'text-white', 'icon' => 'fas fa-exclamation-triangle'],
        'Urgent' => ['bg' => 'bg-orange-500', 'text' => 'text-white', 'icon' => 'fas fa-fire'],
        'Non-Urgent' => ['bg' => 'bg-yellow-400', 'text' => 'text-gray-900', 'icon' => 'fas fa-clock'],
        'Routine' => ['bg' => 'bg-green-600', 'text' => 'text-white', 'icon' => 'fas fa-check-circle'],
        'N/A' => ['bg' => 'bg-gray-400', 'text' => 'text-white', 'icon' => 'fas fa-minus-circle'],
    ];

    $style = $priorityStyles[$priority];
@endphp


                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-bold text-blue-600 border-r border-gray-300">{{ $visit->visit_token }}</td>
                            <td class="px-6 py-4 text-gray-900 border-r border-gray-300">{{ $visit->patient->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 border-r border-gray-300">{{ \Carbon\Carbon::parse($visit->registration_date)->format('H:i A') }}</td>
                            <td class="px-6 py-4 text-sm border-r border-gray-300 {{ $waitColor }}">{{ $waitTimeDisplay }}</td>

                            <!-- Triage Priority Column -->
                            <td class="px-6 py-4 border-r border-gray-300">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $style['bg'] }} {{ $style['text'] }} shadow-md">
                                    @if ($priority != 'N/A')
                                        <i class="{{ $style['icon'] }} mr-1"></i>
                                    @endif
                                    {{ $priority }}
                                </span>
                            </td>

                            <!-- Status Column -->
                            <td class="px-6 py-4 border-r border-gray-300">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColor }}">
                                    {{ $visit->status }}
                                </span>
                            </td>

                            <!-- Action Column -->
                            <td class="px-6 py-4 flex gap-2">
                                @if ($visit->status == 'Waiting for Triage')
                                    <a href="{{ route('triage.start', $visit->visit_token) }}" 
                                        class="inline-flex items-center px-3 py-1 text-sm rounded-md text-white bg-yellow-600 hover:bg-yellow-700 transition transform hover:scale-[1.05]">
                                        <i class="fas fa-clipboard-list mr-1"></i> Start Triage
                                    </a>
                                @elseif ($visit->status == 'Triage Completed')
                                    <a href="{{ route('triage.edit', $visit->visit_token) }}" 
                                        class="inline-flex items-center px-3 py-1 text-sm rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition transform hover:scale-[1.05]">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </a>
                                    <a href="{{ route('triage.view', $visit->visit_token) }}" 
                                        class="inline-flex items-center px-3 py-1 text-sm rounded-md text-gray-700 bg-white border hover:bg-gray-50 transition transform hover:scale-[1.05]">
                                        <i class="fas fa-eye mr-1"></i> View
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500 bg-gray-50">
                                <i class="fas fa-hand-peace mr-2"></i> No patients currently waiting for triage matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($data['triageQueue']->lastPage() > 1)
            <div class="mt-6 flex justify-center">
                {{ $data['triageQueue']->appends(request()->except('page'))->links() }}
            </div>
        @endif
    </div>
</div>
