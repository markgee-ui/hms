@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4 sm:p-6">
    <h1 class="text-3xl font-extrabold text-gray-900 mb-6 border-b pb-2">
        Patients Awaiting Lab Results
    </h1>
    <p class="text-gray-600 mb-8">
        This list shows all patients you have referred to the Lab/Radiology department who have not yet been assigned a new status (e.g., waiting for results to review).
    </p>

    <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
        @if($visits->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <i class="fas fa-check-circle text-4xl mb-3 text-green-500"></i>
                <p class="text-lg font-semibold">
                    Great job! No patients are currently awaiting lab results from your referrals.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">
                                Patient Name
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">
                                Visit Token
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">
                                Time Referred
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($visits as $visit)
                            <tr class="hover:bg-yellow-50/50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-200">
                                    {{ $visit->patient->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">
                                    {{ $visit->visit_token }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">
                                    {{ $visit->updated_at->diffForHumans() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('consultation.view', $visit->visit_token) }}"
                                       class="text-indigo-600 hover:text-indigo-900 font-semibold transition duration-150">
                                        Review Consultation
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
