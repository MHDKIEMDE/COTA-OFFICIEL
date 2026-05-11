<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const sidebarOpen = ref(false);
const page = usePage();

const user = computed(() => page.props.auth?.user);
const isAuthenticated = computed(() => !!user.value);

const navigation = [
    { name: 'Dashboard', href: '/', icon: 'bi-house-door', routeName: 'home' },
    { name: 'Pronostics', href: '/predictions', icon: 'bi-trophy', routeName: 'predictions' },
    { name: 'Historique', href: '/history', icon: 'bi-clock-history', routeName: 'history' },
    { name: 'Statistiques', href: '/statistics', icon: 'bi-bar-chart-line', routeName: 'statistics' },
];

const userNavigation = [
    { name: 'Profil', href: '/profile', icon: 'bi-person', routeName: 'profile' },
    { name: 'Abonnement', href: '/subscription', icon: 'bi-credit-card', routeName: 'subscription' },
    { name: 'Parrainage', href: '/referral', icon: 'bi-gift', routeName: 'referral' },
];

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const closeSidebar = () => {
    sidebarOpen.value = false;
};

const isActive = (routeName) => {
    return page.url.startsWith(`/${routeName}`) || 
           (routeName === 'home' && page.url === '/');
};

const getUserInitials = () => {
    if (!user.value?.name) return '?';
    return user.value.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};
</script>

<template>
    <!-- Sidebar -->
    <aside class="sidebar" :class="{ 'show': sidebarOpen }">
        <!-- Brand -->
        <div class="sidebar-brand">
            <Link href="/" class="d-flex align-items-center gap-3 text-decoration-none">
                <div class="d-flex align-items-center justify-content-center rounded-3" 
                     style="width: 48px; height: 48px; background: linear-gradient(135deg, #E91E8C 0%, #8B5CF6 100%);">
                    <i class="bi bi-lightning-charge-fill text-white fs-4"></i>
                </div>
                <div>
                    <h5 class="mb-0 text-white fw-bold">COTA</h5>
                    <small class="text-muted-light">Prédictions Football</small>
                </div>
            </Link>
        </div>

        <!-- Navigation -->
        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item" v-for="item in navigation" :key="item.name">
                    <Link :href="item.href" 
                          class="nav-link" 
                          :class="{ 'active': isActive(item.routeName) }"
                          @click="closeSidebar">
                        <i :class="['bi', item.icon]"></i>
                        <span>{{ item.name }}</span>
                    </Link>
                </li>
            </ul>

            <!-- User Section -->
            <template v-if="isAuthenticated">
                <hr class="my-3 mx-3 border-secondary">
                <p class="px-4 mb-2 text-muted-light small text-uppercase fw-semibold">Mon Compte</p>
                <ul class="nav flex-column">
                    <li class="nav-item" v-for="item in userNavigation" :key="item.name">
                        <Link :href="item.href" 
                              class="nav-link" 
                              :class="{ 'active': isActive(item.routeName) }"
                              @click="closeSidebar">
                            <i :class="['bi', item.icon]"></i>
                            <span>{{ item.name }}</span>
                        </Link>
                    </li>
                </ul>
            </template>
        </nav>

        <!-- User Footer -->
        <div class="sidebar-footer">
            <template v-if="isAuthenticated">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-circle text-white fw-semibold"
                         style="width: 40px; height: 40px; background: linear-gradient(135deg, #E91E8C, #8B5CF6);">
                        {{ getUserInitials() }}
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <p class="mb-0 text-white fw-medium text-truncate">{{ user.name }}</p>
                        <small class="text-muted-light">
                            <span v-if="user.is_premium" class="premium-badge">
                                <i class="bi bi-star-fill"></i> Premium
                            </span>
                            <span v-else>Gratuit</span>
                        </small>
                    </div>
                    <Link href="/logout" method="post" as="button" 
                          class="btn btn-link text-muted-light p-0" title="Déconnexion">
                        <i class="bi bi-box-arrow-right fs-5"></i>
                    </Link>
                </div>
            </template>
            <template v-else>
                <div class="d-grid gap-2">
                    <Link href="/login" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Connexion
                    </Link>
                </div>
            </template>
        </div>
    </aside>

    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" :class="{ 'show': sidebarOpen }" @click="closeSidebar"></div>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Bar -->
        <header class="sticky-top bg-body border-bottom border-secondary py-3 px-4">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-link text-white d-lg-none p-0" @click="toggleSidebar">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                    <h1 class="h5 mb-0 text-white">
                        <slot name="header">Dashboard</slot>
                    </h1>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <!-- Date -->
                    <span class="text-muted-light small d-none d-md-block">
                        <i class="bi bi-calendar3 me-1"></i>
                        {{ new Date().toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' }) }}
                    </span>

                    <!-- Notifications -->
                    <button class="btn btn-link text-white position-relative p-0" v-if="isAuthenticated">
                        <i class="bi bi-bell fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                              style="font-size: 0.6rem;">
                            3
                        </span>
                    </button>

                    <!-- User Menu (Desktop) -->
                    <div class="dropdown d-none d-lg-block" v-if="isAuthenticated">
                        <button class="btn btn-link text-white d-flex align-items-center gap-2 text-decoration-none p-0" 
                                data-bs-toggle="dropdown">
                            <div class="d-flex align-items-center justify-content-center rounded-circle text-white fw-semibold"
                                 style="width: 36px; height: 36px; background: linear-gradient(135deg, #E91E8C, #8B5CF6);">
                                {{ getUserInitials() }}
                            </div>
                            <i class="bi bi-chevron-down small"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><Link class="dropdown-item" href="/profile"><i class="bi bi-person me-2"></i>Profil</Link></li>
                            <li><Link class="dropdown-item" href="/subscription"><i class="bi bi-credit-card me-2"></i>Abonnement</Link></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <Link href="/logout" method="post" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <!-- Login Button (Guest) -->
                    <Link href="/login" class="btn btn-primary btn-sm" v-else>
                        <i class="bi bi-box-arrow-in-right me-1"></i>
                        Connexion
                    </Link>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="p-4">
            <slot />
        </div>
    </main>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" @click="toggleSidebar">
        <i class="bi" :class="sidebarOpen ? 'bi-x-lg' : 'bi-list'" style="font-size: 1.5rem;"></i>
    </button>
</template>

