<script setup>
import { computed, ref } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
import CreateHero from '@/Components/ui/CreateHero.vue';
import CurrencyPrefix from '@/Components/ui/CurrencyPrefix.vue';
import { useCurrency } from '@/composables/useCurrency';

const props = defineProps({
    title: {
        type: String,
        default: 'Resources Management',
    },
    resources: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const showModal = ref(false);
const searchQuery = ref('');
const typeFilter = ref('all');

const form = useForm({
    name: '',
    type: 'human',
    role_or_category: '',
    cost_per_hour: 0,
    availability_status: 'available',
});

const flashMessage = computed(() => page.props.flash?.message);

const filteredResources = computed(() => {
    return props.resources.filter((resource) => {
        const haystack = `${resource.name} ${resource.role_or_category || ''}`.toLowerCase();
        const matchesSearch = haystack.includes(searchQuery.value.toLowerCase());
        const matchesType = typeFilter.value === 'all' || resource.type === typeFilter.value;

        return matchesSearch && matchesType;
    });
});

const typeClass = (type) => {
    const classes = {
        human: 'bg-primary/10 text-primary',
        hardware: 'bg-info/10 text-info',
        software: 'bg-success/10 text-success',
        material: 'bg-warning/10 text-warning',
    };

    return classes[type] || 'bg-secondary/10 text-secondary';
};

const availabilityClass = (status) => {
    const classes = {
        available: 'bg-success/10 text-success',
        allocated: 'bg-primary/10 text-primary',
        unavailable: 'bg-danger/10 text-danger',
    };

    return classes[status] || 'bg-secondary/10 text-secondary';
};

const { formatCurrency: formatMoney } = useCurrency();
const formatCurrency = (amount) => formatMoney(amount, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const nextAvailability = (status) => {
    const cycle = {
        available: 'allocated',
        allocated: 'unavailable',
        unavailable: 'available',
    };

    return cycle[status] || 'available';
};

const openModal = () => {
    form.reset();
    form.clearErrors();
    form.type = 'human';
    form.availability_status = 'available';
    form.cost_per_hour = 0;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const submitResource = () => {
    form.post('/resources', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showModal.value = false;
        },
    });
};

const toggleAvailability = (resource) => {
    router.put(`/resources/${resource.id}`, {
        availability_status: nextAvailability(resource.availability_status),
    }, { preserveScroll: true });
};

const deleteResource = (resource) => {
    if (!window.confirm(`Delete resource "${resource.name}"?`)) {
        return;
    }

    router.delete(`/resources/${resource.id}`, { preserveScroll: true });
};
</script>

<template>
    <AppLayout :title="title">
        <PageHeader :title="title" subtitle="Track people, hardware, software, and material allocations">
            <template #actions>
                <button type="button" class="ti-btn ti-btn-primary btn-wave" @click="openModal">
                    <i class="ri-add-line me-1"></i> Add Resource
                </button>
            </template>
        </PageHeader>

        <CreateHero :title="title" subtitle="Add people, hardware, software, or materials." pill="Resource" class="mb-4" />

        <div v-if="flashMessage" class="alert alert-success mb-4">
            {{ flashMessage }}
        </div>

        <div class="box">
            <div class="box-header flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            class="ti-form-control !ps-10"
                            placeholder="Search resources..."
                        >
                        <i class="ri-search-line absolute start-3 top-1/2 -translate-y-1/2 text-textmuted"></i>
                    </div>
                    <select v-model="typeFilter" class="ti-form-select w-auto">
                        <option value="all">All Types</option>
                        <option value="human">Human</option>
                        <option value="hardware">Hardware</option>
                        <option value="software">Software</option>
                        <option value="material">Material</option>
                    </select>
                </div>
            </div>

            <div class="box-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover whitespace-nowrap">
                        <thead>
                            <tr>
                                <th>Resource</th>
                                <th>Type</th>
                                <th>Role / Category</th>
                                <th>Cost / Hour</th>
                                <th>Availability</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="resource in filteredResources" :key="resource.id">
                                <td class="font-medium">{{ resource.name }}</td>
                                <td>
                                    <span class="badge" :class="typeClass(resource.type)">{{ resource.type }}</span>
                                </td>
                                <td>{{ resource.role_or_category || '—' }}</td>
                                <td>{{ formatCurrency(resource.cost_per_hour) }}</td>
                                <td>
                                    <button
                                        type="button"
                                        class="badge"
                                        :class="availabilityClass(resource.availability_status)"
                                        @click="toggleAvailability(resource)"
                                    >
                                        {{ resource.availability_status }}
                                    </button>
                                </td>
                                <td>
                                    <button
                                        type="button"
                                        class="ti-btn ti-btn-soft-danger ti-btn-icon ti-btn-sm"
                                        @click="deleteResource(resource)"
                                    >
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!filteredResources.length">
                                <td colspan="6" class="text-center text-textmuted py-6">
                                    No resources found. Add a resource to get started.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div v-if="showModal" style="position: fixed; inset: 0; z-index: 1055; background: rgba(15, 23, 42, 0.45); display: flex; align-items: center; justify-content: center; padding: 1rem;">
            <div class="box w-full max-w-xl mb-0">
                <div class="box-header flex items-center justify-between">
                    <h6 class="box-title mb-0">Allocate Resource</h6>
                    <button type="button" class="ti-btn ti-btn-sm ti-btn-light" @click="closeModal">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
                <form @submit.prevent="submitResource">
                    <div class="box-body space-y-4">
                        <div>
                            <label class="form-label">Name</label>
                            <input v-model="form.name" type="text" class="ti-form-control" required>
                            <p v-if="form.errors.name" class="text-danger text-xs mt-1">{{ form.errors.name }}</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Type</label>
                                <select v-model="form.type" class="ti-form-select" required>
                                    <option value="human">Human</option>
                                    <option value="hardware">Hardware</option>
                                    <option value="software">Software</option>
                                    <option value="material">Material</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Role / Category</label>
                                <input v-model="form.role_or_category" type="text" class="ti-form-control">
                            </div>
                            <div>
                                <label class="form-label">Cost Per Hour</label>
                                <div class="input-group">
                                    <CurrencyPrefix />
                                    <input v-model="form.cost_per_hour" type="number" min="0" step="0.01" class="ti-form-control" required>
                                </div>
                                <p v-if="form.errors.cost_per_hour" class="text-danger text-xs mt-1">{{ form.errors.cost_per_hour }}</p>
                            </div>
                            <div>
                                <label class="form-label">Availability</label>
                                <select v-model="form.availability_status" class="ti-form-select" required>
                                    <option value="available">Available</option>
                                    <option value="allocated">Allocated</option>
                                    <option value="unavailable">Unavailable</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer flex justify-end gap-2">
                        <button type="button" class="ti-btn ti-btn-light" @click="closeModal">Cancel</button>
                        <button type="submit" class="ti-btn ti-btn-primary" :disabled="form.processing">
                            Save Resource
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
