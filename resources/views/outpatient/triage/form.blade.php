@extends('layouts.app')

@section('content')
<div class="p-6 bg-white shadow-2xl rounded-xl max-w-4xl mx-auto my-8">
    <div class="flex justify-between items-center border-b pb-4 mb-6">
        <h2 class="text-3xl font-extrabold text-indigo-700">Triage Assessment</h2>
        <a href="{{ route('outpatient.dashboard') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition">
            <i class="fas fa-arrow-left mr-1"></i> Back to Queue
        </a>
    </div>

    <!-- Patient Info Card -->
    <div class="bg-indigo-50 border-l-4 border-indigo-500 p-4 mb-6 rounded-lg shadow-md">
        <h3 class="text-xl font-bold text-indigo-800">
            Patient: {{ $visit->patient->name }} 
            <span class="text-sm font-normal ml-2 text-indigo-600">({{ $visit->patient->patient_id }})</span>
        </h3>
        <p class="text-sm text-indigo-700">Visit Token: <span class="font-mono text-base">{{ $visit->visit_token }}</span> | Age: {{ $visit->patient->age }} | Gender: {{ $visit->patient->gender }}</p>
    </div>

    <form action="{{ route('triage.store', $visit->id) }}" method="POST" class="space-y-6">
        @csrf
        
        <h4 class="text-xl font-semibold text-gray-700 border-b pb-2">Vital Signs</h4>

        <!-- Vitals Input Grid -->
        <div class="grid grid-cols-2 gap-6">
            
            <div>
                <label for="bp" class="block text-sm font-medium text-gray-700 mb-1">Blood Pressure (mmHg)</label>
                <input type="text" name="bp" id="bp" required value="{{ old('bp') }}" placeholder="e.g., 120/80"
                       class="p-3 block w-full border border-gray-300 rounded-md @error('bp') border-red-500 @enderror">
                @error('bp')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="temperature" class="block text-sm font-medium text-gray-700 mb-1">Temperature (°C)</label>
                <input type="number" step="0.1" name="temperature" id="temperature" required value="{{ old('temperature') }}"
                       class="p-3 block w-full border border-gray-300 rounded-md @error('temperature') border-red-500 @enderror">
                @error('temperature')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="pulse" class="block text-sm font-medium text-gray-700 mb-1">Pulse Rate (BPM)</label>
                <input type="number" name="pulse" id="pulse" required value="{{ old('pulse') }}"
                       class="p-3 block w-full border border-gray-300 rounded-md @error('pulse') border-red-500 @enderror">
                @error('pulse')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="weight" class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                <input type="number" step="0.1" name="weight" id="weight" required value="{{ old('weight') }}"
                       class="p-3 block w-full border border-gray-300 rounded-md @error('weight') border-red-500 @enderror">
                @error('weight')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <h4 class="text-xl font-semibold text-gray-700 border-b pb-2 mt-6">Preliminary Assessment</h4>

        <!-- Chief Complaint -->
        <div>
            <label for="chief_complaint" class="block text-sm font-medium text-gray-700 mb-1">Chief Complaint <span class="text-red-500">*</span></label>
            <input type="text" name="chief_complaint" id="chief_complaint" required value="{{ old('chief_complaint') }}" placeholder="Short description of the main reason for visit"
                   class="p-3 block w-full border border-gray-300 rounded-md @error('chief_complaint') border-red-500 @enderror">
            @error('chief_complaint')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <!-- Symptoms -->
        <div>
            <label for="symptoms" class="block text-sm font-medium text-gray-700 mb-1">Detailed Symptoms and History</label>
            <textarea name="symptoms" id="symptoms" rows="3" placeholder="Additional symptoms, duration, history of illness, etc."
                      class="p-3 block w-full border border-gray-300 rounded-md @error('symptoms') border-red-500 @enderror">{{ old('symptoms') }}</textarea>
            @error('symptoms')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <!-- Submit Button -->
        <div class="pt-4">
            <button type="submit" class="w-full py-3 bg-green-600 text-white font-extrabold text-lg rounded-lg shadow-xl hover:bg-green-700 transition duration-150 transform hover:scale-[1.005]">
                <i class="fas fa-clipboard-check mr-2"></i> Save Triage & Send to Doctor
            </button>
        </div>
    </form>
</div>
@endsection
