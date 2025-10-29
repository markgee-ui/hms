@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4 sm:p-6">
        <h1 class="text-3xl font-extrabold text-gray-900 mb-6 border-b pb-2">
            My Prescriptions
        </h1>
        <p class="text-gray-600 mb-8">
            Below is a list of all prescriptions you’ve issued, along with their current status and related patient details.
        </p>

        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
            @if($prescriptions->isEmpty())
                <div class="p-8 text-center text-gray-500">
                    <i class="fas fa-info-circle text-4xl mb-3 text-indigo-500"></i>
                    <p class="text-lg font-semibold">
                        You haven’t issued any prescriptions yet.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-300">
                        <thead class="bg-gray-100 border-b border-gray-300">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase border-r border-gray-300">
                                    Patient</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase border-r border-gray-300">
                                    Visit Token</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase border-r border-gray-300">
                                    Prescription Details</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase border-r border-gray-300">
                                    Status</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Created</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-300">
                            @foreach($prescriptions as $prescription)
                                <tr class="hover:bg-indigo-50 transition duration-150">
                                    <td class="px-6 py-4 border-r border-gray-300">
                                        {{ $prescription->patient->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 border-r border-gray-300">
                                        {{ $prescription->visit->visit_token ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 border-r border-gray-300">
                                        @if($prescription->items->isEmpty())
                                            <span class="text-gray-400 italic">No items</span>
                                        @else
                                            <ul class="list-disc list-inside text-sm text-gray-700">
                                                @foreach($prescription->items as $item)
                                                    <li>
                                                        {{ $item->medication->name ?? 'N/A' }} —
                                                        {{ $item->dosage ?? '-' }},
                                                        {{ $item->frequency ?? '-' }},
                                                        {{ $item->duration ?? '-' }},
                                                        Qty: {{ $item->quantity }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 border-r border-gray-300">
                                        <span class="px-3 py-1 rounded-full text-white text-xs font-semibold 
                                                    @if($prescription->status == 'Pending') bg-yellow-500 
                                                    @elseif($prescription->status == 'Dispensed') bg-green-600 
                                                    @else bg-gray-400 @endif">
                                            {{ $prescription->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-500">
                                        {{ $prescription->created_at->diffForHumans() }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- Pagination Links -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $prescriptions->links('pagination::tailwind') }}
                </div>
            @endif
        </div>
    </div>
@endsection