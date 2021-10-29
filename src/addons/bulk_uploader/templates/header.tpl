{literal}
<script type='text/javascript'>
		var item_state;
		function expandCollapse(item)
		{
			if(typeof item == 'undefined') item = 0;
			
			if(item)
			{
				var new_item = document.getElementById(item);
				var butext = document.getElementById('butext');
				var buimg = document.getElementById('buimg');
				if(!new_item)
				{ alert('element:' +item+ 'does not exists.' ); return false; }
				var itempos = new_item.style.position;
				var expanded = (itempos == 'absolute' || item_state==1)? 'relative':'absolute';
				if(expanded == 'relative')
				{
					item_state = 0;
					new_item.className ='collapsed';
					buimg.src='bulk_uploader/images/expand_bulk_uploader.gif';
					//butext.innerHTML = 'Expand';
				}
				else
				{
					item_state = 1;
					new_item.className ='expanded';
					buimg.src='bulk_uploader/images/collapse_bulk_uploader.gif';
					//butext.innerHTML = 'Collapse';
				}
			}
		}
</script>
		
<script type="text/javascript">
	function searchUsername() 
	{
		alert('test');
	}
	function swapBottomSelect(selectElement, selectNumericId)
	{
		bottomDropDown =("bottomSelect"+selectNumericId)
		textArea = document.getElementById("textArea"+selectNumericId)
		if(bottomDropDown.disabled){bottomDropDown.disabled=false;textArea.disabled=false;}
		if(selectElement.selectedIndex==0) {
			bottomDropDown.options[0].style.display='';
			bottomDropDown.selectedIndex=0;
			bottomDropDown.disabled=true;
			textArea.disabled=true;
			return
	}
		selectedNewIndex=false;
		if(selectElement.selectedIndex==1) {
			bottomDropDown.options[0].style.display='none';
			for(i=0;i<bottomDropDown.options.length;i++) {
				if(!selectedNewIndex&&i!=0){bottomDropDown.selectedIndex=i;selectedNewIndex=true;}
				bottomDropDown.options[i].style.display='';
			}
			return
		}
		for(i=0;i<bottomDropDown.options.length;i++) {
			if(bottomDropDown.options[i].id!=selectElement.options[selectElement.selectedIndex].value){
				bottomDropDown.options[i].style.display='none';
			}else{
				if(!selectedNewIndex){bottomDropDown.selectedIndex=i;selectedNewIndex=true;}
				bottomDropDown.options[i].style.display='';
			}
		}
	}
	function alterBottomSelect(selectElement,selectId) {
		k = 0;
		disabled = false;
		while(otherSelectElements = document.getElementById("bottomSelect"+k)) {
			if(k!=selectId) {
				if(selectElement.selectedIndex!=0) {
					if(selectElement.selectedIndex==otherSelectElements.selectedIndex){
						alert('This field has already been assigned a default value');
					}
				}
			}
	
			k++;
		}
	}
	function createDefaultFieldRow(numericId,uniqueId) {
		defaultFieldTBody = document.createElement("tbody");
		document.getElementById('defaultTable').appendChild(defaultFieldTBody);
		defaultFieldRow = document.createElement("tr");
		defaultFieldTBody.appendChild(defaultFieldRow);
		defaultFieldRow.className = 'form_row';
		defaultFieldColumn = document.createElement("td");
		defaultFieldRow.appendChild(defaultFieldColumn);
		defaultFieldColumn.className = 'defaultColumn'
		{$bulkDefaultColumn}
		defaultFieldColumn = document.createElement("td");
		defaultFieldRow.appendChild(defaultFieldColumn);
		defaultFieldTextArea = document.createElement("textarea");
		defaultFieldColumn.appendChild(defaultFieldTextArea);
		defaultFieldTextArea.name = 'bulkDefaultData[' + numericId + ']';
	}
	function createCustomTitleRow(numericId,uniqueId) {
		defaultFieldTBody = document.createElement("tbody");
		document.getElementById('customTitleTable').appendChild(defaultFieldTBody);
		defaultFieldRow = document.createElement("tr");
		defaultFieldTBody.appendChild(defaultFieldRow);
		defaultFieldRow.className = 'form_row';
		defaultFieldColumn = document.createElement("td");
		defaultFieldRow.appendChild(defaultFieldColumn);
		defaultFieldColumn.className = 'defaultColumn'
		{$bulkCustomTitle}
		defaultFieldColumn = document.createElement("td");
		defaultFieldRow.appendChild(defaultFieldColumn);
	}
	</script>
					

	<script type'text/javascript'>
	function swapBottomSelect(selectElement, selectNumericId) {
		bottomDropDown = document.getElementById("bottomSelect"+selectNumericId)
		if(bottomDropDown.disabled){bottomDropDown.disabled=false;}
		if(selectElement.selectedIndex==0) {
			bottomDropDown.options[0].style.display='';
			bottomDropDown.selectedIndex=0;
			bottomDropDown.disabled=true;
			return
		}
		selectedNewIndex=false;
		if(selectElement.selectedIndex==1) {
			bottomDropDown.options[0].style.display='none';
			for(i=0;i<bottomDropDown.options.length;i++) {
				if(!selectedNewIndex&&i!=0){bottomDropDown.selectedIndex=i;selectedNewIndex=true;}
				bottomDropDown.options[i].style.display='';
			}
			return
		}
		for(i=0;i<bottomDropDown.options.length;i++) {
			if(bottomDropDown.options[i].id!=selectElement.options[selectElement.selectedIndex].value){
				bottomDropDown.options[i].style.display='none';
			}else{
				if(!selectedNewIndex){bottomDropDown.selectedIndex=i;selectedNewIndex=true;}
				bottomDropDown.options[i].style.display='';
			}
		}
	}
	function alterBottomSelect(selectElement,selectId) {
		k = 0;
		disabled = false;
		while(otherSelectElements = document.getElementById("bottomSelect"+k)) {
			if(k!=selectId) {
				if(selectElement.selectedIndex!=0) {
					if(selectElement.selectedIndex==otherSelectElements.selectedIndex){
						alert('This field is already being used by column number: '+k);
					}
				}
			}
			k++;
		}
	}
		</script>
{/literal}