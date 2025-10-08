@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto mt-8">
    <div class="bg-white p-8 rounded-2xl shadow-xl border border-gray-100">
        <!-- Patient Demographics Section -->
        <h2 class="text-3xl font-bold mb-6 text-gray-800 border-b pb-2">
            <i class="fas fa-user-circle text-blue-500 mr-2"></i> Patient Demographics
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4 text-gray-700">
            <p class="text-lg"><span class="font-semibold text-gray-900">Name:</span> {{ $patient->name }}</p>
            <p class="text-lg"><span class="font-semibold text-gray-900">Phone:</span> {{ $patient->phone }}</p>
            <p class="text-lg"><span class="font-semibold text-gray-900">National ID:</span> {{ $patient->national_id }}</p>
            <p class="text-lg"><span class="font-semibold text-gray-900">Age:</span> {{ $patient->age }}</p>
            <p class="text-lg"><span class="font-semibold text-gray-900">Gender:</span> {{ $patient->gender }}</p>
            <p class="text-lg md:col-span-2"><span class="font-semibold text-gray-900">Address:</span> {{ $patient->address }}</p>
        </div>

    </div>

    <!-- Visit History Table Section -->
    <div class="mt-10 bg-white p-8 rounded-2xl shadow-xl border border-gray-100">
        <h3 class="text-3xl font-bold mb-6 text-gray-800 border-b pb-2">
            <i class="fas fa-history text-blue-500 mr-2"></i> Visit History
        </h3>

        @if ($patient->visits->isEmpty())
            <div class="text-center py-6 text-gray-500 border border-dashed rounded-lg">
                <i class="fas fa-exclamation-circle text-xl mb-2"></i>
                <p class="font-medium">No previous visit records found for this patient.</p>
            </div>
        @else
            <div class="overflow-x-auto rounded-lg shadow-sm border">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Visit Token
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Registration Date
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($patient->visits->sortByDesc('registration_date') as $visit)
                            <tr class="hover:bg-blue-50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-700">
                                    {{ $visit->visit_token }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if ($visit->status === 'Completed') bg-green-100 text-green-800
                                        @elseif ($visit->status === 'Registered') bg-yellow-100 text-yellow-800
                                        @else bg-blue-100 text-blue-800
                                        @endif">
                                        {{ $visit->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $visit->registration_date->format('d M Y, h:i A') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    {{-- Example action: Link to start the triage or consultation workflow --}}
                                    <a href="#" class="text-indigo-600 hover:text-indigo-900 transition duration-150">View Details</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    
    <div class="mt-8 mb-12 text-center">
        <a href="{{ route('outpatient.dashboard') }}" 
           class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-full shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>
</div>
@endsection
