<template>
    <div class="rule-list">
        <list-rule-item v-for="(rule, index) in $root.rules" :key="rule.id"
                        :rule="rule" :index="index+1">
        </list-rule-item>
    </div>
</template>

<script>
import listRuleItem from './ListRuleItem.vue'
export default {
  components: {listRuleItem},
}
</script>
