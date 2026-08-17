// ---- Add/Edit form ----

function toggle_connection_fields() {
	var type = $("#connection_type").val();
	$(".field-network, .field-windows_local, .field-rawbt").hide();
	$(".field-" + type).show();

	var help = {
		network: 'The printer must have a static IP on your WiFi/LAN and accept raw ESC/POS printing (usually port 9100). Works from any PC or phone on the network.',
		windows_local: 'The printer must be shared on this Windows PC (right click the printer in Windows &rarr; Printer properties &rarr; Sharing &rarr; Share this printer). This server will print to it directly, from any device.',
		rawbt: 'Used only from the phone the printer is physically attached to. Install the RawBT app on that phone, connect the printer inside RawBT, then click "Use on This Device" on the printers list from that phone\'s browser.'
	};
	$("#connection_type_help").text(help[type] || '');
}

$('#test_connection').on("click", function () {
	var base_url = $("#base_url").val().trim();

	if (!$("#connection_type").val()) {
		toastr["warning"]("Choose a Connection Type first!");
		return;
	}
	if ($("#connection_type").val() === 'network' && !$("#ip_address").val().trim()) {
		toastr["warning"]("Enter the Printer IP Address first!");
		return;
	}
	if ($("#connection_type").val() === 'windows_local' && !$("#windows_printer_name").val().trim()) {
		toastr["warning"]("Enter the Shared Printer Name first!");
		return;
	}

	var $btn = $(this);
	$btn.attr('disabled', true).text('Testing...');
	PrinterBridge.testConnection(base_url, '#printer-form', function (result) {
		$btn.attr('disabled', false).text('Test');
		if (result.status === 'success') {
			toastr["success"](result.message || "Test print sent! Check the printer.");
		} else {
			toastr["error"]("Test failed: " + (result.message || 'Unknown error'));
		}
	});
});

$('#save,#update').on("click", function (e) {
	var base_url = $("#base_url").val().trim();
	var flag = true;

	function check_field(id) {
		if (!$("#" + id).val().trim()) {
			$('#' + id + '_msg').fadeIn(200).show().html('Required Field').addClass('required');
			flag = false;
		} else {
			$('#' + id + '_msg').fadeOut(200).hide();
		}
	}

	check_field("name");

	if (flag == false) {
		toastr["warning"]("You have Missed Something to Fillup!");
		return;
	}

	var this_id = this.id;
	var command = (this_id == "update") ? 'update' : 'save';

	if (!confirm("Do You Wants to " + (command == 'update' ? 'Update' : 'Save') + " Record ?")) {
		return;
	}

	e.preventDefault();
	var data = new FormData($('#printer-form')[0]);
	data.append('command', command);
	if (!xss_validation(data)) { return false; }

	$(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
	$("#" + this_id).attr('disabled', true);
	$.ajax({
		type: 'POST',
		url: base_url + 'printers/save_or_update',
		data: data,
		cache: false,
		contentType: false,
		processData: false,
		success: function (result) {
			if (result == "success") {
				window.location = base_url + "printers";
				return;
			} else {
				toastr["error"](result);
			}
			$("#" + this_id).attr('disabled', false);
			$(".overlay").remove();
		},
		error: function () {
			toastr["error"]("Error! Something Went Wrong!");
			$("#" + this_id).attr('disabled', false);
			$(".overlay").remove();
		}
	});
});

// ---- List page ----

function update_status(id, status) {
	var base_url = $("#base_url").val().trim();
	$.post(base_url + "printers/update_status", { id: id, status: status }, function (result) {
		if (result == "success") {
			toastr["success"]("Status Updated Successfully!");
			var isActive = (status == 1);
			$("#span_" + id).attr('onclick', 'update_status(' + id + ',' + (isActive ? 0 : 1) + ')');
			$("#span_" + id).attr('class', isActive ? 'label label-success' : 'label label-danger');
			$("#span_" + id).html(isActive ? 'Active' : ' Inactive ');
		} else {
			toastr["error"]("Failed to Update Status.Try again!");
		}
	});
}

function delete_printer(id) {
	var base_url = $("#base_url").val().trim();
	if (!confirm("Do You Wants to Delete Record ?")) { return; }
	$(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
	$.post(base_url + "printers/delete_printer", { id: id }, function (result) {
		if (result == "success") {
			toastr["success"]("Record Deleted Successfully!");
			location.reload();
		} else {
			toastr["error"]("Failed to Delete .Try again!");
		}
		$(".overlay").remove();
	});
}

function test_print(printerId) {
	var base_url = $("#base_url").val().trim();
	$(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
	PrinterBridge.testPrint(base_url, printerId, function (result) {
		$(".overlay").remove();
		if (result.status === 'success') {
			toastr["success"]("Test print sent! Check the printer.");
		} else {
			toastr["error"]("Test print failed: " + (result.message || 'Unknown error'));
		}
	});
}

function use_on_this_device(printerId, connectionType, name) {
	if (connectionType === 'rawbt' && !confirm("This will make THIS device (browser) send print jobs to RawBT for '" + name + "'. Only do this on the phone the printer is physically connected to. Continue?")) {
		return;
	}
	PrinterBridge.setDevicePrinter({ id: printerId, connection_type: connectionType, name: name });
	toastr["success"]("'" + name + "' is now this device's printer.");
	render_this_device_printer();
}

function clear_device_printer() {
	PrinterBridge.clearDevicePrinter();
	render_this_device_printer();
}

function render_this_device_printer() {
	var el = $("#this_device_printer");
	if (el.length === 0) { return; }
	var printer = PrinterBridge.getDevicePrinter();
	el.text(printer ? printer.name : 'Not set (falls back to the print preview popup)');
}
