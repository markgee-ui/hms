@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4 sm:p-6">
    <h1 class="text-3xl font-extrabold text-gray-900 mb-6 border-b pb-2">
        My Lab Results Sent
    </h1>
    <p class="text-gray-600 mb-8">
        These are all the lab and radiology results you have submitted.
    </p>

    <!-- Filter & Search -->
    <form method="GET" action="{{ route('lab.results.lab_queue') }}" class="mb-6 flex flex-wrap gap-4 items-center">
        <input 
            type="text" 
            name="search" 
            value="{{ $filters['search'] }}" 
            placeholder="Search by Patient Name or Token..." 
            class="px-4 py-2 border rounded-lg flex-grow focus:ring-blue-500 focus:border-blue-500"
        >

        <select name="status" onchange="this.form.submit()" class="px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
            <option value="">All</option>
            <option value="Completed" {{ $filters['status'] == 'Completed' ? 'selected' : '' }}>Completed</option>
            <option value="Pending Verification" {{ $filters['status'] == 'Pending Verification' ? 'selected' : '' }}>Pending Verification</option>
        </select>

        @if($filters['search'] || $filters['status'])
            <a href="{{ route('lab.results.lab_queue') }}" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Clear</a>
        @endif
    </form>

    <!-- Results Table -->
    <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-300">
        @if($labResults->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <i class="fas fa-flask text-4xl mb-3 text-blue-500"></i>
                <p>No lab results found yet.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse border border-gray-300 text-sm">
                    <thead class="bg-gray-100 border-b border-gray-300">
                        <tr class="divide-x divide-gray-300">
                            <th class="px-6 py-3 text-left font-semibold text-gray-600">Patient</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600">Tests</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600">Result Summary</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600">Doctor</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600">Status</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-600">Date Sent</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-300">
                        @foreach($labResults as $req)
                            <tr class="hover:bg-blue-50 transition divide-x divide-gray-300">
                                <td class="px-6 py-4">
                                    {{ $req->visit->patient->name ?? 'N/A' }}<br>
                                    <span class="text-xs text-gray-500">Token: {{ $req->visit->visit_token }}</span>
                                </td>

                                <td class="px-6 py-4 text-gray-700">
                                    @if(is_array($req->tests_requested))
                                        {{ implode(', ', $req->tests_requested) }}
                                    @else
                                        {{ $req->tests_requested }}
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-gray-700">
                                    @if($req->results)
                                        <span class="text-sm text-gray-800">{{ Str::limit($req->results, 80) }}</span>
                                    @else
                                        <span class="text-xs text-gray-500">No results recorded</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-gray-600">
                                    {{ $req->doctor->name ?? 'Unknown' }}
                                </td>

                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold 
                                        @if($req->status == 'Completed') bg-green-100 text-green-700 
                                        @else bg-yellow-100 text-yellow-700 @endif">
                                        {{ $req->status }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-gray-500">
                                    {{ $req->updated_at->format('d M, Y h:i A') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="p-4 border-t border-gray-300 bg-gray-50">
                    {{ $labResults->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
