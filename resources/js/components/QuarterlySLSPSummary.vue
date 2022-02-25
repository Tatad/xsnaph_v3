<template>
	<div>
        <div class="row container wrapper" style="padding-bottom:12rem">
        	<form class="container">

		<h5>Date Range</h5>
        		<div class="row">
        			<div class="col-md-3">
						<datepicker 
				      :value="this.defaultDate"
				      :format="DatePickerFormat"
				      minimum-view="year"              
				      name="datepicker"
				      id="input-id"
				      input-class="form-control" v-model="form.year" required></datepicker>
				</div>
				<div class="col-md-2">
			      <select class="form-control" v-model="form.quarter" required>
			      	<option value="1">1st Quarter</option>
			      	<option value="2">2nd Quarter</option>
			      	<option value="3">3rd Quarter</option>
			      	<option value="4">4th Quarter</option>
			      </select>
			  </div>

				<div class="col-md-5">
		            <button v-if="isBusy == false" class="btn btn-primary" @click.prevent="getQuarterlySummary()">Download</button>
		            <button v-if="isBusy == true" class="btn btn-primary" disabled>Downloading....</button>
		        </div>
        		</div>
		        
    </form>
        </div>

	</div>
</template>

<script type="text/javascript">
    import moment from 'moment';
    import Datepicker from 'vuejs-datepicker';
	import {ServerTable, ClientTable, Event} from 'vue-tables-2-premium';
	import Toasted from 'vue-toasted';

    Vue.use(ClientTable, {
        
    });

    Vue.filter('formatDate', function(value) {
      if (value) {
        return moment(String(value)).format('MMMM D, YYYY')
      }
    })

    Vue.config.productionTip = false
	Vue.use(Toasted, {
	  duration: 5000
	})
	export default{
        components: {
            moment,
            Datepicker
        },
		data(){
			return{
				defaultDate: '2018-12-04',
    			DatePickerFormat: 'yyyy',
				childColumns: ['selected','contact_name','period_from','period_to','source','reference','description','tax','tax_rate','tax_rate_name','gross','net','actions'],
				editableColumns:['quantity'],
				columns: ['batch_number','created_at','actions'],
				slspRecords: [],
				options: {
			      headings: {
			      	selected: '',
			        batch_number: 'Batch Number',
			        created_at: 'Uploaded at'
			      },
			      sortable: ['gross', 'net'],
			      filterable: ['gross', 'net'],
			      purchaseRecord:{}
			    },
			    checkedRows: [],
			    form: {year: moment.now(), quarter: '1'},
			    isBusy: false
			}
		},
		mounted(){
			//this.getQuartylySLSPSummary()
			console.log(moment(moment.now()).year())
		},
		methods:{
			getQuarterlySummary(){
				this.isBusy = true;
				this.form.year = moment(this.form.year).format('Y')
				//this.form.year = moment(String(this.form.year)).format('Y')
				axios.post('/download-quarterly-slsp-summary',this.form,
                {
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/vnd.ms-excel'
                    },
                    responseType: "blob"
                }).then((response) => {
                    //window.open(response.data,'_blank');
                    console.log(response.data);
                    this.isBusy = false;
                    let blob = new Blob([response.data], { type: 'application/vnd.ms-excel' })
                    let link = document.createElement('a')
                    link.href = window.URL.createObjectURL(blob)
                    link.download = 'quarterly-summary-report'+'.xlsx'
                    link.click()
                })
			}
		}
	}
</script>

<style type="text/css">
	.wrapper{
		padding-bottom: 5rem;
	}

	.VueTables__child-row-toggler {
	  width: 16px;
	  height: 16px;
	  line-height: 16px;
	  display: block;
	  margin: auto;
	  text-align: center;
	}

	.VueTables__child-row-toggler--closed::before {
	  content: "+";
	}

	.VueTables__child-row-toggler--open::before {
	  content: "-";
	}

	[v-cloak] {
	  display:none;
	}
</style>