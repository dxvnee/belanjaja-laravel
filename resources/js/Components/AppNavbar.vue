<script setup>
import { ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AccountDropdown from "@/Components/AccountDropdown.vue";
import Hamburger from "@/Components/Hamburger.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { Icons } from "@/Icons";

const showingNavigationDropdown = ref(false);

const form = useForm({
    search: "",
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

const submit = () => {
    router.get(
        route("dashboard"),
        {
            search: form.search,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const logout = () => {
    router.post(route("logout"));
};

const jual = () => {
    router.get(route("jual.index"));
};
</script>

<template>
    <nav
        class="w-full border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-800 border"
    >
        <!-- Primary Navigation Menu -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <AuthenticationCardLogo :size="64" />
                    </div>
                    <h1 class="text-2xl font-black text-primary-600">
                        Belanjaja!
                    </h1>
                </div>

                <div class="hidden sm:flex sm:items-center sm:ms-6 w-full">
                    <div class="ms-2 w-full">
                        <form @submit.prevent="submit">
                            <TextInput
                                id="search"
                                v-model="form.search"
                                type="text"
                                class="w-full"
                                placeholder="Cari barang yang kamu butuhkan..."
                                icon="search"
                                :iconClick="() => {}"
                            />
                        </form>
                    </div>

                    <!-- Settings Dropdown -->
                    <div class=" ms-5 flex justify-end items-center">
                        <component :is="Icons['cart']" class="size-6 text-gray-500 dark:text-gray-400"></component>

                        <AccountDropdown :logout="logout" />
                        <div>
                            <PrimaryButton
                                class="w-full flex items-center min-w-20"
                                :class="{
                                    'opacity-25': form.processing,
                                }"
                                :disabled="form.processing"
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
            <div class="pt-2 pb-3 space-y-1">
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
