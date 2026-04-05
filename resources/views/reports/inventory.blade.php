<!DOCTYPE html>
<html>
<head>
    <title>Library Inventory Summary</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
        .stats { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Library Inventory Summary</h1>
        <p>Generated on: {{ now()->format('Y-m-d H:i') }}</p>
    </div>

    <div class="stats">
        <p><strong>Total Active Copies:</strong> {{ $totalBooks }}</p>
        <p><strong>Total Available Copies:</strong> {{ $availableBooks }}</p>
        <p><strong>Lost Copies:</strong> {{ $lostCopies }}</p>
        <p><strong>Damaged Copies:</strong> {{ $damagedCopies }}</p>
    </div>

    <h3>Inventory by Category</h3>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Number of Books</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $category)
            <tr>
                <td>{{ $category->name }}</td>
                <td>{{ $category->books_count }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
