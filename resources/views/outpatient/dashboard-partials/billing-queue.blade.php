<div class="container mx-auto p-4 sm:p-6">
    <h1 class="text-3xl font-extrabold text-gray-900 mb-6 border-b pb-2">
        Billing Queue
    </h1>
    <p class="text-gray-600 mb-8">
        This queue lists all patient visits that are **ready for final payment** (medications dispensed, but bill not settled).
    </p>

    <form method="GET" action="{{ route('billing.queue') }}" 
          class="mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
        
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
            @if(request()->filled('search'))
                <a href="{{ route('billing.queue') }}"
                   class="py-3 px-4 bg-gray-200 text-gray-700 rounded-lg shadow-sm hover:bg-gray-300 flex items-center justify-center">
                    Clear Search
                </a>
            @else
                <button type="submit"
                    class="py-3 px-4 bg-blue-600 text-white rounded-lg shadow-sm hover:bg-blue-700">
                    Search
                </button>
            @endif

            <a href="{{ route('billing.queue', ['search' => request('search')]) }}" 
                class="py-3 px-4 bg-purple-600 text-white rounded-lg shadow-sm hover:bg-purple-700 transition transform hover:scale-[1.05] flex items-center justify-center">
                <i class="fas fa-sync-alt mr-2"></i> Refresh
            </a>
        </div>
    </form>

    <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-300">
        {{-- Check the correct variable $billingQueue which is passed from the dashboard controller --}}
        @if(empty($billingQueue) || $billingQueue->isEmpty())
            <div class="p-8 text-center text-gray-500 border-t border-gray-300">
                <i class="fas fa-hand-holding-usd text-4xl mb-3 text-green-500"></i>
                <p class="text-lg font-semibold">
                    The billing queue is clear! All patients have settled their accounts.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse border border-gray-300 text-sm">
                    <thead class="bg-gray-100 border-b border-gray-300">
                        <tr class="divide-x divide-gray-300">
                            <th class="px-6 py-3 text-left font-semibold text-gray-600">
                                Patient Name / Token
                            </th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600">
                                Status From Pharmacy
                            </th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600">
                                Estimated Total Due
                            </th>
                            <th class="px-6 py-3 text-right font-semibold text-gray-600">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-300">
                        @foreach($billingQueue as $visit)
                            <tr class="hover:bg-blue-50 transition duration-150 divide-x divide-gray-300">
                                <td class="px-6 py-4 text-gray-900">
                                    <div class="font-medium">{{ $visit->patient->name ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Token: <span class="font-semibold text-blue-600">{{ $visit->visit_token ?? 'N/A' }}</span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-gray-700">
                                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-yellow-100 text-yellow-800">
                                        {{ $visit->status ?? 'Billing' }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-green-700 font-bold">
                                    {{-- Placeholder: Replace with actual calculated bill amount property or accessor --}}
                                    KES {{ number_format(rand(500, 5000), 2) }} 
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('billing.process', $visit->id) }}"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md shadow-sm 
                                                text-white bg-green-600 hover:bg-green-700 transition transform hover:scale-[1.05]">
                                        <i class="fas fa-dollar-sign mr-2"></i> Process Payment
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if ($billingQueue->lastPage() > 1)
                    <div class="p-4 flex justify-center border-t border-gray-300 bg-gray-50">
                        {{ $billingQueue->appends(request()->except('page'))->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>