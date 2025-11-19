@php
    $appId = config('services.onesignal.app_id');
@endphp

@if ($appId)
    <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
    <script>
        window.OneSignalDeferred = window.OneSignalDeferred || [];
        OneSignalDeferred.push(async function (OneSignal) {
            await OneSignal.init({
                appId: @js($appId),
            });
        });
    </script>
@endif
