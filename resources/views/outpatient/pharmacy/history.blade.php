@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4 sm:p-6">
    <h1 class="text-3xl font-extrabold text-gray-900 mb-6 border-b pb-2">
        Prescription History
    </h1>
    <p class="text-gray-600 mb-8">
        This log shows all prescriptions you have successfully **Dispensed** to patients.
    </p>

    <form method="GET" action="{{ route('pharmacy.history') }}"
          class="mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
        
        <div class="relative flex-grow w-full md:w-1/3">
            <input 
                type="text"
                name="search"
                placeholder="Search by Patient Name..."
                value="{{ request('search') }}"
                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg 
                       focus:ring-indigo-500 focus:border-indigo-500 text-gray-800"
            >
            <i class="fas fa-search absolute left-3 top-4 text-gray-400"></i>
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto">
            @if(request()->filled('search'))
                <a href="{{ route('pharmacy.history') }}"
                   class="py-3 px-4 bg-gray-200 text-gray-700 rounded-lg shadow-sm hover:bg-gray-300 flex items-center justify-center">
                    Clear Search
                </a>
            @else
                <button type="submit"
                    class="py-3 px-4 bg-indigo-600 text-white rounded-lg shadow-sm hover:bg-indigo-700">
                    Search
                </button>
            @endif

            <a href="{{ route('pharmacy.history', ['search' => request('search')]) }}" 
                class="py-3 px-4 bg-purple-600 text-white rounded-lg shadow-sm hover:bg-purple-700 transition transform hover:scale-[1.05] flex items-center justify-center">
                <i class="fas fa-sync-alt mr-2"></i> Refresh
            </a>
        </div>
    </form>

    <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-300">
        @if($dispensed->isEmpty())
            <div class="p-8 text-center text-gray-500 border-t border-gray-300">
                <i class="fas fa-pills text-4xl mb-3 text-indigo-500"></i>
                <p class="text-lg font-semibold">
                    No dispensed prescriptions found in your history.
                </p>
                @if(request('search'))
                    <p class="text-sm mt-2">Try clearing the search filter.</p>
                @endif
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
                                Drugs Dispensed
                            </th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600">
                                Prescribed By
                            </th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600">
                                Dispensation Time
                            </th>
                            <th class="px-6 py-3 text-center font-semibold text-gray-600">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-300">
                        @foreach($dispensed as $prescription)
                            <tr class="hover:bg-blue-50 transition duration-150 divide-x divide-gray-300">
                                <td class="px-6 py-4 text-gray-900">
                                    <div class="font-medium">{{ $prescription->visit->patient->name ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Token: {{ $prescription->visit->visit_token ?? 'N/A' }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-gray-700 max-w-xs whitespace-normal">
                                    <p class="text-xs sm:text-sm italic">
                                        {{ Str::limit($prescription->prescription_details ?? 'No details recorded', 100) }}
                                    </p>
                                    @if($prescription->dispense_notes)
                                        <div class="text-xs text-indigo-500 mt-1">
                                            Notes: {{ Str::limit($prescription->dispense_notes, 50) }}
                                        </div>
                                    @endif
                                </td>
                                
                                <td class="px-6 py-4 text-gray-700">
                                    {{ $prescription->doctor->name ?? 'N/A' }}
                                </td>

                                <td class="px-6 py-4 text-gray-500">
                                    {{ $prescription->updated_at->format('M d, Y h:i A') }}
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <span class="text-xs text-green-600 font-semibold border border-green-400 px-3 py-1 rounded-full bg-green-50">
                                        Dispensed
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if ($dispensed->lastPage() > 1)
                    <div class="p-4 flex justify-center border-t border-gray-300 bg-gray-50">
                        {{ $dispensed->appends(request()->except('page'))->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection