@extends('layouts.dashboard')

@section('title', 'Thêm hạng phòng')
@section('page-title', 'Thêm hạng phòng')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Thông tin hạng phòng</h5>
    </div>

    <div class="card-body">
        <form action="{{ route('owner.room-types.store') }}" method="POST" enctype="multipart/form-data">
            @include('owner.room-types._form')
        </form>
    </div>
</div>
@endsection 