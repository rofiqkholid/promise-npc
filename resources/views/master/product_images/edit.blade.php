@extends('layouts.app')

@section('title', 'Upload Label Image - ' . $product->part_no)
@section('page_title', 'Master Data / Product Label Images / ' . $product->part_no)

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 max-w-2xl">

    {{-- Header --}}
    <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-image text-emerald-500"></i> Upload Label Image
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                <strong>Part No:</strong> <span class="text-blue-600 dark:text-blue-400 font-bold">{{ $product->part_no }}</span>
                &nbsp;|&nbsp;
                <strong>Name:</strong> {{ $product->part_name }}
            </p>
        </div>
        <a href="{{ route('master.product-images.index', request()->query()) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 shadow-sm text-sm font-medium transition dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 border border-gray-300 dark:border-gray-600">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="p-6 space-y-6">

        {{-- Current Image Preview --}}
        <div>
            <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-3 flex items-center gap-2">
                <i class="fa-solid fa-eye text-gray-400"></i> Current Label Image
            </h3>
            <div class="border-2 border-dashed border-gray-200 dark:border-gray-600 p-6 flex flex-col items-center justify-center text-center bg-gray-50 dark:bg-gray-800/50 min-h-[200px]">
                @if($product->productDetail && $product->productDetail->label_image_path)
                    <img src="{{ asset('storage/' . ltrim(str_replace('public/', '', $product->productDetail->label_image_path), '/')) }}"
                         alt="Current Label Image"
                         class="max-h-[200px] max-w-full object-contain mb-3">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 text-xs font-bold rounded-full border border-emerald-200 dark:border-emerald-800">
                        <i class="fa-solid fa-check-circle"></i> Image already exists — upload a new one to replace it
                    </span>
                @else
                    <i class="fa-solid fa-image text-5xl text-gray-300 dark:text-gray-600 mb-3"></i>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">No label image for this part yet</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Upload a product photo that will appear on QC labels when printed</p>
                @endif
            </div>
        </div>

        {{-- Upload Form --}}
        <div>
            <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-3 flex items-center gap-2">
                <i class="fa-solid fa-upload text-emerald-500"></i>
                {{ $product->productDetail && $product->productDetail->label_image_path ? 'Replace Label Image' : 'Upload Label Image' }}
            </h3>

            <form action="{{ route('master.product-images.update', array_merge(['product' => $product->hashed_id], request()->query())) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="border-2 border-dashed border-emerald-300 dark:border-emerald-700 p-6 bg-emerald-50/50 dark:bg-emerald-900/10">
                    {{-- Preview on select --}}
                    <div id="preview-area" class="hidden mb-4 flex justify-center">
                        <img id="img-preview" src="" alt="Preview" class="max-h-[180px] max-w-full object-contain border border-emerald-200 rounded">
                    </div>

                    <div class="flex flex-col items-center text-center">
                        <i class="fa-solid fa-tag text-3xl text-emerald-400 mb-2" id="upload-icon"></i>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mb-1" id="upload-hint">Click or drag image file here</p>
                        <p class="text-xs text-gray-400 mb-4">Format: JPG, PNG, GIF, WebP — Max. 4 MB</p>
                        <label for="label_image"
                               class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-[13px] font-semibold rounded transition">
                            <i class="fa-solid fa-folder-open"></i> Choose File
                        </label>
                        <input type="file" id="label_image" name="label_image" class="hidden" accept="image/*" onchange="previewImage(this)">
                        <p id="filename-label" class="text-xs text-gray-500 mt-2 italic">No file selected</p>
                    </div>
                </div>

                @error('label_image')
                <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
                    <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                </p>
                @enderror

                <div class="mt-4 flex gap-3 justify-end">
                    <a href="{{ route('master.product-images.index', request()->query()) }}"
                       class="px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 transition shadow-sm text-[13px] font-medium">
                        Cancel
                    </a>
                    <button type="submit" id="submit-btn" disabled
                            class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 disabled:cursor-not-allowed text-white transition shadow-sm font-bold flex items-center gap-2 text-sm">
                        <i class="fa-solid fa-floppy-disk"></i> Save Label Image
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewImage(input) {
    const file = input.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('img-preview').src = e.target.result;
        document.getElementById('preview-area').classList.remove('hidden');
        document.getElementById('preview-area').classList.add('flex');
        document.getElementById('upload-icon').classList.add('hidden');
        document.getElementById('upload-hint').classList.add('hidden');
        document.getElementById('filename-label').textContent = file.name;
        document.getElementById('submit-btn').disabled = false;
    };
    reader.readAsDataURL(file);
}
</script>
@endpush
