<?php
require_once __DIR__ . '/config.php';
$config = loadCredentials();
$client_id = $config['client_id'];
$csrf_token = $config['csrf_token'];

// Protected page: require sign-in (redirects to login.php when signed out)
$authMode = 'require';

// View configuration for the shared head/header partials
$pageTitle = 'Gmail Manager';
$headingText = 'Gmail Manager';
$headingClasses = 'from-blue-600 to-purple-600 dark:from-blue-400 dark:to-purple-400';
$navLinks = [
    ['href' => 'senders.php', 'label' => 'Senders Manager', 'class' => 'text-purple-600 dark:text-purple-400'],
    ['href' => 'all_emails.php', 'label' => 'All Emails Stats', 'class' => 'text-blue-600 dark:text-blue-400'],
];
$authBtnClass = 'bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition';
$signoutBtnClass = 'bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-bold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition';
?>
<!DOCTYPE html>
<html lang="en">
<?php include __DIR__ . '/app/views/partials/head.php'; ?>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen font-sans transition-colors duration-300">

<div id="authStatus" class="fixed top-4 right-4 px-4 py-2 rounded shadow-lg hidden z-50"></div>

<div class="container mx-auto px-4 py-8 max-w-7xl">
    <?php include __DIR__ . '/app/views/partials/header.php'; ?>


    <div id="controls" class="bg-white dark:bg-gray-800 p-4 md:p-6 rounded-xl shadow-lg mb-8 hidden transition-colors duration-300">
        <div class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-center justify-between">
            <div class="flex flex-col sm:flex-row flex-wrap gap-3 justify-center lg:justify-start">
                <button onclick="refreshEmails()" class="bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold py-2 px-4 rounded-lg transition shadow-sm text-sm md:text-base">
                    Refresh
                </button>
                <button onclick="deleteSelectedEmails()" class="bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-700 dark:text-red-400 font-semibold py-2 px-4 rounded-lg transition shadow-sm border border-red-200 dark:border-red-800 text-sm md:text-base">
                    Delete Selected
                </button>
                <button onclick="promptDeleteBySender()" class="bg-orange-50 dark:bg-orange-900/30 hover:bg-orange-100 dark:hover:bg-orange-900/50 text-orange-700 dark:text-orange-400 font-semibold py-2 px-4 rounded-lg transition shadow-sm border border-orange-200 dark:border-orange-800 text-sm md:text-base">
                    Delete All from Sender
                </button>
                <div class="relative inline-block text-left w-full sm:w-auto">
                    <select id="labelAction" onchange="handleLabelAction(this.value)" class="w-full sm:w-auto bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm text-sm md:text-base">
                        <option value="">Manage Labels...</option>
                        <option value="STARRED">Add Star</option>
                        <option value="UNREAD">Mark as Unread</option>
                        <option value="IMPORTANT">Mark as Important</option>
                        <option value="remove_STARRED">Remove Star</option>
                        <option value="remove_UNREAD">Mark as Read</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-2 w-full lg:w-auto">
                <input type="text" id="searchInput" placeholder="Search emails..." class="flex-grow lg:w-64 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm text-sm md:text-base">
                <button onclick="searchEmails()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition shadow-md text-sm md:text-base">
                    Search
                </button>
            </div>
        </div>
    </div>

    <div id="emailListContainer" class="bg-white dark:bg-gray-800 rounded-xl shadow-xl overflow-hidden hidden transition-colors duration-300">
        <!-- Desktop Table View -->
        <table class="min-w-full leading-normal hidden md:table">
            <thead>
                <tr>
                    <th class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider w-10">
                        <input type="checkbox" onchange="toggleSelectAll(this)" class="form-checkbox h-4 w-4 text-blue-600 rounded border-gray-300 dark:border-gray-600 focus:ring-blue-500">
                    </th>
                    <th class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider w-1/4">
                        Sender
                    </th>
                    <th class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                        Subject
                    </th>
                    <th class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider w-32">
                        Date
                    </th>
                </tr>
            </thead>
            <tbody id="emailsBody" class="divide-y divide-gray-200 dark:divide-gray-700">
                <!-- Emails will be inserted here -->
            </tbody>
        </table>

        <!-- Mobile Card View -->
        <div id="mobileEmailList" class="md:hidden grid grid-cols-1 divide-y divide-gray-200 dark:divide-gray-700">
            <!-- Mobile cards will be inserted here -->
        </div>

        <div class="px-5 py-5 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 flex flex-col xs:flex-row items-center xs:justify-between">
            <span id="pagingInfo" class="text-xs xs:text-sm text-gray-600 dark:text-gray-400 mb-2 xs:mb-0">
                Showing emails
            </span>
            <div class="inline-flex rounded-md shadow-sm">
                <button onclick="getPrevPage()" id="prevBtn" class="text-sm bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-medium py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-l-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors" disabled>
                    Prev
                </button>
                <button onclick="getNextPage()" id="nextBtn" class="text-sm bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-medium py-2 px-4 border-t border-b border-r border-gray-300 dark:border-gray-600 rounded-r-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors" disabled>
                    Next
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Email View Modal -->
<div id="emailModal" class="modal">
    <div class="modal-content dark:bg-gray-800 dark:text-white w-full md:w-4/5 max-w-4xl m-4 md:m-auto h-[90vh] md:h-auto flex flex-col rounded-lg shadow-2xl">
        <div class="modal-header dark:border-gray-700 p-4 border-b flex justify-between items-center">
            <h2 id="modalSubject" class="text-lg md:text-xl font-semibold text-gray-800 dark:text-white truncate pr-4"></h2>
            <span class="close text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white cursor-pointer text-2xl" onclick="closeModal()">&times;</span>
        </div>
        <div class="p-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row justify-between mb-2 gap-1">
                <span id="modalFrom" class="font-medium text-gray-700 dark:text-gray-300 text-sm md:text-base break-all"></span>
                <span id="modalDate" class="text-xs md:text-sm text-gray-500 dark:text-gray-400"></span>
            </div>
            <div id="modalLabels" class="flex flex-wrap gap-1"></div>
        </div>
        <div id="modalBody" class="modal-body prose max-w-none dark:prose-invert p-4 flex-grow overflow-y-auto"></div>
        <div class="p-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
             <button onclick="closeModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow w-full sm:w-auto">Okay</button>
        </div>
    </div>
</div>

<!-- UI layer -->
<script src="js/ui/toast.js"></script>
<script src="js/ui/modal.js"></script>
<script src="js/ui/theme.js"></script>
<!-- Core layer -->
<script src="js/core/session.js"></script>
<script src="js/core/gmailClient.js"></script>
<script src="js/core/auth.js"></script>
<!-- Page layer -->
<script src="js/pages/manager.js"></script>
<!-- Google SDKs -->
<script async defer src="https://apis.google.com/js/api.js" onload="gapiLoaded()"></script>
<script async defer src="https://accounts.google.com/gsi/client" onload="gisLoaded()"></script>
</body>
</html>
