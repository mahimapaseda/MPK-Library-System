<!DOCTYPE html>
<html>
<head>
    <title>Overdue Books Report</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Overdue Books Report</h1>
        <p>Generated on: {{ now()->format('Y-m-d H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Member</th>
                <th>Book Title</th>
                <th>Issued At</th>
                <th>Due Date</th>
                <th>Days Overdue</th>
            </tr>
        </thead>
        <tbody>
            @foreach($overdueIssues as $issue)
            <tr>
                <td>{{ $issue->member->name }} ({{ $issue->member->member_id }})</td>
                <td>{{ $issue->book->title }}</td>
                <td>{{ $issue->issued_at->format('Y-m-d') }}</td>
                <td>{{ $issue->due_date->format('Y-m-d') }}</td>
                <td>{{ now()->diffInDays($issue->due_date) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
