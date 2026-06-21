@extends('layouts.dashboard')

@section('title', 'Sửa phòng')
@section('page-title', 'Sửa phòng')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Cập nhật thông tin phòng</h5>
    </div>

    <div class="card-body">
        <form action="{{ route('owner.rooms.update', $room) }}" method="POST">
            @method('PUT')
            @include('owner.rooms._form')
        </form>
    </div>
</div>
@endsection