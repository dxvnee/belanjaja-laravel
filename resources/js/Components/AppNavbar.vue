<script setup>
import { ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";
import SearchAutocomplete from "@/Components/SearchAutocomplete.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AccountDropdown from "@/Components/AccountDropdown.vue";
import Hamburger from "@/Components/Hamburger.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { Icons } from "@/Icons";
import IconButton from "./IconButton.vue";
import { useDarkMode } from "@/Composable/useDarkMode";

const showingNavigationDropdown = ref(false);
const { isDark, toggleDarkMode } = useDarkMode();
const page = usePage();

const props = defineProps({
    routeName: {
        type: String,
        default: "dashboard.search",
    },
});

const switchToTeam = (team) => {
    router.put(
        route("current-team.update"),
        {
            team_id: team.id,
        },
        {
            preserveState: false,
        },
    );
};

const logout = () => {
    router.post(route("logout"));
};

const jual = () => {
    router.get(route("jual.index"));
};

const cart = () => {
    router.get(route("cart.index"));
};

</script>

<template>
    <nav
        class="w-full border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-800 border"
    >
        <!-- Primary Navigation Menu -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-1">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <AuthenticationCardLogo :size="48" />
                    </div>
                    <h1 class="text-2xl font-black text-primary-600">
                        Belanjaja!
                    </h1>
                </div>

                <div class="hidden sm:flex sm:items-center sm:ms-6 w-full">
                    <div class="ms-2 w-full">
                        <SearchAutocomplete
                            id="search"
                            :routeName="routeName"
                            placeholder="Cari barang yang kamu butuhkan..."
                        />
                    </div>

                    <!-- Settings Dropdown -->
                    <div class=" ms-5 flex justify-end items-center">
                        <IconButton icons="cart" :fun="cart" />

                        <AccountDropdown :logout="logout" />
                        <div>
                            <PrimaryButton
                                class="w-full flex items-center min-w-20"
                                @click="jual"
                            >
                                + Jual
                            </PrimaryButton>
                        </div>
                    </div>
                </div>

                <!-- Hamburger -->
                <Hamburger
                    v-model:showingNavigationDropdown="
                        showingNavigationDropdown
                    "
                />
            </div>
        </div>

        <!-- Responsive Navigation Menu -->
        <div
            :class="{
                block: showingNavigationDropdown,
                hidden: !showingNavigationDropdown,
            }"
            class="sm:hidden"
        >
            <div class="px-4 pt-3 pb-2">
                <SearchAutocomplete
                    id="mobile-search"
                    :routeName="routeName"
                    placeholder="Cari barang..."
                />
            </div>

            <div class="pt-1 pb-3 space-y-1">
                <ResponsiveNavLink
                    :href="route('dashboard')"
                    :active="route().current('dashboard')"
                >
                    Dashboard
                </ResponsiveNavLink>
            </div>

            <!-- Responsive Settings Options -->
            <div
                class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600"
            >
                <div class="flex items-center px-4">
                    <div
                        v-if="$page.props.jetstream.managesProfilePhotos"
                        class="shrink-0 me-3"
                    >
                        <img
                            class="size-10 rounded-full object-cover"
                            :src="$page.props.auth.user.profile_photo_url"
                            :alt="$page.props.auth.user.name"
                        />
                    </div>

                    <div>
                        <div
                            class="font-medium text-base text-gray-800 dark:text-gray-200"
                        >
                            {{ $page.props.auth.user.name }}
                        </div>
                        <div class="font-medium text-sm text-gray-500">
                            {{ $page.props.auth.user.email }}
                        </div>
                    </div>
                </div>

                <div class="mt-3 space-y-1">
                    <ResponsiveNavLink
                        :href="route('profile.show')"
                        :active="route().current('profile.show')"
                    >
                        Profile
                    </ResponsiveNavLink>

                    <ResponsiveNavLink
                        :href="route('admin.show')"
                        :active="route().current('admin.show')"
                    >
                        Dashboard Penjual
                    </ResponsiveNavLink>

                    <ResponsiveNavLink
                        :href="route('admin.orders')"
                        :active="route().current('admin.orders')"
                    >
                        Pesanan Masuk
                    </ResponsiveNavLink>

                    <ResponsiveNavLink
                        :href="route('orders.index')"
                        :active="route().current('orders.index')"
                    >
                        Pembelian Saya
                    </ResponsiveNavLink>

                    <ResponsiveNavLink as="button" @click="toggleDarkMode">
                        <div class="flex items-center justify-between w-full">
                            <span>Mode Tampilan</span>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                {{ isDark ? '🌙 Dark' : '☀️ Light' }}
                            </span>
                        </div>
                    </ResponsiveNavLink>

                    <ResponsiveNavLink
                        v-if="$page.props.jetstream.hasApiFeatures"
                        :href="route('api-tokens.index')"
                        :active="route().current('api-tokens.index')"
                    >
                        API Tokens
                    </ResponsiveNavLink>

                    <!-- Authentication -->
                    <form method="POST" @submit.prevent="logout">
                        <ResponsiveNavLink as="button"
                            >Log Out</ResponsiveNavLink
                        >
                    </form>

                    <!-- Team Management -->
                    <template v-if="$page.props.jetstream.hasTeamFeatures">
                        <div
                            class="border-t border-gray-200 dark:border-gray-600"
                        />

                        <div class="block px-4 py-2 text-xs text-gray-400">
                            Manage Team
                        </div>

                        <!-- Team Settings -->
                        <ResponsiveNavLink
                            :href="
                                route(
                                    'teams.show',
                                    $page.props.auth.user.current_team,
                                )
                            "
                            :active="route().current('teams.show')"
                        >
                            Team Settings
                        </ResponsiveNavLink>

                        <ResponsiveNavLink
                            v-if="$page.props.jetstream.canCreateTeams"
                            :href="route('teams.create')"
                            :active="route().current('teams.create')"
                        >
                            Create New Team
                        </ResponsiveNavLink>

                        <!-- Team Switcher -->
                        <template
                            v-if="$page.props.auth.user.all_teams.length > 1"
                        >
                            <div
                                class="border-t border-gray-200 dark:border-gray-600"
                            />

                            <div class="block px-4 py-2 text-xs text-gray-400">
                                Switch Teams
                            </div>

                            <template
                                v-for="team in $page.props.auth.user.all_teams"
                                :key="team.id"
                            >
                                <form @submit.prevent="switchToTeam(team)">
                                    <ResponsiveNavLink as="button">
                                        <div class="flex items-center">
                                            <svg
                                                v-if="
                                                    team.id ==
                                                    $page.props.auth.user
                                                        .current_team_id
                                                "
                                                class="me-2 size-5 text-green-400"
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke-width="1.5"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>
                                            <div>{{ team.name }}</div>
                                        </div>
                                    </ResponsiveNavLink>
                                </form>
                            </template>
                        </template>
                    </template>
                </div>
            </div>
        </div>
    </nav>
</template>
