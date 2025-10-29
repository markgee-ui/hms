@extends('layouts.app')

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

                {{-- Patient Details --}}
                <div class="bg-white p-6 rounded-lg shadow-md border-t-4">
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
                        {{ $prescription->visit->patient->age ?? 'N/A' }} yo,
                        {{ $prescription->visit->patient->gender ?? 'N/A' }}
                    </p>
                </div>

                {{-- Doctor Details --}}
                <div class="bg-white p-6 rounded-lg shadow-md border-t-4">
                    <h2 class="text-xl font-semibold mb-3 text-gray-800 flex items-center">
                        <i class="fas fa-user-md mr-2 text-purple-500"></i> Prescribing Doctor
                    </h2>
                    <p class="text-sm font-medium text-gray-900">{{ $prescription->doctor->name ?? 'N/A' }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        Issued: {{ $prescription->created_at->format('M d, Y h:i A') }}
                        ({{ $prescription->created_at->diffForHumans() }})
                    </p>
                </div>

                {{-- Doctor's Notes --}}
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

            {{-- RIGHT COLUMN --}}
            <div class="lg:col-span-2 space-y-6">

                {{--UPDATED Prescription Details --}}
                <div class="bg-white p-6 rounded-lg shadow-xl border-l-4">
                    <h2 class="text-2xl font-bold mb-4 text-green-700 flex items-center">
                        <i class="fas fa-prescription-bottle-alt mr-2"></i> Prescription Details
                    </h2>

                    @if($prescription->items->isEmpty())
                        <p class="text-gray-500 italic">No prescription items available.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-gray-300">
                                <thead class="bg-green-100 border-b">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 border-r">Medication
                                        </th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 border-r">Dosage</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 border-r">Frequency
                                        </th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 border-r">Duration
                                        </th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Quantity</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($prescription->items as $item)
                                        <tr class="hover:bg-green-50 transition">
                                            <td class="px-4 py-2">{{ $item->medication->name ?? 'N/A' }}</td>
                                            <td class="px-4 py-2">{{ $item->dosage ?? '-' }}</td>
                                            <td class="px-4 py-2">{{ $item->frequency ?? '-' }}</td>
                                            <td class="px-4 py-2">{{ $item->duration ?? '-' }}</td>
                                            <td class="px-4 py-2">{{ $item->quantity }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Drug Availability Table --}}
                <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 ">
                    <h2 class="text-xl font-semibold mb-3 text-gray-800 flex items-center">
                        <i class="fas fa-warehouse mr-2 text-teal-500"></i>
                        Drug Availability Check
                    </h2>

                    @if(empty($drugAvailability))
                        <p class="text-gray-500 italic">No prescription items to check.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-gray-200 divide-y divide-gray-100">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Medication</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Dosage</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Frequency</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Duration</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Quantity</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Stock</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($drugAvailability as $drug)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ $drug['medication'] }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-600">{{ $drug['dosage'] }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-600">{{ $drug['frequency'] }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-600">{{ $drug['duration'] }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-600">{{ $drug['quantity'] }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-600">{{ $drug['stockLevel'] }}</td>
                                            <td class="px-4 py-2 text-sm">
                                                <span class="px-2 py-1 rounded-full text-xs font-semibold text-white 
                                                            {{ $drug['isAvailable'] ? 'bg-green-600' : 'bg-red-500' }}">
                                                    {{ $drug['statusMessage'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>



                {{-- Dispensation Form --}}
                <div class="bg-white p-6 rounded-lg shadow-xl border-t-4">
                    <h2 class="text-2xl font-bold mb-4 text-indigo-700">Dispensation Action</h2>

                    @if($prescription->status == 'Dispensed')
                        <div class="p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-lg text-center">
                            <i class="fas fa-check-circle mr-2"></i> This prescription has already been marked as **Dispensed**.
                        </div>
                    @elseif(!$isAllAvailable)
                        <div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg text-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            **Cannot Dispense:** One or more medications are currently out of stock.
                        </div>

                    @else
                        <form action="{{ route('pharmacy.dispense', $prescription->id) }}" method="POST">
                            @csrf

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