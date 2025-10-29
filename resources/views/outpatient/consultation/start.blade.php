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

    {{-- Patient Info Block --}}
    <div class="mb-6 border border-gray-200 rounded-lg p-4 bg-gray-50">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
            <div class="col-span-2 lg:col-span-1">
                <span class="font-semibold text-gray-600">Patient:</span> 
                <span class="font-medium text-gray-900">{{ $visit->patient->name }} ({{ $visit->patient->gender }}, {{ $visit->patient->age }} yo)</span>
            </div>
            <div class="col-span-2 lg:col-span-1">
                <span class="font-semibold text-gray-600">Visit Token:</span> 
                <span class="font-medium text-blue-600">{{ $visit->visit_token }}</span>
            </div>
            <div class="col-span-2 lg:col-span-1">
                <span class="font-semibold text-gray-600">Triage Priority:</span> 
                <span class="font-bold text-lg @if($visit->triage->triage_category === 'Emergency') text-red-600 @elseif($visit->triage->triage_category === 'Urgent') text-orange-500 @else text-green-600 @endif">{{ $visit->triage->triage_category ?? 'N/A' }}</span>
            </div>
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
            
            {{-- Diagnosis --}}
            <div>
                <label for="diagnosis" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-microscope mr-1 text-red-500"></i> Diagnosis <span class="text-red-500">*</span>
                </label>
                <textarea name="diagnosis" id="diagnosis" rows="3" required
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3 focus:ring-indigo-500 focus:border-indigo-500"
                >{{ old('diagnosis', $consultation->diagnosis ?? '') }}</textarea>
                @error('diagnosis')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- Detailed Notes --}}
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-file-medical-alt mr-1 text-blue-500"></i> Detailed Notes / History of Present Illness
                </label>
                <textarea name="notes" id="notes" rows="5"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3 focus:ring-indigo-500 focus:border-indigo-500"
                >{{ old('notes', $consultation->notes ?? '') }}</textarea>
                @error('notes')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- Initial Treatment Plan (still text for general info) --}}
            <div>
                <label for="treatment_plan" class="block text-sm font-medium text-gray-700 mb-1">
                    <i class="fas fa-pills mr-1 text-green-500"></i> Initial Treatment / Management Plan
                </label>
                <textarea name="treatment_plan" id="treatment_plan" rows="3"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3 focus:ring-indigo-500 focus:border-indigo-500"
                >{{ old('treatment_plan', $consultation->treatment_plan ?? '') }}</textarea>
                @error('treatment_plan')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
        
        <hr class="my-8">

        {{-- UPDATED: Dynamic Prescription Section --}}
        <div id="pharmacy-prescription-section" class="mt-8 pt-6 border-t border-gray-200 
            @if(old('next_step') !== 'Pharmacy' && empty($consultation->prescription_items)) hidden @endif">
            
            <h3 class="text-xl font-semibold mb-4 text-gray-800 flex justify-between items-center">
                <span><i class="fas fa-file-prescription mr-2 text-green-600"></i> **Prescription Builder**</span>
                <button type="button" id="add-prescription-btn" class="text-sm font-medium text-white bg-green-500 hover:bg-green-600 px-4 py-2 rounded-md transition shadow-md">
                    <i class="fas fa-plus mr-1"></i> Add Medication
                </button>
            </h3>

            <div id="prescription-list" class="space-y-4">
                {{-- Dynamic prescription rows will be appended here --}}
                
                @if (empty($consultation->prescription_items))
                    <p class="text-sm text-gray-500 py-4 text-center border-2 border-dashed rounded-lg">No medications added yet.</p>
                @endif
                
                {{-- If editing, load existing prescriptions here --}}
                {{-- @foreach(old('prescriptions', $consultation->prescription_items ?? []) as $index => $item)
                    // Render existing item using the template structure below
                @endforeach --}}
            </div>
        </div>
        {{-- END UPDATED: Prescription Section --}}


        {{-- UPDATED: Lab and Radiology Orders Section --}}
        <div id="lab-rad-orders-section" class="mt-8 pt-6 border-t border-gray-200 
            @if(old('next_step') !== 'Lab/Rad' && empty($consultation->lab_orders) && empty($consultation->radiology_orders)) hidden @endif">
            
            <h3 class="text-xl font-semibold mb-4 text-gray-800">
                <i class="fas fa-vials mr-2 text-yellow-600"></i> **Lab & Radiology Orders**
            </h3>

            <div class="space-y-6">
                
                {{-- Lab Test Catalog Selection --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-flask mr-1 text-yellow-600"></i> Laboratory Test Orders (Select from Catalog)
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 border p-4 rounded-lg bg-white shadow-inner max-h-80 overflow-y-auto">
                        @forelse ($labTests ?? [] as $test)
                            <label class="inline-flex items-center p-2 bg-gray-100 hover:bg-gray-200 rounded-md transition cursor-pointer w-full">
                                <input 
                                    type="checkbox" 
                                    name="lab_test_ids[]" 
                                    value="{{ $test->id }}" 
                                    class="form-checkbox h-4 w-4 text-yellow-600 border-gray-300 rounded focus:ring-yellow-500"
                                    {{-- Use a check to re-select on validation error or for editing --}}
                                    @checked(in_array($test->id, old('lab_test_ids', $consultation->lab_orders_ids ?? [])))
                                >
                                <span class="ml-2 text-sm text-gray-700 font-medium truncate" title="{{ $test->name }}">
                                    {{ $test->name }}
                                    <span class="text-xs text-gray-500 block">({{ $test->price ?? 'No Price' }})</span>
                                </span>
                            </label>
                        @empty
                            <p class="col-span-4 text-center text-red-500 font-medium">Lab Test Catalog is empty. Please contact the administrator.</p>
                        @endforelse
                    </div>
                    @error('lab_test_ids')<p class="mt-2 text-sm text-red-600">Please select at least one lab test or clear the Lab/Rad option.</p>@enderror
                </div>
                
                {{-- Radiology Orders (Still free text for non-standardized orders) --}}
                <div>
                    <label for="radiology_orders" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-x-ray mr-1 text-red-600"></i> Radiology/Imaging Orders (Free Text)
                    </label>
                    <textarea name="radiology_orders" id="radiology_orders" rows="3"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-3 focus:ring-red-500 focus:border-red-500"
                        placeholder="e.g., Chest X-Ray (CXR), Abdominal Ultrasound, CT Head w/o contrast.">{{ old('radiology_orders', $consultation->radiology_orders ?? '') }}</textarea>
                    @error('radiology_orders')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
        {{-- END UPDATED: Lab and Radiology Orders Section --}}

        {{-- Next Destination --}}
        <div class="mt-8 pt-6 border-t border-gray-200">
            <h3 class="text-xl font-semibold mb-4 text-gray-800">
                <i class="fas fa-share-square mr-2"></i> Select Next Patient Destination
            </h3>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                
                @php 
                    $currentNextStep = old('next_step', $consultation->next_step ?? null); 
                @endphp

                <label class="block cursor-pointer">
                    <input type="radio" name="next_step" value="Pharmacy" id="pharmacy_radio" class="sr-only next-step-radio" required @checked($currentNextStep === 'Pharmacy')>
                    <div class="p-4 border-2 border-gray-300 rounded-lg text-center hover:border-indigo-500 transition duration-150 @if($currentNextStep === 'Pharmacy') bg-indigo-50 border-indigo-600 ring-2 ring-indigo-500 @endif">
                        <i class="fas fa-prescription-bottle-alt text-4xl text-green-600 mb-2"></i>
                        <p class="font-semibold text-gray-800">Pharmacy</p>
                        <p class="text-xs text-gray-500">Issue medications.</p>
                    </div>
                </label>

                <label class="block cursor-pointer">
                    <input type="radio" name="next_step" value="Lab/Rad" id="lab_rad_radio" class="sr-only next-step-radio" required @checked($currentNextStep === 'Lab/Rad')>
                    <div class="p-4 border-2 border-gray-300 rounded-lg text-center hover:border-indigo-500 transition duration-150 @if($currentNextStep === 'Lab/Rad') bg-indigo-50 border-indigo-600 ring-2 ring-indigo-500 @endif">
                        <i class="fas fa-flask text-4xl text-yellow-600 mb-2"></i>
                        <p class="font-semibold text-gray-800">Lab / Radiology</p>
                        <p class="text-xs text-gray-500">Order tests/scans.</p>
                    </div>
                </label>

                <label class="block cursor-pointer">
                    <input type="radio" name="next_step" value="Inpatient" class="sr-only next-step-radio" required @checked($currentNextStep === 'Inpatient')>
                    <div class="p-4 border-2 border-gray-300 rounded-lg text-center hover:border-indigo-500 transition duration-150 @if($currentNextStep === 'Inpatient') bg-indigo-50 border-indigo-600 ring-2 ring-indigo-500 @endif">
                        <i class="fas fa-bed text-4xl text-red-600 mb-2"></i>
                        <p class="font-semibold text-gray-800">Admit Inpatient</p>
                        <p class="text-xs text-gray-500">Requires hospitalization.</p>
                    </div>
                </label>

                <label class="block cursor-pointer">
                    <input type="radio" name="next_step" value="Discharged" class="sr-only next-step-radio" required @checked($currentNextStep === 'Discharged')>
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

{{-- Hidden Template for Dynamic Prescription Row --}}
<div id="prescription-template" class="hidden">
    <div class="prescription-item p-4 border border-green-300 rounded-lg bg-green-50 shadow-sm relative">
        <button type="button" class="remove-prescription-btn absolute top-2 right-2 text-red-500 hover:text-red-700">
            <i class="fas fa-times-circle"></i>
        </button>
        
        <div class="grid grid-cols-6 gap-3">
            {{-- Medication Selection --}}
            <div class="col-span-6">
                <label class="block text-xs font-medium text-gray-700 mb-1">Medication <span class="text-red-500">*</span></label>
                <select name="prescriptions[0][medication_id]" required
                    class="block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm focus:ring-green-500 focus:border-green-500">
                    <option value="">-- Select Medication --</option>
                    @foreach ($medications ?? [] as $med)
                        <option value="{{ $med->id }}" data-price="{{ $med->price ?? 0 }}">
                            {{ $med->name }} ({{ $med->price ?? 'No Price' }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Quantity --}}
            <div class="col-span-2">
                <label class="block text-xs font-medium text-gray-700 mb-1">Quantity <span class="text-red-500">*</span></label>
                <input type="number" name="prescriptions[0][quantity]" min="1" required
                    class="block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm focus:ring-green-500 focus:border-green-500" placeholder="e.g., 14">
            </div>

            {{-- Dosage/Route --}}
            <div class="col-span-2">
                <label class="block text-xs font-medium text-gray-700 mb-1">Dosage / Route</label>
                <input type="text" name="prescriptions[0][dosage]"
                    class="block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm focus:ring-green-500 focus:border-green-500" placeholder="e.g., 500mg or Topical">
            </div>

            {{-- Duration --}}
            <div class="col-span-2">
                <label class="block text-xs font-medium text-gray-700 mb-1">Duration</label>
                <input type="text" name="prescriptions[0][duration]"
                    class="block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm focus:ring-green-500 focus:border-green-500" placeholder="e.g., 7 days">
            </div>

            {{-- Frequency --}}
            <div class="col-span-6">
                <label class="block text-xs font-medium text-gray-700 mb-1">Frequency</label>
                <input type="text" name="prescriptions[0][frequency]"
                    class="block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm focus:ring-green-500 focus:border-green-500" placeholder="e.g., Once Daily (OD), Twice a Day (BD), PRN">
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const pharmacyRadio = document.getElementById('pharmacy_radio');
        const labRadRadio = document.getElementById('lab_rad_radio');
        const pharmacySection = document.getElementById('pharmacy-prescription-section');
        const ordersSection = document.getElementById('lab-rad-orders-section');
        const nextStepRadios = document.querySelectorAll('.next-step-radio');

        const addPrescriptionBtn = document.getElementById('add-prescription-btn');
        const prescriptionList = document.getElementById('prescription-list');
        const template = document.getElementById('prescription-template').innerHTML;
        let prescriptionIndex = 0;

        /**
         * Toggles the visibility of the input sections based on the selected next step.
         */
        function toggleInputSections() {
            // Hide all specialized sections initially
            pharmacySection.classList.add('hidden');
            ordersSection.classList.add('hidden');
            
            if (pharmacyRadio && pharmacyRadio.checked) {
                // If Pharmacy is checked, show Prescription section
                pharmacySection.classList.remove('hidden');
            } else if (labRadRadio && labRadRadio.checked) {
                // If Lab/Rad is checked, show Lab/Rad Orders section
                ordersSection.classList.remove('hidden');
            }
        }
        
        // --- Dynamic Prescription Functions ---

        /**
         * Adds a new prescription item row to the form.
         */
        function addPrescriptionItem() {
            // Replace the placeholder index '0' in the template with the current index
            let newRow = template.replace(/\[0\]/g, '[' + prescriptionIndex + ']');
            
            // If the "No medications added yet." message exists, remove it
            const emptyMessage = prescriptionList.querySelector('p.text-sm.text-gray-500');
            if (emptyMessage) {
                emptyMessage.remove();
            }

            // Append the new row HTML to the list
            prescriptionList.insertAdjacentHTML('beforeend', newRow);
            
            // Find the newly added item and its remove button
            const newItem = prescriptionList.lastElementChild;
            const removeBtn = newItem.querySelector('.remove-prescription-btn');
            
            // Add listener to the remove button
            removeBtn.addEventListener('click', function() {
                newItem.remove();
                // If no items remain, show the empty message (optional, but good UX)
                if (prescriptionList.children.length === 0) {
                    prescriptionList.insertAdjacentHTML('beforeend', '<p class="text-sm text-gray-500 py-4 text-center border-2 border-dashed rounded-lg">No medications added yet.</p>');
                }
            });

            prescriptionIndex++;
        }

        // --- Event Listeners and Initial State ---

        // Add event listener to all radio buttons for section toggling
        nextStepRadios.forEach(radio => {
            radio.addEventListener('change', toggleInputSections);
        });

        // Add event listener for the 'Add Medication' button
        addPrescriptionBtn.addEventListener('click', addPrescriptionItem);

        // Initial check on page load
        toggleInputSections();
        
        // If the section is visible on load (e.g., old data exists or form error), ensure the index is correct
        if (pharmacySection && !pharmacySection.classList.contains('hidden') && prescriptionList.children.length > 0) {
            // If you are loading existing data, you'd need logic here to populate the form and set the starting index.
            // For simplicity in this answer, we'll start index at 0, assuming existing data is handled by Laravel's old() function or not present.
        } else if (pharmacySection && !pharmacySection.classList.contains('hidden') && prescriptionList.children.length === 0) {
            // If Pharmacy is the selected destination but no prescriptions exist (first time), add one starter row for better UX.
            // addPrescriptionItem(); 
        }
    });
</script>
@endsection