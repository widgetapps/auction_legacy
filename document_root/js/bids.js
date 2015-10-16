
jQuery(document).ready(function() {
	jQuery('#itemNo').blur(function() {
		validateItem(jQuery('#itemNo'));
	});
	
	jQuery('#submitBid').click(function() {
		submitBid();
	});
	
	setInterval('getItemList()',1000);
	getItemList();
});

// This function works
function validateItem(itemNumber) {
	if (itemNumber.val() != "" && itemNumber.val() > 0) {
		jQuery.getJSON('/bids/ajax/validateitem', { id: itemNumber.val() }, function(data) {
			if (data.code == "success") {
				jQuery('#bidAmount').val(parseInt(data.currentBid) + 5);
				jQuery('#bidAmount').select();
				jQuery('#item_name').html(data.itemName);
			} else {
				alert("Invalid item number.");
				jQuery('#itemNo').val("");
				jQuery('#itemNo').focus();
				jQuery('#item_name').html('[item name]');
			}
		});
	}
}

// This function works
function getItemList() {
	$('#itemList').load('/bids/ajax/getitemlist');
}

//TODO: this function does not work yet
function submitBid () {
	// Bid MUST be a multiple of 5.  Complain if it isn't.
	if (jQuery('#bidAmount').val() % 5 != 0) {
		alert('Bid must be a multiple of $5.00');
		jQuery('#bidAmount').focus();
		jQuery('#bidAmount').select();
		return;
	}
	
	// check the phone number
	if (jQuery('#phone1').val() == '' || jQuery('#phone2').val() == '' || jQuery('#phone3').val() == ''){
		alert('You must provide a proper phone number.');
		return;
	}
	jQuery.getJSON('/bids/ajax/submitbid', { itemNo: jQuery("#itemNo").val(), bidAmount: jQuery("#bidAmount").val(), phone1: jQuery("#phone1").val(), phone2: jQuery("#phone2").val(), phone3: jQuery("#phone3").val() }, function(data) {
		switch (data.code) {
			case "success":
				// All is well.  Return focus to the item No.
				alert('Bid accepted.');
				jQuery("#itemNo").focus();
				jQuery("#itemNo").val('');
				jQuery("#bidAmount").val('');
				jQuery('#item_name').html('[item name]');
				//jQuery("#phone1").val() = '';
				//jQuery("#phone2").val() = '';
				//jQuery("#phone3").val() = '';
				break;
			case "scammer":
				// The person is scamming us
				alert('This bidder cannot bid in this auction.');
				jQuery("#itemNo").focus();
				jQuery("#itemNo").val('');
				jQuery("#bidAmount").val('');
				jQuery("#phone1").val('');
				jQuery("#phone2").val('');
				jQuery("#phone3").val('');
				jQuery('#item_name').html('[item name]');
				break;
			case "invalidItem":
				// item number was not valid.
				alert('Invalid Item Number entered.');
				jQuery("#itemNo").focus();
				jQuery("#itemNo").select();
				break;
			case "tooLow":
				alert('Minumum bid is $' + data.currentBid + '.  Please enter a higher bid.');
				jQuery("#bidAmount").focus();
				jQuery("#bidAmount").select();
				break;
			case "bidCollision":
				alert('Current bid is $' + data.currentBid + '.  Please enter a higher bid.');
				jQuery("#bidAmount").focus();
				jQuery("#bidAmount").select();
				break;
			case "bidTooHigh":
				// Someone already made a bid equal or higher than that amount.
				alert('Bid is a lot higher than the last bid.\nPlease enter a lower bid, or speak to the admin.');
				jQuery("#bidAmount").val('');
				jQuery("#bidAmount").focus();
				jQuery("#bidAmount").select();
				break;
			case "phoneError":
				// Someone already made a bid equal or higher than that amount.
				alert('The phone number was too short, please enter a proper phone number');
				jQuery("#phone1").val('');
				jQuery("#phone2").val('');
				jQuery("#phone3").val('');
				jQuery("#phone1").focus();
				break;
			default:
				alert('No response code from server.');
		}
	});
}


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
