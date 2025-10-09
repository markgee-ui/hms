@extends('layouts.app')

@section('content')
<div class="min-h-screen w-full bg-gray-50 p-8">
    <div class="bg-white p-10 rounded-2xl shadow-2xl border border-gray-100 mb-10 w-full">
        <!-- Patient Demographics Section -->
        <div class="flex justify-between items-center mb-6 border-b pb-3">
            <h2 class="text-4xl font-extrabold text-blue-700 flex items-center">
                <i class="fas fa-user-circle text-blue-500 mr-3"></i> Patient Demographics
            </h2>
            <a href="{{ route('outpatient.dashboard') }}" 
               class="inline-flex items-center px-5 py-2 text-base font-semibold text-gray-600 hover:text-blue-700 transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-10 gap-y-6 text-gray-700 text-lg">
            <p><span class="font-bold text-gray-900">Name:</span> {{ $patient->name }}</p>
            <p><span class="font-bold text-gray-900">Phone:</span> {{ $patient->phone }}</p>
            <p><span class="font-bold text-gray-900">National ID:</span> {{ $patient->national_id }}</p>
            <p><span class="font-bold text-gray-900">Age:</span> {{ $patient->age }}</p>
            <p><span class="font-bold text-gray-900">Gender:</span> {{ $patient->gender }}</p>
            <p class="md:col-span-3"><span class="font-bold text-gray-900">Address:</span> {{ $patient->address }}</p>
        </div>
    </div>

    <!-- Visit History Table Section -->
    <div class="bg-white p-10 rounded-2xl shadow-2xl border border-gray-100 w-full">
        <h3 class="text-4xl font-extrabold mb-8 text-gray-800 border-b pb-3 flex items-center">
            <i class="fas fa-history text-blue-500 mr-3"></i> Visit History
        </h3>

        @if ($patient->visits->isEmpty())
            <div class="text-center py-10 text-gray-500 border border-dashed rounded-lg bg-gray-50">
                <i class="fas fa-exclamation-circle text-3xl mb-3 text-gray-400"></i>
                <p class="text-lg font-medium">No previous visit records found for this patient.</p>
            </div>
        @else
            <div class="overflow-x-auto rounded-xl shadow-md border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-blue-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Visit Token</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Registration Date</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($patient->visits->sortByDesc('registration_date') as $visit)
                            <tr class="hover:bg-blue-50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-base font-medium text-blue-700">{{ $visit->visit_token }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full 
                                        @if ($visit->status === 'Completed') bg-green-100 text-green-800
                                        @elseif ($visit->status === 'Registered') bg-yellow-100 text-yellow-800
                                        @else bg-blue-100 text-blue-800
                                        @endif">
                                        {{ $visit->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $visit->registration_date->format('d M Y, h:i A') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold">
                                    <a href="#" class="text-blue-600 hover:text-blue-800 transition duration-150">
                                        <i class="fas fa-eye mr-1"></i> View Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
