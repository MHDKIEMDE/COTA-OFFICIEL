-- =============================================
-- COTA — Schéma base de données Supabase
-- =============================================

-- Extension pour UUID
create extension if not exists "uuid-ossp";

-- =============================================
-- PROFILES (extension de auth.users)
-- =============================================
create table public.profiles (
  id uuid references auth.users(id) on delete cascade primary key,
  email text,
  phone text,
  full_name text,
  avatar_url text,
  role text not null default 'free' check (role in ('free', 'premium', 'admin')),
  referral_code text unique default substr(md5(random()::text), 1, 8),
  referred_by uuid references public.profiles(id),
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

-- =============================================
-- SUBSCRIPTIONS
-- =============================================
create table public.subscriptions (
  id uuid primary key default uuid_generate_v4(),
  user_id uuid references public.profiles(id) on delete cascade not null,
  plan text not null check (plan in ('monthly', 'quarterly', 'yearly')),
  status text not null default 'active' check (status in ('active', 'expired', 'cancelled')),
  amount numeric(10,2) not null,
  currency text not null default 'XOF',
  payment_method text check (payment_method in ('wave', 'orange_money', 'mtn', 'moov')),
  paydunya_token text,
  start_date timestamptz not null default now(),
  end_date timestamptz not null,
  created_at timestamptz not null default now()
);

-- =============================================
-- LEAGUES (compétitions)
-- =============================================
create table public.leagues (
  id uuid primary key default uuid_generate_v4(),
  api_id integer unique,
  name text not null,
  country text,
  logo_url text,
  tier integer not null default 4 check (tier between 1 and 4),
  is_active boolean not null default true,
  created_at timestamptz not null default now()
);

-- =============================================
-- MATCHES
-- =============================================
create table public.matches (
  id uuid primary key default uuid_generate_v4(),
  api_id integer unique,
  league_id uuid references public.leagues(id),
  home_team text not null,
  away_team text not null,
  home_logo_url text,
  away_logo_url text,
  match_date timestamptz not null,
  status text not null default 'scheduled' check (status in ('scheduled', 'live', 'finished', 'cancelled')),
  home_score integer,
  away_score integer,
  created_at timestamptz not null default now()
);

-- =============================================
-- PREDICTIONS
-- =============================================
create table public.predictions (
  id uuid primary key default uuid_generate_v4(),
  match_id uuid references public.matches(id) on delete cascade not null,
  prediction text not null,
  confidence integer not null check (confidence between 1 and 4),
  score numeric(5,2) not null check (score between 0 and 100),
  odds numeric(6,2),
  is_premium boolean not null default false,
  result text check (result in ('win', 'loss', 'void')),
  analysis text,
  created_at timestamptz not null default now()
);

-- =============================================
-- DAILY COUPONS
-- =============================================
create table public.daily_coupons (
  id uuid primary key default uuid_generate_v4(),
  date date not null unique,
  prediction_ids uuid[] not null default '{}',
  total_odds numeric(8,2),
  confidence integer check (confidence between 1 and 4),
  result text check (result in ('win', 'loss', 'void')),
  created_at timestamptz not null default now()
);

-- =============================================
-- AFFILIATE LINKS
-- =============================================
create table public.affiliate_links (
  id uuid primary key default uuid_generate_v4(),
  bookmaker text not null,
  logo_url text,
  url text not null,
  bonus_description text,
  is_active boolean not null default true,
  created_at timestamptz not null default now()
);

-- =============================================
-- RLS — Row Level Security
-- =============================================
alter table public.profiles enable row level security;
alter table public.subscriptions enable row level security;
alter table public.leagues enable row level security;
alter table public.matches enable row level security;
alter table public.predictions enable row level security;
alter table public.daily_coupons enable row level security;
alter table public.affiliate_links enable row level security;

-- Profiles : chaque user voit son propre profil
create policy "profiles_select_own" on public.profiles
  for select using (auth.uid() = id);
create policy "profiles_update_own" on public.profiles
  for update using (auth.uid() = id);

-- Admin voit tout
create policy "profiles_admin_all" on public.profiles
  for all using (
    exists (select 1 from public.profiles where id = auth.uid() and role = 'admin')
  );

-- Leagues : tout le monde peut lire
create policy "leagues_public_read" on public.leagues
  for select using (true);

-- Matches : tout le monde peut lire
create policy "matches_public_read" on public.matches
  for select using (true);

-- Predictions : free voit non-premium, premium voit tout
create policy "predictions_free_read" on public.predictions
  for select using (
    is_premium = false
    or exists (
      select 1 from public.profiles
      where id = auth.uid() and role in ('premium', 'admin')
    )
  );

-- Daily coupons : tout le monde voit la liste, premium voit le détail
create policy "coupons_public_read" on public.daily_coupons
  for select using (true);

-- Subscriptions : chaque user voit les siennes
create policy "subscriptions_own" on public.subscriptions
  for select using (auth.uid() = user_id);

-- Affiliate links : public
create policy "affiliate_public_read" on public.affiliate_links
  for select using (is_active = true);

-- =============================================
-- TRIGGER : créer un profile à l'inscription
-- =============================================
create or replace function public.handle_new_user()
returns trigger language plpgsql security definer as $$
begin
  insert into public.profiles (id, email, phone)
  values (
    new.id,
    new.email,
    new.phone
  );
  return new;
end;
$$;

create trigger on_auth_user_created
  after insert on auth.users
  for each row execute procedure public.handle_new_user();

-- =============================================
-- TRIGGER : updated_at auto
-- =============================================
create or replace function public.set_updated_at()
returns trigger language plpgsql as $$
begin
  new.updated_at = now();
  return new;
end;
$$;

create trigger profiles_updated_at
  before update on public.profiles
  for each row execute procedure public.set_updated_at();
