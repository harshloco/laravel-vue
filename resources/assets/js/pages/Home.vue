<template>
    <div class="container py-3">
        <div class="row">
            <div class="col-12 text-center">
                <div class="form">
                    <div class="form-group row">
                        <div class="col-md-5">
                            <label>Occupation 1</label>
                            <select-occupation v-model="occupation_1"></select-occupation>
                        </div>
                        <div class="col-md-5">
                            <label>Occupation 2</label>
                            <select-occupation v-model="occupation_2"></select-occupation>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-danger btn-block mt-4" @click.prevent="compare" :disabled="!occupation_1 || !occupation_2 || loading">
                                <template v-if="loading">
                                    <i class="fa fa-refresh fa-spin"></i>
                                </template>
                                <template v-else>
                                    Compare
                                </template>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <template v-if="match > 0 && !loading">
            <div class="col-12 text-center">
                <h1>{{ match }}% match</h1>
            </div>
            </template>
            <template v-else-if="match =='0' && !loading">
            <div class="col-12 text-center">
                <h1>No result found, try again later</h1>
            </div>
            </template>
            <template v-else-if="!match && !loading">
                <div class="col-12 text-center">
                    Please select two Occupations from above and click Compare
                </div>
            </template>
            <template v-else-if="loading">
                <div class="col-12 text-center">
                    Please wait...
                </div>
            </template>
        </div>
        <div class="container" v-if="result">
            <VueGoodTable
                :columns="columns"
                :rows="filteredRows"
                :pagination-options="{
                    enabled: true,
                    mode: 'records',
                    perPage: 4,
                    position: 'bottom',
                    perPageDropdown: [4, 8, 16],
                    dropdownAllowAll: false,
                    setCurrentPage: 1,
                    nextLabel: 'next',
                    prevLabel: 'prev',
                    rowsPerPageLabel: 'Rows per page',
                    ofLabel: 'of',
                    pageLabel: 'page', 
                    allLabel: 'All',
                    compactMode: true,
                    styleClass:'vgt-table striped'
                }"
            />
        </div>
    </div>
</template>

<script>
    import SelectOccupation from '../components/form-controls/SelectOccupation';
    import 'vue-good-table/dist/vue-good-table.css'
    import { VueGoodTable } from 'vue-good-table';
    export default {
        name: 'home-page',
        components: {
            SelectOccupation,
            VueGoodTable
        },
        data() {
            return {
                loading: false,
                occupation_1: null,
                occupation_2: null,
                match: null,
                result: null,
                columns: [
                    {
                        label: 'Skill',
                        field: 'skills',
                    },
                    {
                        label: 'Occupation 1 Skill Importance',
                        field: 'code1',
                    },
                    {
                        label: 'Occupation 2 Skill Importance',
                        field: 'code2',
                    },
                    {
                        label: 'Skill Description',
                        field: 'description',
                    },
                ],
            }
        },
        methods: {
            compare() {
                this.loading = true;
                this.result = null;
                this.axios.post('/api/compare', {
                    occupation_1: this.occupation_1,
                    occupation_2: this.occupation_2
                }).then((response) => {
                    this.loading = false;
                    this.match = response.data.match;
                    this.result = response.data.result.length > 0 ? response.data.result : null;
                }).catch(() => {
                    this.loading = false;
                });
            }
        },
        computed: {
            filteredRows() {
                let rows = this.result;
                let result = [];
                rows.forEach((repo) => {
                    Object.entries(repo).forEach(([key, value]) => {
                        let data = {
                            "id": "1",
                            "skills": value[0]['label'],
                            "code1": value[0]['value'],
                            "code2": value[1]['value'],
                            "description": value[0]['description']
                        }
                        result.push(data);
                    });
                });
            return result;
            },
        }
    }
</script>

<style lang="scss" scoped>
    .form-group {
        label {
            font-size: 0.8rem;
            text-align: left;
            display: block;
            margin-bottom: 0.2rem
        }
    }
</style>