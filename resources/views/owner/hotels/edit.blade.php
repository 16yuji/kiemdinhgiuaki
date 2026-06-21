@extends('layouts.dashboard')

@section('title', 'Sửa khách sạn')
@section('page-title', 'Sửa khách sạn')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Cập nhật thông tin khách sạn</h5>
    </div>

    <div class="card-body">
        <form action="{{ route('owner.hotels.update', $hotel) }}" method="POST" enctype="multipart/form-data">
            @method('PUT')
            @include('owner.hotels._form')
        </form>
    </div>
</div>
@endsection