<?php
require_once __DIR__ . '/../../../config/database.php';
require_admin_auth();

$page_title = "Chat Management";
$current_page = "chat";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Support - KingLang Admin</title>
    
    <link rel="icon" href="../../../public/images/main-logo-icon.png" type="">
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://kit.fontawesome.com/066bf74adc.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   
    <script>
        // Set initial sidebar state before page renders to prevent flickering
        (function() {
            // Apply state immediately
            const isCollapsed = localStorage.getItem('sidebarCollapsed');
            if (isCollapsed === 'true') {
                // Add class to html element for CSS rules to apply immediately
                document.documentElement.classList.add('sidebar-collapsed');
            } else if (isCollapsed === 'false') {
                document.documentElement.classList.add('sidebar-expanded');
            } else {
                // If no saved state, default to expanded on desktop, collapsed on mobile
                if (window.innerWidth <= 768) {
                    document.documentElement.classList.add('sidebar-collapsed');
                    localStorage.setItem('sidebarCollapsed', 'true');
                } else {
                    document.documentElement.classList.add('sidebar-expanded');
                    localStorage.setItem('sidebarCollapsed', 'false');
                }
            }
        })();
        // Bot Responses Management
        var botResponsesCache = {};

        function setBotResponsesLoading() {
            var list = document.getElementById('bot-responses-list');
            if (!list) return;
            list.innerHTML = '<div class="text-center py-4">\
                <div class="spinner-border text-primary" role="status">\
                    <span class="visually-hidden">Loading...</span>\
                </div>\
            </div>';
        }

        function fetchBotResponses() {
            setBotResponsesLoading();
            fetch('/api/admin/chat/bot-responses')
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data && data.success) {
                        renderBotResponsesList(data.responses || []);
                    } else {
                        renderBotResponsesError(data && (data.message || data.error) || 'Failed to load bot responses');
                    }
                })
                .catch(function(err) {
                    renderBotResponsesError(err && err.message ? err.message : 'Network error while loading bot responses');
                });
        }

        function renderBotResponsesError(message) {
            var list = document.getElementById('bot-responses-list');
            if (!list) return;
            list.innerHTML = '<div class="alert alert-danger">\
                <i class="fas fa-exclamation-triangle me-2"></i>' + (message || 'Error') + '\
            </div>\
            <div class="text-end">\
                <button class="btn btn-sm btn-outline-primary" onclick="fetchBotResponses()">Retry</button>\
            </div>';
        }

        function renderBotResponsesList(responses) {
            var list = document.getElementById('bot-responses-list');
            if (!list) return;

            botResponsesCache = {};
            for (var i = 0; i < responses.length; i++) {
                var r = responses[i];
                if (r && r.id != null) botResponsesCache[r.id] = r;
            }

            if (!responses || responses.length === 0) {
                list.innerHTML = '<div class="empty-state">\
                    <i class="fas fa-robot"></i>\
                    <h4>No bot responses yet</h4>\
                    <p>Create keywords and automated replies to assist users.</p>\
                    <div class="mt-3">\
                        <button class="btn btn-primary" onclick="showAddResponseForm()"><i class="fas fa-plus me-1"></i>Add Response</button>\
                    </div>\
                </div>';
                return;
            }

            var html = '' +
                '<div class="table-responsive">' +
                    '<table class="table table-sm align-middle">' +
                        '<thead>' +
                            '<tr>' +
                                '<th style="width: 20%">Keyword</th>' +
                                '<th style="width: 20%">Category</th>' +
                                '<th>Response</th>' +
                                '<th style="width: 10%" class="text-center">Active</th>' +
                                '<th style="width: 16%" class="text-end">Actions</th>' +
                            '</tr>' +
                        '</thead>' +
                        '<tbody>';

            for (var j = 0; j < responses.length; j++) {
                var item = responses[j] || {};
                var truncated = (item.response || '').length > 120 ? (item.response || '').substring(0, 120) + '…' : (item.response || '');
                html += '<tr>' +
                    '<td><code>' + escapeHtmlInline(item.keyword || '') + '</code></td>' +
                    '<td>' + (item.category ? escapeHtmlInline(item.category) : '<span class="text-muted">—</span>') + '</td>' +
                    '<td>' + escapeHtmlInline(truncated) + '</td>' +
                    '<td class="text-center">' + (String(item.is_active) === '1' || item.is_active === 1 ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>') + '</td>' +
                    '<td class="text-end">' +
                            '<button class="btn btn-sm btn-outline-primary me-2" onclick="showEditResponseForm(' + item.id + ')"><i class="fas fa-edit me-1"></i></button>' +
                        '<button class="btn btn-sm btn-outline-danger" onclick="deleteBotResponse(' + item.id + ')"><i class="fas fa-trash me-1"></i></button>' +
                    '</td>' +
                '</tr>';
            }

            html += '</tbody></table></div>' +
                '<div class="text-end">' +
                    '<button class="btn btn-primary btn-sm" onclick="showAddResponseForm()"><i class="fas fa-plus"></i> Add Response</button>' +
                '</div>';

            list.innerHTML = html;
        }

        function escapeHtmlInline(text) {
            var div = document.createElement('div');
            div.textContent = text == null ? '' : String(text);
            return div.innerHTML;
        }

        function showAddResponseForm() {
            showResponseForm({});
        }

        function showEditResponseForm(id) {
            var data = botResponsesCache[id];
            if (!data) return;
            showResponseForm(data);
        }

        function showResponseForm(data) {
            var list = document.getElementById('bot-responses-list');
            if (!list) return;
            var isEdit = !!(data && data.id != null);
            var keyword = data.keyword || '';
            var category = data.category || '';
            var response = data.response || '';
            var isActive = (String(data.is_active) === '1' || data.is_active === 1) ? 'checked' : '';

            var formHtml = '' +
                '<div class="mb-3">' +
                    '<label class="form-label">Keyword</label>' +
                    '<input type="text" class="form-control" id="brf-keyword" value="' + escapeHtmlInline(keyword) + '" placeholder="e.g., pricing, reservation, location" />' +
                '</div>' +
                '<div class="mb-3">' +
                    '<label class="form-label">Category <span class="text-muted">(optional)</span></label>' +
                    '<input type="text" class="form-control" id="brf-category" value="' + escapeHtmlInline(category) + '" placeholder="e.g., booking, payment" />' +
                '</div>' +
                '<div class="mb-3">' +
                    '<label class="form-label">Response</label>' +
                    '<textarea class="form-control" id="brf-response" rows="6" placeholder="Type the automated response...">' + escapeHtmlInline(response) + '</textarea>' +
                '</div>' +
                '<div class="form-check form-switch mb-3">' +
                    '<input class="form-check-input" type="checkbox" id="brf-active" ' + isActive + ' />' +
                    '<label class="form-check-label" for="brf-active">Active</label>' +
                '</div>' +
                '<div class="d-flex justify-content-between">' +
                    '<button class="btn btn-secondary" onclick="fetchBotResponses()"><i class="fas fa-arrow-left me-1"></i>Back</button>' +
                    '<button class="btn btn-primary" onclick="saveBotResponse(' + (isEdit ? data.id : 'null') + ')"><i class="fas fa-save me-1"></i>' + (isEdit ? 'Update' : 'Save') + '</button>' +
                '</div>';

            list.innerHTML = formHtml;
        }

        function saveBotResponse(id) {
            var keyword = document.getElementById('brf-keyword').value.trim();
            var category = document.getElementById('brf-category').value.trim();
            var response = document.getElementById('brf-response').value.trim();
            var isActive = document.getElementById('brf-active').checked ? 1 : 0;

            if (!keyword || !response) {
                if (window.Swal) {
                    Swal.fire({ icon: 'warning', title: 'Missing fields', text: 'Keyword and Response are required.' });
                } else {
                    alert('Keyword and Response are required.');
                }
                return;
            }

            setBotResponsesLoading();
            fetch('/api/admin/chat/bot-responses/save', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id || undefined, keyword: keyword, response: response, category: category, is_active: isActive })
            })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data && data.success) {
                        if (window.Swal) {
                            Swal.fire({ icon: 'success', title: 'Saved', timer: 1200, showConfirmButton: false });
                        }
                        fetchBotResponses();
                    } else {
                        if (window.Swal) {
                            Swal.fire({ icon: 'error', title: 'Save failed', text: (data && (data.message || data.error)) || 'Unknown error' });
                        } else {
                            alert('Failed to save: ' + ((data && (data.message || data.error)) || 'Unknown error'));
                        }
                        fetchBotResponses();
                    }
                })
                .catch(function(err) {
                    if (window.Swal) {
                        Swal.fire({ icon: 'error', title: 'Network error', text: err && err.message ? err.message : 'Please try again' });
                    } else {
                        alert('Network error saving response');
                    }
                    fetchBotResponses();
                });
        }

        function deleteBotResponse(id) {
            var proceed = function() {
                setBotResponsesLoading();
                fetch('/api/admin/chat/bot-responses/' + id + '/delete')
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        if (data && data.success) {
                            if (window.Swal) {
                                Swal.fire({ icon: 'success', title: 'Deleted', timer: 1000, showConfirmButton: false });
                            }
                            fetchBotResponses();
                        } else {
                            if (window.Swal) {
                                Swal.fire({ icon: 'error', title: 'Delete failed', text: (data && (data.message || data.error)) || 'Unknown error' });
                            }
                            fetchBotResponses();
                        }
                    })
                    .catch(function(err) {
                        if (window.Swal) {
                            Swal.fire({ icon: 'error', title: 'Network error', text: err && err.message ? err.message : 'Please try again' });
                        }
                        fetchBotResponses();
                    });
            };

            if (window.Swal) {
                Swal.fire({
                    title: 'Delete this response?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it',
                    cancelButtonText: 'Cancel'
                }).then(function(result) {
                    if (result.isConfirmed) proceed();
                });
            } else {
                if (confirm('Delete this response?')) proceed();
            }
        }

        // Hook up modal events
        var botResponsesModal = document.getElementById('botResponsesModal');
        if (botResponsesModal) {
            // Fetch as soon as modal begins to open (more reliable on first open)
            botResponsesModal.addEventListener('show.bs.modal', function() {
                fetchBotResponses();
            });
            // Also fetch once fully shown (safety net)
            botResponsesModal.addEventListener('shown.bs.modal', function() {
                fetchBotResponses();
            });
            botResponsesModal.addEventListener('hidden.bs.modal', function() {
                setBotResponsesLoading();
            });
        }

        // Preload data so first open has content ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() { fetchBotResponses(); });
        } else {
            fetchBotResponses();
        }
    </script>
    <style>
        :root {
            --primary-green: #198754;
            --secondary-green: #28a745;
            --light-green: #d1f7c4;
            --hover-green: #20c997;
            --success-green: #198754;
            --warning-orange: #fd7e14;
            --danger-red: #dc3545;
            --info-blue: #0dcaf0;
            --light-gray: #f8f9fa;
            --border-gray: #dee2e6;
            --text-muted: #6c757d;
        }

        /* Sidebar styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            background: #fff;
            color: black;
            box-shadow: 5px 0 15px rgba(25, 188, 63, 0.32);
            transition: width 0.3s;
            z-index: 1000;
            display: flex;
            border-radius: 0 10px 10px 0;
            flex-direction: column;
            overflow-x: hidden; /* Prevent horizontal scroll */
            width: 250px; /* Default expanded state */
        }

        /* Collapsed state applied directly through HTML class */
        html.sidebar-collapsed .sidebar {
            width: 4.5rem;
        }
        
        html.sidebar-collapsed .content {
            margin-left: 4.5rem;
        }
        
        html.sidebar-collapsed .sidebar .menu-text {
            opacity: 0;
        }
        
        html.sidebar-collapsed .toggle-btn {
            left: 0.75rem;
            opacity: 0;
        }

        /* Apply expanded class by default if html has sidebar-expanded class */
        html.sidebar-expanded .sidebar {
            width: 250px;
        }
        
        html.sidebar-expanded .content {
            margin-left: 250px;
        }
        
        html.sidebar-expanded .sidebar .menu-text {
            opacity: 1;
        }
        
        html.sidebar-expanded .toggle-btn {
            left: 200px;
            opacity: 1;
        }

        .sidebar.collapsed {
            width: 4.5rem;
        }

        .sidebar.expanded {
            width: 250px;
        }

        .sidebar-header {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            min-height: 65px;
            position: relative; /* For absolute positioning of children */
            min-width: 250px; /* Match expanded width */
        }

        .sidebar-header img {
            position: absolute;
            left: 1rem;
        }

        .brand-text {
            margin: 0;
            position: absolute;
            left: 3rem;
            opacity: 1;
            transition: opacity 0.3s;
        }

        .toggle-btn {
            background: transparent;
            border: none;
            color: black;
            cursor: pointer;
            padding: 0.5rem;
            position: absolute;
            left: 200px; /* Position from left */
            transition: all 0.3s;
        }

        .sidebar.collapsed .toggle-btn {
            left: 0.75rem; /* Center when collapsed */
            opacity: 0;
        }

        .toggle-btn:hover {
            color: rgba(0, 0, 0, 0.8);
        }

        .sidebar-link {
            color: rgba(0, 0, 0, 0.8);
            text-decoration: none;
            padding: 0.8rem 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.2s;
            min-width: 250px; /* Match expanded width */
        }

        .sidebar-link .icon {
            min-width: 2rem;
            text-align: center;
        }

        .sidebar-link:hover {
            color: black;
            background: #d1f7c4;
        }

        .sidebar-link.active {
            color: black;
            background: #d1f7c4;
        }   

        .sidebar-link i {
            font-size: 1.25rem;
            min-width: 2rem;
            text-align: center;
        }

        .menu-text {
            opacity: 1;
            transition: opacity 0.3s;
        }

        .sidebar.collapsed .menu-text {
            opacity: 0;
        }

        .sidebar-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar-menu {
            flex: 1;
        }

        .content {
            margin-left: 250px;
            transition: margin-left 0.3s;
        }

        .content.collapsed {
            margin-left: 4.5rem;
        }

        @media (min-width: 1400px) {
            .container-fluid {
                max-width: 98%;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 4.5rem;
            }
            .content {
                margin-left: 4.5rem;
            }
            .menu-text {
                opacity: 0;
            }   
            .toggle-btn {
                left: 0.75rem;
            }
        }
    

        body {
            background-color: var(--light-gray);
        }

        .admin-chat-container { 
            height: calc(90vh - 200px); 
            min-height: 500px; 
        }

        .conversation-list { 
            max-height: 420px; /* reduced height with internal scroll */
            overflow-y: auto; 
            border-right: 1px solid var(--border-gray); 
        }

        .conversation-item { 
            padding: 15px; 
            border-bottom: 1px solid var(--border-gray); 
            cursor: pointer; 
            transition: all 0.2s ease; 
        }

        .conversation-item:hover { 
            background-color: var(--light-gray); 
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .conversation-item.active { 
            background-color: rgba(25, 135, 84, 0.1); 
            border-left: 4px solid var(--primary-green); 
        }

        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 15px; 
            margin-bottom: 30px; 
        }

        .stat-item { 
            background: white; 
            padding: 20px; 
            border-radius: 10px; 
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            text-align: center; 
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid var(--border-gray);
        }

        .stat-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        }

        .stat-number { 
            font-size: 2rem; 
            font-weight: bold; 
            color: var(--primary-green); 
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .empty-state { 
            text-align: center; 
            padding: 60px 20px; 
            color: var(--text-muted); 
        }

        .empty-state i { 
            font-size: 4rem; 
            margin-bottom: 20px; 
            opacity: 0.3; 
            color: var(--primary-green);
        }

        .empty-state h4 {
            color: var(--text-muted);
            font-weight: 500;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        /* Card styling */
        .card {
            border: 1px solid var(--border-gray);
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            background: white;
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid var(--border-gray);
            border-radius: 10px 10px 0 0 !important;
            padding: 1rem 1.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Tab styling */
        .nav-tabs .nav-link {
            border: none;
            color: var(--text-muted);
            font-weight: 500;
            padding: 0.5rem 0.75rem; /* reduced height */
            font-size: 0.95rem;
            border-radius: 0;
            transition: all 0.2s ease;
        }

        /* Make tab header scrollable if it overflows horizontally */
        .card-header-tabs {
            overflow-x: auto;
            overflow-y: hidden;
            white-space: nowrap;
            scrollbar-width: thin;
        }
        .card-header-tabs::-webkit-scrollbar {
            height: 6px;
        }
        .card-header-tabs::-webkit-scrollbar-thumb {
            background-color: rgba(0,0,0,0.2);
            border-radius: 6px;
        }

        .nav-tabs .nav-link:hover {
            color: var(--primary-green);
            background-color: rgba(25, 135, 84, 0.05);
        }

        .nav-tabs .nav-link.active {
            color: var(--primary-green);
            background-color: white;
            border-bottom: 3px solid var(--primary-green);
        }

        /* Badge styling */
        .badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.35em 0.65em;
        }

        .badge.bg-warning {
            background-color: var(--warning-orange) !important;
        }

        .badge.bg-success {
            background-color: var(--success-green) !important;
        }

        /* Button styling */
        .btn-primary {
            background-color: var(--primary-green);
            border-color: var(--primary-green);
        }

        .btn-primary:hover {
            background-color: var(--hover-green);
            border-color: var(--hover-green);
        }

        .btn-outline-primary {
            color: var(--primary-green);
            border-color: var(--primary-green);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-green);
            border-color: var(--primary-green);
        }

        /* Conversation cards */
        .conversation-card {
            background: white;
            border: 1px solid var(--border-gray);
            border-radius: 8px;
            margin-bottom: 10px;
            transition: all 0.2s ease;
        }

        .conversation-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }

        .conversation-card h6 {
            color: #333;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .conversation-card p {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .conversation-card small {
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        /* Chat area styling */
        .chat-area {
            background: white;
            border-radius: 10px;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .chat-header {
            background: var(--primary-green);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 10px 10px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
        }

        .message {
            margin-bottom: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            max-width: 80%;
        }

        .message.client-message {
            background-color: var(--light-gray);
            margin-left: auto;
            text-align: right;
        }

        .message.admin-message {
            background-color: rgba(25, 135, 84, 0.1);
            border: 1px solid rgba(25, 135, 84, 0.2);
            max-width: 100%;
        }

        .message.bot-message {
            background-color: rgba(13, 202, 240, 0.1);
            border: 1px solid rgba(13, 202, 240, 0.2);
        }

        .message.system-message {
            background-color: rgba(108, 117, 125, 0.1);
            border: 1px solid rgba(108, 117, 125, 0.2);
            text-align: center;
            max-width: 100%;
            font-style: italic;
        }

        .message-input-area {
            padding: 1rem;
            border-top: 1px solid var(--border-gray);
            background: white;
            border-radius: 0 0 10px 10px;
        }

        .input-group input {
            border: 1px solid var(--border-gray);
            border-radius: 20px 0 0 20px;
        }

        .input-group input:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
        }

        .send-button {
            background-color: var(--primary-green);
            border-color: var(--primary-green);
            border-radius: 0 20px 20px 0;
        }

        .send-button:hover {
            background-color: var(--hover-green);
            border-color: var(--hover-green);
        }

        /* Status indicators */
        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }

        .status-indicator.online {
            background-color: var(--success-green);
        }

        .status-indicator.offline {
            background-color: var(--text-muted);
        }

        /* Modal styling */
        .modal-content {
            border: 1px solid var(--border-gray);
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .modal-header {
            background-color: var(--primary-green);
            color: white;
            border-radius: 10px 10px 0 0;
            border-bottom: 1px solid var(--border-gray);
        }

        .modal-header .btn-close {
            filter: invert(1);
        }

        .modal-title {
            font-weight: 600;
        }

        /* Spinner styling */
        .spinner-border.text-primary {
            color: var(--primary-green) !important;
        }

        /* Modal scrollbar styling */
        .modal-body::-webkit-scrollbar {
            width: 8px;
        }
        
        .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        .modal-body::-webkit-scrollbar-thumb {
            background: var(--primary-green);
            border-radius: 4px;
        }
        
        .modal-body::-webkit-scrollbar-thumb:hover {
            background: var(--hover-green);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .stat-number {
                font-size: 1.5rem;
            }
            
            /* Mobile modal adjustments */
            #botResponsesModal .modal-dialog {
                max-width: 95% !important;
                margin: 10px auto;
            }
            
            #botResponsesModal .modal-body {
                max-height: 60vh !important;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <!-- Sidebar Header -->
        <div class="sidebar-header border-bottom border-secondary">
            <img src="../../../../public/images/main-logo.png" alt="logo" height="35px">
            <h5 class="ms-3 brand-text menu-text">KingLang</h5>
            <button class="toggle-btn" id="toggleBtn">
                <i class="bi bi-chevron-left fs-4"></i>
            </button>
        </div>

        <div class="sidebar-content">
            <!-- Sidebar Menu -->
            <div class="sidebar-menu pb-2 ">
                <a href="/admin/dashboard" class="sidebar-link">
                    <i class="bi bi-grid"></i>
                    <span class="menu-text">Dashboard</span>
                </a>   
                <a href="/admin/booking-requests" class="sidebar-link">
                    <i class="bi bi-journals fs-5"></i>
                    <span class="menu-text">Bookings</span>
                </a>
                <?php if ($_SESSION['role'] == 'Super Admin'): ?>
                    <a href="/admin/users" class="sidebar-link">
                        <i class="bi bi-people"></i>
                        <span class="menu-text">Users</span>
                        
                    </a>
                    <a href="/admin/audit-trail" class="sidebar-link">
                        <i class="bi bi-clock-history"></i>
                        <span class="menu-text">Audit Trail</span>
                    </a>
                <?php endif; ?>
                
                <a href="/admin/payment-management" class="sidebar-link">
                    <i class="bi bi-wallet2"></i>
                    <span class="menu-text">Payments</span>
                </a>
                <a href="/admin/reports" class="sidebar-link">
                    <i class="bi bi-clipboard-data"></i>
                    <span class="menu-text">Reports</span>  
                </a>
                <a href="/admin/testimonials" class="sidebar-link">
                    <i class="bi bi-star"></i>
                    <span class="menu-text">Testimonials</span>  
                </a>
                <a href="/admin/slideshow" class="sidebar-link <?= $currentPage == 'slideshow' ? 'active' : ''; ?>">
                    <i class="bi bi-images"></i>
                    <span class="menu-text">Slideshow</span>  
                </a>
                <a href="/admin/bus-management" class="sidebar-link">
                    <i class="bi bi-bus-front"></i>
                    <span class="menu-text">Buses</span>  
                </a>
                <a href="/admin/driver-management" class="sidebar-link">
                    <i class="bi bi-person-badge"></i>
                    <span class="menu-text">Drivers</span>
                </a>
                <a href="/admin/settings" class="sidebar-link">
                    <i class="bi bi-gear"></i>
                    <span class="menu-text">Settings</span>
                </a>
                <a href="/admin/chat" class="sidebar-link active">
                    <i class="bi bi-chat"></i>
                    <span class="menu-text">Chats</span>
                </a>

            </div>

            <!-- Sidebar Footer -->
            <div class="border-top border-secondary">
                <a href="/admin/logout" class="sidebar-link">
                    <i class="bi bi-box-arrow-left"></i>
                    <span class="menu-text">Logout</span>
                </a>
            </div>
        </div>
    </div>  
        
    <div class="content collapsed " id="content">
        <div class="container-fluid py-3 px-3 px-xl-4">
            <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap p-0 m-0 mb-2">
                <div class="p-0">
                    <h3><i class="bi bi-chat-fill me-2 text-success"></i>Chat Management</h3>
                    <p class="text-muted mb-0">Manage customer conversations and bot responses</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" onclick="refreshData()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#botResponsesModal">
                        <i class="fas fa-robot"></i> Bot Responses
                    </button>
                </div>
            </div>
            <hr>
            
            <!-- Statistics -->
            <div class="stats-grid" id="stats-grid">
                <div class="stat-item">
                    <div class="stat-number" id="stat-today">0</div>
                    <div class="stat-label">Conversations Today</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" id="stat-active">0</div>
                    <div class="stat-label">Active Conversations</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" id="stat-pending">0</div>
                    <div class="stat-label">Pending Conversations</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" id="stat-messages">0</div>
                    <div class="stat-label">Messages Today</div>
                </div>
            </div>
            
            <!-- Chat Interface -->
            <div class="row admin-chat-container">
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" id="conversationTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                                        Pending <span class="badge bg-warning ms-1" id="pending-count">0</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="active-tab" data-bs-toggle="tab" data-bs-target="#active" type="button" role="tab">
                                        Active <span class="badge bg-success ms-1" id="active-count">0</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="ended-tab" data-bs-toggle="tab" data-bs-target="#ended" type="button" role="tab">
                                        Ended
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body p-0">
                            <div class="tab-content h-100" id="conversationTabContent">
                                <div class="tab-pane fade show active h-100" id="pending" role="tabpanel">
                                    <div class="conversation-list" id="pending-conversations">
                                        <div class="empty-state">
                                            <i class="fas fa-clock"></i>
                                            <h4>No Pending Conversations</h4>
                                            <p>All conversations are currently being handled</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade h-100" id="active" role="tabpanel">
                                    <div class="conversation-list" id="active-conversations">
                                        <div class="empty-state">
                                            <i class="fas fa-comments"></i>
                                            <h4>No Active Conversations</h4>
                                            <p>No conversations are currently active</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade h-100" id="ended" role="tabpanel">
                                    <div class="conversation-list" id="ended-conversations">
                                        <div class="empty-state">
                                            <i class="fas fa-history"></i>
                                            <h4>No Ended Conversations</h4>
                                            <p>No conversations have been ended yet</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="card h-100">
                        <div class="chat-area" id="chat-area">
                            <div class="empty-state">
                                <i class="fas fa-comment-dots"></i>
                                <h4>Select a Conversation</h4>
                                <p>Choose a conversation from the list to start chatting with customers</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bot Responses Modal -->
    <div class="modal fade" id="botResponsesModal" tabindex="-1">
        <div class="modal-dialog modal-xl" style="max-width: 900px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-robot me-2"></i>Bot Responses Management</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6>Manage automated responses</h6>
                        <button class="btn btn-primary btn-sm" onclick="showAddResponseForm()">
                            <i class="fas fa-plus"></i> Add Response
                        </button>
                    </div>
                    <div id="bot-responses-list">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../../../public/js/assets/sidebar.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    console.log('Loading admin chat script...');
    
    // Inline JavaScript to bypass file serving issues
    console.log("Admin chat system starting...");
    

    // Handle window resize
    window.addEventListener('resize', checkWidth);

        var AdminChatManager = {
            config: {
                apiBase: '/api/admin/chat',
                refreshInterval: 3000
            },
            
            state: {
                currentConversationId: null,
                conversations: [],
                messages: [],
                conversationStatus: null,
                isLoading: false,
                endedBy: null, // 'admin' | 'client' | null
                currentConversationClientName: null
            },
            
            elements: {},
            
            init: function() {
                console.log("AdminChatManager initializing...");
                this.cacheElements();
                this.bindEvents();
                this.loadDashboard();
                this.startPolling();
                console.log("AdminChatManager initialized successfully");
            },
            
            cacheElements: function() {
                this.elements = {
                    pendingTab: document.getElementById('pending-tab'),
                    activeTab: document.getElementById('active-tab'),
                    endedTab: document.getElementById('ended-tab'),
                    pendingConversations: document.getElementById('pending-conversations'),
                    activeConversations: document.getElementById('active-conversations'),
                    endedConversations: document.getElementById('ended-conversations'),
                    chatArea: document.getElementById('chat-area')
                };
                
                console.log("Core elements cached successfully");
                
                // Only check for elements that should exist
                var requiredElements = ['pendingTab', 'activeTab', 'endedTab', 'pendingConversations', 'activeConversations', 'endedConversations', 'chatArea'];
                var missingElements = [];
                
                for (var i = 0; i < requiredElements.length; i++) {
                    var key = requiredElements[i];
                    if (!this.elements[key]) {
                        missingElements.push(key);
                    }
                }
                
                if (missingElements.length > 0) {
                    console.warn("Missing required elements:", missingElements);
                } else {
                    console.log("✠All required elements found");
                }
            },
            
            bindEvents: function() {
                var self = this;
                
                if (this.elements.pendingTab) {
                    this.elements.pendingTab.addEventListener('click', function() {
                        self.loadConversations('pending');
                        self.loadStats(); // Refresh stats when switching tabs
                    });
                }
                
                if (this.elements.activeTab) {
                    this.elements.activeTab.addEventListener('click', function() {
                        self.loadConversations('active');
                        self.loadStats(); // Refresh stats when switching tabs
                    });
                }
                
                if (this.elements.endedTab) {
                    this.elements.endedTab.addEventListener('click', function() {
                        self.loadConversations('ended');
                        self.loadStats(); // Refresh stats when switching tabs
                    });
                }
                
                if (this.elements.sendButton) {
                    this.elements.sendButton.addEventListener('click', function() {
                        self.sendMessage();
                    });
                }
                
                if (this.elements.messageInput) {
                    this.elements.messageInput.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter' && !e.shiftKey) {
                            e.preventDefault();
                            self.sendMessage();
                        }
                    });
                }
                
                // End conversation button is created dynamically, so we'll handle it with onclick attribute
            },
            
            loadDashboard: function() {
                console.log("Loading dashboard...");
                this.loadConversations('pending');
                this.loadStats();
                
                // Remove test element mutations in production
            },
            
            loadConversations: function(type) {
                var self = this;
                console.log("Loading " + type + " conversations...");
                
                fetch(this.config.apiBase + '/' + type)
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        console.log("API Response for " + type + ":", data);
                        if (data.success) {
                            self.state.conversations = data.conversations || [];
                            console.log("Setting conversations:", self.state.conversations);
                            self.displayConversations(type);
                            
                            // Update stats immediately with current data
                            self.updateStatsFromCurrentData();
                        } else {
                            console.error("Failed to load conversations:", data.message || data.error);
                        }
                    })
                    .catch(function(error) {
                        console.error("Error loading conversations:", error);
                    });
            },
            
            displayConversations: function(type) {
                console.log("displayConversations called with type:", type);
                
                // Get the correct container based on type
                var container;
                switch (type) {
                    case 'pending':
                        container = this.elements.pendingConversations;
                        break;
                    case 'active':
                        container = this.elements.activeConversations;
                        break;
                    case 'ended':
                        container = this.elements.endedConversations;
                        break;
                    default:
                        console.error("Unknown conversation type:", type);
                        return;
                }
                
                console.log("Container element:", container);
                console.log("conversations to display:", this.state.conversations);
                
                if (!container) {
                    console.error(type + " conversations container not found!");
                    return;
                }
                
                var html = '';
                if (this.state.conversations.length === 0) {
                    html = '<div class="empty-state"><i class="fas fa-clock"></i><p>No ' + type + ' conversations</p></div>';
                    console.log("No conversations, showing empty message");
                } else {
                    console.log("Creating HTML for", this.state.conversations.length, "conversations");
                    for (var i = 0; i < this.state.conversations.length; i++) {
                        var conv = this.state.conversations[i];
                        html += this.createConversationCard(conv, type);
                    }
                }
                
                console.log("Setting innerHTML to:", html.substring(0, 200) + "...");
                container.innerHTML = html;
                this.bindConversationEvents();
                
                // Update tab badge count
                this.updateTabBadge(type, this.state.conversations.length);
            },
            
            createConversationCard: function(conv, type) {
                var statusBadge = this.getStatusBadge(conv.status);
                var timeAgo = this.formatTimeAgo(conv.updated_at);
                var actionButton = this.getActionButton(conv, type);
                var lastMessage = conv.last_message ? (conv.last_message.length > 50 ? conv.last_message.substring(0, 50) + '...' : conv.last_message) : 'No messages yet';
                var adminInfo = conv.admin_name ? '<small class="text-success"><i class="fas fa-user-tie me-1"></i>' + conv.admin_name + '</small>' : '';
                
                return '<div class="conversation-card p-3" data-id="' + conv.id + '">' +
                       '<div class="d-flex justify-content-between align-items-start mb-2">' +
                       '<div class="flex-grow-1">' +
                       '<h6 class="mb-1"><i class="fas fa-hashtag me-1 text-muted"></i>Conversation #' + conv.id + '</h6>' +
                       '<p class="mb-1"><i class="fas fa-user me-1 text-muted"></i>' + (conv.client_name || 'Unknown Client') + '</p>' +
                       adminInfo +
                       '</div>' +
                       '<div class="text-end">' +
                       statusBadge +
                       '</div>' +
                       '</div>' +
                       '<div class="mb-2">' +
                       '<small class="text-muted"><i class="fas fa-comment me-1"></i>' + lastMessage + '</small>' +
                       '</div>' +
                       '<div class="d-flex justify-content-between align-items-center">' +
                       '<small class="text-muted"><i class="fas fa-clock me-1"></i>' + timeAgo + '</small>' +
                       actionButton +
                       '</div>' +
                       '</div>';
            },
            
            getStatusBadge: function(status) {
                var badgeClass = 'badge ';
                var text = status;
                
                switch (status) {
                    case 'bot':
                        badgeClass += 'bg-info';
                        text = 'Bot';
                        break;
                    case 'human_requested':
                        badgeClass += 'bg-warning';
                        text = 'Human Requested';
                        break;
                    case 'human_assigned':
                        badgeClass += 'bg-success';
                        text = 'Human Assigned';
                        break;
                    case 'ended':
                        badgeClass += 'bg-secondary';
                        text = 'Ended';
                        break;
                }
                
                return '<span class="' + badgeClass + '">' + text + '</span><br>';
            },
            
            getActionButton: function(conv, type) {
                if (type === 'pending') {
                    return '<button class="btn btn-sm btn-primary assign-btn" data-id="' + conv.id + '"><i class="fas fa-user-plus me-1"></i>Assign to Me</button>';
                } else if (type === 'active') {
                    return '<button class="btn btn-sm btn-outline-primary view-btn" data-id="' + conv.id + '"><i class="fas fa-comments me-1"></i>View Chat</button>';
                } else {
                    return '<button class="btn btn-sm btn-outline-secondary view-btn" data-id="' + conv.id + '"><i class="fas fa-history me-1"></i>View History</button>';
                }
            },
            
            formatTimeAgo: function(timestamp) {
                var now = new Date();
                var time = new Date(timestamp);
                var diff = Math.floor((now - time) / 1000);
                
                if (diff < 60) return diff + 's ago';
                if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
                if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
                return Math.floor(diff / 86400) + 'd ago';
            },
            
            bindConversationEvents: function() {
                var self = this;
                
                console.log("Binding conversation events...");
                
                var assignBtns = document.querySelectorAll('.assign-btn');
                console.log("Found " + assignBtns.length + " assign buttons");
                for (var i = 0; i < assignBtns.length; i++) {
                    assignBtns[i].addEventListener('click', function() {
                        console.log("Assign button clicked for conversation:", this.getAttribute('data-id'));
                        self.assignConversation(this.getAttribute('data-id'));
                    });
                }
                
                var viewBtns = document.querySelectorAll('.view-btn');
                console.log("Found " + viewBtns.length + " view buttons");
                for (var i = 0; i < viewBtns.length; i++) {
                    viewBtns[i].addEventListener('click', function() {
                        console.log("View button clicked for conversation:", this.getAttribute('data-id'));
                        self.openConversation(this.getAttribute('data-id'));
                    });
                }
            },
            
            assignConversation: function(conversationId) {
                var self = this;
                console.log("Assigning conversation " + conversationId);
                
                fetch(this.config.apiBase + '/assign', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        conversation_id: conversationId
                    })
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.success) {
                        self.openConversation(conversationId);
                        self.loadConversations('pending');
                    } else {
                        alert("Failed to assign conversation: " + (data.message || data.error));
                    }
                })
                .catch(function(error) {
                    console.error("Error assigning conversation:", error);
                    alert("Error assigning conversation");
                });
            },
            
            openConversation: function(conversationId) {
                console.log("=𙔠Opening conversation " + conversationId);
                this.state.currentConversationId = conversationId;
                
                // Get initial conversation status from the conversation list
                var conversation = this.state.conversations.find(function(conv) {
                    return conv.id == conversationId;
                });
                
                // Initialize conversation status from list data
                this.state.conversationStatus = conversation ? conversation.status : null;
                // Cache client name for display purposes
                this.state.currentConversationClientName = conversation && conversation.client_name ? conversation.client_name : null;
                
                console.log("Conversation from list:", conversation);
                console.log("Initial conversation status:", this.state.conversationStatus);
                
                // If status is not found, determine based on active tab
                if (this.state.conversationStatus === null || this.state.conversationStatus === undefined) {
                    var activeTab = document.querySelector('.nav-link.active');
                    if (activeTab && activeTab.textContent.includes('Ended')) {
                        this.state.conversationStatus = 'ended';
                        console.log("Setting status to 'ended' based on active tab");
                    } else {
                        // For active/pending tabs, assume it's an active conversation
                        this.state.conversationStatus = 'human_assigned';
                        console.log("Setting status to 'human_assigned' based on active tab");
                    }
                }
                
                // Load and display the actual messages
                this.loadMessages(conversationId);
                
                // Check conversation status immediately to get the latest status
                this.checkConversationStatus(conversationId);
                
                // Start tracking conversation status (matching client-side behavior)
                this.trackConversationStatus(conversationId);
                
                // Mark conversation as viewed by admin
                this.markConversationViewed(conversationId);
            },
            
            markConversationViewed: function(conversationId) {
                // Mark conversation as viewed by admin (similar to client-side)
                fetch(this.config.apiBase + '/view/' + conversationId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.success) {
                        console.log("=񅠠Conversation marked as viewed by admin");
                    }
                })
                .catch(function(error) {
                    console.debug("Mark viewed error:", error.message);
                });
            },
            
            loadMessages: function(conversationId, silentRefresh) {
                var self = this;
                if (!silentRefresh) {
                    console.log("Loading messages for conversation " + conversationId);
                }
                
                // Store current scroll position for silent refresh
                var messagesContainer = document.querySelector('.messages-container');
                var wasScrolledToBottom = false;
                var scrollTop = 0;
                
                if (messagesContainer && silentRefresh) {
                    scrollTop = messagesContainer.scrollTop;
                    var scrollHeight = messagesContainer.scrollHeight;
                    var clientHeight = messagesContainer.clientHeight;
                    wasScrolledToBottom = (scrollTop + clientHeight >= scrollHeight - 10);
                }
                
                fetch(this.config.apiBase + '/messages/' + conversationId)
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        if (!silentRefresh) {
                            console.log("Messages API response:", data);
                        }
                        
                        if (data.success) {
                            var newMessages = data.messages || [];
                            var hasNewMessages = false;
                            
                            // Check if there are new messages
                            if (self.state.messages.length !== newMessages.length) {
                                hasNewMessages = true;
                                if (silentRefresh) {
                                    console.log("=𕄠New message received! Updating conversation " + conversationId);
                                }
                            }
                            
                            self.state.messages = newMessages;
                            
                            if (!silentRefresh) {
                                console.log("Loaded " + self.state.messages.length + " messages");
                            }
                            
                            self.displayMessages(conversationId, silentRefresh, wasScrolledToBottom, scrollTop);
                            
                            // Show notification for new messages during silent refresh
                            if (silentRefresh && hasNewMessages) {
                                self.showNewMessageNotification();
                            }
                        } else {
                            if (!silentRefresh) {
                                console.error("Failed to load messages:", data.message || data.error);
                                self.showErrorMessage("Failed to load conversation messages");
                            }
                        }
                    })
                    .catch(function(error) {
                        if (!silentRefresh) {
                            console.error("Error loading messages:", error);
                            self.showErrorMessage("Error loading conversation messages");
                        }
                    });
            },
            
            displayMessages: function(conversationId, silentRefresh, wasScrolledToBottom, originalScrollTop) {
                if (!silentRefresh) {
                    console.log("Displaying messages for conversation " + conversationId);
                }
                
                console.log("Current conversation status:", this.state.conversationStatus);
                console.log("Should show end button:", this.state.conversationStatus !== 'ended');
                
                if (!this.elements.chatArea) {
                    console.error("Chat area not found");
                    return;
                }
                
                // Create chat interface with system theme
                var html = '<style>@keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }</style>' +
                           '<div class="admin-chat-interface" style="height: 100%; display: flex; flex-direction: column; background: white; border-radius: 10px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);">' +
                           
                           // Chat Header with system theme
                           '<div class="chat-header" style="background: var(--primary-green); color: white; padding: 16px 20px; border-radius: 10px 10px 0 0; display: flex; justify-content: space-between; align-items: center;">' +
                           '<div class="d-flex align-items-center">' +
                           '<i class="fas fa-headset me-2"></i>' +
                           '<span style="font-weight: 600; font-size: 16px;">Admin Support - Conversation #' + conversationId + '</span>' +
                           '<span class="live-indicator ms-2" id="live-status-' + conversationId + '" style="background: var(--success-green); padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 500;">' +
                           '<i class="fas fa-circle" style="font-size: 6px; margin-right: 4px; animation: pulse 2s infinite;"></i>LIVE • ADMIN' +
                           '</span>' +
                           '<span class="connection-status ms-2" style="font-size: 11px; opacity: 0.8;">' +
                           '<i class="fas fa-wifi" style="font-size: 10px; margin-right: 2px;"></i>Connected' +
                           '</span>' +
                           '</div>' +
                           '<div class="d-flex align-items-center">' +
                           (this.state.conversationStatus !== 'ended' ? 
                           '<button class="btn btn-outline-light btn-sm me-2" onclick="AdminChatManager.endConversation()" style="border-radius: 6px; padding: 6px 12px; font-size: 12px; border: 1px solid rgba(255,255,255,0.3);">' +
                           '<i class="fas fa-times-circle me-1"></i>End Chat' +
                           '</button>' : '') +
                           '<button class="btn btn-sm text-white" onclick="AdminChatManager.closeChat()" style="background: none; border: none; font-size: 18px; opacity: 0.8; padding: 4px 8px;">' +
                           '<i class="fas fa-times"></i>' +
                           '</button>' +
                           '</div>' +
                           '</div>' +
                           
                           // Messages Container with system theme
                           '<div class="messages-container" style="flex: 1; padding: 20px; overflow-y: auto; background: var(--light-gray); max-height: 400px;">';
                
                if (this.state.messages.length === 0) {
                    html += '<div class="text-center text-muted" style="padding: 40px 20px;">No messages in this conversation</div>';
                } else {
                    for (var i = 0; i < this.state.messages.length; i++) {
                        var msg = this.state.messages[i];
                        html += this.createMessageHTML(msg);
                    }
                }
                
                html += '</div>';
                
                // Only show input area if conversation is not ended
                if (this.state.conversationStatus !== 'ended') {
                    html += // Input Area with system theme
                        '<div class="chat-input-area" style="padding: 16px 20px; border-top: 1px solid var(--border-gray); background: white; border-radius: 0 0 10px 10px;">' +
                        '<div class="input-group">' +
                        '<input type="text" class="form-control" id="admin-message-input" placeholder="Type your message..." style="border-radius: 20px 0 0 20px; border: 1px solid var(--border-gray); padding: 12px 16px;" onkeypress="if(event.key===\'Enter\') AdminChatManager.sendAdminMessage()">' +
                        '<button class="btn btn-primary" onclick="AdminChatManager.sendAdminMessage()" style="border-radius: 0 20px 20px 0; background: var(--primary-green); border: 1px solid var(--primary-green); padding: 12px 20px;">' +
                        '<i class="fas fa-paper-plane"></i>' +
                        '</button>' +
                        '</div>' +
                        '</div>';
                } else {
                    // Show ended conversation message instead of input
                    var endedBy = this.state.endedBy || 'client';
                    var clientName = this.state.currentConversationClientName || 'Client';
                    var endedMsg = endedBy !== 'client' ? `This conversation has been ended by you.` : ('This conversation has been ended by ' + this.escapeHtml(clientName) + '.');
                    html += '<div class="chat-ended-message" style="padding: 16px 20px; border-top: 1px solid var(--border-gray); background: #f8f9fa; border-radius: 0 0 10px 10px; text-align: center;">' +
                        '<div class="text-muted">' +
                        '<i class="fas fa-lock me-2"></i>' +
                        endedMsg + ' No further messages can be sent.' +
                        '</div>' +
                        '</div>';
                }
                
                html += '</div>';
                
                this.elements.chatArea.innerHTML = html;
                
                // Handle scroll position for real-time updates
                if (silentRefresh && !wasScrolledToBottom) {
                    // Preserve scroll position if user was reading older messages
                    var messagesContainer = document.querySelector('.messages-container');
                    if (messagesContainer) {
                        messagesContainer.scrollTop = originalScrollTop;
                    }
                } else {
                    // Auto-scroll to bottom for new conversations or when user was at bottom
                    this.scrollToBottom();
                }
            },
            
            createMessageHTML: function(msg) {
                var senderLabel = this.getSenderLabel(msg.sender_type);
                var time = new Date(msg.sent_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                var messageContent = this.escapeHtml(msg.message);
                
                // Style messages like the client-side chat widget
                switch (msg.sender_type) {
                    case 'admin':
                        // Admin messages - right aligned, green background
                        return '<div class="message message-admin mb-3 d-flex justify-content-end">' +
                               '<div class="message-bubble" style="background: var(--primary-green); color: white; padding: 12px 16px; border-radius: 18px 18px 4px 18px; max-width: 100%; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">' +
                               '<div class="message-content" style="font-size: 14px; line-height: 1.4;">' + messageContent + '</div>' +
                               '<div class="message-time text-end mt-1" style="font-size: 11px; opacity: 0.8;">You • ' + time + '</div>' +
                               '</div>' +
                               '</div>';
                               
                    case 'client':
                        // Client messages - left aligned, light background
                        return '<div class="message message-client mb-3 d-flex justify-content-start">' +
                               '<div class="message-bubble" style="background: white; color: #333; padding: 12px 16px; border-radius: 18px 18px 18px 4px; max-width: 70%; box-shadow: 0 1px 2px rgba(0,0,0,0.1); border: 1px solid var(--border-gray);">' +
                               '<div class="message-content" style="font-size: 14px; line-height: 1.4;">' + messageContent + '</div>' +
                               '<div class="message-time mt-1" style="font-size: 11px; opacity: 0.7;">Client • ' + time + '</div>' +
                               '</div>' +
                               '</div>';
                               
                    case 'bot':
                        // Bot messages - left aligned, bot styling
                        return '<div class="message message-bot mb-3 d-flex justify-content-start">' +
                               '<div class="d-flex align-items-start">' +
                               '<div class="bot-avatar me-2" style="width: 32px; height: 32px; background: var(--info-blue); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">' +
                               '<i class="fas fa-robot" style="color: white; font-size: 14px;"></i>' +
                               '</div>' +
                               '<div class="message-bubble" style="background: white; color: #333; padding: 12px 16px; border-radius: 18px 18px 18px 4px; max-width: 60%; box-shadow: 0 1px 2px rgba(0,0,0,0.1); border: 1px solid var(--border-gray);">' +
                               '<div class="message-sender" style="font-size: 12px; font-weight: 600; color: var(--info-blue); margin-bottom: 4px;">KingLang Assistant</div>' +
                               '<div class="message-content" style="font-size: 14px; line-height: 1.4;">' + messageContent + '</div>' +
                               '<div class="message-time mt-1" style="font-size: 11px; opacity: 0.7;">' + time + '</div>' +
                               '</div>' +
                               '</div>' +
                               '</div>';
                               
                    case 'system':
                        // System messages - centered, italic
                        return '<div class="message message-system mb-3 d-flex justify-content-center">' +
                               '<div class="message-bubble" style="background: rgba(108, 117, 125, 0.1); color: var(--text-muted); padding: 8px 12px; border-radius: 12px; font-size: 13px; font-style: italic; text-align: center; max-width: 80%; border: 1px solid rgba(108, 117, 125, 0.2);">' +
                               messageContent + ' • ' + time +
                               '</div>' +
                               '</div>';
                               
                    default:
                        return '<div class="message mb-3">' + messageContent + '</div>';
                }
            },
            
            getSenderLabel: function(senderType) {
                switch (senderType) {
                    case 'client': return 'Client';
                    case 'admin': return 'You';
                    case 'bot': return 'Bot';
                    case 'system': return 'System';
                    default: return 'Unknown';
                }
            },
            
            escapeHtml: function(text) {
                var div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            },
            
            showChatContainer: function() {
                if (this.elements.chatArea) {
                    // For now, just show a message that chat is selected
                    this.elements.chatArea.innerHTML = '<div class="p-4"><h4>Chat Interface</h4><p>Chat functionality will be implemented here.</p></div>';
                }
            },
            
            showChatInterface: function(conversationId) {
                console.log("Showing chat interface for conversation " + conversationId);
                
                if (this.elements.chatArea) {
                    // Create a more detailed chat interface
                    var html = '<div class="p-4">' +
                               '<div class="d-flex justify-content-between align-items-center mb-3">' +
                               '<h4>Conversation #' + conversationId + '</h4>' +
                               '<button class="btn btn-sm btn-outline-danger" onclick="AdminChatManager.closeChat()">Close</button>' +
                               '</div>' +
                               '<div class="alert alert-info">' +
                               '<i class="fas fa-info-circle me-2"></i>' +
                               'Chat interface opened successfully! Full messaging functionality will be implemented here.' +
                               '</div>' +
                               '<div class="border rounded p-3 bg-light">' +
                               '<h6>Conversation Details:</h6>' +
                               '<p><strong>ID:</strong> ' + conversationId + '</p>' +
                               '<p><strong>Status:</strong> Ready for messaging</p>' +
                               '<p><strong>Action:</strong> View Chat button is working correctly!</p>' +
                               '</div>' +
                               '</div>';
                    
                    this.elements.chatArea.innerHTML = html;
                    console.log("✠Chat interface displayed successfully");
                }
            },
            
            closeChat: function() {
                this.hideChatContainer();
                // Stop real-time updates when closing chat
                this.stopPolling();
            },
            
            showNewMessageNotification: function() {
                // Show a subtle notification that new messages arrived
                var chatHeader = document.querySelector('.chat-header');
                if (chatHeader) {
                    // Flash the header briefly to indicate new message
                    chatHeader.style.backgroundColor = 'var(--success-green)';
                    setTimeout(function() {
                        chatHeader.style.backgroundColor = 'var(--primary-green)';
                    }, 300);
                }
                
                // Optional: Play a subtle sound (commented out to avoid annoyance)
                // this.playNotificationSound();
            },
            
            playNotificationSound: function() {
                // Enhanced notification sound matching client-side behavior
                try {
                    var audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    var oscillator = audioContext.createOscillator();
                    var gainNode = audioContext.createGain();
                    
                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);
                    
                    // Pleasant notification tone (like messaging apps)
                    oscillator.frequency.setValueAtTime(880, audioContext.currentTime); // A5 note
                    oscillator.frequency.setValueAtTime(660, audioContext.currentTime + 0.1); // E5 note
                    
                    gainNode.gain.setValueAtTime(0.15, audioContext.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
                    
                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.3);
                    
                    console.log("=𒤠Notification sound played");
                } catch (e) {
                    console.debug("Audio notification not available:", e.message);
                }
            },
            
            // Add conversation status tracking (matching client-side)
            trackConversationStatus: function(conversationId) {
                var self = this;
                
                // Check conversation status periodically
                setInterval(function() {
                    if (self.state.currentConversationId === conversationId) {
                        self.checkConversationStatus(conversationId);
                    }
                }, 3000); // Every 3 seconds
            },
            
            checkConversationStatus: function(conversationId) {
                var self = this;
                
                fetch(this.config.apiBase + '/../chat/status/' + conversationId)
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        if (data.success && data.status) {
                            self.handleConversationStatusChange(data.status);
                        }
                    })
                    .catch(function(error) {
                        console.debug("Status check error:", error.message);
                    });
            },
            
            handleConversationStatusChange: function(status) {
                // Store the conversation status in state
                this.state.conversationStatus = status.status;
                if (status.ended_by) {
                    this.state.endedBy = status.ended_by; // expect 'admin' or 'client'
                }
                if (status.client_name) {
                    this.state.currentConversationClientName = status.client_name;
                }
                
                // Handle conversation status changes (matching client-side Socket.io events)
                switch (status.status) {
                    case 'human_assigned':
                        console.log("=𪀍‽𿀠Admin assigned to conversation");
                        this.showSystemNotification({message: "You are now handling this conversation"});
                        break;
                        
                    case 'ended':
                        console.log("=𖤠Conversation ended");
                        this.handleConversationEnded(status);
                        break;
                        
                    case 'human_requested':
                        console.log("=𢸍⁂☏︠Human assistance requested");
                        break;
                }
                
                // Refresh the chat interface to update the end button visibility
                if (this.state.currentConversationId) {
                    this.displayMessages(this.state.currentConversationId, true);
                }
            },
            
            handleConversationEnded: function(status) {
                // Handle conversation ending (matching client-side behavior)
                this.showSystemNotification({
                    message: "This conversation has been ended. Thank you for your assistance!"
                });
                
                // Auto-close chat after 3 seconds (matching client-side)
                setTimeout(() => {
                    this.closeChat();
                }, 3000);
            },
            
            scrollToBottom: function() {
                var messagesContainer = document.querySelector('.messages-container');
                if (messagesContainer) {
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }
            },
            
            showErrorMessage: function(message) {
                if (this.elements.chatArea) {
                    var html = '<div class="p-4 text-center">' +
                               '<div class="alert alert-danger">' +
                               '<i class="fas fa-exclamation-triangle me-2"></i>' +
                               message +
                               '</div>' +
                               '<button class="btn btn-secondary" onclick="AdminChatManager.hideChatContainer()">Back to Conversations</button>' +
                               '</div>';
                    this.elements.chatArea.innerHTML = html;
                }
            },
            
            sendMessage: function() {
                // Legacy function - redirects to new function
                this.sendAdminMessage();
            },
            
            sendAdminMessage: function() {
                var messageInput = document.getElementById('admin-message-input');
                if (!messageInput || !this.state.currentConversationId) {
                    console.warn("Cannot send message - missing input or conversation ID");
                    return;
                }
                
                var message = messageInput.value.trim();
                if (!message) {
                    console.warn("Cannot send empty message");
                    return;
                }
                
                console.log("Sending admin message:", message);
                var self = this;
                
                fetch(this.config.apiBase + '/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        conversation_id: this.state.currentConversationId,
                        message: message
                    })
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.success) {
                        messageInput.value = '';
                        // Immediately reload messages to show the new message
                        self.loadMessages(self.state.currentConversationId);
                        console.log("✠Message sent successfully");
                        
                        // Focus back on input for continued conversation
                        setTimeout(function() {
                            var newInput = document.getElementById('admin-message-input');
                            if (newInput) {
                                newInput.focus();
                            }
                        }, 100);
                    } else {
                        alert("Failed to send message: " + (data.message || data.error));
                    }
                })
                .catch(function(error) {
                    console.error("Error sending message:", error);
                    alert("Error sending message");
                });
            },
            
            endConversation: function() {
                if (!this.state.currentConversationId) return;
                
                // if (!confirm('Are you sure you want to end this conversation?')) return;
                
                // Prompt for reason
                // var reason = prompt('Please provide a reason for ending this conversation (optional):');
                // if (reason === null) return; // User cancelled
                
                var self = this;
                
                fetch(this.config.apiBase + '/end', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        conversation_id: this.state.currentConversationId,
                        reason: 'Admin ended the conversation'
                    })
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.success) {
                        // Update conversation status to ended
                        self.state.conversationStatus = 'ended';
                        self.state.endedBy = 'admin';
                        
                        // alert("Conversation ended successfully");
                        self.hideChatContainer();
                        // Refresh active list and global stats so counts update immediately
                        self.loadConversations('active');
                        self.loadStats();
                    } else {
                        alert("Failed to end conversation: " + (data.message || data.error));
                    }
                })
                .catch(function(error) {
                    console.error("Error ending conversation:", error);
                    alert("Error ending conversation");
                });
            },
            
            hideChatContainer: function() {
                if (this.elements.chatArea) {
                    this.elements.chatArea.innerHTML = '<div class="empty-state"><i class="fas fa-comment-dots"></i><h4>Select a conversation</h4><p>Choose a conversation from the list to start chatting with customers</p></div>';
                }
                this.state.currentConversationId = null;
                this.state.conversationStatus = null;
                this.state.endedBy = null;
                this.state.currentConversationClientName = null;
            },
            
            startPolling: function() {
                // Real-time polling matching client-side Socket.io behavior
                var self = this;
                
                // Main conversation polling (matches client-side message checking)
                this.messagePollingInterval = setInterval(function() {
                    if (self.state.currentConversationId && self.isActivelyViewing()) {
                        self.checkForNewMessages();
                    }
                }, 1500); // Fast polling for real-time feel (1.5 seconds)
                
                // Conversation list polling (less frequent)
                this.listPollingInterval = setInterval(function() {
                    if (!self.state.currentConversationId) {
                        self.refreshConversationLists();
                    }
                }, 5000); // Every 5 seconds when not in active chat
                
                console.log("=𝄠Real-time polling started - matching client-side behavior");
            },
            
            stopPolling: function() {
                if (this.messagePollingInterval) {
                    clearInterval(this.messagePollingInterval);
                    this.messagePollingInterval = null;
                }
                if (this.listPollingInterval) {
                    clearInterval(this.listPollingInterval);
                    this.listPollingInterval = null;
                }
                console.log("=𝄠Real-time polling stopped");
            },
            
            isActivelyViewing: function() {
                // Check if the chat interface is currently visible
                var chatInterface = document.querySelector('.admin-chat-interface');
                return chatInterface && chatInterface.offsetParent !== null;
            },
            
            checkForNewMessages: function() {
                // This matches the client-side checkForNewMessages function
                var self = this;
                
                if (!this.state.currentConversationId) return;
                
                fetch(this.config.apiBase + '/messages/' + this.state.currentConversationId)
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            var newMessages = data.messages || [];
                            var currentMessageCount = self.state.messages.length;
                            
                            // Check if there are new messages (matching client-side logic)
                            if (newMessages.length > currentMessageCount) {
                                console.log("=𻀠New messages detected: " + (newMessages.length - currentMessageCount) + " new message(s)");
                                
                                // Get only the new messages
                                var newMessagesOnly = newMessages.slice(currentMessageCount);
                                
                                // Add new messages to state
                                self.state.messages = newMessages;
                                
                                // Display new messages (matching client-side behavior)
                                self.displayNewMessages(newMessagesOnly);
                                
                                // Show notification for new messages
                                self.handleNewMessageNotification(newMessagesOnly);
                                
                                // Auto-scroll to bottom (matching client-side)
                                self.scrollToBottom();
                            }
                        }
                    })
                    .catch(function(error) {
                        // Silent fail for polling errors
                        console.debug("Polling error (normal):", error.message);
                    });
            },
            
            displayNewMessages: function(newMessages) {
                // Append new messages to existing chat without full refresh
                var messagesContainer = document.querySelector('.messages-container');
                if (!messagesContainer) return;
                
                for (var i = 0; i < newMessages.length; i++) {
                    var messageHtml = this.createMessageHTML(newMessages[i]);
                    messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
                }
            },
            
            handleNewMessageNotification: function(newMessages) {
                // Handle notifications for new messages (matching client-side)
                for (var i = 0; i < newMessages.length; i++) {
                    var message = newMessages[i];
                    
                    // Show different notifications based on sender type
                    if (message.sender_type === 'client') {
                        this.showClientMessageNotification(message);
                    } else if (message.sender_type === 'bot') {
                        this.showBotMessageNotification(message);
                    } else if (message.sender_type === 'system') {
                        this.showSystemNotification(message);
                    }
                }
            },
            
            showClientMessageNotification: function(message) {
                // Flash header for client messages
                this.flashHeader('#ff9800', 'Client message received');
                
                // Play notification sound
                this.playNotificationSound();
                
                // Update browser title if tab is not active
                if (document.hidden) {
                    this.updateTitleWithNotification('New message from client');
                }
            },
            
            showBotMessageNotification: function(message) {
                // Subtle notification for bot messages
                this.flashHeader('#2196f3', 'Bot response');
            },
            
            showSystemNotification: function(message) {
                // System message notification
                this.flashHeader('#4caf50', 'System update');
                console.log("=񈠠System message:", message.message);
            },
            
            flashHeader: function(color, message) {
                var chatHeader = document.querySelector('.chat-header');
                if (chatHeader) {
                    var originalColor = chatHeader.style.backgroundColor || '#4285f4';
                    chatHeader.style.backgroundColor = color;
                    
                    // Flash effect
                    setTimeout(function() {
                        chatHeader.style.backgroundColor = originalColor;
                    }, 400);
                }
            },
            
            updateTitleWithNotification: function(message) {
                // Update browser title to show notification
                var originalTitle = document.title;
                document.title = "=𻀠" + message + " - " + originalTitle;
                
                // Reset title when tab becomes active
                var self = this;
                var resetTitle = function() {
                    document.title = originalTitle;
                    document.removeEventListener('visibilitychange', resetTitle);
                };
                
                document.addEventListener('visibilitychange', function() {
                    if (!document.hidden) {
                        resetTitle();
                    }
                });
            },
            
            refreshConversationLists: function() {
                // Refresh conversation lists when not in active chat
                var self = this;
                var currentTab = this.getCurrentTabType();
                
                if (currentTab) {
                    this.loadConversations(currentTab);
                }
            },
            
            loadStats: function() {
                var self = this;
                console.log("Loading dashboard stats...");
                
                fetch(this.config.apiBase + '/stats')
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        if (data.success && data.stats) {
                            self.updateStats(data.stats);
                        } else {
                            console.error("Failed to load stats:", data.message || data.error);
                        }
                    })
                    .catch(function(error) {
                        console.error("Error loading stats:", error);
                        // Fallback: calculate basic stats from current data
                        self.calculateBasicStats();
                    });
            },
            
            updateStats: function(stats) {
                // Update dashboard cards with correct IDs
                var elements = {
                    'stat-today': stats.conversations_today || stats.total_conversations || 0,
                    'stat-active': stats.active_conversations || 0,
                    'stat-pending': stats.pending_conversations || 0,
                    'stat-messages': stats.messages_today || 0
                };
                
                for (var id in elements) {
                    var element = document.getElementById(id);
                    if (element) {
                        element.textContent = elements[id];
                        console.log("Updated " + id + " to " + elements[id]);
                    } else {
                        console.warn("Element not found: " + id);
                    }
                }
                // Sync tab badges with stats for immediate correctness
                var pendingBadge = document.getElementById('pending-count');
                if (pendingBadge) {
                    pendingBadge.textContent = elements['stat-pending'];
                }
                var activeBadge = document.getElementById('active-count');
                if (activeBadge) {
                    activeBadge.textContent = elements['stat-active'];
                }

                console.log("Stats updated:", stats);
            },
            
            calculateBasicStats: function() {
                // Fallback method to calculate basic stats from API calls
                var self = this;
                var stats = {
                    conversations_today: 0,
                    active_conversations: 0,
                    pending_conversations: 0,
                    messages_today: 0
                };
                
                // Get counts for each type
                Promise.all([
                    fetch(this.config.apiBase + '/pending').then(function(r) { return r.json(); }),
                    fetch(this.config.apiBase + '/active').then(function(r) { return r.json(); }),
                    fetch(this.config.apiBase + '/ended').then(function(r) { return r.json(); })
                ])
                .then(function(results) {
                    var pending = results[0];
                    var active = results[1];
                    var ended = results[2];
                    
                    if (pending.success) stats.pending_conversations = pending.conversations.length;
                    if (active.success) stats.active_conversations = active.conversations.length;
                    
                    // Calculate conversations today (pending + active + ended)
                    stats.conversations_today = stats.pending_conversations + stats.active_conversations;
                    if (ended.success) stats.conversations_today += ended.conversations.length;
                    
                    // For messages today, we'll use a simple estimate
                    stats.messages_today = stats.conversations_today * 3; // Rough estimate
                    
                    self.updateStats(stats);
                })
                .catch(function(error) {
                    console.error("Error calculating basic stats:", error);
                });
            },
            
            updateTabBadge: function(type, count) {
                var badgeId = type + '-count';
                console.log("Looking for badge element:", badgeId);
                var badge = document.getElementById(badgeId);
                if (badge) {
                    badge.textContent = count;
                    console.log("✠Updated " + badgeId + " to " + count);
                } else {
                    console.warn("L✠Badge element not found:", badgeId);
                }
            },
            
            updateStatsFromCurrentData: function() {
                console.log("Updating stats from current data...");
                
                // Update the current tab's count immediately
                var currentType = this.getCurrentTabType();
                var currentCount = this.state.conversations.length;
                
                console.log("Current tab type:", currentType, "Count:", currentCount);
                
                if (currentType) {
                    this.updateTabBadge(currentType, currentCount);
                }
            },
            
            getCurrentTabType: function() {
                // Check which tab is currently active
                if (this.elements.pendingTab && this.elements.pendingTab.classList.contains('active')) {
                    return 'pending';
                }
                if (this.elements.activeTab && this.elements.activeTab.classList.contains('active')) {
                    return 'active';
                }
                if (this.elements.endedTab && this.elements.endedTab.classList.contains('active')) {
                    return 'ended';
                }
                return 'pending'; // Default to pending
            },
            
            testElementsExist: function() {
                console.log("=𓔠Testing if elements exist...");
                
                // Test dashboard cards
                var dashboardCards = ['stat-today', 'stat-active', 'stat-pending', 'stat-messages'];
                for (var i = 0; i < dashboardCards.length; i++) {
                    var id = dashboardCards[i];
                    var element = document.getElementById(id);
                    if (element) {
                        console.log("✠Found dashboard card:", id);
                        // Test updating it
                        element.textContent = "TEST";
                    } else {
                        console.log("L✠Missing dashboard card:", id);
                    }
                }
                
                // Test tab badges
                var badges = ['pending-count', 'active-count'];
                for (var i = 0; i < badges.length; i++) {
                    var id = badges[i];
                    var element = document.getElementById(id);
                    if (element) {
                        console.log("✠Found badge:", id);
                        // Test updating it
                        element.textContent = "99";
                    } else {
                        console.log("L✠Missing badge:", id);
                    }
                }
                
                console.log("=𓔠Element test complete");
            }
        };

        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                AdminChatManager.init();
            });
        } else {
            AdminChatManager.init();
        }

        window.AdminChatManager = AdminChatManager;
        console.log("Admin chat script loaded");
    </script>
</body>
</html>