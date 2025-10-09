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
               ← Back to Triage Queue
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
                        $vitals = [
                            'BP (mmHg)' => $visit->triage->bp ?? 'N/A',
                            'Temp (°C)' => $visit->triage->temperature ?? 'N/A',
                            'Pulse (bpm)' => $visit->triage->pulse ?? 'N/A',
                            'SpO2 (%)' => $visit->triage->spo2 ?? 'N/A',
                            'Weight (kg)' => $visit->triage->weight ?? 'N/A',
                            'Height (cm)' => $visit->triage->height ?? 'N/A'
                        ];
                    @endphp

                    @foreach ($vitals as $label => $value)
                        <div class="text-center p-5 bg-gray-50 rounded-lg border border-gray-200 hover:shadow transition">
                            <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
                            <p class="text-2xl font-extrabold text-gray-800 mt-1">{{ $value }}</p>
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

                    <!-- Nursing Notes -->
                    <div class="p-6 bg-gray-50 rounded-lg border border-gray-200 hover:shadow transition">
                        <p class="text-lg font-semibold text-gray-900 mb-2">Symptoms</p>
                        <p class="text-gray-700 leading-relaxed">
                            {{ $visit->triage->symptoms ?? 'No symptoms notes recorded.' }}
                        </p>
                    </div>
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
                <p class="text-2xl font-medium mb-2">Triage record not found or not completed yet.</p>
                <p class="text-sm">Check the visit status or verify the record exists.</p>
            </div>
        @endif
    </div>
</div>
@endsection
