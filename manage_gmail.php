<!DOCTYPE html>
<html>
<head>
    <title>Gmail API Quickstart</title>
    <meta charset="utf-8"/>
    <style>
        .email {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 10px;
        }

        .email input[type="checkbox"] {
            margin-right: 5px;
        }
              /* Modal styles */
              .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        /* Close button style */
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
<p>Gmail API Quickstart</p>

<!-- Add search functionality -->
<input type="text" id="searchInput" placeholder="Enter sender's email">
<button id="searchButton" onclick="searchEmails()">Search</button>

<!-- Add buttons for new functionalities -->
<button id="authorize_button" onclick="handleAuthClick()">Authorize</button>
<button id="get_senders_button" style="visibility: hidden;" onclick="getAllSenders()">Get Senders</button>
<button id="get_emails_button" style="visibility: hidden;" onclick="getAllEmails()">Get All Emails</button>
<button id="delete_emails_button" style="visibility: hidden;" onclick="deleteSelectedEmails()">Delete Selected Emails</button>
<button id="read_emails_button" style="visibility: hidden;" onclick="readSelectedEmails()">Read Selected Emails</button>
<button id="create_email_button" style="visibility: hidden;" onclick="createEmail()">Create Email</button>
<button id="select_all_button" style="visibility: hidden;" onclick="selectAll()">Select All</button>
<button id="next_button" style="visibility: hidden;" onclick="getNextPage()">Next</button>
<button id="signout_button" style="visibility: hidden;" onclick="handleSignoutClick()">Sign Out</button>

<div id="emails"></div>

<!-- The modal -->
<div id="myModal" class="modal">
    <!-- Modal content -->
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <div id="modalContent"></div>
    </div>
</div>

