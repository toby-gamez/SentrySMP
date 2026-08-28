<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSettings;
use Illuminate\Http\Request;

class PaymentSettingsController extends Controller
{
    public function index()
    {
        $settings = PaymentSettings::current();
        return view('admin.settings.payment', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = [
            'enable_payments' => $request->boolean('enable_payments'),
            'disable_stripe'  => $request->boolean('disable_stripe'),
            'disable_paypal'  => $request->boolean('disable_paypal'),
            'updated_at'      => now(),
        ];

        $settings = PaymentSettings::first();
        if ($settings) {
            $settings->update($data);
        } else {
            PaymentSettings::create($data);
        }

        return back()->with('success', 'Payment settings saved.');
    }

    public function resetStats()
    {
        $settings = PaymentSettings::first();
        if ($settings) {
            $settings->update(['stats_reset_at' => now()]);
        } else {
            PaymentSettings::create([
                'enable_payments' => true,
                'disable_stripe'  => false,
                'disable_paypal'  => false,
                'stats_reset_at'  => now(),
            ]);
        }

        return back()->with('success', 'Revenue and scoreboard stats have been reset.');
    }
}
