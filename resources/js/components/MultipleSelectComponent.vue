<template>
    <div class="card">
        <div class="card-body" v-show="last_page > 1">
            <input v-model="query" @keyup="load(false)" type="text" class="form-control" :placeholder="`Search For ${label}`" />
        </div>
        <hr class="m-0 p-0" v-show="last_page > 1">
        <div class="card-body" v-show="last_page > 1">
            <div v-if="selectedItems.length" >
                <div class="custom-control custom-checkbox" v-for="item in selectedItems" :key="`check-${item.id}`">
                    <input type="checkbox" class="custom-control-input" :name="`${name}[]`" v-model="selected" :value="item.id" :id="`check-${item.id}`" @change="change(item.id, item.name, $event.target.checked)" />
                    <label class="custom-control-label" :for="`check-${item.id}`">{{item.name}}</label>
                </div>
            </div>
            <div v-else>No {{ label }}</div>
        </div>
        <hr class="m-0 p-0" v-show="last_page > 1">
        <div class="card-body">
            <div v-if="loading">Loading...</div>
            <div class="my-content" v-else >
                <div class="custom-control custom-checkbox" v-for="item in items" :key="`customCheck-${item.id}`">
                    <input type="checkbox" class="custom-control-input" v-model="selected" :value="item.id" :id="`customCheck-${item.id}`" @change="change(item.id, item.name, $event.target.checked)" />
                    <label class="custom-control-label" :for="`customCheck-${item.id}`">{{item.name}}</label>
                </div>

                <div v-if="last_page > page">
                    <button @click.prevent="load(true)" class="btn btn-primary btn-sm">More</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            apiSearch: String,
            apiSelected: String,
            label: String,
            name: String,
            preselect: String,
        },
        data() {
            return {
                loading: false,
                query: '',
                page: 1,
                items: [],
                selectedItems: [],
                last_page: 0,
                selected: JSON.parse(this.preselect)
            }
        },
        created() {
            this.load(false);
            this.loadSelected(this.selected)
        },
        methods: {
            load(more) {

                if(more) {
                    this.page++
                } else {
                    this.page = 1
                }

                this.loading = true
                axios.get(`${this.apiSearch}?query=${this.query}&page=${this.page}`).then(res => {
                    this.loading = false
                    if(more) {
                        res.data.data.forEach(item => {
                            this.items.push(item)
                        })
                    } else {
                        this.items = res.data.data
                    }
                    this.last_page = res.data.last_page;
                }).catch(err => {
                    this.loading = false
                    console.log(err)
                });
            },
            loadSelected(items) {
                axios.post(`${this.apiSelected}`,{
                    items
                }).then(res => {
                    this.selectedItems = res.data.data
                }).catch(err => {
                    console.log(err)
                });
            },
            change(id, name, checked) {
                if(checked) {
                    this.selectedItems.push({
                        'id': id,
                        'name': name
                    })
                } else {
                    this.selectedItems.splice(this.selectedItems.indexOf(this.selectedItems.find(item => (item.id === id))), 1)
                }

                console.log(this.selected)
                console.log(this.selectedItems)
            }
        }
    }
</script>

<style>
    .my-content {
        overflow-y: auto;
        max-height: 200px;
    }
</style>
