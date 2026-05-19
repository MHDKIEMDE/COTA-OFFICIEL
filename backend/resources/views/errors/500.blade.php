@extends('errors.layout-error')

@section('code', '500')
@section('title', 'ERREUR SERVEUR')
@section('subtitle', 'Une erreur interne s\'est produite. Réessayez dans quelques instants.')

@section('icon')
<svg class="icon" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M32 10 L56 52 H8 Z" stroke="#ff5b3a" stroke-width="3" stroke-linejoin="round"/>
  <line x1="32" y1="26" x2="32" y2="38" stroke="#ff5b3a" stroke-width="3" stroke-linecap="round"/>
  <circle cx="32" cy="44" r="1.5" fill="#ff5b3a"/>
</svg>
@endsection
