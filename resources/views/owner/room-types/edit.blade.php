@extends('layouts.dashboard')

@section('title', 'Sửa hạng phòng')
@section('page-title', 'Sửa hạng phòng')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Cập nhật hạng phòng</h5>
    </div>

    <div class="card-body">
        <form action="{{ route('owner.room-types.update', $roomType) }}" method="POST" enctype="multipart/form-data">
            @method('PUT')
            @include('owner.room-types._form')
        </form>
    </div>
</div>
@endsection