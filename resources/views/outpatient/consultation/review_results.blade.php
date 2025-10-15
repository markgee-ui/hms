@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4 sm:p-6">
    <h1 class="text-3xl font-extrabold text-gray-900 mb-6 border-b pb-2">
        Review Lab Results & Add Prescription
    </h1>

    <!-- Patient Info -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-2">Patient Information</h2>
        <p><strong>Name:</strong> {{ $visit->patient->name }}</p>
        <p><strong>Visit Token:</strong> {{ $visit->visit_token }}</p>
    </div>

    <!-- Lab Results -->
    <div class="bg-gray-50 rounded-xl shadow-inner p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Lab & Radiology Results</h2>
        @foreach($visit->labRequests as $lab)
            <div class="border-b border-gray-300 pb-4 mb-4">
                @if(is_array($lab->tests_requested))
    <div class="mb-3">
        <strong>Tests Requested:</strong>
        <ul class="list-disc pl-6 text-gray-700">
            @foreach($lab->tests_requested as $category => $tests)
                <li><strong>{{ $category }}:</strong> {{ $tests }}</li>
            @endforeach
        </ul>
    </div>
@else
    <p><strong>Tests Requested:</strong> {{ $lab->tests_requested }}</p>
@endif

                <p><strong>Result:</strong> {{ $lab->results ?? 'No results provided' }}</p>
                <p><strong>Status:</strong> {{ ucfirst($lab->status) }}</p>
            </div>
        @endforeach
    </div>

    <!-- Prescription Form -->
   <div class="bg-white rounded-xl shadow-xl p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Add Prescription</h2>
        <form method="POST" action="{{ route('consultation.prescribe', $visit->visit_token) }}">
            @csrf
            <textarea name="prescription_data" rows="6" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-indigo-400 focus:outline-none" placeholder="Enter medicines, dosage, frequency..."></textarea>

            @error('prescription_data')
                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
            @enderror

            <div class="flex justify-end mt-4">
                 <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-semibold">
                    Send to Pharmacy Queue
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
