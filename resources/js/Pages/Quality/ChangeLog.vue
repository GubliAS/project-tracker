<script setup>
import Changelog from './Changelog.vue';
defineProps({ title: String, changes: Array });
</script>
<template><Changelog :title="title || 'Change Log'" :changes="changes" /></template>
