<?php

namespace App\Http\Controllers;

use App\Models\CctvLayoutSetting;
use App\Models\CompanyBranch;
use App\Services\CctvLayoutService;
use App\Http\Requests\StoreCctvLayoutRequest;
use App\Http\Requests\UpdateCctvLayoutRequest;
use Illuminate\Http\Request;

class CctvLayoutController extends Controller {
    protected $cctvLayoutService;

    public function __construct(CctvLayoutService $cctvLayoutService) {
        $this->cctvLayoutService = $cctvLayoutService;
    }

    public function index(Request $request) {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);
        $filters = [];
        if ($request->filled('layout_type')) $filters['layout_type'] = $request->input('layout_type');
        if ($request->filled('is_active')) $filters['is_active'] = $request->input('is_active');

        $layouts = $this->cctvLayoutService->getPaginate($search, $perPage, $filters);
        $statistics = $this->cctvLayoutService->getStatistics();

        return view('cctv-layouts.index', compact('layouts', 'statistics'));
    }

    public function create() {
        $branches = CompanyBranch::active()->with('devices')->get();
        return view('cctv-layouts.create', compact('branches'));
    }

    public function store(StoreCctvLayoutRequest $request) {
        try {
            $layoutData = [
                'layout_name' => $request->layout_name,
                'layout_type' => $request->layout_type,
                'description' => $request->description,
                'total_positions' => count($request->positions),
                'is_default' => $request->is_default ?? false,
                'is_active' => $request->is_active ?? true,
                'created_by' => auth()->id(),
            ];

            $layout = $this->cctvLayoutService->createLayoutWithPositions($layoutData, $request->positions);

            if ($request->is_default) {
                $this->cctvLayoutService->setDefaultLayout($layout->id);
            }

            return redirect()->route('cctv-layouts.index')
                ->with('success', 'CCTV layout created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to create layout: ' . $e->getMessage());
        }
    }

    public function show(CctvLayoutSetting $cctvLayout) {
        $layout = $this->cctvLayoutService->getLayoutWithPositions($cctvLayout->id);
        return view('cctv-layouts.show', compact('layout'));
    }

    public function edit(CctvLayoutSetting $cctvLayout) {
        $layout = $this->cctvLayoutService->getLayoutWithPositions($cctvLayout->id);
        $branches = CompanyBranch::active()->with('devices')->get();
        return view('cctv-layouts.edit', compact('layout', 'branches'));
    }

    public function update(UpdateCctvLayoutRequest $request, CctvLayoutSetting $cctvLayout) {
        try {
            $layoutData = [
                'layout_name' => $request->layout_name,
                'layout_type' => $request->layout_type,
                'description' => $request->description,
                'total_positions' => count($request->positions),
                'is_default' => $request->is_default ?? false,
                'is_active' => $request->is_active ?? true,
            ];

            $this->cctvLayoutService->updateLayoutWithPositions($cctvLayout, $layoutData, $request->positions);

            if ($request->is_default) {
                $this->cctvLayoutService->setDefaultLayout($cctvLayout->id);
            }

            return redirect()->route('cctv-layouts.show', $cctvLayout)
                ->with('success', 'Layout updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to update layout: ' . $e->getMessage());
        }
    }

    public function destroy(CctvLayoutSetting $cctvLayout) {
        try {
            if ($cctvLayout->is_default) {
                return redirect()->back()->with('error', 'Cannot delete default layout.');
            }
            $cctvLayout->delete();
            return redirect()->route('cctv-layouts.index')
                ->with('success', 'Layout deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete: ' . $e->getMessage());
        }
    }
}
