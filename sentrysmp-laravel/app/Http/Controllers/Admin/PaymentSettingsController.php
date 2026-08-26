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
            'EnablePayments' => $request->boolean('EnablePayments'),
            'DisableStripe'  => $request->boolean('DisableStripe'),
            'DisablePayPal'  => $request->boolean('DisablePayPal'),
            'UpdatedAt'      => now(),
        ];

        $settings = PaymentSettings::first();
        if ($settings) {
            $settings->update($data);
        } else {
            PaymentSettings::create($data);
        }

        return back()->with('success', 'Payment settings saved.');
    }
}
