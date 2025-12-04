<template>
    <div id="wrapper">
        <Navbar/>
        <Sidebar/>
        <div class="content-page">
            <router-view></router-view>
            <Footer/>
        </div>
    </div>
</template>
<script>
import Navbar from './Navbar'
import Sidebar from './Sidebar'
import Footer from './Footer'

export default {
    components: {
        Navbar,
        Sidebar,
        Footer
    }
}
</script>
<style>
.multiselect__tags {
  min-height: unset !important;
  border-radius: unset !important;
  padding: 4px 40px 0 8px !important;
}

.multiselect__tag {
  margin-bottom: 0 !important;
}

.multiselect__placeholder {
  margin-bottom: 5px !important;
}
</style>
