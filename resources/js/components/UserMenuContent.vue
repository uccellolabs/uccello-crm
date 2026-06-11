<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Languages, LogOut, Settings } from '@lucide/vue';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import { useTranslations } from '@/composables/useTranslations';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';
import type { User } from '@/types';

type Props = {
    user: User;
};

const { t } = useTranslations();

const handleLogout = () => {
    router.flushAll();
};

defineProps<Props>();
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full cursor-pointer" :href="edit()" prefetch>
                <Settings class="mr-2 h-4 w-4" />
                {{ t('Settings') }}
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <div
        class="flex items-center justify-between gap-2 px-2 py-1.5"
        @click.stop
    >
        <span class="flex items-center gap-2 text-sm text-muted-foreground">
            <Languages class="h-4 w-4" />
            {{ t('Language') }}
        </span>
        <LanguageSwitcher />
    </div>
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true">
        <Link
            class="block w-full cursor-pointer"
            :href="logout()"
            @click="handleLogout"
            as="button"
            data-test="logout-button"
        >
            <LogOut class="mr-2 h-4 w-4" />
            {{ t('Log out') }}
        </Link>
    </DropdownMenuItem>
</template>
