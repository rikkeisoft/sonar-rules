<template>
    <b-navbar toggleable="md" type="dark" variant="danger">
        <b-nav-toggle target="nav-collapse"></b-nav-toggle>
        <b-link class="navbar-brand" to="#">
            <img src="img/rikkei-white.png" height="50" class="d-inline-block align-top" alt="">
        </b-link>
        <b-collapse is-nav class="collapse" id="nar-collapse">
            <b-nav is-nav-bar class="mr-auto">
                <b-nav-item v-for="(lang,code) in $root.languages" :key="code"
                            :active="code === $root.language" :href="toHref(code)">
                    {{lang}}
                </b-nav-item>
            </b-nav>
            <span class="navbar-text">Coding Rules</span>
        </b-collapse>
    </b-navbar>
</template>
<script>
    export default {
        methods: {
            toHref(code) {
                return code +'.html'
            }
        }
    }
</script>
