@extends('layouts.app')

@section('content')
<div class="p-6 bg-white shadow-2xl rounded-xl max-w-4xl mx-auto my-8">
    <!-- Back Button -->
    <a href="{{ route('outpatient.dashboard') }}" 
       class="inline-flex items-center px-4 py-2 mb-6 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition duration-150">
        <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
    </a>
    
    <h2 class="text-3xl font-extrabold text-gray-800 border-b-2 border-purple-100 pb-3 mb-6">
        <i class="fas fa-user-edit text-purple-600 mr-2"></i> Edit Patient Record
    </h2>
    
    <p class="text-lg text-gray-600 mb-6">
        Editing **{{ $patient->name }}** (Patient ID: <span class="font-mono text-purple-600">{{ $patient->patient_id }}</span>)
    </p>

    <!-- Patient Editing Form -->
    <form action="{{ route('patient.update', $patient->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 border border-purple-200 rounded-lg bg-purple-50">
        @csrf
        @method('PUT') {{-- Used to spoof the PUT request for updates --}}
        
        <!-- Row 1: Name & Phone -->
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" id="name" required 
                   value="{{ old('name', $patient->name) }}"
                   class="p-3 block w-full border border-gray-300 rounded-md @error('name') border-red-500 @enderror">
            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
            <input type="tel" name="phone" id="phone" required 
                   value="{{ old('phone', $patient->phone) }}"
                   class="p-3 block w-full border border-gray-300 rounded-md @error('phone') border-red-500 @enderror">
            @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <!-- Row 2: National ID & Age -->
        <div>
            <label for="national_id" class="block text-sm font-medium text-gray-700 mb-1">National ID (Optional)</label>
            <input type="text" name="national_id" id="national_id" 
                   value="{{ old('national_id', $patient->national_id) }}"
                   class="p-3 block w-full border border-gray-300 rounded-md @error('national_id') border-red-500 @enderror">
            @error('national_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="age" class="block text-sm font-medium text-gray-700 mb-1">Age <span class="text-red-500">*</span></label>
            <input type="number" name="age" id="age" required min="0" max="120"
                   value="{{ old('age', $patient->age) }}"
                   class="p-3 block w-full border border-gray-300 rounded-md @error('age') border-red-500 @enderror">
            @error('age')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <!-- Row 3: Gender & Next of Kin -->
        <div>
            <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">Gender <span class="text-red-500">*</span></label>
            <select name="gender" id="gender" required 
                     class="p-3 block w-full border border-gray-300 rounded-md @error('gender') border-red-500 @enderror">
                <option value="">Select Gender</option>
                <option value="Male" {{ old('gender', $patient->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                <option value="Female" {{ old('gender', $patient->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                <option value="Other" {{ old('gender', $patient->gender) == 'Other' ? 'selected' : '' }}>Other</option>
            </select>
            @error('gender')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        
        <div>
            <label for="next_of_kin" class="block text-sm font-medium text-gray-700 mb-1">Next of Kin Contact</label>
            <input type="text" name="next_of_kin" id="next_of_kin" 
                   value="{{ old('next_of_kin', $patient->next_of_kin) }}"
                   class="p-3 block w-full border border-gray-300 rounded-md @error('next_of_kin') border-red-500 @enderror">
            @error('next_of_kin')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <!-- Row 4: Address (Full Width) -->
        <div class="col-span-1 md:col-span-2">
            <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address / Residence</label>
            <textarea name="address" id="address" rows="2" 
                      class="p-3 block w-full border border-gray-300 rounded-md @error('address') border-red-500 @enderror">{{ old('address', $patient->address) }}</textarea>
            @error('address')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <!-- Submit Button -->
        <div class="col-span-1 md:col-span-2 pt-4">
            <button type="submit" class="w-full py-4 bg-purple-600 text-white font-extrabold text-lg rounded-lg shadow-xl hover:bg-purple-700 transition duration-150 transform hover:scale-[1.005]">
                <i class="fas fa-save mr-2"></i> Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
