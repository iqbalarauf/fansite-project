<template>

    <Head :title="title" />

        <AppLayout title="Dashboard">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Manage Blog Posts
            </h2>
                <Link :href="route('posts.create')" class="inline-block bg-blue-600 text-white px-4 py-2 rounded">Create
                Post
                </Link>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="mb-4">
                <ActionMessage :on="!!success" class="text-sm text-green-600">{{ success }}</ActionMessage>
                <div v-if="error" class="text-sm text-red-600 mt-1">{{ error }}</div>
            </div>
            <div v-if="postsList && postsList.length">
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Title
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Published</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <transition-group
                            tag="tbody"
                            class="bg-white divide-y divide-gray-200"
                            name="fade"
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 transform -translate-y-2"
                            enter-to-class="opacity-100 transform translate-y-0"
                            leave-active-class="transition ease-in duration-200"
                            leave-from-class="opacity-100 transform translate-y-0"
                            leave-to-class="opacity-0 transform -translate-y-2"
                        >
                                    <tr v-for="post in postsList" :key="post.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                    <Link :href="route('blog.show', post.slug)">{{ post.title }}</Link>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ post.status }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ post.published_at ? formatDate(post.published_at) : '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                    <Link :href="route('posts.edit', post.slug)"
                                        class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</Link>
                                    <form :action="route('posts.destroy', post.slug)" method="post"
                                        style="display:inline">
                                        <input type="hidden" name="_method" value="DELETE" />
                                        <input type="hidden" name="_token" :value="$page.props.csrf_token" />
                                        <button @click.prevent="destroy(post.slug)"
                                            :disabled="deleting == post.slug"
                                            :class="deleting == post.slug ? 'text-red-300 cursor-not-allowed' : 'text-red-600 hover:text-red-800'">
                                            <span v-if="deleting == post.slug">Deleting…</span>
                                            <span v-else>Delete</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        </transition-group>
                    </table>
                </div>

                <div class="mt-6 flex items-center space-x-2">
                    <button v-if="postsMeta.prev_page_url" @click="visit(postsMeta.prev_page_url)"
                        class="px-3 py-1 bg-gray-200 rounded">Previous</button>
                    <button v-if="postsMeta.next_page_url" @click="visit(postsMeta.next_page_url)"
                        class="px-3 py-1 bg-gray-200 rounded">Next</button>
                    <div class="text-sm text-gray-500 ms-4">Total: {{ postsMeta.total }}</div>
                </div>
            </div>
            <div v-else class="text-gray-600">You have no posts yet.
                <Link :href="route('posts.create')" class="text-blue-600">Create one</Link>.
            </div>
        </div>
        </AppLayout>

        
</template>

<script setup>
import { ref, watch } from 'vue';
import ActionMessage from '@/Components/ActionMessage.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { formatDate } from '@/Helpers/formatDate';
import AppLayout from '@/Layouts/AppLayout.vue';

const title = 'Manage Blog Posts';
const props = defineProps({
    posts: Object,
    title: String,
});

// local reactive copy of the posts data so we can update it optimistically
const postsList = ref(props.posts?.data ? [...props.posts.data] : []);
// keep pagination meta locally so we can update immediately after optimistic changes
const postsMeta = ref(props.posts ? { ...props.posts } : { prev_page_url: null, next_page_url: null, total: 0, per_page: 20, current_page: 1 });
const deleting = ref(null);
const error = ref('');

// keep local state in-sync when Inertia provides fresh props (e.g. when paging)
watch(() => props.posts, (p) => {
    postsList.value = p?.data ? [...p.data] : [];
    postsMeta.value = p ? { ...p } : postsMeta.value;
});

const visit = (url) => router.visit(url);

const success = ref('');

const destroy = (slug) => {
    if (!confirm('Delete this post?')) return;

    // find and remove the post optimistically
    const index = postsList.value.findIndex(p => p.slug === slug || p.id === slug);
    if (index === -1) return;

    const removed = postsList.value.splice(index, 1)[0];
    deleting.value = slug;

    // adjust total immediately
    postsMeta.value.total = Math.max(0, (postsMeta.value.total || 0) - 1);
    // if removing this item makes the current page the last page, clear next_page_url
    const perPage = postsMeta.value.per_page || 20;
    const totalPages = Math.max(1, Math.ceil(postsMeta.value.total / perPage));
    if ((postsMeta.value.current_page || 1) >= totalPages) {
        postsMeta.value.next_page_url = null;
    }

    // send delete to server. keep the current page (preserveState) so we
    // don't force a full page replacement — we already removed the row.
    router.delete(route('posts.destroy', slug), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            success.value = 'Post deleted.';
            deleting.value = null;

            // if there are no items left on this page but there is a previous
            // page, navigate to it so the user sees posts for the previous page
            if (postsList.value.length === 0 && postsMeta.value.prev_page_url) {
                setTimeout(() => router.visit(postsMeta.value.prev_page_url), 300);
            }
        },
        onError: () => {
            // revert optimistic removal
            postsList.value.splice(index, 0, removed);
            deleting.value = null;
            error.value = 'Failed to delete post — please try again.';
            setTimeout(() => error.value = '', 3000);
        },
    });
};

const showingNavigationDropdown = ref(false);

const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};

const logout = () => {
    router.post(route('logout'));
};
</script>
