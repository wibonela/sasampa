<x-app-layout>
    <div class="fade-in">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <!-- Page Header -->
                <div class="mb-4">
                    <h1 class="page-title">Add Expense Category</h1>
                    <p class="page-subtitle">Create a new expense category</p>
                </div>

                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('expense-categories.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}" required autofocus
                                       placeholder="e.g., Malighafi, Vifungashio, Nishati">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="3"
                                          placeholder="Describe what expenses belong to this category">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active
                                    </label>
                                    <div class="text-secondary small">Inactive categories won't appear in the expense form</div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i>Save Category
                                </button>
                                <a href="{{ route('expense-categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
