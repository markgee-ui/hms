@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4 sm:p-6">
    <h1 class="text-3xl font-extrabold text-gray-900 mb-6 border-b pb-2">
        Patients with Lab & Radiology Results Ready
    </h1>
    <p class="text-gray-600 mb-8">
        These patients have completed lab or radiology results that have been sent back by the Lab Technician for your review.
    </p>

    <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
        @if($visits->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <i class="fas fa-check-circle text-4xl mb-3 text-green-500"></i>
                <p class="text-lg font-semibold">
                    Great job! No patients are currently awaiting your review.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-300">
                    <thead class="bg-gray-100 border-b border-gray-300">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase border-r border-gray-300">Patient Name</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase border-r border-gray-300">Visit Token</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase border-r border-gray-300">Referred By</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase border-r border-gray-300">Updated</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-300">
                        @foreach($visits as $visit)
                            <tr class="hover:bg-yellow-50">
                                <td class="px-6 py-4 border-r border-gray-300">{{ $visit->patient->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 border-r border-gray-300">{{ $visit->visit_token }}</td>
                                <td class="px-6 py-4 border-r border-gray-300">{{ $visit->labRequests->first()->doctor->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 border-r border-gray-300">{{ $visit->updated_at->diffForHumans() }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('consultation.review', $visit->visit_token) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                        Review Results
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
             <!-- Pagination Links -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                {{ $visits->links('pagination::tailwind') }}
            </div>
        @endif
    </div>
</div>
@endsection
