<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Building2,
    CheckSquare,
    KanbanSquare,
    LayoutGrid,
    ListChecks,
    SlidersHorizontal,
    Users,
    Workflow,
} from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import SidebarPromo from '@/components/SidebarPromo.vue';
import TeamSwitcher from '@/components/TeamSwitcher.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useTranslations } from '@/composables/useTranslations';
import { dashboard } from '@/routes';
import { index as companiesIndex } from '@/routes/companies';
import { index as contactsIndex } from '@/routes/contacts';
import { index as customFieldsIndex } from '@/routes/custom-fields';
import { board as pipelineBoard } from '@/routes/deals';
import { index as picklistsIndex } from '@/routes/picklists';
import { index as pipelineSettingsIndex } from '@/routes/pipeline-settings';
import { index as tasksIndex } from '@/routes/tasks';
import type { NavItem } from '@/types';

const page = usePage();
const { t } = useTranslations();

const teamSlug = computed(() => page.props.currentTeam?.slug ?? null);
const canManageCustomFields = computed(
    () => page.props.permissions?.manageCustomFields ?? false,
);

const dashboardUrl = computed(() =>
    teamSlug.value ? dashboard(teamSlug.value).url : '/',
);

const platformNavItems = computed<NavItem[]>(() => {
    if (!teamSlug.value) {
        return [];
    }

    return [
        { title: t('Dashboard'), href: dashboardUrl.value, icon: LayoutGrid },
        {
            title: t('Companies'),
            href: companiesIndex(teamSlug.value).url,
            icon: Building2,
        },
        {
            title: t('Contacts'),
            href: contactsIndex(teamSlug.value).url,
            icon: Users,
        },
        {
            title: t('Pipeline'),
            href: pipelineBoard(teamSlug.value).url,
            icon: KanbanSquare,
        },
        {
            title: t('Tasks'),
            href: tasksIndex(teamSlug.value).url,
            icon: CheckSquare,
        },
    ];
});

const adminNavItems = computed<NavItem[]>(() => {
    if (!teamSlug.value || !canManageCustomFields.value) {
        return [];
    }

    return [
        {
            title: t('Custom fields'),
            href: customFieldsIndex(teamSlug.value).url,
            icon: SlidersHorizontal,
        },
        {
            title: t('Picklists'),
            href: picklistsIndex(teamSlug.value).url,
            icon: ListChecks,
        },
        {
            title: t('Pipeline stages'),
            href: pipelineSettingsIndex(teamSlug.value).url,
            icon: Workflow,
        },
    ];
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboardUrl">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
            <SidebarMenu>
                <SidebarMenuItem>
                    <TeamSwitcher />
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="platformNavItems" :label="t('Platform')" />
            <NavMain
                v-if="adminNavItems.length"
                :items="adminNavItems"
                :label="t('Administration')"
            />
        </SidebarContent>

        <SidebarFooter>
            <SidebarPromo v-if="page.props.sidebarPromo" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
