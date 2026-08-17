/**
 * Read a JSON payload out of a jQuery AJAX success callback.
 *
 * jQuery guesses the response type from Content-Type when no dataType is set.
 * Laravel's response()->json() sends application/json, so jQuery has ALREADY
 * parsed the body into an object by the time the callback runs, and calling
 * jQuery.parseJSON() on it throws '"[object Object]" is not valid JSON'. The
 * old CodeIgniter endpoints echoed json_encode() as text/html, which is why
 * the original parseJSON() calls worked. Accept both shapes.
 */
function parse_ajax_json(result) {
	if (typeof result === 'string') {
		try {
			return JSON.parse(result);
		} catch (e) {
			return null;
		}
	}
	return result;
}

/*Check XSS Code*/
function xss_validation(data) {
	if(typeof data=='object'){
		for (var value of data.values()) {
		   if(typeof value!='object' && (value.trim()!='' && value.indexOf("<script>") != -1)){
		   	toastr["error"]("Failed!! to Continue! XSS Code found as Input!");
		   	return false;
		   }
		}
		return true;
	}
	else{
		if(typeof value!='object' && (data.trim()!='' && data.indexOf("<script>") != -1)){
		   	toastr["error"]("Failed!! to Continue! XSS Code found as Input!");
		   	return false;
		}
		return true;
	}
}
//end
function calculate_inclusive(amount,tax){
	amount = parseFloat(amount);
	tax = parseFloat(tax);
 	return (amount * tax / (100+ tax)).toFixed(2);//By tally
}
function calculate_exclusive(amount,tax){
	amount = parseFloat(amount);
	tax = parseFloat(tax);
	return ((amount*tax)/parseFloat(100)).toFixed(2);
}
function app_number_format(num=0){
	return num.toLocaleString('en-US', {minimumFractionDigits: 1, maximumFractionDigits: 2});
}
function get_float_type_data(location=''){
  var res = $(location).val();
  return (isNaN(parseFloat(res))) ? parseFloat(0) : parseFloat(res);
 }