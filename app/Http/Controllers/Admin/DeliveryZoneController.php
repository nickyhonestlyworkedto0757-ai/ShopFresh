<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;

class DeliveryZoneController extends Controller
{
    public function index()
    {
        $zones = DeliveryZone::orderBy('fee')->paginate(20);
        return view('admin.delivery.zones', compact('zones'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:delivery_zones', 'fee' => 'required|numeric|min:0', 'is_active' => 'nullable|boolean']);
        $data['is_active'] = $request->boolean('is_active', true);
        DeliveryZone::create($data);
        ActivityLogger::log('delivery.create', $data['name'], 'Zone created, fee ' . $data['fee']);
        return back()->with('success', 'Delivery zone created.');
    }

    public function update(Request $request, DeliveryZone $zone)
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'fee' => 'required|numeric|min:0', 'is_active' => 'nullable|boolean']);
        $data['is_active'] = $request->boolean('is_active', true);
        $zone->update($data);
        ActivityLogger::log('delivery.update', $zone->name, 'Zone updated');
        return back()->with('success', 'Delivery zone updated.');
    }

    public function destroy(DeliveryZone $zone)
    {
        ActivityLogger::log('delivery.delete', $zone->name, 'Zone deleted');
        $zone->delete();
        return back()->with('success', 'Delivery zone deleted.');
    }
}
