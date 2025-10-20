<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Person Tracking Export</title>
  <style>
    body {
      font-family: 'DejaVu Sans', Arial, sans-serif;
      font-size: 11px;
      color: #333;
    }

    .header {
      text-align: center;
      margin-bottom: 30px;
      border-bottom: 2px solid #9333EA;
      padding-bottom: 15px;
    }

    .header h1 {
      margin: 0;
      color: #9333EA;
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
      background: #9333EA;
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

    .re-id {
      font-family: 'Courier New', monospace;
      font-size: 9px;
      color: #1f2937;
    }

    .badge {
      display: inline-block;
      padding: 2px 6px;
      border-radius: 3px;
      font-size: 9px;
      font-weight: bold;
    }

    .badge-active {
      background: #D1FAE5;
      color: #065F46;
    }

    .badge-inactive {
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
      background: #faf5ff;
      border: 1px solid #d8b4fe;
      padding: 10px;
      margin-top: 20px;
      border-radius: 5px;
    }

    .summary-box strong {
      color: #6b21a8;
    }
  </style>
</head>

<body>
  <div class="header">
    <h1>Person Tracking Report</h1>
    <p>Generated on {{ now()->format('l, F d, Y - H:i:s') }}</p>
  </div>

  @if (!empty($filters))
    <div class="filter-info">
      <strong>Applied Filters:</strong>
      @if (isset($filters['branch_id']))
        | Branch: <strong>{{ \App\Models\CompanyBranch::find($filters['branch_id'])->branch_name ?? 'N/A' }}</strong>
      @endif
      @if (isset($filters['date_from']))
        | From: <strong>{{ $filters['date_from'] }}</strong>
      @endif
      @if (isset($filters['date_to']))
        | To: <strong>{{ $filters['date_to'] }}</strong>
      @endif
    </div>
  @endif

  @if ($persons->count() > 0)
    <table>
      <thead>
        <tr>
          <th style="width: 25%;">Re-ID</th>
          <th style="width: 15%;">Person Name</th>
          <th style="width: 12%;">Detection Date</th>
          <th class="text-center" style="width: 12%;">First Detected</th>
          <th class="text-center" style="width: 12%;">Last Detected</th>
          <th class="text-center" style="width: 8%;">Branches</th>
          <th class="text-center" style="width: 8%;">Detections</th>
          <th class="text-center" style="width: 8%;">Status</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($persons as $person)
          <tr>
            <td class="re-id">{{ $person->re_id }}</td>
            <td>{{ $person->person_name ?: 'Unknown' }}</td>
            <td>{{ \Carbon\Carbon::parse($person->detection_date)->format('Y-m-d') }}</td>
            <td class="text-center">
              {{ $person->first_detected_at ? \Carbon\Carbon::parse($person->first_detected_at)->format('Y-m-d H:i') : 'N/A' }}
            </td>
            <td class="text-center">
              {{ $person->last_detected_at ? \Carbon\Carbon::parse($person->last_detected_at)->format('Y-m-d H:i') : 'N/A' }}
            </td>
            <td class="text-center">{{ $person->total_detection_branch_count }}</td>
            <td class="text-center">{{ number_format($person->total_actual_count) }}</td>
            <td class="text-center">
              <span class="badge badge-{{ $person->status === 'active' ? 'active' : 'inactive' }}">
                {{ ucfirst($person->status) }}
              </span>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <div class="summary-box">
      <strong>Summary:</strong> Total Records: <strong>{{ number_format($persons->count()) }}</strong>
      | Active: <strong>{{ number_format($persons->where('status', 'active')->count()) }}</strong>
      | Inactive: <strong>{{ number_format($persons->where('status', 'inactive')->count()) }}</strong>
      | Total Detections: <strong>{{ number_format($persons->sum('total_actual_count')) }}</strong>
    </div>
  @else
    <div class="no-data">
      No person tracking records found matching the selected criteria
    </div>
  @endif

  <div class="footer">
    CCTV Dashboard System | Person Tracking Report | Page 1 of 1
  </div>
</body>

</html>
