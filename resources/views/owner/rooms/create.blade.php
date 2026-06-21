@extends('layouts.dashboard')

@section('title', 'Thêm phòng')
@section('page-title', 'Thêm phòng')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Thông tin phòng</h5>
    </div>

    <div class="card-body">
        <form action="{{ route('owner.rooms.store') }}" method="POST">
            @include('owner.rooms._form')
        </form>
    </div>
</div>
@endsection