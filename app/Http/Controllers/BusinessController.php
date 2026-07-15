<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Services\LayoutCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BusinessController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Business/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'tax_id' => ['nullable', 'string', 'max:255'],
            'default_currency' => ['required', 'string', 'size:3'],
            'template' => ['required', 'string', Rule::in(array_keys(LayoutCatalog::all()))],
            'tagline' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'show_name_on_receipt' => ['boolean'],
            'logo' => ['nullable', 'image', 'max:2048'], // 2MB max
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_url'] = $request->file('logo')->store('logos', config('receipts.uploads_disk'));
        }
        unset($data['logo']);

        $business = Business::create([
            ...$data,
            'owner_id' => $request->user()->id,
        ]);

        $business->members()->attach($request->user()->id, ['role' => 'owner']);
        $request->user()->forceFill(['business_id' => $business->id])->save();

        return redirect()->route('dashboard')->with('success', 'Business created successfully.');
    }

    public function switch(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'business_id' => [
                'required',
                Rule::exists('business_user', 'business_id')->where('user_id', $request->user()->id),
            ],
        ]);

        $request->user()->forceFill(['business_id' => $data['business_id']])->save();

        return redirect()->route('dashboard')->with('success', 'Switched business successfully.');
    }

    public function edit(Request $request): Response
    {
        return Inertia::render('Business/Edit', [
            'business' => $request->user()->business,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'tax_id' => ['nullable', 'string', 'max:255'],
            'default_currency' => ['required', 'string', 'size:3'],
            'template' => ['required', 'string', Rule::in(array_keys(LayoutCatalog::all()))],
            'tagline' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'show_name_on_receipt' => ['boolean'],
            'logo' => ['nullable', 'image', 'max:2048'], // 2MB max
        ]);

        $business = $request->user()->business;

        if ($request->hasFile('logo')) {
            if ($business->logo_url) {
                Storage::disk(config('receipts.uploads_disk'))->delete($business->logo_url);
            }
            $data['logo_url'] = $request->file('logo')->store('logos', config('receipts.uploads_disk'));
        }
        unset($data['logo']);

        $business->update($data);

        return redirect()->route('business.edit')->with('success', 'Business settings updated successfully.');
    }
}
