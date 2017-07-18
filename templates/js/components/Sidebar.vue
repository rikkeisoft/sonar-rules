<template>
    <div class="sidebar sticky-top">
        <!--<b-nav v-b-scrollspy="'#list-rules'" class="sidebar-module" type="ul" vertical>-->
        <b-nav v-b-scrollspy class="sidebar-module" type="ul" vertical>
            <b-nav-item v-for="(rule, index) in $root.rules" :key="rule.id"
                        tag="li" :href="toHref(rule)"
            >
                {{ index + 1 }}. {{ rule.name }}
            </b-nav-item>
        </b-nav>
    </div><!-- /.-sidebar -->
</template>
<script>
    export default {
        methods: {
            toHref(rule) {
                return '#' + rule.id
            },
            scrollIntoView($event) {
                const href = $event.target.getAttribute('href')
                if (href) {
                    const el = document.querySelector(href)
                    if (el) {
                        el.scrollIntoView({
                            behavior: 'smooth'
                        })
                    }
                }
            }
        }
    }
</script>