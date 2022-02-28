<template>
    <div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <td>Business Name</td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
                <tr v-for="org in organizations">
                    <td>{{org.org_name}}</td>
                    <td>
                        <button v-if="org.org_id == null" type="button" class="btn btn-primary" @click="showModal(org)">
                          Register organisation
                        </button>
                        <a v-else :href="/select-organization/+org.tenant_id"><button class="btn btn-primary">Select organisation</button></a>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Modal -->
        <div class="modal fade" id="registerModal" tabindex="-1" role="dialog" aria-labelledby="registerModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{orgInfo.org_name}} Registration</h5>
              </div>
                <form method="POST" @submit.prevent="saveOrganisation">
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">Tax Identification Number</label>

                            <div class="col-md-6">
                                <input id="tin_number" type="text" class="form-control" name="tin_number" v-model="register.tinNumber" required autocomplete="tin_number" autofocus>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="branch_code" class="col-md-4 col-form-label text-md-right">Branch Code</label>

                            <div class="col-md-6">
                                <input id="branch_code" type="number" class="form-control" name="branch_code" v-model="register.branchCode" required autocomplete="branch_code">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="rdo_code" class="col-md-4 col-form-label text-md-right">RDO Code</label>

                            <div class="col-md-6">
                                <select class="form-control" name="rdo_code" v-model="register.rdoCode">
                                    <option v-for="code in rdoCodes" :value="code.code">{{code.code}}</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="classification" class="col-md-4 col-form-label text-md-right">Classification</label>

                            <div class="col-md-6">
                                <select class="form-control" required="" name="classification" id="classification" v-model="register.classification">
                                    <option value="individual">Individual</option>
                                    <option value="non-individual">Non-Individual</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row" id="first_name_container">
                            <label for="reporting_cycle" class="col-md-4 col-form-label text-md-right">Reporting Cycle</label>

                            <div class="col-md-6">
                                <select class="form-control" required="" name="reporting_cycle" id="reporting_cycle" v-model="register.reportingCycle" @change="getReportingCycleValue">
                                    <option value="fiscal">Fiscal</option>
                                    <option value="calendar">Calendar</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row" id="last_name_container">
                            <label for="fiscal_calendar_end" class="col-md-4 col-form-label text-md-right">Fiscal Month End</label>

                            <div class="col-md-6">
                                <select v-if="register.reportingCycle == 'fiscal'" class="form-control" required="" name="fiscal_calendar_end" id="fiscal_calendar_end" v-model="register.fiscalCalendar">
                                    <option value="January">January</option>
                                    <option value="February">February</option>
                                    <option value="March">March</option>
                                    <option value="April">April</option>
                                    <option value="May">May</option>
                                    <option value="June">June</option>
                                    <option value="July">July</option>
                                    <option value="August">August</option>
                                    <option value="September">September</option>
                                    <option value="October">October</option>
                                    <option value="November">November</option>
                                    <option value="December">December</option>
                                </select>
                                <input type="text" class="form-control" v-else v-model="register.fiscalCalendar" readonly>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">Company Name</label>

                            <div class="col-md-6">
                                <input id="organization_name" type="text" class="form-control" name="organization_name" required autocomplete="organization_name" autofocus v-model="register.organizationName">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">Trade Name</label>

                            <div class="col-md-6">
                                <input id="trade_name" type="text" class="form-control" name="trade_name" required autocomplete="trade_name" autofocus v-model="register.tradeName">
                            </div>
                        </div>

                        <div class="form-group row" id="first_name_container" v-if="this.register.classification == 'individual'">
                            <label for="contact_number" class="col-md-4 col-form-label text-md-right">First Name</label>

                            <div class="col-md-6">
                                <input id="first_name" type="text" class="form-control" name="first_name" v-model="register.firstName" required autocomplete="first_name">
                            </div>
                        </div>

                        <div class="form-group row" id="first_name_container" v-if="this.register.classification == 'individual'">
                            <label for="middle_name" class="col-md-4 col-form-label text-md-right">Middle Name</label>

                            <div class="col-md-6">
                                <input id="middle_name" type="text" class="form-control" name="middle_name" required autocomplete="middle_name" v-model="register.middleName">
                            </div>
                        </div>

                        <div class="form-group row" id="last_name_container" v-if="this.register.classification == 'individual'">
                            <label for="last_name" class="col-md-4 col-form-label text-md-right">Last Name</label>

                            <div class="col-md-6">
                                <input id="last_name" type="text" class="form-control" name="last_name" v-model="register.lastName" required autocomplete="last_name">
                            </div>
                        </div>

                        <div class="form-group row" id="last_name_container">
                            <label for="email" class="col-md-4 col-form-label text-md-right">Email Address</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control" name="email" v-model="register.email" required autocomplete="email">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="sub_street" class="col-md-4 col-form-label text-md-right">Sub-street</label>

                            <div class="col-md-6">
                                <input id="sub_street" type="text" class="form-control" name="sub_street" v-model="register.subStreet" required autocomplete="sub_street">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="street" class="col-md-4 col-form-label text-md-right">Street</label>

                            <div class="col-md-6">
                                <input id="street" type="text" class="form-control" name="street" v-model="register.street" required autocomplete="street">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="barangay" class="col-md-4 col-form-label text-md-right">Barangay</label>

                            <div class="col-md-6">
                                <input id="barangay" type="text" class="form-control" name="barangay" v-model="register.barangay" required autocomplete="barangay">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="city" class="col-md-4 col-form-label text-md-right">City/Municipality</label>

                            <div class="col-md-6">
                                <input id="city" type="text" class="form-control" name="city" v-model="register.city" required autocomplete="city">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="province" class="col-md-4 col-form-label text-md-right">Province</label>

                            <div class="col-md-6">
                                <input id="province" type="text" class="form-control" name="province" v-model="register.province" required autocomplete="province">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="zip_code" class="col-md-4 col-form-label text-md-right">Zip Code</label>

                            <div class="col-md-6">
                                <input id="zip_code" type="text" class="form-control" name="zip_code" v-model="register.zipCode" required autocomplete="zip_code">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" @click.prevent="closeModal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
          </div>
        </div>
    </div>
