jQuery(function($){

	// 台灣鄉鎮下拉選單
	function setTwAddress(type){

		function updateValue( field ){
			$("#"+field+"_state").val($(".woocommerce-address-fields select[name=\'county\']").val());
			$("#"+field+"_city").val($(".woocommerce-address-fields select[name=\'district\']").val());
			$("#"+field+"_postcode").val($(".woocommerce-address-fields input[name=\'zipcode\']").val());
		}

    $('#' + type + '_state').hide ().parent ().attr ('data-role', 'county').attr ('data-value', $('#' + type + '_state').val ())
    $('#' + type + '_city').hide ().parent ().attr ('data-role', 'district').attr ('data-value', $('#' + type + '_city').val ())
    $('#' + type + '_postcode').hide ().parent ().attr ('data-role', 'zipcode').attr ('data-value', $('#' + type + '_postcode').val ())

    $(".woocommerce-address-fields").twzipcode();

		$("select[name=\'county\'],select[name=\'district\']").change (function () { updateValue (type) })
		$("input[name=\'zipcode\']").keyup (function () { updateValue (type) })

    return;
	}

	$(document).ready(function(){

    if ($('#shipping_state').length > 0)
  		setTwAddress('shipping');

    if ($('#billing_state').length > 0)
      setTwAddress('billing');
	})
})