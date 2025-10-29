@extends('layouts.app')

@section('content')

<div class="min-h-screen w-full p-4 sm:p-6 lg:p-8 bg-gray-50 font-sans">
<div class="max-w-4xl mx-auto bg-white shadow-2xl rounded-xl overflow-hidden">

    <!-- Header -->
    <div class="bg-indigo-600 p-6 text-white sm:flex sm:justify-between sm:items-center">
        <h1 class="text-3xl font-extrabold flex items-center">
            <i class="fas fa-microscope mr-3"></i>
            Process Lab Request
        </h1>
        <div class="mt-2 sm:mt-0 text-lg font-medium">
            Visit Token: <span class="bg-indigo-700 py-1 px-3 rounded-lg">{{ $labRequest->visit->visit_token ?? 'N/A' }}</span>
        </div>
    </div>

    <!-- Patient and Request Details -->
    <div class="p-6 border-b border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Patient Info -->
            <div class="bg-blue-50 p-4 rounded-lg shadow-inner">
                <h2 class="text-xl font-semibold text-blue-800 mb-2 border-b border-blue-200 pb-1">Patient Details</h2>
                <p class="text-gray-700"><span class="font-medium">Name:</span> {{ $labRequest->visit->patient->name ?? 'N/A' }}</p>
                <p class="text-gray-700"><span class="font-medium">Date of Birth:</span> {{ $labRequest->visit->patient->dob ?? 'N/A' }}</p>
                <p class="text-gray-700"><span class="font-medium">Gender:</span> {{ $labRequest->visit->patient->gender ?? 'N/A' }}</p>
            </div>

            <!-- Request Info -->
            <div class="bg-green-50 p-4 rounded-lg shadow-inner">
                <h2 class="text-xl font-semibold text-green-800 mb-2 border-b border-green-200 pb-1">Request Details</h2>
                <p class="text-gray-700"><span class="font-medium">Ordered By:</span> Dr. {{ $labRequest->doctor->name ?? 'N/A' }}</p>
                <p class="text-gray-700"><span class="font-medium">Time Requested:</span> {{ $labRequest->created_at->format('Y-m-d H:i') }}</p>
                <p class="text-gray-700"><span class="font-medium">Current Status:</span> 
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                        {{ $labRequest->status }}
                    </span>
                </p>
            </div>
        </div>
    </div>

    <!-- Requested Tests List -->
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Requested Tests</h2>
        
        <ul class="space-y-3">
           @forelse($labRequest->tests as $test)
    <li class="flex items-center bg-gray-100 p-3 rounded-lg shadow-sm">
        <i class="fas fa-flask text-indigo-500 mr-3"></i>
        <span class="text-gray-700 font-medium">
            {{ $test->labTest->name ?? 'Unknown Test' }}
        </span>
    </li>
@empty
    <li class="text-gray-500 italic">No specific tests were listed on this request.</li>
@endforelse

        </ul>
    </div>
    
    <!-- Results Submission Form -->
    <div class="p-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Enter Results</h2>
        
             <form action="{{ route('lab.results.store', $labRequest) }}" method="POST">
                 @csrf


            <!-- Text Area for Results -->
            <div class="mb-6">
                <label for="results_data" class="block text-sm font-medium text-gray-700 mb-2">
                    Laboratory/Radiology Findings (Detailed Report)
                </label>
                <textarea 
                    id="results_data" 
                    name="results_data" 
                    rows="10" 
                    class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm p-4 focus:ring-indigo-500 focus:border-indigo-500 text-gray-800" 
                    placeholder="Enter the complete results report, including measurements, reference ranges, and interpretation here..."
                    required>{{ old('results_data', $labRequest->results) }}</textarea>
                
                @error('results_data')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-4">
                <a href="{{ route('outpatient.dashboard', ['role' => 'labtech']) }}" 
                   class="inline-flex items-center px-4 py-3 border border-gray-300 text-base font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition shadow-md">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Queue
                </a>

                <button type="submit" 
                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg shadow-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition transform hover:scale-[1.02]">
                    <i class="fas fa-save mr-2"></i> Save & Complete Request
                </button>
            </div>
        </form>
    </div>

</div>

</div>

@endsection