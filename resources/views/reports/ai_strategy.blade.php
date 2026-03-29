<!DOCTYPE html>
<html>
<head>
    <title>AI Strategy & Recommendations Report</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #1e293b; margin: 0; padding: 40px; }
        .header { border-bottom: 2px solid #6366f1; padding-bottom: 20px; margin-bottom: 30px; }
        .title { font-size: 24px; font-weight: 900; color: #4338ca; }
        .subtitle { font-size: 12px; color: #64748b; margin-top: 5px; text-transform: uppercase; letter-spacing: 1px; }
        
        .section { margin-bottom: 30px; }
        .section-title { font-size: 14px; font-weight: 900; color: #475569; text-transform: uppercase; letter-spacing: 1px; border-left: 4px solid #6366f1; padding-left: 10px; margin-bottom: 15px; }
        
        .summary-grid { display: block; margin-bottom: 20px; }
        .stat-card { display: inline-block; width: 30%; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; margin-right: 2%; vertical-align: top; }
        .stat-label { font-size: 10px; font-weight: bold; color: #94a3b8; text-transform: uppercase; }
        .stat-value { font-size: 18px; font-weight: 900; color: #1e293b; margin-top: 5px; }

        .alert-box { border-radius: 12px; padding: 15px; margin-bottom: 10px; border: 1px solid #e2e8f0; }
        .alert-title { font-size: 14px; font-weight: 900; }
        .alert-detail { font-size: 12px; color: #64748b; margin-top: 5px; }
        
        .priority-high { color: #e11d48; border-color: #fecdd3; background-color: #fff1f2; }
        .priority-medium { color: #d97706; border-color: #fde68a; background-color: #fffbeb; }
        .priority-low { color: #059669; border-color: #a7f3d0; background-color: #ecfdf5; }
        
        .recommendation { border-left: 2px solid #e2e8f0; padding-left: 15px; margin-bottom: 20px; }
        .rec-title { font-size: 14px; font-weight: 900; color: #1e293b; }
        .rec-detail { font-size: 12px; color: #64748b; margin-top: 5px; line-height: 1.5; }

        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 10px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Library Intelligence Report</div>
        <div class="subtitle">AI-Driven Strategy & Strategic Recommendations</div>
        <p style="font-size: 12px; margin-top: 10px;">Generated on: {{ now()->format('M d, Y H:i') }} | Risk Level: <span style="font-weight: 900; text-transform: uppercase;">{{ $insights['summary']['risk_level'] }}</span></p>
    </div>

    <div class="section">
        <div class="section-title">Performance Snapshot</div>
        <div class="summary-grid">
            <div class="stat-card">
                <div class="stat-label">Overdue Rate</div>
                <div class="stat-value">{{ $insights['summary']['overdue_rate'] }}%</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Pending Fines</div>
                <div class="stat-value">Rs. {{ number_format($insights['summary']['pending_fine_amount'], 2) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Low Stock Exposure</div>
                <div class="stat-value">{{ $insights['summary']['low_stock_ratio'] }}%</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Automated Risk Alerts</div>
        @foreach($insights['alerts'] as $alert)
        <div class="alert-box priority-{{ $alert['severity'] }}">
            <div class="alert-title">{{ $alert['title'] }} ({{ strtoupper($alert['severity']) }})</div>
            <div class="alert-detail">{{ $alert['detail'] }}</div>
        </div>
        @endforeach
    </div>

    <div class="section" style="page-break-before: auto;">
        <div class="section-title">Strategic Action Recommendations</div>
        @foreach($insights['recommendations'] as $rec)
        <div class="recommendation">
            <div class="rec-title">{{ $rec['title'] }}</div>
            <div style="font-size: 10px; font-weight: 900; text-transform: uppercase; color: #6366f1; margin: 5px 0;">Priority: {{ $rec['priority'] }}</div>
            <div class="rec-detail">{{ $rec['detail'] }}</div>
        </div>
        @endforeach
    </div>

    @if(count($insights['high_demand_books']) > 0)
    <div class="section">
        <div class="section-title">Critical Inventory Pressure (Stock-Out Detection)</div>
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
            <thead>
                <tr style="background-color: #f8fafc; text-align: left;">
                    <th style="padding: 10px; border-bottom: 1px solid #e2e8f0;">Book Title</th>
                    <th style="padding: 10px; border-bottom: 1px solid #e2e8f0;">Primary Author</th>
                    <th style="padding: 10px; border-bottom: 1px solid #e2e8f0;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($insights['high_demand_books'] as $book)
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #f1f5f9; font-weight: 900;">{{ $book->title }}</td>
                    <td style="padding: 10px; border-bottom: 1px solid #f1f5f9;">{{ $book->author }}</td>
                    <td style="padding: 10px; border-bottom: 1px solid #f1f5f9; color: #ef4444; font-weight: bold;">CRITICAL STOCK</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        Confidential Library Analytics Report | Powered by Library Intelligence Engine
    </div>
</body>
</html>
