<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Event Logs Export</title>
  <style>
    body {
      font-family: 'DejaVu Sans', Arial, sans-serif;
      font-size: 11px;
      color: #333;
    }

    .header {
      text-align: center;
      margin-bottom: 30px;
      border-bottom: 2px solid #DC2626;
      padding-bottom: 15px;
    }

    .header h1 {
      margin: 0;
      color: #DC2626;
      font-size: 24px;
    }

    .header p {
      margin: 5px 0 0 0;
      color: #666;
      font-size: 13px;
    }

    .filter-info {
      background: #f3f4f6;
      padding: 12px;
      margin-bottom: 20px;
      border-radius: 5px;
      font-size: 10px;
    }

    .filter-info strong {
      color: #374151;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    th {
      background: #DC2626;
      color: white;
      padding: 8px 6px;
      text-align: left;
      font-weight: bold;
      font-size: 10px;
      text-transform: uppercase;
    }

    td {
      padding: 8px 6px;
      border-bottom: 1px solid #e5e7eb;
      font-size: 10px;
    }

    tr:nth-child(even) {
      background: #f9fafb;
    }

    .text-center {
      text-align: center;
    }

    .text-right {
      text-align: right;
    }

    .badge {
      display: inline-block;
      padding: 2px 6px;
      border-radius: 3px;
      font-size: 9px;
      font-weight: bold;
    }

    .badge-detection {
      background: #D1FAE5;
      color: #065F46;
    }

    .badge-alert {
      background: #FEE2E2;
      color: #991B1B;
    }

    .badge-motion {
      background: #FEF3C7;
      color: #92400E;
    }

    .badge-manual {
      background: #E5E7EB;
      color: #374151;
    }

    .badge-yes {
      background: #D1FAE5;
      color: #065F46;
    }

    .badge-no {
      background: #FEE2E2;
      color: #991B1B;
    }

    .footer {
      margin-top: 30px;
      padding-top: 15px;
      border-top: 1px solid #e5e7eb;
      text-align: center;
      color: #666;
      font-size: 9px;
    }

    .no-data {
      text-align: center;
      padding: 40px;
      color: #9ca3af;
      font-style: italic;
    }

    .summary-box {
      background: #f0fdf4;
      border: 1px solid #bbf7d0;
      padding: 10px;
      margin-top: 20px;
      border-radius: 5px;
    }

    .summary-box strong {
      color: #166534;
    }
  </style>
</head>

<body>
  <div class="header">
    <h1>Event Logs Report</h1>
    <p>Generated on {{ now()->format('l, F d, Y - H:i:s') }}</p>
  </div>

  @if (!empty($filters))
    <div class="filter-info">
      <strong>Applied Filters:</strong>
      @if (isset($filters['event_type']))
        | Event Type: <strong>{{ ucfirst($filters['event_type']) }}</strong>
      @endif
      @if (isset($filters['branch_id']))
        | Branch: <strong>{{ \App\Models\CompanyBranch::find($filters['branch_id'])->branch_name ?? 'N/A' }}</strong>
      @endif
    </div>
  @endif

  @if ($events->count() > 0)
    <table>
      <thead>
        <tr>
          <th style="width: 12%;">Event Type</th>
          <th style="width: 15%;">Branch</th>
          <th style="width: 15%;">Device</th>
          <th style="width: 15%;">Re-ID</th>
          <th class="text-center" style="width: 8%;">Count</th>
          <th style="width: 18%;">Timestamp</th>
          <th class="text-center" style="width: 6%;">Img</th>
          <th class="text-center" style="width: 6%;">Msg</th>
          <th class="text-center" style="width: 6%;">Notif</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($events as $event)
          <tr>
            <td>
              <span
                class="badge badge-{{ $event->event_type === 'detection' ? 'detection' : ($event->event_type === 'alert' ? 'alert' : ($event->event_type === 'motion' ? 'motion' : 'manual')) }}">
                {{ ucfirst($event->event_type) }}
              </span>
            </td>
            <td>{{ $event->branch->branch_name ?? 'N/A' }}</td>
            <td>{{ $event->device->device_name ?? 'N/A' }}</td>
            <td style="font-family: monospace; font-size: 9px;">
              {{ $event->re_id ? \Illuminate\Support\Str::limit($event->re_id, 18) : 'N/A' }}
            </td>
            <td class="text-center">{{ $event->detected_count }}</td>
            <td>{{ \Carbon\Carbon::parse($event->event_timestamp)->format('Y-m-d H:i:s') }}</td>
            <td class="text-center">
              <span class="badge badge-{{ $event->image_sent ? 'yes' : 'no' }}">
                {{ $event->image_sent ? '✓' : '✗' }}
              </span>
            </td>
            <td class="text-center">
              <span class="badge badge-{{ $event->message_sent ? 'yes' : 'no' }}">
                {{ $event->message_sent ? '✓' : '✗' }}
              </span>
            </td>
            <td class="text-center">
              <span class="badge badge-{{ $event->notification_sent ? 'yes' : 'no' }}">
                {{ $event->notification_sent ? '✓' : '✗' }}
              </span>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <div class="summary-box">
      <strong>Summary:</strong> Total Events: <strong>{{ number_format($events->count()) }}</strong>
      | Detection: <strong>{{ number_format($events->where('event_type', 'detection')->count()) }}</strong>
      | Alert: <strong>{{ number_format($events->where('event_type', 'alert')->count()) }}</strong>
      | Motion: <strong>{{ number_format($events->where('event_type', 'motion')->count()) }}</strong>
      | Manual: <strong>{{ number_format($events->where('event_type', 'manual')->count()) }}</strong>
    </div>
  @else
    <div class="no-data">
      No event logs found matching the selected criteria
    </div>
  @endif

  <div class="footer">
    CCTV Dashboard System | Event Logs Report | Page 1 of 1
  </div>
</body>

</html>
