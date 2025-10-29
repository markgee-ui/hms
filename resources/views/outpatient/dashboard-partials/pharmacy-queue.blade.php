<div class="container mx-auto p-4 sm:p-6">
    <h1 class="text-3xl font-extrabold text-gray-900 mb-6 border-b pb-2">
        Pharmacy Queue
    </h1>
    <p class="text-gray-600 mb-8">
        This queue displays all prescriptions sent by doctors that are currently awaiting dispensation 
        in the <strong>Pharmacy</strong>. You can search, filter, and manage each request below.
    </p>

    <form method="GET" action="{{ route('outpatient.dashboard') }}"
          class="mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
        <input type="hidden" name="role" value="pharmacist">

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
            {{-- NOTE: Adjusted options for clearer status values ('Pending' is used as the non-dispensed status) --}}
            <select name="status" onchange="this.form.submit()" 
                class="py-3 px-4 border border-gray-300 rounded-lg shadow-sm 
                        focus:ring-blue-500 focus:border-blue-500">
                <option value="">All Prescriptions</option>
                <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Dispensed" {{ request('status') == 'Dispensed' ? 'selected' : '' }}>Dispensed</option>
            </select>

            @if(request()->filled('search') || request()->filled('status'))
                <a href="{{ route('outpatient.dashboard', ['role' => 'pharmacist']) }}"
                   class="py-3 px-4 bg-gray-200 text-gray-700 rounded-lg shadow-sm hover:bg-gray-300 flex items-center justify-center">
                    Clear
                </a>
            @else
                <button type="submit"
                    class="py-3 px-4 bg-blue-600 text-white rounded-lg shadow-sm hover:bg-blue-700">
                    Filter
                </button>
            @endif

            <a href="{{ route('outpatient.dashboard', [
                    'role' => 'pharmacist',
                    'search' => request('search'),
                    'status' => request('status')
                ]) }}" 
                class="py-3 px-4 bg-purple-600 text-white rounded-lg shadow-sm hover:bg-purple-700 transition transform hover:scale-[1.05] flex items-center justify-center">
                <i class="fas fa-sync-alt mr-2"></i> Refresh
            </a>
        </div>
    </form>

    <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-300">
        @if($data['pharmacyQueue']->isEmpty())
            <div class="p-8 text-center text-gray-500 border-t border-gray-300">
                <i class="fas fa-pills text-4xl mb-3 text-blue-500"></i>
                <p class="text-lg font-semibold">
                    The pharmacy queue is clear! No pending prescriptions at the moment.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse border border-gray-300 text-sm">
                    <thead class="bg-gray-100 border-b border-gray-300">
                        <tr class="divide-x divide-gray-300">
                            <th class="px-6 py-3 text-left font-semibold text-gray-600 border-b border-gray-300">
                                Patient Name / Token
                            </th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600 border-b border-gray-300">
                                Prescribed Drugs (Doctor)
                            </th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600 border-b border-gray-300">
                                Date Issued
                            </th>
                            <th class="px-6 py-3 text-right font-semibold text-gray-600 border-b border-gray-300">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-300">
                        @foreach($data['pharmacyQueue'] as $prescription)
                            <tr class="hover:bg-blue-50 transition duration-150 divide-x divide-gray-300">
                                <td class="px-6 py-4 text-gray-900">
                                    <div class="font-medium">{{ $prescription->visit->patient->name ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Token: {{ $prescription->visit->visit_token ?? 'N/A' }}
                                    </div>
                                </td>

                           <td class="px-6 py-4 text-gray-700 align-top">
    <div class="font-semibold text-gray-900 text-xs sm:text-sm mb-1">
        Prescribed by:
        <span class="text-indigo-600 font-medium">
            {{ $prescription->doctor->name ?? 'Unassigned' }}
        </span>
    </div>

    @if($prescription->items->isEmpty())
        <span class="text-gray-400 italic text-sm">No items in prescription</span>
    @else
        <ul class="list-disc list-inside text-xs sm:text-sm text-gray-700 space-y-1">
            @foreach($prescription->items as $item)
                <li>
                    <span class="font-medium">{{ $item->medication->name ?? 'N/A' }}</span>
                    <span class="text-gray-600">
                        — {{ $item->dosage ?? '-' }},
                        {{ $item->frequency ?? '-' }},
                        {{ $item->duration ?? '-' }},
                        Qty: {{ $item->quantity }}
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
</td>


                                <td class="px-6 py-4 text-gray-500">
                                    {{ $prescription->created_at->diffForHumans() }}
                                </td>

                                <td class="px-6 py-4 text-right">
                                    @if($prescription->status == 'Pending')
                                        {{-- Updated action to point to a new detailed view route --}}
                                        <a href="{{ route('pharmacy.view', $prescription->id) }}"
                                            class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md shadow-sm 
                                                    text-white bg-blue-600 hover:bg-blue-700 transition transform hover:scale-[1.05]">
                                            <i class="fas fa-eye mr-2"></i> View & Process
                                        </a>
                                    @else
                                        <span class="text-xs text-green-600 font-semibold border border-green-400 px-3 py-1 rounded-full">
                                            Dispensed
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if ($data['pharmacyQueue']->lastPage() > 1)
                    <div class="p-4 flex justify-center border-t border-gray-300 bg-gray-50">
                        {{ $data['pharmacyQueue']->appends(request()->except('page'))->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>