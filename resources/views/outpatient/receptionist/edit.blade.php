@extends('layouts.app')

@section('content')
<div class="min-h-screen w-full bg-gray-50 p-10">
    <div class="bg-white p-10 rounded-2xl shadow-2xl border border-gray-100 mx-auto w-full">

        <!-- Back Button -->
        <div class="flex justify-between items-center mb-8 border-b pb-3">
            <h2 class="text-4xl font-extrabold text-gray-800 flex items-center">
                <i class="fas fa-user-edit text-purple-600 mr-3"></i> Edit Patient Record
            </h2>
            <a href="{{ route('outpatient.dashboard') }}" 
               class="inline-flex items-center px-5 py-2 text-base font-semibold text-gray-600 hover:text-purple-700 transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>

        <p class="text-xl text-gray-600 mb-8">
            Editing <span class="font-bold text-gray-900">{{ $patient->name }}</span> 
            (Patient ID: <span class="font-mono text-purple-700">{{ $patient->patient_id }}</span>)
        </p>

        <!-- Patient Editing Form -->
        <form action="{{ route('patient.update', $patient->id) }}" method="POST" 
              class="grid grid-cols-1 md:grid-cols-2 gap-8 bg-purple-50 p-8 rounded-2xl border border-purple-200 shadow-inner">
            @csrf
            @method('PUT')

            <!-- Row 1: Name & Phone -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" required 
                       value="{{ old('name', $patient->name) }}"
                       class="p-3 block w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-400 focus:border-purple-400 @error('name') border-red-500 @enderror">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                <input type="tel" name="phone" id="phone" required 
                       value="{{ old('phone', $patient->phone) }}"
                       class="p-3 block w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-400 focus:border-purple-400 @error('phone') border-red-500 @enderror">
                @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Row 2: National ID & Age -->
            <div>
                <label for="national_id" class="block text-sm font-medium text-gray-700 mb-1">National ID (Optional)</label>
                <input type="text" name="national_id" id="national_id" 
                       value="{{ old('national_id', $patient->national_id) }}"
                       class="p-3 block w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-400 focus:border-purple-400 @error('national_id') border-red-500 @enderror">
                @error('national_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="age" class="block text-sm font-medium text-gray-700 mb-1">Age <span class="text-red-500">*</span></label>
                <input type="number" name="age" id="age" required min="0" max="120"
                       value="{{ old('age', $patient->age) }}"
                       class="p-3 block w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-400 focus:border-purple-400 @error('age') border-red-500 @enderror">
                @error('age')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Row 3: Gender & Next of Kin -->
            <div>
                <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">Gender <span class="text-red-500">*</span></label>
                <select name="gender" id="gender" required 
                        class="p-3 block w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-400 focus:border-purple-400 @error('gender') border-red-500 @enderror">
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
                       class="p-3 block w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-400 focus:border-purple-400 @error('next_of_kin') border-red-500 @enderror">
                @error('next_of_kin')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Row 4: Address (Full Width) -->
            <div class="col-span-1 md:col-span-2">
                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address / Residence</label>
                <textarea name="address" id="address" rows="2" 
                          class="p-3 block w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-400 focus:border-purple-400 @error('address') border-red-500 @enderror">{{ old('address', $patient->address) }}</textarea>
                @error('address')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Submit Button -->
            <div class="col-span-1 md:col-span-2 pt-6">
                <button type="submit" 
                        class="w-full py-4 bg-purple-600 text-white font-extrabold text-lg rounded-xl shadow-xl hover:bg-purple-700 transition duration-150 transform hover:scale-[1.01]">
                    <i class="fas fa-save mr-2"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
