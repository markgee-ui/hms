@extends('layouts.app')

@section('content')
<div class="w-full min-h-screen bg-gray-50 py-10">
    <div class="w-11/12 mx-auto bg-white p-10 rounded-2xl shadow-lg border border-gray-200">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b-2 border-indigo-500 pb-4 mb-6">
            <h2 class="text-3xl font-bold text-gray-800">
                Edit Triage Assessment
            </h2>
            <a href="{{ route('outpatient.dashboard', ['role' => 'nurse']) }}"
               class="mt-4 md:mt-0 inline-flex items-center justify-center px-6 py-2 text-sm font-semibold rounded-full text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
               ← Back to Queue
            </a>
        </div>

        <!-- Patient Info -->
        <div class="bg-gray-50 border-l-4 border-indigo-600 p-6 mb-8 rounded-lg shadow-sm">
            <h3 class="text-xl font-bold text-gray-900">
                Patient: {{ $triage->visit->patient->name ?? 'N/A' }}
                <span class="text-sm font-normal ml-2 text-gray-600">
                    ({{ $triage->visit->patient->patient_id ?? 'N/A' }})
                </span>
            </h3>
            <p class="text-sm text-gray-700 mt-1">
                Visit Token: <span class="font-mono text-base text-indigo-700">{{ $triage->visit->visit_token }}</span> 
                | Age: {{ $triage->visit->patient->age ?? 'N/A' }} 
                | Gender: {{ $triage->visit->patient->gender ?? 'N/A' }}
            </p>
        </div>

        <!-- Edit Form -->
        <form action="{{ route('triage.update', $triage->visit->visit_token) }}" method="POST" class="space-y-8">
            @csrf
            @method('PATCH')

            <!-- Vital Signs -->
            <div>
                <h4 class="text-xl font-semibold text-gray-700 border-b pb-2 mb-4">Vital Signs</h4>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div>
                        <label for="bp" class="block text-sm font-medium text-gray-700 mb-1">Blood Pressure (mmHg)</label>
                        <input type="text" name="bp" id="bp" required
                               value="{{ old('bp', $triage->bp) }}"
                               placeholder="e.g., 120/80"
                               class="p-3 w-full border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('bp') border-red-500 @enderror">
                        @error('bp')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="temperature" class="block text-sm font-medium text-gray-700 mb-1">Temperature (°C)</label>
                        <input type="number" step="0.1" name="temperature" id="temperature" required
                               value="{{ old('temperature', $triage->temperature) }}"
                               class="p-3 w-full border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('temperature') border-red-500 @enderror">
                        @error('temperature')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="pulse" class="block text-sm font-medium text-gray-700 mb-1">Pulse Rate (BPM)</label>
                        <input type="number" name="pulse" id="pulse" required
                               value="{{ old('pulse', $triage->pulse) }}"
                               class="p-3 w-full border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('pulse') border-red-500 @enderror">
                        @error('pulse')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="weight" class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                        <input type="number" step="0.1" name="weight" id="weight" required
                               value="{{ old('weight', $triage->weight) }}"
                               class="p-3 w-full border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('weight') border-red-500 @enderror">
                        @error('weight')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Preliminary Assessment -->
            <div>
                <h4 class="text-xl font-semibold text-gray-700 border-b pb-2 mb-4">Preliminary Assessment</h4>

                <div class="space-y-6">
                    <div>
                        <label for="chief_complaint" class="block text-sm font-medium text-gray-700 mb-1">
                            Chief Complaint <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="chief_complaint" id="chief_complaint" required
                               value="{{ old('chief_complaint', $triage->chief_complaint) }}"
                               placeholder="Short description of the main reason for visit"
                               class="p-3 w-full border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('chief_complaint') border-red-500 @enderror">
                        @error('chief_complaint')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="symptoms" class="block text-sm font-medium text-gray-700 mb-1">Detailed Symptoms and History</label>
                        <textarea name="symptoms" id="symptoms" rows="4"
                                  placeholder="Additional symptoms, duration, history of illness, etc."
                                  class="p-3 w-full border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('symptoms') border-red-500 @enderror">{{ old('symptoms', $triage->symptoms) }}</textarea>
                        @error('symptoms')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-6 flex justify-end">
                <button type="submit"
                        class="py-3 px-8 bg-indigo-600 text-white font-semibold text-lg rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
                    Save Triage Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
