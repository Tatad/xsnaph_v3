<template>
	<div>
		<v-client-table :columns="columns" v-model="purchaseRecords" :options="options">

		    <div slot="child_row" slot-scope="props">
		    	<button class="btn btn-danger" @click.prevent="showDeleteModal()">Delete Multiple 2307 Record</button>
		    	<button class="btn btn-primary" @click.prevent="downloadMultiple2307()">Download Multiple 2307 Record</button>
		    	<v-client-table :columns="childColumns" v-model="props.row.data" :options="options">

		    		<!-- <div slot="quantity" slot-scope="{row, update, setEditing, isEditing, revertValue}">
				      <span @click="setEditing(true)" v-if="!isEditing()">
				        <a>{{row.quantity}}</a>
				      </span>
				      <span v-else>
				        <input type="text" v-model="row.quantity">
				        <button type="button" class="btn btn-info btn-xs" @click="update(row.quantity); setEditing(false)">Submit</button>
				       <button type="button" class="btn btn-default btn-xs" @click="revertValue(); setEditing(false)">Cancel</button>
				      
				      </span>

				    </div> -->

				    <input slot="selected" slot-scope="props" type="checkbox" :value="props.row.id" v-model="checkedRows">

				    <div slot="actions" slot-scope="props">
				    	<a :href="/download-2307/+props.row.id" target="_blank"><button class="btn btn-primary">Download</button></a>
				    	<button @click.prevent="showDeleteModal(props.row)" class="btn btn-danger">Remove</button>
				    </div>

		    	</v-client-table>
		    </div>

		    <div slot="created_at" slot-scope="props">
                {{props.row.created_at | formatDate}}
             </div>

             <div slot="actions" slot-scope="props">
             	<a :href="/download-2307/+props.row.id" target="_blank"><button class="btn btn-primary">Download</button></a>
             </div>


            <div slot="batch_number" slot-scope="props">
                {{props.row.id}}
             </div>
	  </v-client-table>

	  <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
		  <div class="modal-dialog modal-dialog-centered" role="document">
		    <div class="modal-content">
		      <div class="modal-header">
		        <h5 class="modal-title" id="exampleModalLongTitle">Delete Confirmation</h5>
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
		          <span aria-hidden="true">&times;</span>
		        </button>
		      </div>
		      <div class="modal-body">
		        Are you sure you want to delete this record, this action is irreversible.
		      </div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-secondary" data-dismiss="modal" @click.prevent="closeModal()">Close</button>
		        <button type="button" class="btn btn-primary" @click.prevent=removeRecord()>Continue</button>
		      </div>
		    </div>
		  </div>
		</div>

	</div>
</template>

<script type="text/javascript">
    import moment from 'moment';
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
        },
		data(){
			return{
				childColumns: ['selected','contact_name','period_from','period_to','source','reference','description','item_code','quantity','unit_price','gross','account','account_code','actions'],
				editableColumns:['quantity'],
				columns: ['batch_number','created_at','actions'],
				purchaseRecords: [],
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
			    checkedRows: []
			}
		},
		mounted(){
			this.getP2307Record()
		},
		methods:{
			getP2307Record(){
				axios.get('/get-2307-records').then((response) => {
					this.purchaseRecords = response.data
				})
			},
			removeRecord(){
				if(this.checkedRows.length != 0){
					axios.post('/delete-2307-records', this.checkedRows).then((response) => {	
						$('#deleteModal').modal('hide')
						this.getP2307Record()
						this.$toasted.show('2307 Record has been modified')
					})
				}else{
					axios.post('/remove-2307-record', this.purchaseRecord).then((response) => {
						$('#deleteModal').modal('hide')
						this.getP2307Record()
						this.$toasted.show('2307 Record has been modified')
					})
				}
	
			},
			showDeleteModal(record){

				this.purchaseRecord = record
				$('#deleteModal').modal('show')
			},
			closeModal(){
				$('#deleteModal').modal('hide')
			},
			downloadMultiple2307(){
				// axios.post('/download-multiple-2307', this.checkedRows).then((response) => {	
				// 	// $('#deleteModal').modal('hide')
				// 	// this.getP2307Record()
				// 	// this.$toasted.show('2307 Record has been modified')
				// })
				axios.post('/download-multiple-2307',this.checkedRows,
                {
                    responseType: "blob"
                }).then((response) => {
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