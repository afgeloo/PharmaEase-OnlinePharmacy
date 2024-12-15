$(document).ready(function() {
    var element = $('.floating-chat');
    var myStorage = localStorage;
    var strictProductSearch = false; // Default mode is general chat

    if (!myStorage.getItem('chatID')) {
        myStorage.setItem('chatID', createUUID());
    }

    setTimeout(function() {
        element.addClass('enter');
    }, 1000);

    element.click(openElement);

    function openElement() {
        var messages = element.find('.messages');
        var textInput = element.find('.text-box');
        element.find('>i').hide();
        element.addClass('expand');
        element.find('.chat').addClass('enter');
        var strLength = textInput.val().length * 2;
        textInput.keydown(onMetaAndEnter).prop("disabled", false).focus();
        textInput.css("font-size", "12px"); // Add this line to set the font size
        element.off('click', openElement);
        element.find('.header button').click(closeElement);
        element.find('#sendMessage').click(sendNewMessage);
        element.find('.predefined-message').click(sendPredefinedMessage);
        element.find('#toggleSearchMode').click(toggleSearchMode);
        messages.scrollTop(messages.prop("scrollHeight"));
    }

    function closeElement() {
        element.find('.chat').removeClass('enter').hide();
        element.find('>i').show();
        element.removeClass('expand');
        element.find('.header button').off('click', closeElement);
        element.find('#sendMessage').off('click', sendNewMessage);
        element.find('.predefined-message').off('click', sendPredefinedMessage);
        element.find('#toggleSearchMode').off('click', toggleSearchMode);
        element.find('.text-box').off('keydown', onMetaAndEnter).prop("disabled", true).blur();
        setTimeout(function() {
            element.find('.chat').removeClass('enter').show();
            element.click(openElement);
        }, 500);
    }

    function createUUID() {
        // http://www.ietf.org/rfc/rfc4122.txt
        var s = [];
        var hexDigits = "0123456789abcdef";
        for (var i = 0; i < 36; i++) {
            s[i] = hexDigits.substr(Math.floor(Math.random() * 0x10), 1);
        }
        s[14] = "4"; // bits 12-15 of the time_hi_and_version field to 0010
        s[19] = hexDigits.substr((s[19] & 0x3) | 0x8, 1); // bits 6-7 of the clock_seq_hi_and_reserved to 01
        s[8] = s[13] = s[18] = s[23] = "-";

        var uuid = s.join("");
        return uuid;
    }

    function sendNewMessage() {
        var userInput = $('.text-box');
        var newMessage = userInput.html().replace(/\<div\>|\<br.*?\>/ig, '\n').replace(/\<\/div\>/g, '').trim().replace(/\n/g, '<br>');

        if (!newMessage) return;

        var messagesContainer = $('.messages');

        messagesContainer.append([
            '<li class="self">',
            newMessage,
            '</li>'
        ].join(''));

        // clean out old message
        userInput.html('');
        // focus on input
        userInput.focus();

        messagesContainer.finish().animate({
            scrollTop: messagesContainer.prop("scrollHeight")
        }, 250);

        // Handle predefined replies and product search
        if (strictProductSearch) {
            searchProduct(newMessage, messagesContainer);
        } else {
            handlePredefinedReplies(newMessage);
        }
    }

    function onMetaAndEnter(event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            sendNewMessage();
        }
    }

    function sendPredefinedMessage() {
        var message = $(this).text();
        var messagesContainer = $('.messages');

        messagesContainer.append([
            '<li class="self">',
            message,
            '</li>'
        ].join(''));

        messagesContainer.finish().animate({
            scrollTop: messagesContainer.prop("scrollHeight")
        }, 250);

        handlePredefinedReplies(message);
    }

    function showThinking(container) {
        const thinkingDots = $('<li class="other thinking"><span>.</span><span>.</span><span>.</span></li>');
        container.append(thinkingDots);
        container.finish().animate({
            scrollTop: container.prop("scrollHeight")
        }, 250);
        return thinkingDots;
    }

    function typeReply(reply, container) {
        let words = reply.split(' ');
        let index = 0;
        const interval = setInterval(() => {
            if (index < words.length) {
                container.append(words[index] + ' ');
                index++;
            } else {
                clearInterval(interval);
            }
        }, 100); // Adjust typing speed here
    }

    function handlePredefinedReplies(message) {
        var messagesContainer = $('.messages');
        var reply = '';
        var lowerCaseMessage = message.toLowerCase();

        if (lowerCaseMessage.includes("what is pharmaease")) {
            reply = "PharmaEase is an online pharmacy designed to empower local pharmacies by providing a digital avenue to offer their services and products. PharmaEase ensures that individuals can access essential medications conveniently, especially during emergencies when immediate assistance may not be available. By connecting pharmacies directly with consumers, PharmaEase enhances accessibility to healthcare and supports the modernization of local pharmaceutical services.";
        } else if (lowerCaseMessage.includes("how to order")) {
            reply = "To order from PharmaEase, simply browse our product catalog, add items to your cart, and proceed to checkout. You can create an account or log in directly if you have an existing one. Fill in your delivery details, choose a payment method, and confirm your order. Your medications will be delivered to your doorstep.";
        } else if (lowerCaseMessage.includes("what products do you offer")) {
            reply = "We offer a wide range of products including Medicines, Health Supplements, Personal Care items, and Medical Supplies. Browse our categories in the navigation menu to explore our offerings.";
        } else if (/^(hello+|hi+|hey+)[!?,.]*$/.test(lowerCaseMessage)) {
            reply = "Hello! How can I assist you today?";
        } else if (lowerCaseMessage.includes("thank you") || lowerCaseMessage.includes("thanks") || lowerCaseMessage.includes("thank")) {
            reply = "You're always welcome with PharmaEase!";
        } else {
            // Check for product-related queries
            const productQuery = lowerCaseMessage.match(/(?:where can i find|where|is|available|product|find|can you check|do you have|search for|look for|find me|show me|check for)\s+(.+)/i);
            if (productQuery) {
                const productName = productQuery[1].trim();
                searchProduct(productName, messagesContainer);
                return;
            } else {
                reply = "I'm sorry, I can't determine what you said. Please try again.";
            }
        }

        if (reply) {
            const thinkingDots = showThinking(messagesContainer);
            setTimeout(() => {
                thinkingDots.remove();
                const replyContainer = $('<li class="other"></li>');
                messagesContainer.append(replyContainer);
                typeReply(reply, replyContainer);
                messagesContainer.finish().animate({
                    scrollTop: messagesContainer.prop("scrollHeight")
                }, 250);
            }, 2000); // Delay before typing starts
        }
    }

    function searchProduct(productName, container) {
        $.ajax({
            url: 'searchProduct.php',
            method: 'POST',
            data: { productName: productName },
            success: function(response) {
                const thinkingDots = showThinking(container);
                setTimeout(() => {
                    thinkingDots.remove();
                    const replyContainer = $('<li class="other"></li>');
                    container.append(replyContainer);
                    typeReply(response, replyContainer);
                    container.finish().animate({
                        scrollTop: container.prop("scrollHeight")
                    }, 250);
                }, 2000); // Delay before typing starts
            },
            error: function() {
                const thinkingDots = showThinking(container);
                setTimeout(() => {
                    thinkingDots.remove();
                    const replyContainer = $('<li class="other"></li>');
                    container.append(replyContainer);
                    typeReply("Sorry, there was an error processing your request. Please try again later.", replyContainer);
                    container.finish().animate({
                        scrollTop: container.prop("scrollHeight")
                    }, 250);
                }, 2000); // Delay before typing starts
            }
        });
    }

    function toggleSearchMode() {
        strictProductSearch = !strictProductSearch;
        const modeText = strictProductSearch ? "Product Search Mode" : "General Chat Mode";
        $('.search-mode').text(modeText);
    }

    // Ensure the close button works
    $('.floating-chat .header button').click(closeElement);
});