<script type="text/javascript">
    /* exported gapiLoaded */
    /* exported gisLoaded */
    /* exported handleAuthClick */
    /* exported handleSignoutClick */
    /* exported getAllEmails */
    /* exported getAllSenders */
    /* exported filterEmails */
    /* exported deleteSelectedEmails */
    /* exported readSelectedEmails */
    /* exported createEmail */
    /* exported selectAll */
    /* exported getNextPage */

    // TODO: Replace with your own client ID and API key from the Developer Console
    const CLIENT_ID = '521496165001-umur3bumfmljk64qvkta1911jp7n72co.apps.googleusercontent.com';
    const API_KEY = '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';

    const DISCOVERY_DOC = 'https://www.googleapis.com/discovery/v1/apis/gmail/v1/rest';
    const SCOPES = [
        "https://mail.google.com/",
        "https://www.googleapis.com/auth/gmail.readonly",
        "https://www.googleapis.com/auth/gmail.modify"
    ];

    let tokenClient;
    let gapiInited = false;
    let gisInited = false;
    let nextPageToken = null;

    function gapiLoaded() {
        gapi.load('client', initializeGapiClient);
    }

    async function initializeGapiClient() {
        await gapi.client.init({
            apiKey: API_KEY,
            clientId: CLIENT_ID,
            discoveryDocs: [DISCOVERY_DOC],
            scope: SCOPES.join(' ')
        });
        gapiInited = true;
        maybeEnableButtons();
    }

    function gisLoaded() {
        tokenClient = google.accounts.oauth2.initTokenClient({
            client_id: CLIENT_ID,
            scope: SCOPES.join(' '),
            callback: handleAuthCallback
        });
        gisInited = true;
        maybeEnableButtons();
    }

    function maybeEnableButtons() {
        if (gapiInited && gisInited) {
            document.getElementById('authorize_button').style.visibility = 'visible';
        }
    }

    function handleAuthClick() {
        if (gapi.auth2.getAuthInstance().isSignedIn.get()) {
            handleSignoutClick();
        } else {
            tokenClient.requestAccessToken({prompt: 'select_account'});
        }
    }

    function handleAuthCallback(response) {
        if (response.error) {
            console.error('Authorization error:', response.error);
        } else {
            gapi.auth2.getAuthInstance().signIn();
            // Show all buttons after authorization
            showButtons();
        }
    }

    function handleSignoutClick() {
        if (gapi.auth2.getAuthInstance().isSignedIn.get()) {
            gapi.auth2.getAuthInstance().signOut();
        }
        // Hide buttons and clear content
        document.getElementById('content').innerText = '';
        hideButtons();
    }

    function showButtons() {
        document.getElementById('get_senders_button').style.visibility = 'visible';
        document.getElementById('get_emails_button').style.visibility = 'visible';
        document.getElementById('delete_emails_button').style.visibility = 'visible';
        document.getElementById('read_emails_button').style.visibility = 'visible';
        document.getElementById('create_email_button').style.visibility = 'visible';
        document.getElementById('select_all_button').style.visibility = 'visible';
        document.getElementById('next_button').style.visibility = 'visible';
        document.getElementById('signout_button').style.visibility = 'visible';
    }

    function hideButtons() {
        document.getElementById('get_senders_button').style.visibility = 'hidden';
        document.getElementById('get_emails_button').style.visibility = 'hidden';
        document.getElementById('delete_emails_button').style.visibility = 'hidden';
        document.getElementById('read_emails_button').style.visibility = 'hidden';
        document.getElementById('create_email_button').style.visibility = 'hidden';
        document.getElementById('select_all_button').style.visibility = 'hidden';
        document.getElementById('next_button').style.visibility = 'hidden';
        document.getElementById('signout_button').style.visibility = 'hidden';
    }

    /**
     * Print all Labels in the authorized user's inbox. If no labels
     * are found an appropriate message is printed.
     */
    async function getAllEmails() {
        let response;
        try {
            response = await gapi.client.gmail.users.messages.list({
                'userId': 'me',
                'maxResults': 100, // Adjust based on your needs
                'pageToken': nextPageToken
            });
        } catch (err) {
            document.getElementById('content').innerText = err.message;
            return;
        }
        const emails = response.result.messages;
        nextPageToken = response.result.nextPageToken;
        if (!emails || emails.length === 0) {
            document.getElementById('content').innerText = 'No emails found.';
            return;
        }
        let output = '';
        for (let i = 0; i < emails.length; i++) {
            const emailId = emails[i].id;
            const emailContent = await getEmailContent(emailId);
            output += `<div class="email"><input type="checkbox" data-id="${emailId}"/> ${emailContent}</div>`;
        }
        document.getElementById('emails').innerHTML = output;
    }

    /**
     * Fetches the content of a specific email given its ID.
     */
    async function getEmailContent(emailId) {
        let response;
        try {
            response = await gapi.client.gmail.users.messages.get({
                'userId': 'me',
                'id': emailId
            });
        } catch (err) {
            return `Error retrieving email with ID ${emailId}: ${err.message}`;
        }
        const email = response.result;
        if (!email) {
            return `No email found with ID ${emailId}.`;
        }
        const headers = email.payload.headers;
        let subject = '';
        let from = '';
        for (let i = 0; i < headers.length; i++) {
            if (headers[i].name === 'Subject') {
                subject = headers[i].value;
            } else if (headers[i].name === 'From') {
                from = headers[i].value;
            }
        }
        console.log(headers);
        const body = email.snippet;
        return `Subject: ${subject}<br/>From: ${from}<br/>${body}`;
    }

    /**
     * Get list of all senders.
     */
    async function getAllSenders() {
    let response;
    try {
        response = await gapi.client.gmail.users.messages.list({
            'userId': 'me',
            'maxResults': 4000,
            'pageToken': nextPageToken
        });
    } catch (err) {
        document.getElementById('content').innerText = err.message;
        return;
    }
    
    const emailIds = response.result.messages.map(message => message.id); 

    if (!emailIds || emailIds.length === 0) {
        document.getElementById('content').innerText = 'No emails found.';
        return;
    }

    let sendersSet = new Set(); 

    for (let i = 0; i < emailIds.length; i++) {
        let senderEmail = await getEmailSenderEmail(emailIds[i]);
        if (senderEmail) {
            sendersSet.add(senderEmail);
        }
    }

    let output = '';
    sendersSet.forEach((email) => {
        output += `${email}<br/>`;
    });
    document.getElementById('emails').innerHTML = output;
}

