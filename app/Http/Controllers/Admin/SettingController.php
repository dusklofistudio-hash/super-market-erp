<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    protected array $keys = [
        'company_name' => 'string',
        'company_email' => 'string',
        'company_phone' => 'string',
        'company_address' => 'string',
        'default_currency' => 'string',
        'default_currency_symbol' => 'string',
        'date_format' => 'string',
        'time_format' => 'string',
    ];

    public function edit(): Response
    {
        $settings = [];
        foreach ($this->keys as $key => $_type) {
            $settings[$key] = Setting::get($key);
        }

        return Inertia::render('Settings/Edit', ['settings' => $settings]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [];
        foreach ($this->keys as $key => $_type) {
            $rules[$key] = ['nullable', 'string', 'max:255'];
        }
        $data = $request->validate($rules);
        foreach ($data as $k => $v) {
            Setting::put($k, $v, $this->keys[$k]);
        }

        return back()->with('success', __('messages.success.updated', ['resource' => __('messages.menu.settings')]));
    }
}