</template>

<script>
    export default {
        data(){
            return{
                organizations:[],
                rdoCodes:[],
                orgInfo: {},
                register:{
                    tinNumber:'',
                    branchCode:'',
                    rdoCode:'',
                    classification:'individual',
                    reportingCycle:'calendar',
                    fiscalCalendar:'December',
                    tradeName: '',
                    organizationName:'',
                    firstName:'',
                    middleName:'',
                    lastName:'',
                    email:'',
                    subStreet:'',
                    street:'',
                    barangay:'',
                    city:'',
                    province:'',
                    zipCode:''
                }
            }
        },
        mounted() {
            this.getRDOCodes()
            this.getOrganizations()
        },
        methods:{
            getOrganizations(){
                axios.get('/get-organizations').then((response) => {
                    this.organizations = response.data
                })
            },
            showModal(org){
                this.register.tenantId = org.tenant_id
                this.register.tradeName = org.org_name
                this.register.organizationName = org.org_name
                this.orgInfo = org
                $('#registerModal').modal('show')
            },
            closeModal(){
                $('#registerModal').modal('hide')
            },
            getRDOCodes(){
                axios.get('/get-rdo-codes').then((response) => {
                    this.rdoCodes = response.data
                })
            },
            getReportingCycleValue(){
                if(this.register.reportingCycle == 'calendar'){
                    this.register.fiscalCalendar = 'December'
                }
            },
            saveOrganisation(){
                console.log(this.register)
                this.register.orgId = this.orgInfo.id
                axios.post('/save-organization', this.register).then((response)=>{
                    $('#registerModal').modal('hide')
                    this.getOrganizations()
                })
            }
        }
    }
</script>

<style type="text/css">
    .form-group{
        padding-bottom: 10px;
    }
</style>
