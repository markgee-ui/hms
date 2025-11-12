@extends('layouts.app')

@section('title', 'Outpatient Dashboard')

@section('content')
<div class="p-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Outpatient Flow Overview</h1>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-6 mb-8">
        
        @php
            // UPDATED FLOW MAP with new, granular Triage statuses
            $flowMap = [
                'Registered' => ['color' => 'bg-blue-100 text-blue-800', 'icon' => 'user-plus'],
                'Waiting for Triage' => ['color' => 'bg-yellow-100 text-yellow-800', 'icon' => 'thermometer'], // Nurse's queue
                'Triage Completed' => ['color' => 'bg-orange-100 text-orange-800', 'icon' => 'user-md'],      // Ready for Doctor (Hand-off status)
                'Consultation' => ['color' => 'bg-indigo-100 text-indigo-800', 'icon' => 'stethoscope'],
                'Lab/Rad' => ['color' => 'bg-purple-100 text-purple-800', 'icon' => 'flask'],
                'Pharmacy' => ['color' => 'bg-green-100 text-green-800', 'icon' => 'pills'],
                'Billing' => ['color' => 'bg-red-100 text-red-800', 'icon' => 'credit-card'],
                'Completed' => ['color' => 'bg-gray-100 text-gray-800', 'icon' => 'check-circle'], // Added Completed status for completeness
            ];
        @endphp

        {{-- Filter out any statuses from the database that don't exist in the map (optional safety) --}}
        @foreach ($data['flowCounts'] as $status => $count)
            @if (isset($flowMap[$status]))
                <div class="p-4 rounded-xl shadow-lg {{ $flowMap[$status]['color'] }} transition duration-300 hover:shadow-xl">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium uppercase">{{ $status }}</p>
                        <i class="fas fa-{{ $flowMap[$status]['icon'] }}"></i> </div>
                    <p class="text-3xl font-extrabold mt-1">{{ $count }}</p>
                    <p class="text-xs mt-1">Patients in queue</p>
                </div>
            @endif
        @endforeach
    </div>

    <div class="mt-8 border-t pt-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">{{ ucfirst($role) }} Actions & Queue</h2>

        @if ($role == 'receptionist')
            @include('outpatient.dashboard-partials.receptionist-queue', ['queue' => $data['receptionQueue']])
        @elseif ($role == 'nurse')
            @include('outpatient.dashboard-partials.nurse-queue', ['queue' => $data['triageQueue']])
        @elseif ($role == 'doctor')
            @include('outpatient.dashboard-partials.doctor-queue', ['queue' => $data['consultationQueue']])
        @elseif ($role == 'labtech')
            @include('outpatient.dashboard-partials.lab-queue', ['queue' => $data['labQueue']])
        @elseif ($role == 'pharmacist')
            @include('outpatient.dashboard-partials.pharmacy-queue', ['queue' => $data['pharmacyQueue']])

        @elseif ($role == 'cashier')
            @include('outpatient.dashboard-partials.billing-queue', ['billingQueue' => $data['billingQueue']])  
        @elseif ($role == 'admin')
            @include('outpatient.dashboard-partials.admin-overview', ['data' => $data])
        @else 
            <p class="text-gray-600">No immediate tasks. View the flow status above for system overview.</p>
        @endif
    </div>
</div>
@endsection
