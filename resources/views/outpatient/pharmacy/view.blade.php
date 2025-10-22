@extends('layouts.app') {{-- Assuming you have a layout file --}}

@section('content')
<div class="max-w-6xl mx-auto p-6 lg:p-10 bg-gray-50 rounded-xl shadow-2xl">
    
    <div class="flex justify-between items-center mb-8 border-b pb-4">
        <h1 class="text-3xl font-bold text-indigo-700 flex items-center">
            <i class="fas fa-file-prescription mr-3"></i> Prescription Processing
        </h1>
        <a href="{{ route('outpatient.dashboard', ['role' => 'pharmacist']) }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
            <i class="fas fa-arrow-left mr-2"></i> Back to Pharmacy Queue
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- LEFT COLUMN: Patient & Doctor Info --}}
        <div class="lg:col-span-1 space-y-6">
            
            {{-- Patient Details Card --}}
            <div class="bg-white p-6 rounded-lg shadow-md border-t-4 ">
                <h2 class="text-xl font-semibold mb-3 text-gray-800 flex items-center">
                    <i class="fas fa-user-injured mr-2 text-blue-500"></i> Patient Details
                </h2>
                <p class="text-sm border-b pb-2 mb-2">
                    <span class="font-medium text-gray-600">Name:</span> 
                    <span class="font-bold text-gray-900">{{ $prescription->visit->patient->name ?? 'N/A' }}</span>
                </p>
                <p class="text-sm border-b pb-2 mb-2">
                    <span class="font-medium text-gray-600">Token:</span> 
                    <span class="font-semibold text-blue-600">{{ $prescription->visit->visit_token ?? 'N/A' }}</span>
                </p>
                <p class="text-sm">
                    <span class="font-medium text-gray-600">Age/Gender:</span> 
                    {{ $prescription->visit->patient->age ?? 'N/A' }} yo, {{ $prescription->visit->patient->gender ?? 'N/A' }}
                </p>
            </div>

            {{-- Doctor Details Card --}}
            <div class="bg-white p-6 rounded-lg shadow-md border-t-4 ">
                <h2 class="text-xl font-semibold mb-3 text-gray-800 flex items-center">
                    <i class="fas fa-user-md mr-2 text-purple-500"></i> Prescribing Doctor
                </h2>
                <p class="text-sm font-medium text-gray-900">{{ $prescription->doctor->name ?? 'N/A' }}</p>
                <p class="text-xs text-gray-500 mt-1">
                    Issued: {{ $prescription->created_at->format('M d, Y h:i A') }} 
                    ({{ $prescription->created_at->diffForHumans() }})
                </p>
            </div>

            {{-- Doctor's Notes (Optional Context) --}}
            @if($prescription->visit->consultation && $prescription->visit->consultation->notes)
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold mb-2 text-gray-700 flex items-center">
                    <i class="fas fa-file-alt mr-2 text-gray-500"></i> Doctor's Consultation Notes
                </h3>
                <p class="text-sm text-gray-600 italic border p-3 rounded-md bg-gray-50">
                    {{ Str::limit($prescription->visit->consultation->notes, 300) }}
                </p>
            </div>
            @endif
        </div>
        
        {{-- RIGHT COLUMN: Prescription, Availability, and Dispense Form --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Prescription Details --}}
            <div class="bg-white p-6 rounded-lg shadow-xl border-l-4 ">
                <h2 class="text-2xl font-bold mb-4 text-green-700 flex items-center">
                    <i class="fas fa-prescription-bottle-alt mr-2"></i> Prescription Details
                </h2>
                <div class="bg-green-50 p-4 border border-green-200 rounded-lg whitespace-pre-wrap">
                    <p class="text-base text-gray-800 font-mono leading-relaxed">
                        {{ $prescription->prescription_details ?? 'No prescription details available.' }}
                    </p>
                </div>
            </div>

            {{-- Drug Availability Check --}}
            <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 
                @if($drugAvailability['isAvailable']) border-teal-500 @else  @endif">
                <h2 class="text-xl font-semibold mb-3 text-gray-800 flex items-center">
                    <i class="fas fa-warehouse mr-2 
                        @if($drugAvailability['isAvailable']) text-teal-500 @else text-red-500 @endif"></i> 
                    Drug Availability Check
                </h2>
                
                <p class="text-lg font-bold 
                    @if($drugAvailability['isAvailable']) text-teal-600 @else text-red-600 @endif">
                    Status: 
                    @if($drugAvailability['isAvailable']) 
                        Available (Stock: {{ $drugAvailability['stockLevel'] }})
                    @else 
                        Out of Stock (Stock: 0)
                    @endif
                </p>
                <p class="text-sm text-gray-600 mt-2 italic">{{ $drugAvailability['simulatedCheckNote'] }}</p>
            </div>

            {{-- Dispensation Form --}}
            <div class="bg-white p-6 rounded-lg shadow-xl border-t-4">
                <h2 class="text-2xl font-bold mb-4 text-indigo-700">Dispensation Action</h2>

                @if($prescription->status == 'Dispensed')
                    <div class="p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-lg text-center">
                        <i class="fas fa-check-circle mr-2"></i> This prescription has already been marked as **Dispensed**.
                    </div>
                @elseif(!$drugAvailability['isAvailable'])
                    <div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg text-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i> **Cannot Dispense:** Medication is currently out of stock.
                    </div>
                @else
                    <form action="{{ route('pharmacy.dispense', $prescription->id) }}" method="POST">
                        @csrf
                        {{-- NOTE: Assuming your storeDispense method accepts a POST request with the Prescription model --}}
                        
                        <div class="mb-4">
                            <label for="dispense_notes" class="block text-sm font-medium text-gray-700 mb-1">
                                Pharmacist Notes (Optional)
                            </label>
                            <textarea name="dispense_notes" id="dispense_notes" rows="3"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="E.g., Patient counselled on side effects. Substitution with brand 'X' approved."></textarea>
                            @error('dispense_notes')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <button type="submit"
                            class="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-lg 
                                   text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition transform hover:scale-[1.01]">
                            <i class="fas fa-check-circle mr-2"></i> Confirm Dispensation & Clear Patient
                        </button>
                    </form>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection