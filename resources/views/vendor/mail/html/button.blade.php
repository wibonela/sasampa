@props([
    'url',
    'color' => 'primary',
    'align' => 'center',
])
<table class="action" align="{{ $align }}" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="{{ $align }}" style="padding: 24px 0;">
<table border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td style="border-radius: 12px; background: linear-gradient(135deg, #0071e3 0%, #007AFF 100%); box-shadow: 0 4px 14px rgba(0, 113, 227, 0.35);">
<a href="{{ $url }}" target="_blank" rel="noopener" style="display: inline-block; padding: 14px 32px; font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'Segoe UI', Roboto, sans-serif; font-size: 15px; font-weight: 600; color: #ffffff; text-decoration: none; border-radius: 12px;">{!! $slot !!}</a>
</td>
</tr>
</table>
</td>
</tr>
</table>
