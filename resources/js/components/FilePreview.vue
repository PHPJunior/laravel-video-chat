<template>
    <div class="col-md-3">
        <img :src="file.file_details.webPath" alt="" class="img-responsive" :alt="file.name">
        <a class="btn btn-success" :href="file.file_details.webPath" target="_blank" download :title="file.name">Download</a>
    </div>
</template>

<script>
    export default {
        props: ['file']
    }
</script>
