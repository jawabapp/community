<template>
    <div>
        <div v-if="loading">Saving...</div>
        <div v-else>
            <select v-model="tagGroup" class="form-control" @change="changeTagGroup">
                <option value="">Please select one</option>
                <option v-for="group in groups" :value="group.id" >{{group.name[lang]}}</option>
            </select>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            tagGroups: String,
            tagId: String,
            tagGroupId: String,
            lang: String
        },
        data() {
            return {
                loading: false,
                tagGroup: this.tagGroupId
            }
        },
        methods: {
            changeTagGroup() {
                this.loading = true
                axios.post(`/admin/api/assign-tag-group`, {
                    "tag_id": this.tagId,
                    "tag_group_id": this.tagGroup,
                }).then(res => {
                    this.loading = false
                    console.log(res.data)
                }).catch(err => {
                    this.loading = false
                    console.log(err)
                });
            }
        },
        computed: {
            groups: function () {
                return JSON.parse(this.tagGroups)
            }
        }
    }
</script>
