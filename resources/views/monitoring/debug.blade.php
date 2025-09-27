<!DOCTYPE html>
<html>
<head>
    <title>Monitoring Debug</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Monitoring Debug: {{ $debugData['district'] }}</h1>

        <!-- Summary -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">Summary</h2>
            <div class="grid grid-cols-2 gap-4">
                <div><strong>Total Contracts:</strong> {{ $debugData['total_contracts'] }}</div>
                <div><strong>Total Paid:</strong> {{ number_format($debugData['total_paid'], 2) }} сўм</div>
                <div class="text-blue-600"><strong>Total from Contracts:</strong> {{ number_format($debugData['total_from_contracts'], 2) }} сўм</div>
                <div class="text-green-600"><strong>Total from Schedules:</strong> {{ number_format($debugData['total_from_schedules'], 2) }} сўм</div>
                <div class="col-span-2 text-red-600 text-lg"><strong>Difference:</strong> {{ number_format($debugData['difference'], 2) }} сўм</div>
            </div>
        </div>

        <!-- Status Breakdown -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">Status Breakdown</h2>
            <div class="grid grid-cols-3 gap-4">
                @foreach($debugData['status_breakdown'] as $status => $count)
                    <div><strong>{{ $status }}:</strong> {{ $count }}</div>
                @endforeach
            </div>
        </div>

        <!-- Permit Type Breakdown -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">Permit Type Breakdown</h2>
            <div class="grid grid-cols-3 gap-4">
                @foreach($debugData['permit_breakdown'] as $type => $count)
                    <div><strong>{{ $type }}:</strong> {{ $count }}</div>
                @endforeach
            </div>
        </div>

        <!-- Schedules Breakdown -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">Payment Schedules Breakdown</h2>
            <div class="grid grid-cols-2 gap-4">
                @foreach($debugData['schedules_breakdown'] as $period => $amount)
                    <div><strong>{{ $period }}:</strong> {{ number_format($amount, 2) }} сўм</div>
                @endforeach
            </div>
        </div>

        <!-- Contract Details -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold mb-4">Contract Details</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 text-left">ID</th>
                            <th class="p-2 text-left">Number</th>
                            <th class="p-2 text-left">Status</th>
                            <th class="p-2 text-left">Permit Type</th>
                            <th class="p-2 text-right">Contract Amount</th>
                            <th class="p-2 text-right">Schedule Amount</th>
                            <th class="p-2 text-right">Paid</th>
                            <th class="p-2 text-right">Difference</th>
                            <th class="p-2 text-center">Schedules</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($debugData['contracts_detail'] as $contract)
                            <tr class="border-t hover:bg-gray-50">
                                <td class="p-2">{{ $contract['id'] }}</td>
                                <td class="p-2">{{ $contract['number'] }}</td>
                                <td class="p-2">{{ $contract['status'] }}</td>
                                <td class="p-2">{{ $contract['permit_type'] }}</td>
                                <td class="p-2 text-right">{{ $contract['contract_amount'] }}</td>
                                <td class="p-2 text-right text-green-600">{{ $contract['schedule_amount'] }}</td>
                                <td class="p-2 text-right">{{ $contract['paid_amount'] }}</td>
                                <td class="p-2 text-right {{ $contract['diff_contract_vs_schedule'] > 0 ? 'text-red-600' : 'text-gray-600' }}">
                                    {{ $contract['diff_contract_vs_schedule'] }}
                                </td>
                                <td class="p-2 text-center">{{ $contract['schedules_count'] }}</td>
                            </tr>
                            <tr>
                                <td colspan="9" class="p-2 text-xs bg-gray-50">
                                    <strong>By Period:</strong>
                                    Q3-2025: {{ number_format($contract['schedules_by_period']['2025_q3'], 0) }} |
                                    Q4-2025: {{ number_format($contract['schedules_by_period']['2025_q4'], 0) }} |
                                    2026: {{ number_format($contract['schedules_by_period']['2026'], 0) }} |
                                    2027: {{ number_format($contract['schedules_by_period']['2027'], 0) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
