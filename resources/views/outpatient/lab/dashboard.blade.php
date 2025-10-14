@extends('layouts.app')

@section('content')

<div class="container mx-auto p-4 sm:p-6">
    <h1 class="text-3xl font-extrabold text-gray-900 mb-6 border-b pb-2">Lab & Radiology Queue</h1>
    <p class="text-gray-600 mb-8">
        This queue shows all patients referred to the Lab or Radiology department by doctors and are currently awaiting testing or results input.
    </p>

    <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
        @if($pendingRequests->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <i class="fas fa-microscope text-4xl mb-3 text-blue-500"></i>
                <p class="text-lg font-semibold">The lab queue is clear! All tests have been processed.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Patient Name
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Requested By (Doctor)
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Time Received
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status / Action
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($pendingRequests as $visit)
                            <tr class="hover:bg-blue-50/50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $visit->patient->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $visit->consultation->doctor->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $visit->created_at->diffForHumans() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('lab.request.process', $visit->id) }}"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                                        Process Request
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
