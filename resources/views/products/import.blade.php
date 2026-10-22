<x-app-layout>
    <div class="fade-in">
        <div class="d-flex align-items-center gap-2 mb-4">
            <a href="{{ route('products.index') }}" class="text-secondary" style="text-decoration: none;">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="page-title mb-0">Import Products</h1>
                <p class="page-subtitle mb-0">Upload a CSV file to add many products at once</p>
            </div>
        </div>

        @if(session('import_errors'))
            <div class="card mb-4 border-warning">
                <div class="card-header">Rows that were skipped</div>
                <div class="card-body">
                    <ul class="mb-0 small">
                        @foreach(session('import_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                    <p class="text-secondary small mt-2 mb-0">Fix these rows and upload them again. Rows already imported are not affected.</p>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-7">
                <div class="card mb-4">
                    <div class="card-header">1. Get the template</div>
                    <div class="card-body">
                        <p>Download the template, fill in one product per row (Excel or Google Sheets), then save it as <strong>CSV</strong>.</p>
                        <a href="{{ route('products.import.template') }}" class="btn btn-outline-primary">
                            <i class="bi bi-download me-1"></i>Download template
                        </a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">2. Upload your file</div>
                    <div class="card-body">
                        <form action="{{ route('products.import.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <input type="file" name="file" accept=".csv,.txt"
                                       class="form-control @error('file') is-invalid @enderror" required>
                                @error('file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload me-1"></i>Import products
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">Columns</div>
                    <div class="card-body small">
                        <p class="mb-2"><strong>Required:</strong> Product Name, Selling Price</p>
                        <ul class="mb-3">
                            <li><strong>Product Name</strong> – e.g. Soda 500ml</li>
                            <li><strong>Category</strong> – e.g. Drinks. Created automatically if it doesn't exist</li>
                            <li><strong>Selling Price</strong> – price the customer pays</li>
                            <li><strong>Cost Price</strong> – what you paid for it (defaults to 0)</li>
                            <li><strong>Opening Stock</strong> – how many you have now, whole number. Set it or the product can't be sold</li>
                            <li><strong>Barcode</strong>, <strong>SKU</strong> – optional, must be unique</li>
                            <li><strong>Low Stock Alert</strong> – warn when stock falls to this number (defaults to 10)</li>
                            <li><strong>Tax Rate %</strong> – 0 to 100 (defaults to 0)</li>
                            <li><strong>Description</strong> – optional</li>
                            <li><strong>Active (yes/no)</strong> – defaults to yes</li>
                        </ul>
                        <p class="text-secondary mb-0">Up to 2,000 products per file. Invalid rows are skipped and listed so you can fix them; valid rows are still imported.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
