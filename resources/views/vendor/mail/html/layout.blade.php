<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<title>{{ config('app.name') }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<style>
/* Base */
body {
    margin: 0;
    padding: 0;
    width: 100%;
    background-color: #f4f4f7;
    -webkit-text-size-adjust: none;
}

table {
    border-collapse: collapse;
}

td {
    font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}

/* Wrapper */
.wrapper {
    width: 100%;
    background-color: #f4f4f7;
    padding: 40px 0;
}

/* Content */
.content {
    width: 100%;
    max-width: 600px;
    margin: 0 auto;
}

/* Header */
.header {
    padding: 32px 0;
    text-align: center;
    background-color: #1a1a2e;
    border-radius: 16px 16px 0 0;
}

.header a {
    font-size: 26px;
    font-weight: 700;
    color: #ffffff !important;
    text-decoration: none;
    letter-spacing: -0.5px;
}

.header-logo {
    font-size: 26px;
    font-weight: 700;
    color: #ffffff !important;
    text-decoration: none;
    letter-spacing: -0.5px;
}

.header-logo-icon {
    display: inline-block;
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #0071e3 0%, #007AFF 100%);
    border-radius: 12px;
    vertical-align: middle;
    margin-right: 12px;
    line-height: 44px;
    text-align: center;
}

.header-logo-icon span {
    color: #ffffff;
    font-size: 20px;
}

/* Body */
.body {
    background-color: #ffffff;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.inner-body {
    width: 100%;
    background-color: #ffffff;
    border-radius: 16px;
}

.content-cell {
    padding: 48px 40px;
    font-size: 15px;
    line-height: 1.7;
    color: #4a5568;
}

/* Typography */
h1 {
    font-size: 24px;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0 0 20px;
    letter-spacing: -0.3px;
}

p {
    margin: 0 0 16px;
    font-size: 15px;
    line-height: 1.7;
    color: #4a5568;
}

/* Button */
.button {
    display: inline-block;
    background: linear-gradient(135deg, #0071e3 0%, #007AFF 100%);
    color: #ffffff !important;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    padding: 14px 32px;
    border-radius: 12px;
    margin: 24px 0;
    box-shadow: 0 4px 14px rgba(0, 113, 227, 0.35);
}

.button:hover {
    background: linear-gradient(135deg, #005bb5 0%, #0066cc 100%);
}

/* Footer */
.footer {
    padding: 32px 0;
    text-align: center;
}

.footer p {
    font-size: 13px;
    color: #9ca3af;
    margin: 0 0 8px;
}

.footer a {
    color: #007AFF;
    text-decoration: none;
}

/* Subcopy */
.subcopy {
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid #e5e7eb;
}

.subcopy p {
    font-size: 13px;
    color: #9ca3af;
    line-height: 1.6;
}

/* Responsive */
@media only screen and (max-width: 600px) {
    .wrapper {
        padding: 20px 16px;
    }

    .content-cell {
        padding: 32px 24px;
    }

    .button {
        display: block;
        text-align: center;
    }
}
</style>
{!! $head ?? '' !!}
</head>
<body>
<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<table class="content" width="600" cellpadding="0" cellspacing="0" role="presentation">
{!! $header ?? '' !!}

<!-- Email Body -->
<tr>
<td class="body" width="100%" cellpadding="0" cellspacing="0">
<table class="inner-body" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="content-cell">
{!! Illuminate\Mail\Markdown::parse($slot) !!}

{!! $subcopy ?? '' !!}
</td>
</tr>
</table>
</td>
</tr>

{!! $footer ?? '' !!}
</table>
</td>
</tr>
</table>
</body>
</html>
