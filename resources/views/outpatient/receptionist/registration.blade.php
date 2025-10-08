@extends('layouts.app')

@section('content')
<div class="p-6 bg-white shadow-2xl rounded-xl max-w-6xl mx-auto my-8">
    
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('outpatient.dashboard') }}" 
           class="inline-flex items-center text-indigo-600 hover:text-indigo-800 transition duration-150 font-medium">
            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    <h2 class="text-3xl font-extrabold text-gray-800 border-b-2 border-indigo-100 pb-3 mb-6">Patient Registration & Lookup</h2>

    <!-- Search Form -->
    <form action="{{ url('/outpatient/search') }}" method="POST" class="flex space-x-3 mb-8 bg-gray-50 p-4 rounded-lg shadow-inner">
        @csrf
        <input type="text" name="search_term" placeholder="Search by National ID, Phone, or Patient ID" 
               value="{{ session('search_term') ?? old('search_term') }}"
               class="flex-1 p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-gray-700">
        <button type="submit" class="px-8 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition duration-150 transform hover:scale-[1.01]">
            <i class="fas fa-search mr-1"></i> Search
        </button>
    </form>

    <!-- Existing Patient Confirmation Block -->
    @if(isset($patient) && $patient)
        <div class="border-l-4 border-green-500 p-6 bg-green-50 mb-8 rounded-lg shadow-md">
            <h3 class="font-bold text-xl text-green-800 flex items-center">
                <i class="fas fa-check-circle mr-2"></i> Patient Found!
            </h3>
            <p class="text-green-700 mt-2">
                <strong>ID:</strong> {{ $patient->patient_id }} | 
                <strong>Name:</strong> {{ $patient->name }} | 
                <strong>Phone:</strong> {{ $patient->phone }}
            </p>
            <p class="mt-4 text-green-700">Confirm details and click **'Start New Outpatient Visit'** to proceed to Triage.</p>

            <form action="{{ url('/outpatient/store') }}" method="POST" class="mt-4">
                @csrf
                <input type="hidden" name="existing_patient_id" value="{{ $patient->id }}">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition shadow-lg">
                    Start New Outpatient Visit
                </button>
            </form>
        </div>
    @else 
        <!-- New Patient Registration Form -->
        <h3 class="text-xl font-semibold text-gray-700 mb-5">
            Register New Patient
            @if(session('search_fail'))
                <span class="text-red-500 text-sm ml-3">(Patient not found by search, please register below)</span>
            @endif
        </h3>

        <form action="{{ url('/outpatient/store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 border border-gray-200 rounded-lg">
            @csrf
            
            <!-- Row 1: Name & Phone -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" required value="{{ old('name') }}"
                        class="p-3 block w-full border border-gray-300 rounded-md @error('name') border-red-500 @enderror">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                <input type="tel" name="phone" id="phone" required value="{{ old('phone') }}"
                        class="p-3 block w-full border border-gray-300 rounded-md @error('phone') border-red-500 @enderror">
                @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Row 2: National ID & Age -->
            <div>
                <label for="national_id" class="block text-sm font-medium text-gray-700 mb-1">National ID (Optional)</label>
                <input type="text" name="national_id" id="national_id" value="{{ old('national_id') }}"
                        class="p-3 block w-full border border-gray-300 rounded-md @error('national_id') border-red-500 @enderror">
                @error('national_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="age" class="block text-sm font-medium text-gray-700 mb-1">Age <span class="text-red-500">*</span></label>
                <input type="number" name="age" id="age" required value="{{ old('age') }}" min="0" max="120"
                        class="p-3 block w-full border border-gray-300 rounded-md @error('age') border-red-500 @enderror">
                @error('age')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Row 3: Gender & Next of Kin -->
            <div>
                <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">Gender <span class="text-red-500">*</span></label>
                <select name="gender" id="gender" required 
                         class="p-3 block w-full border border-gray-300 rounded-md @error('gender') border-red-500 @enderror">
                    <option value="">Select Gender</option>
                    <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                    <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('gender')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            
            <div>
                <label for="next_of_kin" class="block text-sm font-medium text-gray-700 mb-1">Next of Kin Contact</label>
                <input type="text" name="next_of_kin" id="next_of_kin" value="{{ old('next_of_kin') }}"
                        class="p-3 block w-full border border-gray-300 rounded-md @error('next_of_kin') border-red-500 @enderror">
                @error('next_of_kin')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Row 4: Address (Full Width) -->
            <div class="col-span-1 md:col-span-2">
                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address / Residence</label>
                <textarea name="address" id="address" rows="2" 
                              class="p-3 block w-full border border-gray-300 rounded-md @error('address') border-red-500 @enderror">{{ old('address') }}</textarea>
                @error('address')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Submit Button -->
            <div class="col-span-1 md:col-span-2 pt-4">
                <button type="submit" class="w-full py-4 bg-green-600 text-white font-extrabold text-lg rounded-lg shadow-xl hover:bg-green-700 transition duration-150 transform hover:scale-[1.005]">
                    <i class="fas fa-save mr-2"></i> Register & Start Outpatient Visit
                </button>
            </div>
        </form>
    @endif
</div>
@endsection
