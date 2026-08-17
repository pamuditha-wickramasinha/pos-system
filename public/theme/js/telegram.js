function sendTelegramMessage(botToken, chatId, message) {
    // Validate required parameters
    if (!botToken || !chatId || !message) {
        return {
            success: false,
            error: 'Missing required parameters: botToken, chatId, and message are required'
        }
    }

    // Telegram Bot API endpoint
    const apiUrl = `https://api.telegram.org/bot${botToken}/sendMessage`;

    // Prepare request data
    const requestData = {
        chat_id: chatId,
        text: message,
        parse_mode: "Markdown"
    };

    $.ajax({
        type: 'POST',
        url: apiUrl,
        data: requestData,
        dataType: 'json',
        success: function (response) {
            if (response.ok) {
                return {
                    success: true,
                    message: 'Message sent successfully',
                    data: response.result
                }
            } else {
                return {
                    success: false,
                    error: response.description || 'Failed to send message'
                }
            }
        },
        error: function (xhr, status, error) {
            return {
                success: false,
                error: 'Network error: ' + error
            }
        }
    });
}
