<?php

namespace App\Services;

use App\Models\CctvLayoutSetting;
use App\Models\CctvPositionSetting;
use Illuminate\Support\Facades\DB;

class CctvLayoutService extends BaseService {
    public function __construct() {
        $this->model = new CctvLayoutSetting();
        $this->searchableFields = ['layout_name', 'description'];
        $this->orderByColumn = 'created_at';
        $this->orderByDirection = 'desc';
    }

    public function getLayoutWithPositions(int $layoutId) {
        return CctvLayoutSetting::with([
            'positions' => function ($query) {
                $query->orderBy('position_number', 'asc');
            },
            'positions.branch',
            'positions.device',
            'creator'
        ])
            ->find($layoutId);
    }

    public function createLayoutWithPositions(array $layoutData, array $positions): CctvLayoutSetting {
        return DB::transaction(function () use ($layoutData, $positions) {
            $layout = CctvLayoutSetting::create($layoutData);

            foreach ($positions as $position) {
                $layout->positions()->create($position);
            }

            return $layout->load([
                'positions' => function ($query) {
                    $query->orderBy('position_number', 'asc');
                }
            ]);
        });
    }

    public function updateLayoutWithPositions(CctvLayoutSetting $layout, array $layoutData, array $positions): bool {
        return DB::transaction(function () use ($layout, $layoutData, $positions) {
            $layout->update($layoutData);
            $layout->positions()->delete();

            foreach ($positions as $position) {
                $layout->positions()->create($position);
            }

            return true;
        });
    }

    public function setDefaultLayout(int $layoutId): bool {
        return DB::transaction(function () use ($layoutId) {
            CctvLayoutSetting::where('is_default', true)->update(['is_default' => false]);
            return CctvLayoutSetting::where('id', $layoutId)->update(['is_default' => true]);
        });
    }

    public function getDefaultLayout() {
        return CctvLayoutSetting::with([
            'positions' => function ($query) {
                $query->orderBy('position_number', 'asc');
            },
            'positions.branch',
            'positions.device'
        ])
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    public function getStatistics(): array {
        return [
            'total_layouts' => CctvLayoutSetting::count(),
            'active_layouts' => CctvLayoutSetting::active()->count(),
            'by_type' => CctvLayoutSetting::selectRaw('layout_type, COUNT(*) as count')
                ->groupBy('layout_type')->pluck('count', 'layout_type')->toArray(),
        ];
    }
}
