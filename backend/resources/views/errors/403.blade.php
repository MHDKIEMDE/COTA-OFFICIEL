@extends('errors.layout-error')

@section('code', '403')
@section('title', 'ACCÈS REFUSÉ')
@section('subtitle', 'Vous n\'avez pas les droits pour accéder à cette ressource.')

@section('icon')
<svg class="icon" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
  <circle cx="32" cy="32" r="26" stroke="#ff5b3a" stroke-width="3"/>
  <rect x="22" y="28" width="20" height="16" rx="3" stroke="#ff5b3a" stroke-width="3"/>
  <path d="M26 28 V24 C26 19.6 37.4 19.6 37.4 24 V28" stroke="#ff5b3a" stroke-width="3" stroke-linecap="round"/>
</svg>
@endsection
