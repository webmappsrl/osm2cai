<template>
    <panel-item :field="field">
        <template #value>
        <map-multi-linestring-nova :field="field"></map-multi-linestring-nova>
        </template>
    </panel-item>
</template>

<script>
export default {
    props: ['index','resource', 'resourceName', 'resourceId', 'field'],
}
</script>