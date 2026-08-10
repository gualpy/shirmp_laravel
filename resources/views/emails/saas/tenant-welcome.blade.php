<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('mail.welcome_subject', ['app' => config('app.name')]) }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f9fb;font-family:'Segoe UI',Arial,sans-serif;color:#123047;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f9fb;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width:520px;background:#ffffff;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="background:linear-gradient(135deg,#031a29 0%,#0a3850 55%,#0f6b80 100%);padding:28px 32px;">
                            <span style="color:#ffffff;font-size:1.3rem;font-weight:800;letter-spacing:-0.01em;">{{ config('app.name') }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <h1 style="font-size:1.3rem;margin:0 0 16px;">¡Hola, {{ $owner->name }}!</h1>
                            <p style="font-size:0.95rem;line-height:1.6;margin:0 0 16px;">
                                Tu cuenta de <strong>{{ $tenant->company_display_name ?: $tenant->name }}</strong> ya está activa en {{ config('app.name') }}, con el plan <strong>{{ $plan->name }}</strong>.
                            </p>
                            <p style="font-size:0.95rem;line-height:1.6;margin:0 0 24px;">
                                Ya podés entrar con el usuario y la contraseña que definiste al registrarte.
                            </p>
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
                                <tr>
                                    <td style="background:#12b5cb;border-radius:12px;">
                                        <a href="{{ $loginUrl }}" style="display:inline-block;padding:13px 26px;color:#04222f;font-weight:800;text-decoration:none;font-size:0.95rem;">Ir a mi cuenta</a>
                                    </td>
                                </tr>
                            </table>
                            <p style="font-size:0.85rem;line-height:1.6;color:#5b7388;margin:0;">
                                Si no reconocés esta actividad, contactanos respondiendo este correo.
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
