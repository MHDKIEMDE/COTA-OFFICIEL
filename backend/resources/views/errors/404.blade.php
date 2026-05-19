@extends('errors.layout-error')

@section('code', '404')
@section('title', 'PAGE INTROUVABLE')
@section('subtitle', 'La ressource demandée n\'existe pas ou a été déplacée.')

@section('icon')
<svg class="icon" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
  <circle cx="32" cy="32" r="26" stroke="#ff5b3a" stroke-width="3"/>
  <path d="M22 22 L42 42M42 22 L22 42" stroke="#ff5b3a" stroke-width="3" stroke-linecap="round"/>
</svg>
@endsection
