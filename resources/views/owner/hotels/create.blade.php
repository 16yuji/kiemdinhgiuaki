@extends('layouts.dashboard')

@section('title', 'Thêm khách sạn')
@section('page-title', 'Thêm khách sạn')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Thông tin khách sạn</h5>
    </div>

    <div class="card-body">
        <form action="{{ route('owner.hotels.store') }}" method="POST" enctype="multipart/form-data">
            @include('owner.hotels._form')
        </form>
    </div>
</div>
@endsection