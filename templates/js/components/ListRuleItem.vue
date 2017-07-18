<template>
    <div class="rule-item" :id="rule.id"
         :data-tags="rule.tags.join(',')"
         :data-rank="rule.rank"
    >
        <h2 class="rule-name">{{ index }}. {{ rule.name }}</h2>
        <div class="rule-meta">
            <span class="badge badge-default rule-repo">{{ rule.repo }}</span>
            <span class="badge badge-primary rule-type">{{ rule.type }}</span>
            <span class="badge badge-success rule-status">{{ rule.status }}</span>
            <span class="badge badge-info rule-rank">{{ rule.rank }}</span>
        </div>
        <div class="clearfix rule-desc">
            <div v-html="rule.htmlDesc"></div>
        </div>
    </div><!-- /.rule-item -->
</template>

<script>
    export default {
        props: {
            index: {
                type: Number,
                required: true,
            },
            rule: {
                type: Object,
                required: true,
            }
        }
    }
</script>