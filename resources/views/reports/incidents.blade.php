<!DOCTYPE html>
<html>
<head>
    <title>Library Incidents Report</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; vertical-align: top; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Lost And Damaged Incidents Report</h1>
        <p>Generated on: {{ now()->format('Y-m-d H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th>Member</th>
                <th>Book</th>
                <th>Accession</th>
                <th>Resolved At</th>
                <th>Charge</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($incidents as $incident)
            <tr>
                <td>{{ strtoupper($incident->status) }}</td>
                <td>{{ $incident->member->name }} ({{ $incident->member->member_id }})</td>
                <td>{{ $incident->book->title }}</td>
                <td>{{ $incident->copy?->accession_number ?? 'LEGACY' }}</td>
                <td>{{ optional($incident->resolved_at)->format('Y-m-d') }}</td>
                <td>{{ optional($incident->fine)->amount ?? $incident->condition_fee ?? '0.00' }}</td>
                <td>{{ $incident->condition_notes ?: 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
