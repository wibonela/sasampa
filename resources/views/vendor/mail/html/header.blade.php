@props(['url'])
<tr>
<td class="header" style="padding: 32px 0; text-align: center;">
<a href="{{ $url }}" style="text-decoration: none; display: inline-block;">
<table cellpadding="0" cellspacing="0" border="0" style="display: inline-table;">
<tr>
<td style="background: linear-gradient(135deg, #0071e3 0%, #007AFF 100%); width: 44px; height: 44px; border-radius: 12px; text-align: center; vertical-align: middle;">
<span style="color: #ffffff; font-size: 22px; font-weight: bold; line-height: 44px;">S</span>
</td>
<td style="padding-left: 12px; font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'Segoe UI', Roboto, sans-serif; font-size: 24px; font-weight: 700; color: #1a1a2e; letter-spacing: -0.5px; vertical-align: middle;">
{{ config('app.name') }}
</td>
</tr>
</table>
</a>
</td>
</tr>
