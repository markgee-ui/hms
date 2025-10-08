<div class="bg-white shadow rounded-lg p-4">
    <h3 class="text-xl font-medium mb-3">Patients Waiting for Consultation</h3>
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Token</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chief Complaint</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vitals Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($queue as $visit)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-900">{{ $visit->visit_token }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ $visit->patient->name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $visit->triage->chief_complaint ?? 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                        Vitals Recorded
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <a href="{{ route('consultation.start', $visit->visit_token) }}" 
                       class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition">
                        Consult
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-4 text-center text-gray-500">No patients currently waiting for consultation.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>