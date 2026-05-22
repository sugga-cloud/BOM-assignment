<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aero-BOM Control Dashboard</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>
    
    <!-- Vanilla CSS Design System -->
    <style>
        :root {
            --bg-color: #06070a;
            --panel-bg: rgba(13, 16, 26, 0.7);
            --panel-border: rgba(255, 255, 255, 0.08);
            --primary: #00f0ff;
            --primary-glow: rgba(0, 240, 255, 0.4);
            --secondary: #8b5cf6;
            --accent: #3b82f6;
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
            --success: #10b981;
            --success-glow: rgba(16, 185, 129, 0.2);
            --warning: #f59e0b;
            --danger: #f43f5e;
            --danger-glow: rgba(244, 63, 94, 0.2);
            --card-radius: 16px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(139, 92, 246, 0.1) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(0, 240, 255, 0.08) 0%, transparent 40%);
            background-attachment: fixed;
        }

        /* Layout Structure */
        .app-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            background: rgba(8, 10, 16, 0.9);
            border-right: 1px solid var(--panel-border);
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            backdrop-filter: blur(20px);
            z-index: 10;
        }

        .main-content {
            flex: 1;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            gap: 2rem;
            max-width: 1600px;
            margin: 0 auto;
            width: 100%;
        }

        /* Sidebar Elements */
        .logo-area {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 3rem;
        }

        .logo-icon {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 15px var(--primary-glow);
        }

        .logo-icon svg {
            stroke: #000;
            stroke-width: 2.5;
        }

        .logo-text {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.25rem;
            letter-spacing: 1.5px;
            background: linear-gradient(to right, #ffffff, var(--text-muted));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .nav-item a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.85rem 1rem;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 10px;
            font-weight: 500;
            transition: var(--transition);
            border: 1px solid transparent;
        }

        .nav-item.active a, .nav-item a:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.05);
        }

        .nav-item.active a {
            background: linear-gradient(90deg, rgba(0, 240, 255, 0.15) 0%, transparent 100%);
            border-left: 3px solid var(--primary);
        }

        /* Top Header */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--panel-border);
        }

        .title-group h1 {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 2rem;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #fff 30%, var(--text-muted) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .title-group p {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }

        .system-status {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .status-pill {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: var(--success);
            padding: 0.5rem 1rem;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.1);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background-color: var(--success);
            border-radius: 50%;
            animation: pulse 1.8s infinite;
        }

        .user-badge {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--panel-border);
            padding: 0.4rem 1rem;
            border-radius: 30px;
        }

        .avatar {
            width: 28px;
            height: 28px;
            background: var(--secondary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
        }

        /* Metrics Grid */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.5rem;
        }

        .metric-card {
            background: var(--panel-bg);
            border: 1px solid var(--panel-border);
            border-radius: var(--card-radius);
            padding: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            backdrop-filter: blur(15px);
            transition: var(--transition);
        }

        .metric-card:hover {
            transform: translateY(-4px);
            border-color: rgba(0, 240, 255, 0.2);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .metric-info h3 {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .metric-value {
            font-family: 'Outfit', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
        }

        .metric-icon {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--panel-border);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            transition: var(--transition);
        }

        .metric-card:hover .metric-icon {
            background: rgba(0, 240, 255, 0.1);
            border-color: var(--primary);
            color: #fff;
            box-shadow: 0 0 15px var(--primary-glow);
        }

        /* Two-Column Console */
        .dashboard-console {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 1.5rem;
        }

        @media (max-width: 1024px) {
            .dashboard-console {
                grid-template-columns: 1fr;
            }
        }

        .console-panel {
            background: var(--panel-bg);
            border: 1px solid var(--panel-border);
            border-radius: var(--card-radius);
            backdrop-filter: blur(15px);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .panel-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--panel-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .panel-header h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .panel-header svg {
            color: var(--primary);
        }

        .panel-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        /* File Upload Styles */
        .upload-form {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group label {
            font-size: 0.85rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--panel-border);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            color: #fff;
            font-family: inherit;
            font-size: 0.9rem;
            outline: none;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 10px rgba(0, 240, 255, 0.15);
        }

        select.form-control option {
            background: #0d101a;
            color: #fff;
        }

        .drag-drop-area {
            border: 2px dashed rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 2.5rem 1.5rem;
            text-align: center;
            background: rgba(255, 255, 255, 0.01);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
        }

        .drag-drop-area:hover, .drag-drop-area.dragover {
            border-color: var(--primary);
            background: rgba(0, 240, 255, 0.02);
        }

        .upload-icon-container {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.03);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            transition: var(--transition);
        }

        .drag-drop-area:hover .upload-icon-container {
            background: rgba(0, 240, 255, 0.1);
            color: var(--primary);
            box-shadow: 0 0 15px var(--primary-glow);
        }

        .file-info {
            display: none;
            font-size: 0.9rem;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: var(--success);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            width: 100%;
            max-width: 320px;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .upload-btn {
            background: linear-gradient(135deg, var(--accent) 0%, var(--secondary) 100%);
            border: none;
            border-radius: 10px;
            padding: 0.9rem;
            color: #fff;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.25);
            transition: var(--transition);
        }

        .upload-btn:hover {
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
            transform: translateY(-2px);
            filter: brightness(1.1);
        }

        /* Queue Active Job Card */
        .queue-tracker {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--panel-border);
            border-radius: 12px;
            padding: 1.25rem;
            display: none;
            flex-direction: column;
            gap: 0.75rem;
            position: relative;
            overflow: hidden;
        }

        .queue-tracker::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 3px;
            height: 100%;
            background: var(--warning);
        }

        .queue-tracker.completed::after {
            background: var(--success);
        }

        .queue-tracker.failed::after {
            background: var(--danger);
        }

        .queue-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .queue-title {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .queue-badge {
            padding: 0.25rem 0.6rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-processing {
            background: rgba(245, 158, 11, 0.15);
            color: var(--warning);
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .badge-completed {
            background: rgba(16, 185, 129, 0.15);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .badge-failed {
            background: rgba(244, 63, 94, 0.15);
            color: var(--danger);
            border: 1px solid rgba(244, 63, 94, 0.3);
        }

        .progress-bar-container {
            width: 100%;
            height: 6px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, var(--primary) 0%, var(--accent) 100%);
            border-radius: 10px;
            transition: width 0.3s ease;
        }

        .queue-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        /* Procurement Intents / Allocations Tables */
        .console-tabs {
            display: flex;
            border-bottom: 1px solid var(--panel-border);
            background: rgba(0, 0, 0, 0.1);
        }

        .tab-btn {
            flex: 1;
            padding: 1.25rem 1rem;
            background: none;
            border: none;
            color: var(--text-muted);
            font-family: 'Outfit', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-bottom: 2px solid transparent;
        }

        .tab-btn.active {
            color: #fff;
            border-bottom-color: var(--primary);
            background: rgba(255, 255, 255, 0.02);
            text-shadow: 0 0 10px rgba(0, 240, 255, 0.3);
        }

        .tab-pane {
            display: none;
            padding: 1.5rem;
            height: 380px;
            overflow-y: auto;
        }

        .tab-pane.active {
            display: block;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.85rem;
        }

        th {
            color: var(--text-muted);
            font-weight: 600;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--panel-border);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            color: #fff;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .priority-badge {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-weight: 700;
            font-size: 0.7rem;
            text-transform: uppercase;
        }

        .priority-high {
            background: var(--danger-glow);
            color: var(--danger);
            border: 1px solid rgba(244, 63, 94, 0.2);
        }

        .priority-medium {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .priority-low {
            background: rgba(59, 130, 246, 0.1);
            color: var(--accent);
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        /* Stock Monitor Grid */
        .stock-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1.25rem;
        }

        .stock-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--panel-border);
            border-radius: 12px;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            transition: var(--transition);
        }

        .stock-card:hover {
            border-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        .stock-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stock-code {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            color: var(--primary);
            font-size: 1rem;
        }

        .stock-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .stock-ok {
            background-color: var(--success);
            box-shadow: 0 0 8px var(--success);
        }

        .stock-low {
            background-color: var(--warning);
            box-shadow: 0 0 8px var(--warning);
        }

        .stock-empty {
            background-color: var(--danger);
            box-shadow: 0 0 8px var(--danger);
        }

        .stock-desc {
            font-size: 0.8rem;
            color: var(--text-muted);
            line-height: 1.4;
            height: 2.8em;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .stock-qty-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 0.5rem;
        }

        .stock-label {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .stock-qty {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 1.4rem;
            color: #fff;
        }

        /* Audit Timeline */
        .timeline {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            padding: 0.5rem 0;
        }

        .timeline-item {
            display: flex;
            gap: 1.25rem;
            position: relative;
        }

        .timeline-item::after {
            content: '';
            position: absolute;
            top: 24px;
            left: 11px;
            width: 2px;
            height: calc(100% - 12px);
            background: var(--panel-border);
        }

        .timeline-item:last-child::after {
            display: none;
        }

        .timeline-node {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.03);
            border: 2px solid var(--panel-border);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            flex-shrink: 0;
            z-index: 1;
            transition: var(--transition);
        }

        .timeline-item.success .timeline-node {
            border-color: var(--success);
            color: var(--success);
            background: rgba(16, 185, 129, 0.05);
        }

        .timeline-item.failed .timeline-node {
            border-color: var(--danger);
            color: var(--danger);
            background: rgba(244, 63, 94, 0.05);
        }

        .timeline-item.info .timeline-node {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(0, 240, 255, 0.05);
        }

        .timeline-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .timeline-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .timeline-action {
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .timeline-time {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .timeline-desc {
            font-size: 0.85rem;
            color: var(--text-muted);
            line-height: 1.4;
        }

        /* Inspect Payload Code Block */
        .inspect-payload-btn {
            background: none;
            border: none;
            color: var(--primary);
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            align-self: flex-start;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            margin-top: 0.25rem;
            outline: none;
        }

        .inspect-payload-btn:hover {
            text-decoration: underline;
        }

        .payload-block {
            display: none;
            background: #020305;
            border: 1px solid var(--panel-border);
            border-radius: 8px;
            padding: 0.85rem;
            font-family: monospace;
            font-size: 0.75rem;
            color: #34d399;
            overflow-x: auto;
            white-space: pre-wrap;
            max-width: 100%;
        }

        /* Animations */
        @keyframes pulse {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 8px rgba(16, 185, 129, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.15);
        }
    </style>
</head>
<body>
    <div class="app-container">
        
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="logo-area">
                <div class="logo-icon">
                    <i data-feather="cpu"></i>
                </div>
                <span class="logo-text">AERO-BOM</span>
            </div>
            
            <nav>
                <ul class="nav-list">
                    <li class="nav-item active">
                        <a href="#">
                            <i data-feather="grid"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#inventory-section">
                            <i data-feather="archive"></i>
                            <span>Stock Levels</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#audit-section">
                            <i data-feather="shield"></i>
                            <span>Audit Trails</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Dashboard View -->
        <main class="main-content">
            
            <!-- Header section -->
            <header class="dashboard-header">
                <div class="title-group">
                    <h1>Operations Console</h1>
                    <p>BOM Processing, Stock Allocation & Procurement Intents Engine</p>
                </div>
                
                <div class="system-status">
                    <div class="status-pill">
                        <span class="status-dot"></span>
                        <span>Engine: Active</span>
                    </div>
                    <div class="user-badge">
                        <div class="avatar">AD</div>
                        <span>Admin</span>
                    </div>
                </div>
            </header>

            <!-- Metrics Summary Cards -->
            <section class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-info">
                        <h3>Active Projects</h3>
                        <div class="metric-value" id="count-projects">-</div>
                    </div>
                    <div class="metric-icon">
                        <i data-feather="briefcase"></i>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-info">
                        <h3>Inventory Items</h3>
                        <div class="metric-value" id="count-inventory">-</div>
                    </div>
                    <div class="metric-icon">
                        <i data-feather="box"></i>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-info">
                        <h3>Stock Allocated</h3>
                        <div class="metric-value" id="count-allocated">-</div>
                    </div>
                    <div class="metric-icon" style="color: var(--success);">
                        <i data-feather="check-square"></i>
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-info">
                        <h3>Pending Shortfalls</h3>
                        <div class="metric-value" id="count-shortfalls">-</div>
                    </div>
                    <div class="metric-icon" style="color: var(--danger);">
                        <i data-feather="alert-triangle"></i>
                    </div>
                </div>
            </section>

            <!-- Double Column Dashboard Console -->
            <section class="dashboard-console">
                
                <!-- Left Console: File Ingestion & Queue Monitor -->
                <div class="console-panel">
                    <div class="panel-header">
                        <h2><i data-feather="upload-cloud"></i> BOM Ingestion Controller</h2>
                    </div>
                    <div class="panel-body">
                        
                        <!-- Upload Form -->
                        <form id="bom-upload-form" class="upload-form" enctype="multipart/form-data">
                            @csrf
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="project_id">Target Project</label>
                                    <select class="form-control" name="project_id" id="project_id" required>
                                        @foreach($projects as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="version">Version Tag</label>
                                    <input type="text" class="form-control" name="version" id="version" placeholder="e.g. v1.0.0" required>
                                </div>
                            </div>
                            
                            <div class="drag-drop-area" id="drop-area">
                                <input type="file" name="file" id="file-input" accept=".xlsx,.xls,.csv" style="display: none;" required>
                                <div class="upload-icon-container">
                                    <i data-feather="file-plus" style="width: 28px; height: 28px;"></i>
                                </div>
                                <span style="font-weight: 500;">Drag spreadsheet here or click to browse</span>
                                <span style="font-size: 0.75rem; color: var(--text-muted);">Supported formats: .xlsx, .xls, .csv</span>
                                <div class="file-info" id="file-info"></div>
                            </div>
                            
                            <button type="submit" class="upload-btn" id="upload-btn">
                                <i data-feather="play"></i>
                                <span>Initialize Ingestion Stream</span>
                            </button>
                        </form>

                        <!-- Background Job Progress Tracker -->
                        <div class="queue-tracker" id="job-tracker">
                            <div class="queue-header">
                                <span class="queue-title" id="job-title">Processing Ingestion Pipeline</span>
                                <span class="queue-badge badge-processing" id="job-badge">Processing</span>
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-bar-fill" id="job-progress"></div>
                            </div>
                            <div class="queue-details">
                                <div>BOM Line Items: <strong id="job-lines">-</strong></div>
                                <div>Completed: <strong id="job-done">-</strong></div>
                                <div>Allocations: <strong id="job-allocations">-</strong></div>
                                <div>Shortfalls: <strong id="job-intents">-</strong></div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Right Console: Ingestion Results Tables -->
                <div class="console-panel">
                    <div class="console-tabs">
                        <button class="tab-btn active" onclick="switchTab(event, 'tab-shortfalls')">
                            <i data-feather="shopping-bag"></i> Shortfall Pipeline
                        </button>
                        <button class="tab-btn" onclick="switchTab(event, 'tab-allocations')">
                            <i data-feather="activity"></i> Material Allocations
                        </button>
                    </div>
                    
                    <!-- Shortfalls Tab -->
                    <div class="tab-pane active" id="tab-shortfalls">
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Item Code</th>
                                        <th>Shortfall</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="shortfalls-table-body">
                                    <tr>
                                        <td colspan="4" style="text-align: center; color: var(--text-muted);">No shortfall pipelines generated yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Allocations Tab -->
                    <div class="tab-pane" id="tab-allocations">
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Item Code</th>
                                        <th>Description</th>
                                        <th>Allocated Qty</th>
                                        <th>Target Role</th>
                                    </tr>
                                </thead>
                                <tbody id="allocations-table-body">
                                    <tr>
                                        <td colspan="4" style="text-align: center; color: var(--text-muted);">No material allocations processed yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </section>

            <!-- Master Stock Inventory Levels -->
            <section class="console-panel" id="inventory-section">
                <div class="panel-header">
                    <h2><i data-feather="database"></i> Master Warehouse Inventory Levels</h2>
                    <span style="font-size: 0.8rem; color: var(--text-muted);">Updated Dynamically on Allocation Commits</span>
                </div>
                <div class="panel-body">
                    <div class="stock-grid" id="inventory-grid">
                        @foreach($inventory as $inv)
                            <div class="stock-card" data-code="{{ $inv->item_code }}">
                                <div class="stock-header">
                                    <span class="stock-code">{{ $inv->item_code }}</span>
                                    <span class="stock-indicator @if($inv->available_qty > 20) stock-ok @elseif($inv->available_qty > 0) stock-low @else stock-empty @endif"></span>
                                </div>
                                <span class="stock-desc" title="{{ $inv->description }}">{{ $inv->description }}</span>
                                <div class="stock-qty-row">
                                    <span class="stock-label">Available Stock</span>
                                    <span class="stock-qty" data-qty="{{ $inv->available_qty }}">{{ number_format($inv->available_qty, 2) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <!-- Real-time Audit Timeline Ledger -->
            <section class="console-panel" id="audit-section">
                <div class="panel-header">
                    <h2><i data-feather="shield"></i> Real-time System Audit Ledger</h2>
                </div>
                <div class="panel-body">
                    <div class="timeline" id="audit-timeline">
                        <!-- Audit timelines populate here dynamically -->
                        <div style="text-align: center; color: var(--text-muted); font-size: 0.9rem;">Initializing audit ledger logs...</div>
                    </div>
                </div>
            </section>

        </main>
    </div>

    <!-- Reactive Dashboard Controller Logic -->
    <script>
        // Init Feather Icons
        feather.replace();

        // Drag & Drop Handling
        const dropArea = document.getElementById('drop-area');
        const fileInput = document.getElementById('file-input');
        const fileInfo = document.getElementById('file-info');

        dropArea.addEventListener('click', () => fileInput.click());

        dropArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropArea.classList.add('dragover');
        });

        dropArea.addEventListener('dragleave', () => {
            dropArea.classList.remove('dragover');
        });

        dropArea.addEventListener('drop', (e) => {
            e.preventDefault();
            dropArea.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                updateFileDetails();
            }
        });

        fileInput.addEventListener('change', updateFileDetails);

        function updateFileDetails() {
            if (fileInput.files.length) {
                const file = fileInput.files[0];
                fileInfo.textContent = `Selected: ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
                fileInfo.style.display = 'block';
            } else {
                fileInfo.style.display = 'none';
            }
        }

        // Tab Switching
        function switchTab(e, tabId) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
            
            e.currentTarget.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        }

        // API AJAX Ingestion Dispatch & Polling Engine
        const uploadForm = document.getElementById('bom-upload-form');
        const jobTracker = document.getElementById('job-tracker');
        const jobTitle = document.getElementById('job-title');
        const jobBadge = document.getElementById('job-badge');
        const jobProgress = document.getElementById('job-progress');
        const jobLines = document.getElementById('job-lines');
        const jobDone = document.getElementById('job-done');
        const jobAllocations = document.getElementById('job-allocations');
        const jobIntents = document.getElementById('job-intents');
        const uploadBtn = document.getElementById('upload-btn');

        uploadForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(uploadForm);
            
            // Set UI Loading State
            uploadBtn.disabled = true;
            uploadBtn.style.filter = 'brightness(0.7)';
            uploadBtn.innerHTML = `<i data-feather="loader" class="feather-spin"></i><span>Ingesting Spreadsheet...</span>`;
            feather.replace();

            try {
                const response = await fetch('/api/v1/bom/upload', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'File ingestion failed.');
                }

                // Initialized Tracker Card
                jobTracker.style.display = 'flex';
                jobTracker.className = 'queue-tracker';
                jobTitle.textContent = 'Processing Stock Ingestion Pipelines';
                jobBadge.textContent = 'Processing';
                jobBadge.className = 'queue-badge badge-processing';
                jobProgress.style.width = '20%';
                
                // Clear file selection
                fileInput.value = '';
                fileInfo.style.display = 'none';
                document.getElementById('version').value = '';

                // Start polling background queue job
                pollJobStatus(result.data.bom_header_id, result.data.status_url);

            } catch (err) {
                alert(`Upload Failed: ${err.message}`);
            } finally {
                // Restore upload button state
                uploadBtn.disabled = false;
                uploadBtn.style.filter = 'none';
                uploadBtn.innerHTML = `<i data-feather="play"></i><span>Initialize Ingestion Stream</span>`;
                feather.replace();
            }
        });

        // Polling background queue states
        let pollTimer = null;
        function pollJobStatus(headerId, pollUrl) {
            if (pollTimer) clearInterval(pollTimer);

            pollTimer = setInterval(async () => {
                try {
                    const res = await fetch(pollUrl);
                    const statusData = await res.json();

                    if (!statusData.success) {
                        clearInterval(pollTimer);
                        return;
                    }

                    const info = statusData.data;
                    jobLines.textContent = info.line_items_count;
                    jobAllocations.textContent = info.allocated_count;
                    jobIntents.textContent = info.intent_count;

                    if (info.status === 'processing') {
                        jobProgress.style.width = '60%';
                        jobDone.textContent = '0 / ' + info.line_items_count;
                    } else if (info.status === 'completed') {
                        clearInterval(pollTimer);
                        jobProgress.style.width = '100%';
                        jobBadge.textContent = 'Completed';
                        jobBadge.className = 'queue-badge badge-completed';
                        jobTracker.classList.add('completed');
                        jobDone.textContent = info.line_items_count + ' / ' + info.line_items_count;
                        jobTitle.textContent = 'Spreadsheet Ingested & Stock Committed!';
                        
                        // Populate allocations and shortfalls tables with real-time API details
                        buildAllocationsTableData(info.allocations);
                        buildShortfallsTableData(info.shortfalls);

                        // Micro-animation and live refresh of stats/grid
                        refreshDashboardData();

                    } else if (info.status === 'failed') {
                        clearInterval(pollTimer);
                        jobProgress.style.width = '100%';
                        jobBadge.textContent = 'Failed';
                        jobBadge.className = 'queue-badge badge-failed';
                        jobTracker.classList.add('failed');
                        jobTitle.textContent = 'Transaction Rollback: Engine Failure';
                        
                        refreshDashboardData();
                    }

                } catch (e) {
                    console.error("Polling error: ", e);
                }
            }, 1000);
        }

        // Live dashboard data refresh
        async function refreshDashboardData() {
            try {
                const res = await fetch('/api/v1/bom/metrics');
                const metrics = await res.json();

                if (!metrics.success) return;

                const data = metrics.data;

                // Update metrics counters
                document.getElementById('count-projects').textContent = data.projects_count;
                document.getElementById('count-inventory').textContent = data.inventory_count;
                document.getElementById('count-allocated').textContent = data.total_allocations;
                document.getElementById('count-shortfalls').textContent = data.total_shortfalls;

                // Refresh inventory elements on UI
                await reloadWarehouseGrid();

                // Refresh audit timeline ledger logs
                buildAuditTimeline(data.recent_audits);

            } catch (err) {
                console.error("Dashboard refresh error: ", err);
            }
        }

        // Dynamic helper to rebuild allocations table from last upload
        async function buildAllocationsTable() {
            try {
                const response = await fetch('/api/v1/bom/metrics');
                const metrics = await response.json();
                if (metrics.success && metrics.data.recent_uploads.length > 0) {
                    const headerId = metrics.data.recent_uploads[0].id;
                    const details = await fetch(`/api/v1/bom/status/${headerId}`);
                    const detailsJson = await details.json();
                    if (detailsJson.success) {
                        buildAllocationsTableData(detailsJson.data.allocations);
                    }
                }
            } catch(e) {}
        }

        // Dynamic inventory fetch to match and update stock figures
        async function reloadWarehouseGrid() {
            const container = document.getElementById('inventory-grid');
            try {
                // Fetch directly from standard html index using page reloader or ajax fetch
                const response = await fetch('/');
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newGrid = doc.getElementById('inventory-grid').innerHTML;
                container.innerHTML = newGrid;
            } catch (err) {
                console.warn("Stock grid update exception: ", err);
            }
        }

        // Build shortfall lists from last upload
        async function buildShortfallsTable() {
            try {
                const response = await fetch('/api/v1/bom/metrics');
                const metrics = await response.json();
                if (metrics.success && metrics.data.recent_uploads.length > 0) {
                    const headerId = metrics.data.recent_uploads[0].id;
                    const details = await fetch(`/api/v1/bom/status/${headerId}`);
                    const detailsJson = await details.json();
                    if (detailsJson.success) {
                        buildShortfallsTableData(detailsJson.data.shortfalls);
                    }
                }
            } catch(e){}
        }

        // Render standard and high priority shortfalls
        function buildShortfallsTableData(shortfalls) {
            const body = document.getElementById('shortfalls-table-body');
            body.innerHTML = '';
            
            if (!shortfalls || shortfalls.length === 0) {
                body.innerHTML = '<tr><td colspan="4" style="text-align: center; color: var(--text-muted);">No active shortfall lines.</td></tr>';
                return;
            }

            shortfalls.forEach(item => {
                const priorityClass = `priority-${item.priority}`;
                body.innerHTML += `
                    <tr>
                        <td style="font-family: 'Outfit'; font-weight: 600; color: var(--primary);">${item.item_code}</td>
                        <td><strong>${parseFloat(item.shortfall_qty).toFixed(2)}</strong> units</td>
                        <td><span class="priority-badge ${priorityClass}">${item.priority}</span></td>
                        <td><span style="color: var(--text-muted); font-size: 0.8rem;">${item.status}</span></td>
                    </tr>
                `;
            });
        }

        // Rebuild dynamic allocations list
        function buildAllocationsTableData(allocs) {
            const body = document.getElementById('allocations-table-body');
            body.innerHTML = '';

            if (!allocs || allocs.length === 0) {
                body.innerHTML = '<tr><td colspan="4" style="text-align: center; color: var(--text-muted);">No material allocations.</td></tr>';
                return;
            }

            allocs.forEach(a => {
                body.innerHTML += `
                    <tr>
                        <td style="font-family: 'Outfit'; font-weight: 600; color: var(--primary);">${a.item_code}</td>
                        <td style="color: var(--text-muted);">${a.description}</td>
                        <td><strong>${parseFloat(a.allocated_qty).toFixed(2)}</strong> units</td>
                        <td><span style="background: rgba(255,255,255,0.03); padding: 0.2rem 0.5rem; border-radius: 4px; border: 1px solid var(--panel-border); font-size: 0.75rem;">${a.allocated_to}</span></td>
                    </tr>
                `;
            });
        }

        // Build real-time audit trail node timeline
        function buildAuditTimeline(audits) {
            const timeline = document.getElementById('audit-timeline');
            timeline.innerHTML = '';

            if (!audits || audits.length === 0) {
                timeline.innerHTML = '<div style="text-align: center; color: var(--text-muted); font-size: 0.9rem;">No audit logs registered in system.</div>';
                return;
            }

            audits.forEach(a => {
                let statusClass = 'info';
                let icon = 'info';
                
                if (a.action.includes('SUCCESS')) {
                    statusClass = 'success';
                    icon = 'check-circle';
                } else if (a.action.includes('FAILED')) {
                    statusClass = 'failed';
                    icon = 'x-circle';
                } else if (a.action.includes('UPLOAD')) {
                    statusClass = 'info';
                    icon = 'arrow-up-circle';
                }

                const payloadJson = a.payload ? JSON.stringify(a.payload, null, 4) : null;
                const inspectButton = payloadJson ? `<button class="inspect-payload-btn" onclick="togglePayload(${a.id})"><i data-feather="code" style="width: 12px; height: 12px;"></i> Inspect Payload</button>` : '';
                const payloadBlock = payloadJson ? `<pre class="payload-block" id="payload-${a.id}">${payloadJson}</pre>` : '';

                timeline.innerHTML += `
                    <div class="timeline-item ${statusClass}">
                        <div class="timeline-node">
                            <i data-feather="${icon}" style="width: 14px; height: 14px;"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-header">
                                <span class="timeline-action">${a.action.replace(/_/g, ' ')}</span>
                                <span class="timeline-time">${a.created_at}</span>
                            </div>
                            <span class="timeline-desc">${a.description}</span>
                            ${inspectButton}
                            ${payloadBlock}
                        </div>
                    </div>
                `;
            });
            feather.replace();
        }

        // Toggle Payload Collapser
        function togglePayload(auditId) {
            const block = document.getElementById(`payload-${auditId}`);
            if (block.style.display === 'block') {
                block.style.display = 'none';
            } else {
                block.style.display = 'block';
            }
        }

        // Live list fetchers to build the tab tables
        async function fetchAllocationsAndShortfalls() {
            try {
                // Fetch live lists of allocations and shortfalls for tables
                // In production, we'd have standalone endpoints, here we fetch a combined metrics data or details
                const res = await fetch('/api/v1/bom/metrics');
                const metrics = await res.json();
                
                if (!metrics.success) return;

                // Let's scrape or perform dedicated fetches if necessary.
                // We'll query standard listings using a helper to fetch allocations and shortfalls.
                const detailsRes = await fetch('/');
                const html = await detailsRes.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Let's parse custom detail grids from standard records
                // To keep it simple and robust, let's load details via metrics payloads:
                // We'll populate dynamically!
            } catch(e){}
        }

        // Helper to query and build tables from simple metrics
        async function loadTablesData() {
            try {
                // To keep the tables up to date, we can fetch their database lists:
                const response = await fetch('/api/v1/bom/metrics'); // A consolidated metrics payload
                const metrics = await response.json();
                if (metrics.success) {
                    // Let's fetch all items of the last uploaded BOM version
                    const recentUploads = metrics.data.recent_uploads;
                    if (recentUploads.length > 0) {
                        const lastHeaderId = recentUploads[0].id;
                        
                        // Fetch details of last header
                        const detailRes = await fetch(`/api/v1/bom/status/${lastHeaderId}`);
                        const detailData = await detailRes.json();
                        
                        // We will build standard queries or display metrics summary:
                        // Let's do a fetch for allocations/shortfalls of last processed header
                        const listRes = await fetch(`/api/v1/bom/status/${lastHeaderId}`);
                        // Let's just pull allocations/shortfalls directly in the status payload!
                        // That is extremely smart! Let's update status endpoint to return allocations and shortfalls!
                    }
                }
            } catch(e){}
        }

        // Let's fetch the list of shortfalls and allocations on load!
        // To make the status response contain lists when requested, we can update our controller status()
        // to return the relations.
        // Actually, we can fetch allocations and shortfalls of the last BOM from the database:
        // We'll query standard metrics and populate!
        
        // Let's write an AJAX scraper that does a quick pull on page load
        async function initialLoad() {
            await refreshDashboardData();
            
            // Re-render tabular data if any
            try {
                const response = await fetch('/api/v1/bom/metrics');
                const metrics = await response.json();
                if (metrics.success && metrics.data.recent_uploads.length > 0) {
                    const headerId = metrics.data.recent_uploads[0].id;
                    
                    // Fetch details
                    const details = await fetch(`/api/v1/bom/status/${headerId}`);
                    const detailsJson = await details.json();
                    
                    // We'll update tabular records using audit timelines or scrape!
                }
            } catch(e){}
        }

        // Run on bootstrap
        window.addEventListener('DOMContentLoaded', () => {
            initialLoad();
            
            // Re-query metrics every 15s to keep dashboard fresh
            setInterval(refreshDashboardData, 15000);
        });

    </script>
</body>
</html>
