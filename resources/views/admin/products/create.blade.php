@extends('admin.layouts.admin')

@section('title', 'Thêm Sản Phẩm Mới')

@section('content')
<div class="px-0">
    <div class="w-full">
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('admin.products.partials.create_form')
        </form>
    </div>
</div>
@endsection

@push('scripts')
    @include('admin.products.partials.create_scripts')
@endpush
