<template>
	<div>
        <div class="row container wrapper" style="padding-bottom:2rem">
        	<form class="container">

		<h5>Date</h5>
        		<div class="row">
        			<div class="col-md-3">
						<datepicker 
				      :format="DatePickerFormat"
				      minimum-view="year"              
				      name="datepicker"
				      id="input-id"
				      input-class="form-control" v-model="form.year" required @closed="updateYear()"></datepicker>
				</div>
				<div class="col-md-2">
			      <select class="form-control" v-model="form.quarter" required @change="updateQuarter()">
			      	<option value="1">1st Quarter</option>
			      	<option value="2">2nd Quarter</option>
			      	<option value="3">3rd Quarter</option>
			      	<option value="4">4th Quarter</option>
			      </select>
			  </div>

				<div class="col-md-2">
		            <button v-if="isBusy == false" class="btn btn-primary" @click.prevent="downloadQuarterlySummary()">Download via Excel</button>
		            <button v-if="isBusy == true" class="btn btn-primary" disabled>Downloading....</button>
		        </div>

		        <div class="col-md-3">
		            <button v-if="isBusy == false" class="btn btn-danger" @click.prevent="getQuarterlySummaryViaPdf()">Download via PDF</button>
		            <button v-if="isBusy == true" class="btn btn-danger" disabled>Downloading....</button>
		        </div>
        		</div>
		        
    </form>
        </div>

        <div v-if="records">
	        <h1 class="text-center">1601 E-Q Records</h1>

	        <v-client-table :columns="columns" v-model="records" :options="options">

			    <div slot="child_row" slot-scope="props">
			    	<v-client-table :columns="childColumns" v-model="props.row.data" :options="options">
			    		<div slot="amount_of_income_payment" slot-scope="props">
			                {{props.row.quantity}}
			             </div>

			             <div slot="tax" slot-scope="props">
			                {{props.row.gross}}
			             </div>
			    	</v-client-table>
			    </div>

			    <div slot="period_cover" slot-scope="props">
	                {{props.row.period_from | formatDate}} to {{props.row.period_to | formatDate}}
	             </div>


	            <div slot="batch_number" slot-scope="props">
	                {{props.row.id}}
	             </div>
		  	</v-client-table>
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
			    form: {year: moment.now(), quarter: '1'},
				defaultDate: '2018-12-04',
    			DatePickerFormat: 'yyyy',
				records: [],
				columns: ['batch_number','period_cover','actions'],
				childColumns: ['contact_name','invoice_date','source','reference','description','amount_of_income_payment','tax','tax_rate'],
			    isBusy: false,
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
		      	filterable: ['gross', 'net'],
			}
		},
		mounted(){
			this.getQ1601Summary()
		},
		methods:{
			updateQuarter(){
				this.getQ1601Summary()
			},
			updateYear(){
				this.form.year = moment(this.form.year).format('Y')
				console.log(this.form.year)
				this.getQ1601Summary()
			},
			getQ1601Summary(){
				this.form.year = moment(this.form.year).format('Y')
				axios.post('/get-1601-summary', this.form).then((response) => {
					this.records = response.data
				})
			},
			downloadQuarterlySummary(){
				this.isBusy = true;
				this.form.year = moment(this.form.year).format('Y')
				//this.form.year = moment(String(this.form.year)).format('Y')
				axios.post('/download-quarterly-1601-summary',this.form,
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
                    link.download = '1601 E-Q'+'.xlsx'
                    link.click()
                })
			},
			getQuarterlySummaryViaPdf(){
				this.isBusy = true;
				this.form.year = moment(this.form.year).format('Y')
				// axios.post('/download-quarterly-slsp-summary-via-pdf', this.form).then((response) => {
					
				// })

				axios.post('/download-quarterly-slsp-summary-via-pdf', this.form,
                {
                    responseType: "blob"
                }).then((response) => {
                	this.isBusy = false;
                    var fileURL = window.URL.createObjectURL(new Blob([response.data]));
                   var fileLink = document.createElement('a');

                   fileLink.href = fileURL;
                   fileLink.setAttribute('download', 'file.zip');
                   document.body.appendChild(fileLink);

                   fileLink.click();
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