# Flashlight

A [NativePHP for Mobile](https://nativephp.com/docs/mobile) app: one screen, one button, toggles the
device's torch LED.

## How it works

| File | Role |
| --- | --- |
| [routes/web.php](routes/web.php) | `GET /` renders the screen, `POST /flashlight/toggle` flips the LED |
| [app/Http/Controllers/FlashlightController.php](app/Http/Controllers/FlashlightController.php) | Calls `Device::flashlight()` (and `Haptics::vibrate()` on success) |
| [resources/views/flashlight.blade.php](resources/views/flashlight.blade.php) | Torch UI — plain Blade + `fetch()`, no build step |

`Device::flashlight()` returns `['success' => bool, 'state' => bool]`, `state` being the torch's new
on/off state. When there is no bridge behind the request (a desktop browser) or the device has no
torch, the controller falls back to a session-tracked simulated state and flags it as `simulated`
so the UI can say so.

Android's `VIBRATE` and `FLASHLIGHT` permissions are declared by the NativePHP package itself and land
in the generated manifest — nothing to add to `config/nativephp.php`.

## Run it

In a browser (torch simulated):

```bash
php artisan serve
```

On a device or emulator:

```bash
php artisan native:run
```

Building for Android needs a JDK, the Android SDK and Android Studio — `php artisan native:debug`
reports what is missing. iOS builds need macOS + Xcode and `NATIVEPHP_DEVELOPMENT_TEAM` in `.env`.

## Regenerating the native project

The `nativephp/` directory is generated and gitignored — treat it as ephemeral:

```bash
php artisan native:install android --force
```
