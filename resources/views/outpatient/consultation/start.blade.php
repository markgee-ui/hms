{{-- resources/views/outpatient/consultation/start.blade.php --}}

@extends('layouts.app') {{-- Assuming you have a layout file --}}

@section('content')
<div class="max-w-7xl mx-auto p-6 lg:p-10 bg-white shadow-2xl rounded-xl">
    
    <div class="flex justify-between items-center mb-6 border-b pb-2">
        {{-- Back Button --}}
        <h1 class="text-3xl font-bold text-indigo-700 flex items-center">
            <i class="fas fa-user-md mr-3"></i> Patient Consultation
        </h1>
        <a href="{{ route('outpatient.dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
            <i class="fas fa-arrow-left mr-2"></i> Back to Queue
        </a>
    </div>

    <div class="mb-6 border border-gray-200 rounded-lg p-4 bg-gray-50">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
            {{-- Patient Info --}}
            <div class="col-span-2 lg:col-span-1">
                <span class="font-semibold text-gray-600">Patient:</span> 
                <span class="font-medium text-gray-900">{{ $visit->patient->name }} ({{ $visit->patient->gender }}, {{ $visit->patient->age }} yo)</span>
            </div>
            
            {{-- Visit Token --}}
            <div class="col-span-2 lg:col-span-1">
                <span class="font-semibold text-gray-600">Visit Token:</span> 
                <span class="font-medium text-blue-600">{{ $visit->visit_token }}</span>
            </div>
            
            {{-- Triage Priority --}}
            <div class="col-span-2 lg:col-span-1">
                <span class="font-semibold text-gray-600">Triage Priority:</span> 
                <span class="font-bold text-lg @if($visit->triage->triage_category === 'Emergency') text-red-600 @elseif($visit->triage->triage_category === 'Urgent') text-orange-500 @else text-green-600 @endif">{{ $visit->triage->triage_category ?? 'N/A' }}</span>
            </div>

            {{-- Chief Complaint (made full width for visibility) --}}
            <div class="col-span-2 lg:col-span-4 border-t pt-2 mt-2">
                <span class="font-semibold text-gray-600">Chief Complaint:</span> 
                <span class="font-medium text-gray-900">{{ $visit->triage->chief_complaint ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Validation Error!</strong>
            <span class="block sm:inline">Please check the form below for errors.</span>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('consultation.store', $visit->id) }}" method="POST">
        @csrf

        <div class="space-y-6">
            
            <div>
                <label for="diagnosis" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-microscope mr-1 text-red-500"></i> Diagnosis <span class="text-red-500">*</span>
                </label>
                <textarea name="diagnosis" id="diagnosis" rows="3" required
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3 focus:ring-indigo-500 focus:border-indigo-500">
                    {{ old('diagnosis', $consultation->diagnosis) }}
                </textarea>
                @error('diagnosis')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-file-medical-alt mr-1 text-blue-500"></i> Detailed Notes / History of Present Illness
                </label>
                <textarea name="notes" id="notes" rows="5"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3 focus:ring-indigo-500 focus:border-indigo-500">
                    {{ old('notes', $consultation->notes) }}
                </textarea>
                @error('notes')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="treatment_plan" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-pills mr-1 text-green-500"></i> Initial Treatment / Medication Plan
                </label>
                <textarea name="treatment_plan" id="treatment_plan" rows="3"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3 focus:ring-indigo-500 focus:border-indigo-500">
                    {{ old('treatment_plan', $consultation->treatment_plan) }}
                </textarea>
                @error('treatment_plan')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-200">
            <h3 class="text-xl font-semibold mb-4 text-gray-800">
                <i class="fas fa-share-square mr-2"></i> Select Next Patient Destination
            </h3>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                
                @php 
                    $currentNextStep = old('next_step'); 
                @endphp

                <label class="block cursor-pointer">
                    <input type="radio" name="next_step" value="Pharmacy" class="sr-only" required @checked($currentNextStep === 'Pharmacy')>
                    <div class="p-4 border-2 border-gray-300 rounded-lg text-center hover:border-indigo-500 transition duration-150 @if($currentNextStep === 'Pharmacy') bg-indigo-50 border-indigo-600 ring-2 ring-indigo-500 @endif">
                        <i class="fas fa-prescription-bottle-alt text-4xl text-green-600 mb-2"></i>
                        <p class="font-semibold text-gray-800">Pharmacy</p>
                        <p class="text-xs text-gray-500">Issue medications.</p>
                    </div>
                </label>

                <label class="block cursor-pointer">
                    <input type="radio" name="next_step" value="Lab/Rad" class="sr-only" required @checked($currentNextStep === 'Lab/Rad')>
                    <div class="p-4 border-2 border-gray-300 rounded-lg text-center hover:border-indigo-500 transition duration-150 @if($currentNextStep === 'Lab/Rad') bg-indigo-50 border-indigo-600 ring-2 ring-indigo-500 @endif">
                        <i class="fas fa-flask text-4xl text-yellow-600 mb-2"></i>
                        <p class="font-semibold text-gray-800">Lab / Radiology</p>
                        <p class="text-xs text-gray-500">Order tests/scans.</p>
                    </div>
                </label>

                <label class="block cursor-pointer">
                    <input type="radio" name="next_step" value="Inpatient" class="sr-only" required @checked($currentNextStep === 'Inpatient')>
                    <div class="p-4 border-2 border-gray-300 rounded-lg text-center hover:border-indigo-500 transition duration-150 @if($currentNextStep === 'Inpatient') bg-indigo-50 border-indigo-600 ring-2 ring-indigo-500 @endif">
                        <i class="fas fa-bed text-4xl text-red-600 mb-2"></i>
                        <p class="font-semibold text-gray-800">Admit Inpatient</p>
                        <p class="text-xs text-gray-500">Requires hospitalization.</p>
                    </div>
                </label>

                <label class="block cursor-pointer">
                    <input type="radio" name="next_step" value="Discharged" class="sr-only" required @checked($currentNextStep === 'Discharged')>
                    <div class="p-4 border-2 border-gray-300 rounded-lg text-center hover:border-indigo-500 transition duration-150 @if($currentNextStep === 'Discharged') bg-indigo-50 border-indigo-600 ring-2 ring-indigo-500 @endif">
                        <i class="fas fa-sign-out-alt text-4xl text-gray-500 mb-2"></i>
                        <p class="font-semibold text-gray-800">Discharge</p>
                        <p class="text-xs text-gray-500">Consultation complete.</p>
                    </div>
                </label>

            </div>
             @error('next_step')<p class="mt-2 text-sm text-red-600 font-medium">You must select a next destination for the patient.</p>@enderror
        </div>

        <div class="mt-8 flex justify-end">
            <button type="submit" class="w-full md:w-auto inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition transform hover:scale-[1.02]">
                <i class="fas fa-save mr-2"></i> Save Consultation & Send Patient
            </button>
        </div>
    </form>
</div>
@endsection