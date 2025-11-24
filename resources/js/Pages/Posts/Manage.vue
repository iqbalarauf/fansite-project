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
            <div v-if="posts && posts.data.length">
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
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="post in posts.data" :key="post.id" class="hover:bg-gray-50">
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
                                            class="text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    <button v-if="posts.prev_page_url" @click="visit(posts.prev_page_url)"
                        class="px-3 py-1 bg-gray-200 rounded">Previous</button>
                    <button v-if="posts.next_page_url" @click="visit(posts.next_page_url)"
                        class="px-3 py-1 bg-gray-200 rounded">Next</button>
                </div>
            </div>
            <div v-else class="text-gray-600">You have no posts yet.
                <Link :href="route('posts.create')" class="text-blue-600">Create one</Link>.
            </div>
        </div>
        </AppLayout>

        
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { formatDate } from '@/Helpers/formatDate';
import AppLayout from '@/Layouts/AppLayout.vue';

const title = 'Manage Blog Posts';
const props = defineProps({
    posts: Object,
    title: String,
});

const visit = (url) => Inertia.visit(url);

const destroy = (slug) => {
    if (!confirm('Delete this post?')) return;
    Inertia.delete(route('posts.destroy', slug));
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
