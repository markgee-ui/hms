@extends('layouts.app')

@section('content')
<div class="min-h-screen w-full bg-gray-50 py-8 px-6 flex justify-center items-start overflow-y-auto">
    <div class="bg-white shadow-2xl rounded-2xl w-full max-w-5xl p-8 border border-gray-200">
        <div class="flex justify-between items-center border-b pb-4 mb-8">
            <h2 class="text-3xl font-extrabold text-indigo-700 flex items-center gap-2">
                <i class="fas fa-stethoscope text-indigo-500"></i> Triage Assessment
            </h2>
            <a href="{{ route('outpatient.dashboard') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition">
                <i class="fas fa-arrow-left mr-1"></i> Back to Queue
            </a>
        </div>

        <!-- Patient Info Card -->
        <div class="bg-indigo-50 border-l-4 border-indigo-600 p-5 mb-8 rounded-lg shadow-sm">
            <h3 class="text-xl font-bold text-indigo-800">
                Patient: {{ $visit->patient->name }}
                <span class="text-sm font-normal ml-2 text-indigo-600">({{ $visit->patient->patient_id }})</span>
            </h3>
            <p class="text-sm text-indigo-700 mt-1">
                Visit Token: <span class="font-mono text-base">{{ $visit->visit_token }}</span> |
                Age: {{ $visit->patient->age }} | Gender: {{ $visit->patient->gender }}
            </p>
        </div>

        <!-- Triage Form -->
        <form action="{{ route('triage.store', $visit->id) }}" method="POST" class="space-y-8">
            @csrf

            <!-- Vital Signs -->
            <section>
                <h4 class="text-xl font-semibold text-gray-700 border-b pb-2 mb-4">Vital Signs</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="bp" class="block text-sm font-medium text-gray-700 mb-1">Blood Pressure (mmHg)</label>
                        <input type="text" name="bp" id="bp" required value="{{ old('bp') }}" placeholder="e.g., 120/80"
                               class="p-3 block w-full border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 @error('bp') border-red-500 @enderror">
                        @error('bp')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="temperature" class="block text-sm font-medium text-gray-700 mb-1">Temperature (°C)</label>
                        <input type="number" step="0.1" name="temperature" id="temperature" required value="{{ old('temperature') }}"
                               class="p-3 block w-full border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 @error('temperature') border-red-500 @enderror">
                        @error('temperature')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="pulse" class="block text-sm font-medium text-gray-700 mb-1">Pulse Rate (BPM)</label>
                        <input type="number" name="pulse" id="pulse" required value="{{ old('pulse') }}"
                               class="p-3 block w-full border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 @error('pulse') border-red-500 @enderror">
                        @error('pulse')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="weight" class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                        <input type="number" step="0.1" name="weight" id="weight" required value="{{ old('weight') }}"
                               class="p-3 block w-full border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 @error('weight') border-red-500 @enderror">
                        @error('weight')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <!-- Preliminary Assessment -->
            <section>
                <h4 class="text-xl font-semibold text-gray-700 border-b pb-2 mb-4">Preliminary Assessment</h4>
              
                <div class="space-y-6">
                    <div>
                        <label for="chief_complaint" class="block text-sm font-medium text-gray-700 mb-1">
                            Chief Complaint <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="chief_complaint" id="chief_complaint" required
                               value="{{ old('chief_complaint') }}" placeholder="Short description of main reason for visit"
                               class="p-3 block w-full border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 @error('chief_complaint') border-red-500 @enderror">
                        @error('chief_complaint')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="symptoms" class="block text-sm font-medium text-gray-700 mb-1">Detailed Symptoms and History</label>
                        <textarea name="symptoms" id="symptoms" rows="4" placeholder="Additional symptoms, duration, or relevant history"
                                  class="p-3 block w-full border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 @error('symptoms') border-red-500 @enderror">{{ old('symptoms') }}</textarea>
                        @error('symptoms')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <!-- Triage Category -->
            <section>
                <h4 class="text-xl font-semibold text-gray-700 border-b pb-2 mb-4">Triage Category</h4>
                <p class="text-sm text-gray-500 mb-4">Select a triage category based on the patient’s urgency and condition.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <label class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer hover:bg-red-50">
                        <input type="radio" name="triage_category" value="Emergency" required class="text-red-600 focus:ring-red-500">
                        <span class="text-red-700 font-semibold">Emergency</span>
                    </label>

                    <label class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer hover:bg-yellow-50">
                        <input type="radio" name="triage_category" value="Urgent" class="text-yellow-600 focus:ring-yellow-500">
                        <span class="text-yellow-700 font-semibold">Urgent</span>
                    </label>

                    <label class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer hover:bg-blue-50">
                        <input type="radio" name="triage_category" value="Non-Urgent" class="text-blue-600 focus:ring-blue-500">
                        <span class="text-blue-700 font-semibold">Non-Urgent</span>
                    </label>

                    <label class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer hover:bg-green-50">
                        <input type="radio" name="triage_category" value="Routine" class="text-green-600 focus:ring-green-500">
                        <span class="text-green-700 font-semibold">Routine</span>
                    </label>
                </div>
                @error('triage_category')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
            </section>

            <!-- Submit Button -->
            <div class="pt-6">
                <button type="submit"
                        class="w-full py-4 bg-green-600 text-white font-extrabold text-lg rounded-lg shadow-lg hover:bg-green-700 transition duration-150 transform hover:scale-[1.02]">
                    <i class="fas fa-clipboard-check mr-2"></i> Save Triage & Send to Doctor
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
