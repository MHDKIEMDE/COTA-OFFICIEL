@extends('errors.layout-error')

@section('code', '419')
@section('title', 'SESSION EXPIRÉE')
@section('subtitle', 'Votre session a expiré. Veuillez vous reconnecter.')

@section('icon')
<svg class="icon" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
  <circle cx="32" cy="32" r="22" stroke="#ff5b3a" stroke-width="3"/>
  <path d="M32 20 V32 L40 36" stroke="#ff5b3a" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M46 14 C50 18 54 25 54 32" stroke="#ff5b3a" stroke-width="2.5" stroke-linecap="round" stroke-dasharray="3 3"/>
</svg>
@endsection