async function getEmailSenderEmail(emailId) {
    let response;
    try {
        response = await gapi.client.gmail.users.messages.get({
            'userId': 'me',
            'id': emailId
        });
    } catch (err) {
        console.error(`Error retrieving email with ID ${emailId}: ${err.message}`);
        return null;
    }
    const email = response.result;
    if (!email) {
        console.error(`No email found with ID ${emailId}.`);
        return null;
    }

    const headers = email.payload.headers;
    let senderEmail = '';
    for (let i = 0; i < headers.length; i++) {
        if (headers[i].name === 'From') {
            const matches = headers[i].value.match(/<([^>]+)>/);
            if (matches && matches.length === 2) {
                senderEmail = matches[1];
                break;
            }
        }
    }
    return senderEmail;
}


    /**
     * Extract sender email from email content.
     */
    function getEmailSender(emailContent) {
        const match = emailContent.match(/From: ([^\n]+)/);
        if (match && match.length > 1) {
            return match[1];
        }
        return 'Unknown';
    }

    /**
     * Search for emails from a specific sender.
     */
    async function searchEmails() {
        const searchInput = document.getElementById('searchInput').value;
        let response;
        try {
            response = await gapi.client.gmail.users.messages.list({
                'userId': 'me',
                'q': `from:${searchInput}`,
                'maxResults': 1000, 
            });
        } catch (err) {
            document.getElementById('content').innerText = err.message;
            return;
        }
        const emails = response.result.messages;
        if (!emails || emails.length === 0) {
            document.getElementById('content').innerText = 'No emails found.';
            return;
        }
        let output = '';
        for (let i = 0; i < emails.length; i++) {
            const emailId = emails[i].id;
            const emailContent = await getEmailContent(emailId);
            output += `<div class="email"><input type="checkbox" data-id="${emailId}"/> ${emailContent}</div>`;
        }
        document.getElementById('emails').innerHTML = output;
    }

    /**
     * Delete selected emails.
     */
    function deleteSelectedEmails() {
        const selectedEmails = document.querySelectorAll('input[type="checkbox"]:checked');
        if (selectedEmails.length === 0) {
            alert('Please select emails to delete.');
            return;
        }
        const emailIds = [];
        selectedEmails.forEach((email) => {
            emailIds.push(email.getAttribute('data-id'));
        });
        emailIds.forEach(async (emailId) => {
            try {
                await gapi.client.gmail.users.messages.delete({
                    'userId': 'me',
                    'id': emailId
                });
            } catch (err) {
                console.error(`Error deleting email with ID ${emailId}: ${err.message}`);
            }
        });
        
        getAllEmails();
    }

    /**
     * Read selected emails.
     */
    function readSelectedEmails() {
        const selectedEmails = document.querySelectorAll('input[type="checkbox"]:checked');
        if (selectedEmails.length === 0) {
            alert('Please select emails to read.');
            return;
        }
        selectedEmails.forEach(async (email) => {
            const emailId = email.getAttribute('data-id');
            const emailContent = await getEmailContent(emailId);
            openModal(emailContent);
        });
    }

    /**
     * Open the modal with the email content.
     */
    function openModal(emailContent) {
        const modal = document.getElementById('myModal');
        const modalContent = document.getElementById('modalContent');
        modalContent.innerHTML = emailContent;
        modal.style.display = 'block';
    }

    /**
     * Close the modal.
     */
    function closeModal() {
        const modal = document.getElementById('myModal');
        modal.style.display = 'none';
    }

    /**
     * Create a new email.
     */
    function createEmail() {
     
        alert('Functionality to create a new email is not implemented yet.');
    }

    /**
     * Select all emails.
     */
    function selectAll() {
        const checkboxes = document.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach((checkbox) => {
            checkbox.checked = true;
        });
    }

    /**
     * Get the next page of emails.
     */
    function getNextPage() {
        if (nextPageToken) {
            getAllEmails();
        }
    }
</script>
<script async defer src="https://apis.google.com/js/api.js" onload="gapiLoaded()"></script>
<script async defer src="https://accounts.google.com/gsi/client" onload="gisLoaded()"></script>
</body>
</html>
