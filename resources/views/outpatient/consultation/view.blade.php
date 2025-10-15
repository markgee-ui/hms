@extends('layouts.app')

@section('content')
<div class="w-full min-h-screen bg-gray-50 py-10">
    <div class="w-11/12 mx-auto bg-white p-10 rounded-2xl shadow-lg border border-gray-200">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b-2 border-indigo-500 pb-4 mb-6">
            <h2 class="text-3xl font-bold text-gray-800">
                Triage Record View: {{ $visit->patient->name }}
            </h2>
            <a href="{{ route('outpatient.dashboard') }}" 
               class="mt-4 md:mt-0 inline-flex items-center justify-center px-6 py-2 text-sm font-semibold rounded-full text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
                ← Back to Consultation Queue
            </a>
        </div>

        <!-- Patient Overview -->
        <div class="mb-10 p-6 bg-gray-50 border border-gray-200 rounded-xl grid grid-cols-1 md:grid-cols-3 gap-6 text-base">
            <div>
                <span class="font-semibold text-gray-700">Visit Token:</span>
                <span class="text-indigo-600 font-semibold">{{ $visit->visit_token }}</span>
            </div>
            <div>
                <span class="font-semibold text-gray-700">Patient Name:</span>
                <span class="text-gray-900">{{ $visit->patient->name }}</span>
            </div>
            <div>
                <span class="font-semibold text-gray-700">Current Status:</span>
                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                    {{ $visit->status }}
                </span>
            </div>
        </div>

        @if ($visit->triage)
            <!-- Vitals Section -->
            <div class="mb-10">
                <h3 class="text-2xl font-semibold mb-4 text-gray-800 border-b border-gray-300 pb-2">
                    Vital Signs
                </h3>

                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
                    @php
                        // --- VITAL CLASSIFICATION LOGIC ---
                        
                        // 1. Function to determine BP color class
                        function getBpClasses($bpValue) {
                            if ($bpValue === 'N/A' || !str_contains($bpValue, '/')) {
                                return ['bg' => 'bg-gray-50 border-gray-200', 'text' => 'text-gray-800'];
                            }

                            // Safely split and convert BP values
                            $parts = explode('/', $bpValue);
                            if (count($parts) !== 2) {
                                return ['bg' => 'bg-gray-50 border-gray-200', 'text' => 'text-gray-800'];
                            }
                            
                            [$systolic, $diastolic] = array_map('intval', $parts);

                            // CRITICAL / RED (Hypertension Stage 2 or Low Hypotension)
                            // Low is defined as <90/60
                            if ($systolic >= 140 || $diastolic >= 90 || ($systolic < 90 && $diastolic < 60)) {
                                return ['bg' => 'bg-red-100 border-red-400', 'text' => 'text-red-800'];
                            }

                            // WARNING / YELLOW (Elevated or Stage 1 Hypertension)
                            // Elevated: 120-129 S AND <80 D
                            // Stage 1: 130-139 S OR 80-89 D
                            if (($systolic >= 120 && $systolic < 140) || ($diastolic >= 80 && $diastolic < 90)) {
                                return ['bg' => 'bg-yellow-100 border-yellow-400', 'text' => 'text-yellow-800'];
                            }

                            // NORMAL / GREEN
                            return ['bg' => 'bg-green-100 border-green-400', 'text' => 'text-green-800'];
                        }

                        // 2. Function to determine Temperature color class (in Celsius)
                        function getTempClasses($tempValue) {
                            if (!is_numeric($tempValue)) {
                                return ['bg' => 'bg-gray-50 border-gray-200', 'text' => 'text-gray-800'];
                            }

                            $temp = (float) $tempValue;

                            // CRITICAL / RED (Fever or Hypothermia)
                            // Fever >= 38.0 C (100.4 F)
                            // Hypothermia <= 35.0 C (95 F)
                            if ($temp >= 38.0 || $temp <= 35.0) {
                                return ['bg' => 'bg-red-100 border-red-400', 'text' => 'text-red-800'];
                            }

                            // WARNING / YELLOW (Slightly high or slightly low)
                            // Slightly High: 37.4 - 37.9
                            // Slightly Low: 35.1 - 36.4
                            if (($temp > 37.3 && $temp < 38.0) || ($temp > 35.0 && $temp < 36.5)) {
                                 return ['bg' => 'bg-yellow-100 border-yellow-400', 'text' => 'text-yellow-800'];
                            }

                            // NORMAL / GREEN
                            return ['bg' => 'bg-green-100 border-green-400', 'text' => 'text-green-800'];
                        }
                        // --- END OF LOGIC ---


                        $vitals = [
                            'BP (mmHg)' => $visit->triage->bp ?? 'N/A',
                            'Temp (°C)' => $visit->triage->temperature ?? 'N/A',
                            'Pulse (bpm)' => $visit->triage->pulse ?? 'N/A',
                            'SpO2 (%)' => $visit->triage->spo2 ?? 'N/A',
                            'Weight (kg)' => $visit->triage->weight ?? 'N/A',
                            'Height (cm)' => $visit->triage->height ?? 'N/A'
                        ];

                        // Determine classes for BP and Temperature based on logic
                        $bpClasses = getBpClasses($vitals['BP (mmHg)']);
                        $tempClasses = getTempClasses($vitals['Temp (°C)']);
                    @endphp

                    @foreach ($vitals as $label => $value)
                        @php
                            // Default classes for non-critical vitals
                            $classes = ['bg' => 'bg-gray-50 border-gray-200', 'text' => 'text-gray-800'];

                            // Override classes for BP and Temperature
                            if ($label === 'BP (mmHg)') {
                                $classes = $bpClasses;
                            } elseif ($label === 'Temp (°C)') {
                                $classes = $tempClasses;
                            }
                        @endphp
                        {{-- Apply the determined classes to the vital sign card --}}
                        <div class="text-center p-5 rounded-lg border {{ $classes['bg'] }} hover:shadow transition">
                            <p class="text-sm font-medium text-gray-700">{{ $label }}</p>
                            <p class="text-2xl font-extrabold {{ $classes['text'] }} mt-1">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Assessment Details -->
            <div>
                <h3 class="text-2xl font-semibold mb-4 text-gray-800 border-b border-gray-300 pb-2">
                    Assessment Details
                </h3>

                <div class="grid md:grid-cols-2 gap-8">
                    <!-- Chief Complaint -->
                    <div class="p-6 bg-gray-50 rounded-lg border border-gray-200 hover:shadow transition">
                        <p class="text-lg font-semibold text-gray-900 mb-2">Chief Complaint</p>
                        <p class="text-gray-700 leading-relaxed">
                            {{ $visit->triage->chief_complaint ?? 'No complaint recorded.' }}
                        </p>
                    </div>

                    <!-- Symptoms -->
                    <div class="p-6 bg-gray-50 rounded-lg border border-gray-200 hover:shadow transition">
                        <p class="text-lg font-semibold text-gray-900 mb-2">Symptoms</p>
                        <p class="text-gray-700 leading-relaxed">
                            {{ $visit->triage->symptoms ?? 'No symptoms notes recorded.' }}
                        </p>
                    </div>
                    
                    <!-- Triage Category -->
                    <div class="p-6 bg-gray-50 rounded-lg border border-gray-200 hover:shadow transition">
                        <p class="text-lg font-semibold text-gray-900 mb-2">Triage Category</p>
                        <p class="text-gray-700 leading-relaxed">
                            {{ $visit->triage->triage_category ?? 'No category recorded.' }}
                        </p>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-16 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                <p class="text-2xl font-medium mb-2">Patient record not found or not completed yet.</p>
                <p class="text-sm">Check the visit status or verify the record exists.</p>
            </div>
        @endif
    </div>
</div>
@endsection
