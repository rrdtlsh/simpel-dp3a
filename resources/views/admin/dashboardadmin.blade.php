@extends('layouts.admin')

@section('title', 'Dashboard | SIMPEL DP3A')

@section('content')

<div class="header">
    <div class="header-left">
        <div class="hamburger" id="hamburger">☰</div>
        <img src="{{ asset('images/DPPPA ver2.png') }}" alt="Logo DP3A">
    </div>

    <div class="header-right">
        <div class="user-info">
            <div class="user-icon">
                <img src="{{ asset('images/accicon.png') }}" alt="Akun">
            </div>
            <span>Perencanaan</span>
        </div>

        <!-- Logout Laravel -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn-logout">Logout</button>
        </form>
    </div>
</div>

<div class="wrapper">

    <div class="sidebar" id="sidebar">
        <ul id="menu">
            <li class="active">Dashboard</li>
            <li>Permintaan Dokumen</li>
            <li>Verifikasi Dokumen</li>
            <li>Dokumen Masuk</li>
            <li>Pertanyaan Evaluasi PUG</li>
        </ul>
    </div>

    <div class="content">
        <h2>Halo, Bidang Perencanaan!</h2>

        <div class="card-container">
            <div class="card">
                <h4>Permintaan Anda</h4>
                <span>12</span>
            </div>
            <div class="card">
                <h4>Total Arsip</h4>
                <span>58</span>
            </div>
        </div>
    </div>

</div>
@endsection