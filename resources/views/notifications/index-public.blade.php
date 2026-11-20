@extends('layouts.public', ['title' => 'Notifikasi Transaksi'])

@section('content')
    <div class="min-h-[70vh] bg-slate-50 px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
        @include('notifications._list')
    </div>
@endsection
