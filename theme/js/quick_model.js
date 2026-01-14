$('#save').on("click", function (e) {
    var base_url = $("#base_url").val().trim();
    //Initially flag set true
    var flag = true;

    function check_field(id) {

        if (!$("#" + id).val().trim()) //Also check Others????
        {

            $('#' + id + '_msg').fadeIn(200).show().html('Required Field').addClass('required');
            //$('#'+id).css({'background-color' : '#E8E2E9'});
            flag = false;
        }
        else {
            $('#' + id + '_msg').fadeOut(200).hide();
            //$('#'+id).css({'background-color' : '#FFFFFF'});    //White color
        }
    }



    //Validate Input box or selection box should not be blank or empty
    check_field("item_name");
    check_field("category_id");
    check_field("unit_id");//units of measurments
    check_field("price");
    check_field("alert_qty");

    check_field("tax_id");
    check_field("purchase_price");
    check_field("tax_type");
    //check_field("profit_margin");
    check_field("sales_price");

    if (!$.isNumeric($("#alert_qty").val())) {
        toastr["error"]("Miminum Quantity must be a number");
        $("#alert_qty").focus();
        return false;
    }

    if (flag == false) {
        toastr["warning"]("You have Missed Something to Fillup!");
        return;
    }

    var this_id = this.id;

    if (confirm("Do You Wants to Save Record ?")) {
        e.preventDefault();
        data = new FormData($('#items-form')[0]);//form name
        /*Check XSS Code*/
        if (!xss_validation(data)) { return false; }

        var $modal = $('#quick_add_item');
        $("#" + this_id).attr('disabled', true);  //Enable Save or Update button
        $.ajax({
            type: 'POST',
            url: 'items/newitems',
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            success: function (result) {
                //alert(result);return;
                if (result == "success") {
                    // Capture barcode and item_name before clearing
                    var barcode = $('#custom_barcode').val().trim();
                    var item_name = $('#item_name').val().trim();
                    var item_code = $('#item_code').val().trim();
                    var price = $('#price').val().trim();

                    get_details()
                    $('#item_search').val('')
                    $('#item_name').val('වෙනත්')
                    $('#custom_barcode').val('')
                    $('#price').val(0)
                    toastr["success"]("Record Saved Successfully!");

                    // Send Telegram notification
                    var botToken = $('#telegram_bot_token').val().trim();
                    var chatId = $('#telegram_chat_id').val().trim();

                    if (botToken && chatId) {
                        var telegramMessage = `🆕 *New Item Added Successfully!*\n\n` +
                            `📦 *Item Details*\n` +
                            `━━━━━━━━━━━━━━━━━━\n` +
                            `📝 *Item Name:* ${item_name}\n` +
                            `🔢 *Item Code:* \`${item_code}\`\n` +
                            (barcode ? `🏷️ *Barcode:* \`${barcode}\`\n` : '') +
                            `💰 *Price:* ${price}\n` +
                            `━━━━━━━━━━━━━━━━━━\n` +
                            `✅ *Status:* Successfully added to inventory`;

                        sendTelegramMessage(botToken, chatId, telegramMessage);
                    }

                    // Trigger autocomplete with barcode (priority) or item_name
                    var searchValue = '';
                    if (barcode && barcode !== '') {
                        searchValue = barcode;
                    } else if (item_name && item_name !== '') {
                        searchValue = item_name;
                    }

                    if (searchValue !== '') {
                        $('#item_search').val(searchValue);
                        $("#item_search").autocomplete('search');
                    }
                }
                else if (result == "failed") {
                    toastr["error"]("Sorry! Failed to save Record.Try again!");
                }
                else {
                    toastr["error"](result);
                }
                $("#" + this_id).attr('disabled', false);  //Enable Save or Update button

                $modal.modal('hide');
                setTimeout(function () {
                    $modal.removeClass('in');
                    $modal.css('display', 'none');
                    $('.modal-backdrop').remove();
                }, 300);
            }

        });
    }
    return;
});

$('#price').on("change", function (e) {
    let price = $('#price').val();
    $('#final_price').val(price);
    $('#sales_price').val(price);
});