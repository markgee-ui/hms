<div class="container mx-auto p-4 sm:p-6">
    <h1 class="text-3xl font-extrabold text-gray-900 mb-6 border-b pb-2">Lab & Radiology Queue</h1>
    <p class="text-gray-600 mb-8">
        This queue displays all patients referred to the Lab or Radiology department and currently in the 
        <strong>Lab/Rad</strong> stage. You can search, filter, and manage each request below.
    </p>

    <!--  Search Form -->
    <form method="GET" action="{{ route('outpatient.dashboard') }}" 
          class="mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
        <input type="hidden" name="role" value="labtech">

        <!-- Search -->
        <div class="relative flex-grow w-full md:w-1/3">
            <input 
                type="text" 
                name="search" 
                placeholder="Search by Patient Name or Token..." 
                value="{{ request('search') }}"
                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg 
                       focus:ring-blue-500 focus:border-blue-500 text-gray-800"
            >
            <i class="fas fa-search absolute left-3 top-4 text-gray-400"></i>
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto">
            <!-- Lab Request Status Filter -->
            <select name="request_status" onchange="this.form.submit()" 
                class="py-3 px-4 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="">All Requests</option>
                <option value="Pending" {{ request('request_status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Completed" {{ request('request_status') == 'Completed' ? 'selected' : '' }}>Completed</option>
            </select>

            <!-- Filter/Clear Button -->
            @if(request()->filled('search') || request()->filled('request_status'))
                <a href="{{ route('outpatient.dashboard', ['role' => 'labtech']) }}" 
                   class="py-3 px-4 bg-gray-200 text-gray-700 rounded-lg shadow-sm hover:bg-gray-300 flex items-center justify-center">
                    Clear
                </a>
            @else
                <button type="submit" 
                    class="py-3 px-4 bg-blue-600 text-white rounded-lg shadow-sm hover:bg-blue-700">
                    Filter
                </button>
            @endif

            <!-- Refresh -->
            <a href="{{ route('outpatient.dashboard', [
                    'role' => 'labtech',
                    'search' => request('search'),
                    'request_status' => request('request_status')
                ]) }}" 
               class="py-3 px-4 bg-purple-600 text-white rounded-lg shadow-sm hover:bg-purple-700 transition transform hover:scale-[1.05] flex items-center justify-center">
                <i class="fas fa-sync-alt mr-2"></i> Refresh
            </a>
        </div>
    </form>

    <!--Lab Queue Table Section -->
    <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-300">
        @if($data['labQueue']->isEmpty())
            <div class="p-8 text-center text-gray-500 border-t border-gray-300">
                <i class="fas fa-microscope text-4xl mb-3 text-blue-500"></i>
                <p class="text-lg font-semibold">
                    The lab queue is clear! All patients have moved out of the Lab/Rad stage.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse border border-gray-300 text-sm">
                    <thead class="bg-gray-100 border-b border-gray-300">
                        <tr class="divide-x divide-gray-300">
                            <th class="px-6 py-3 text-left font-semibold text-gray-600 border-b border-gray-300">Patient Name / Token</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600 border-b border-gray-300">Requested Tests (Status)</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600 border-b border-gray-300">Time Admitted</th>
                            <th class="px-6 py-3 text-right font-semibold text-gray-600 border-b border-gray-300">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-300">
                        @foreach($data['labQueue'] as $visit)
                            <tr class="hover:bg-blue-50 transition duration-150 divide-x divide-gray-300">
                                <td class="px-6 py-4 text-gray-900">
                                    <div class="font-medium">{{ $visit->patient->name ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500 mt-1">Token: {{ $visit->visit_token }}</div>
                                </td>

                                <td class="px-6 py-4 text-gray-700">
                                    @if($visit->labRequests->isEmpty())
                                        <span class="text-xs text-red-500">No requests found.</span>
                                    @else
                                        @foreach($visit->labRequests as $req)
    <div class="mb-2 p-2 border border-gray-200 rounded-lg bg-gray-50">
        <div class="flex justify-between items-center">
            <div class="font-semibold text-gray-900 text-xs sm:text-sm">
                {{-- Display all test names linked to this request --}}
                @php
                    $testNames = $req->tests->map(fn($t) => $t->labTest->name ?? 'Unknown Test')->toArray();
                @endphp

                {{ implode(', ', $testNames) }}

                <span class="text-xs text-gray-500 block sm:inline">
                    ({{ $req->doctor->name ?? 'Unknown' }})
                </span>
            </div>

            <span class="px-2 py-1 ml-2 rounded-full text-xs font-semibold 
                @if($req->status == 'Completed') bg-green-100 text-green-700 
                @else bg-yellow-100 text-yellow-700 @endif">
                {{ $req->status }}
            </span>
        </div>
    </div>
@endforeach

                                    @endif
                                </td>

                                <td class="px-6 py-4 text-gray-500">
                                    {{ $visit->registration_date->diffForHumans() }}
                                </td>

                                <td class="px-6 py-4 text-right">
                                    @php
                                        $pendingRequest = $visit->labRequests->firstWhere('status', '!=', 'Completed');
                                    @endphp

                                    @if($pendingRequest)
                                        <a href="{{ route('lab.request.process', $pendingRequest->id) }}"
                                            class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md shadow-sm 
                                                   text-white bg-blue-600 hover:bg-blue-700 transition">
                                            Process Tests
                                        </a>
                                    @else
                                        <span class="text-xs text-green-600 font-semibold border border-green-400 px-3 py-1 rounded-full">
                                            All Results Entered
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                @if ($data['labQueue']->lastPage() > 1)
                    <div class="p-4 flex justify-center border-t border-gray-300 bg-gray-50">
                        {{ $data['labQueue']->appends(request()->except('page'))->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
