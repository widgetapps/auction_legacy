function jumpField(theField, fieldLength, nextField){
	if (theField.value.length >= fieldLength){
		if (nextField == 2){
			document.forms[0].phone2.focus();
			document.forms[0].phone2.select();
		} else if (nextField == 3){
			document.forms[0].phone3.focus();
			document.forms[0].phone3.select();
		}
	}
}