<x-app-layout>
    <!-- Toast Notification -->
    <div id="toast-notification" class="toast-notification" style="display: none;">
        <div class="toast-content">
            <i class="bi bi-check-circle-fill toast-icon"></i>
            <span class="toast-message">Settings saved successfully!</span>
        </div>
    </div>

    <style>
        .toast-notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            animation: slideDown 0.3s ease-out;
        }
        .toast-content {
            display: flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #34C759 0%, #30B350 100%);
            color: white;
            padding: 14px 24px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(52, 199, 89, 0.35);
            font-weight: 500;
            font-size: 15px;
        }
        .toast-icon {
            font-size: 20px;
        }
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }
        @keyframes fadeOut {
            from {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
            to {
                opacity: 0;
                transform: translateX(-50%) translateY(-20px);
            }
        }
    </style>

    <div class="fade-in">
        <!-- Page Header -->
        <div class="mb-4">
            <h1 class="page-title">Settings</h1>
            <p class="page-subtitle">Configure your store</p>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Store Logo -->
                    <div class="card mb-4">
                        <div class="card-header">Store Logo</div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    @if($settings['store_logo'])
                                        <img src="{{ Storage::url($settings['store_logo']) }}"
                                             alt="Store Logo"
                                             style="max-width: 120px; max-height: 120px; border-radius: 12px; object-fit: contain; background: var(--apple-gray-6); padding: 8px;">
                                    @else
                                        <div style="width: 120px; height: 120px; border-radius: 12px; background: var(--apple-gray-6); display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-image text-secondary" style="font-size: 40px;"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="col">
                                    <div class="mb-2">
                                        <label for="store_logo" class="form-label mb-1">Upload Logo</label>
                                        <input type="file" class="form-control @error('store_logo') is-invalid @enderror"
                                               id="store_logo" name="store_logo" accept="image/*">
                                        @error('store_logo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Max size: 2MB. Recommended: Square image (e.g., 200x200)</small>
                                    </div>
                                    @if($settings['store_logo'])
                                        <form action="{{ route('settings.remove-logo') }}" method="POST" class="d-inline" id="removeLogoForm">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="if(confirm('Remove logo?')) document.getElementById('removeLogoForm').submit();">
                                                <i class="bi bi-trash me-1"></i>Remove Logo
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Store Information -->
                    <div class="card mb-4">
                        <div class="card-header">Store Information</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="store_name" class="form-label">Store Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('store_name') is-invalid @enderror"
                                           id="store_name" name="store_name"
                                           value="{{ old('store_name', $settings['store_name']) }}" required>
                                    @error('store_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="store_phone" class="form-label">Phone Number</label>
                                    <input type="text" class="form-control @error('store_phone') is-invalid @enderror"
                                           id="store_phone" name="store_phone"
                                           value="{{ old('store_phone', $settings['store_phone']) }}">
                                    @error('store_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="store_email" class="form-label">Email</label>
                                    <input type="email" class="form-control @error('store_email') is-invalid @enderror"
                                           id="store_email" name="store_email"
                                           value="{{ old('store_email', $settings['store_email'] ?? '') }}">
                                    @error('store_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="store_address" class="form-label">Address</label>
                                    <input type="text" class="form-control @error('store_address') is-invalid @enderror"
                                           id="store_address" name="store_address"
                                           value="{{ old('store_address', $settings['store_address']) }}">
                                    @error('store_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- POS Settings -->
                    <div class="card mb-4">
                        <div class="card-header">POS Settings</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="currency_symbol" class="form-label">Currency Symbol <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('currency_symbol') is-invalid @enderror"
                                           id="currency_symbol" name="currency_symbol"
                                           value="{{ old('currency_symbol', $settings['currency_symbol']) }}" required>
                                    @error('currency_symbol')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="default_tax_rate" class="form-label">Default Tax Rate (%)</label>
                                    <input type="number" step="0.01" class="form-control @error('default_tax_rate') is-invalid @enderror"
                                           id="default_tax_rate" name="default_tax_rate"
                                           value="{{ old('default_tax_rate', $settings['default_tax_rate'] ?? 0) }}"
                                           placeholder="0">
                                    @error('default_tax_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-0">
                                    <label for="low_stock_threshold" class="form-label">Low Stock Threshold</label>
                                    <input type="number" class="form-control @error('low_stock_threshold') is-invalid @enderror"
                                           id="low_stock_threshold" name="low_stock_threshold"
                                           value="{{ old('low_stock_threshold', $settings['low_stock_threshold'] ?? 10) }}"
                                           placeholder="10">
                                    @error('low_stock_threshold')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Receipt Settings -->
                    <div class="card mb-4">
                        <div class="card-header">Receipt Settings</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="receipt_header" class="form-label">Receipt Header</label>
                                <textarea class="form-control @error('receipt_header') is-invalid @enderror"
                                          id="receipt_header" name="receipt_header" rows="2"
                                          placeholder="Additional text at the top of receipts">{{ old('receipt_header', $settings['receipt_header']) }}</textarea>
                                @error('receipt_header')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-0">
                                <label for="receipt_footer" class="form-label">Receipt Footer</label>
                                <textarea class="form-control @error('receipt_footer') is-invalid @enderror"
                                          id="receipt_footer" name="receipt_footer" rows="2"
                                          placeholder="e.g., Thank you for your business!">{{ old('receipt_footer', $settings['receipt_footer']) }}</textarea>
                                @error('receipt_footer')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" class="btn btn-primary" id="saveBtn">
                            <i class="bi bi-check-circle me-1"></i>Save Settings
                        </button>
                        <span id="saveStatus" class="text-success fw-medium" style="display: none;">
                            <i class="bi bi-check-circle-fill me-1"></i>Saved!
                        </span>
                    </div>

                </form>
            </div>

            <!-- Preview Sidebar -->
            <div class="col-lg-4">
                <div class="card" style="position: sticky; top: 80px;">
                    <div class="card-header">Receipt Preview</div>
                    <div class="card-body p-3" style="background: #fff; font-family: 'Courier New', monospace; font-size: 12px;">
                        <div style="text-align: center; border-bottom: 1px dashed #ccc; padding-bottom: 12px; margin-bottom: 12px;">
                            @if($settings['store_logo'])
                                <img src="{{ Storage::url($settings['store_logo']) }}"
                                     style="max-width: 80px; max-height: 80px; margin-bottom: 8px;">
                            @endif
                            <div style="font-weight: bold; font-size: 14px;">{{ $settings['store_name'] }}</div>
                            @if($settings['store_address'])
                                <div style="color: #666;">{{ $settings['store_address'] }}</div>
                            @endif
                            @if($settings['store_phone'])
                                <div style="color: #666;">{{ $settings['store_phone'] }}</div>
                            @endif
                            @if($settings['receipt_header'])
                                <div style="margin-top: 8px; color: #333;">{{ $settings['receipt_header'] }}</div>
                            @endif
                        </div>
                        <div style="color: #666; font-size: 11px; margin-bottom: 8px;">
                            Receipt #TXN-20260110-0001<br>
                            {{ now()->format('M d, Y H:i') }}
                        </div>
                        <div style="border-bottom: 1px dashed #ccc; padding-bottom: 8px; margin-bottom: 8px;">
                            <div style="display: flex; justify-content: space-between;">
                                <span>Sample Product x2</span>
                                <span>{{ $settings['currency_symbol'] }} 10,000</span>
                            </div>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-weight: bold;">
                            <span>TOTAL</span>
                            <span>{{ $settings['currency_symbol'] }} 10,000</span>
                        </div>
                        @if($settings['receipt_footer'])
                            <div style="text-align: center; margin-top: 12px; padding-top: 12px; border-top: 1px dashed #ccc; color: #666;">
                                {{ $settings['receipt_footer'] }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const toast = document.getElementById('toast-notification');

                // Show toast
                toast.style.display = 'block';

                // Hide after 3 seconds with animation
                setTimeout(function() {
                    toast.style.animation = 'fadeOut 0.3s ease-out forwards';
                    setTimeout(function() {
                        toast.style.display = 'none';
                    }, 300);
                }, 3000);
            });
        </script>
    @endif
</x-app-layout>
