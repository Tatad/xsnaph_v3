<template>
	<div>
		<input type="file" @change="onChange" value="Upload File" class="form-control" />
	</div>
</template>

<script type="text/javascript">
	export default{
        data(){
            return{

            }
        },
        methods:{
        	onChange(event) {
		      	this.file = event.target.files ? event.target.files[0] : null;
			      	if (this.file) {
			        	const reader = new FileReader();

			        reader.onload = (e) => {
			          /* Parse data */
			          const bstr = e.target.result;
			          const wb = XLSX.read(bstr, { type: 'binary' });
			          /* Get first worksheet */
			          const wsname = wb.SheetNames[0];
			          const ws = wb.Sheets[wsname];
			          /* Convert array of arrays */
			          const data = XLSX.utils.sheet_to_json(ws, { header: 1 });
			        }

			        reader.readAsBinaryString(this.file);
		      	}
		    },
        }
	}
</script>