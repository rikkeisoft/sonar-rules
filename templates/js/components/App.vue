<template>
    <div id="app">
        <navbar></navbar>
        <div class="container-fluid">
            <div class="row row-offcanvas row-offcanvas-right">
                <div class="col-md-9">
                    <!--<p class="float-right hidden-md-up sticky-top" style="top: 1rem">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="offcanvas">List</button>
                    </p>-->
                    <list-rule id="list-rules"></list-rule>

                    <p class="float-right sticky-bottom">
                        <a href="#" class="btn btn-primary btn-sm">Top</a>
                    </p>
                </div><!-- /.rule-list -->

                <div class="col-md-3 sidebar-offcanvas sidebar-offcanvas">
                    <select class="form-control mb-2" id="list-filter" v-model="$root.filter.rank">
                        <option selected value="">All ranks</option>
                        <option v-for="rank in 5" :value="'rank'+rank">Rank {{ rank }}</option>
                    </select>
                    <sidebar></sidebar>
                </div>

            </div><!-- /.row -->
        </div><!-- /.container -->
    </div>
</template>
<script>
import navbar from './Navbar.vue'
import listRule from './ListRule.vue'
import sidebar from './Sidebar.vue'
export default {
  components: {
    navbar,
    listRule,
    sidebar,
  },
}
</script>
