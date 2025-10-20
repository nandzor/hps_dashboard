# Re-ID Uniqueness Logic

## Unique Constraint
- **Constraint:** (re_id, detection_date)
- **Meaning:** Same re_id can exist on different dates
- **Behavior:** Each day creates a new person record

## Example
```
re_id: person_001
├── 2025-10-08 → Record ID: 91 (Person 1)
├── 2025-10-09 → Record ID: 94 (Person 2 - same re_id, different day)
└── 2025-10-10 → Record ID: 97 (Person 3 - same re_id, different day)
```

## Database Changes Made
1. ✅ Removed unique constraint on re_id alone
2. ✅ Kept unique constraint on (re_id, detection_date)
3. ✅ Removed foreign key constraints that depended on re_id alone
4. ✅ ProcessDetectionJob uses firstOrCreate with (re_id, detection_date)

## Benefits
- ✅ Same person can be detected on multiple days
- ✅ Each day creates new detection record
- ✅ Historical tracking per day
- ✅ No conflicts between days

## ProcessDetectionJob Logic
```php
$reIdMaster = ReIdMaster::firstOrCreate(
    [
        're_id' => $this->reId,
        'detection_date' => $today, // Changes each day
    ],
    [
        // Default values for new record
        'total_detection_branch_count' => 1,
        'total_actual_count' => $this->detectedCount,
        'first_detected_at' => $detectionTime,
        'last_detected_at' => $detectionTime,
    ]
);
```

## Testing Results
- ✅ Same re_id can exist on different dates
- ✅ Each day creates new person record
- ✅ Unique constraint: (re_id, detection_date) works correctly
- ✅ ProcessDetectionJob handles daily uniqueness properly
