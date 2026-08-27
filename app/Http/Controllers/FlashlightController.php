<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Native\Mobile\Facades\Biometrics;
use Native\Mobile\Facades\Device;
use Native\Mobile\Facades\Haptics;
use Native\Mobile\Facades\System;

class FlashlightController extends Controller
{
    public function index(Request $request): View
    {
        return view('flashlight', [
            'native' => System::isMobile(),
            'state' => (bool) $request->session()->get('flashlight_state', false),
            'authenticated' => (bool) $request->session()->get('biometric_auth', false),
        ]);
    }

    /**
     * Toggle the torch LED and report the resulting state.
     */
    public function toggle(Request $request): JsonResponse
    {
        $result = Device::flashlight();

        // No bridge or no torch hardware: keep the UI usable by tracking a
        // simulated state instead, and tell the client it isn't the real LED.
        if (! $result['success']) {
            $state = ! $request->session()->get('flashlight_state', false);
            $request->session()->put('flashlight_state', $state);

            return response()->json([
                'success' => true,
                'state' => $state,
                'simulated' => true,
            ]);
        }

        $request->session()->put('flashlight_state', $result['state']);
        Haptics::vibrate();

        return response()->json([
            'success' => true,
            'state' => $result['state'],
            'simulated' => false,
        ]);
    }

    /**
     * Trigger native Fingerprint / Biometric authentication prompt.
     */
    public function authenticateBiometrics(Request $request): JsonResponse
    {
        if (! System::isMobile()) {
            $request->session()->put('biometric_auth', true);

            return response()->json([
                'success' => true,
                'message' => 'Fingerprint authentication simulated',
                'simulated' => true,
            ]);
        }

        try {
            $prompt = Biometrics::prompt();
            $success = $prompt->prompt();

            if ($success) {
                $request->session()->put('biometric_auth', true);
                Haptics::vibrate();
            }

            return response()->json([
                'success' => $success,
                'simulated' => false,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'simulated' => false,
            ], 500);
        }
    }
}
