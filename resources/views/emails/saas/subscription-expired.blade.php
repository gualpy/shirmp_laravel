<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('mail.subscription_expired_subject', ['app' => config('app.name')]) }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f9fb;font-family:'Segoe UI',Arial,sans-serif;color:#123047;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f9fb;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width:520px;background:#ffffff;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="background:linear-gradient(135deg,#2a1a05 0%,#5a3a0a 55%,#8a5a12 100%);padding:28px 32px;">
                            <span style="color:#ffffff;font-size:1.3rem;font-weight:800;letter-spacing:-0.01em;">{{ config('app.name') }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <h1 style="font-size:1.3rem;margin:0 0 16px;">Hola, {{ $owner->name }}</h1>
                            <p style="font-size:0.95rem;line-height:1.6;margin:0 0 16px;">
                                El plan <strong>{{ $plan->name }}</strong> de <strong>{{ $tenant->company_display_name ?: $tenant->name }}</strong> venció el <strong>{{ $endedAt->format('d/m/Y') }}</strong> y el servicio quedó suspendido.
                            </p>
                            <p style="font-size:0.95rem;line-height:1.6;margin:0 0 24px;">
                                Renueva tu plan para recuperar el acceso completo a tu cuenta.
                            </p>
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
                                <tr>
                                    <td style="background:#12b5cb;border-radius:12px;">
                                        <a href="{{ $loginUrl }}" style="display:inline-block;padding:13px 26px;color:#04222f;font-weight:800;text-decoration:none;font-size:0.95rem;">Renovar mi plan</a>
                                    </td>
                                </tr>
                            </table>
                            <p style="font-size:0.85rem;line-height:1.6;color:#5b7388;margin:0;">
                                Si ya renovaste, ignora este mensaje.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px;background:#f4f9fb;text-align:center;">
                            <span style="font-size:0.78rem;color:#5b7388;">{{ config('app.name') }} · {{ $tenant->slug }}</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
