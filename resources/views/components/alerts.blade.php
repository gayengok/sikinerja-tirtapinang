@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 d-flex align-items-center mb-3" role="alert" style="border-radius: 14px;">
        <i class="fa-solid fa-circle-check me-2 fs-5 text-success"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 d-flex align-items-center mb-3" role="alert" style="border-radius: 14px;">
        <i class="fa-solid fa-circle-exclamation me-2 fs-5 text-danger"></i>
        <div>{{ session('error') }}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('warning'))
    <div class="alert alert-warning alert-dismissible fade show shadow-sm border-0 d-flex align-items-center mb-3" role="alert" style="border-radius: 14px;">
        <i class="fa-solid fa-triangle-exclamation me-2 fs-5 text-warning"></i>
        <div>{{ session('warning') }}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-3" role="alert" style="border-radius: 14px;">
        <div class="d-flex">
            <i class="fa-solid fa-circle-exclamation me-2 mt-1 fs-5 text-danger"></i>
            <div>
                <strong class="d-block mb-1">Periksa kembali data yang Anda masukkan:</strong>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

