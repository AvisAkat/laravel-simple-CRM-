@extends('layout.pages-layout')
@section('pageTitle', isset($pageTitle) ? $pageTitle : 'Page Title Here')
@section('content')
    <!-- Dashboard Section -->
    <div id="dashboard">
        <h1 class="section-title">Dashboard</h1>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon indigo">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3 id="totalCustomers">0</h3>
                    <p>Total Customers</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon teal">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="stat-info">
                    <h3 id="activeLeads">0</h3>
                    <p>Active Leads</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3 id="followUpsDue">0</h3>
                    <p>Follow-Ups Due</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3 id="dealsClosed">0</h3>
                    <p>Deals Closed</p>
                </div>
            </div>
        </div>

        <!-- Dashboard Split Sub-Grid: Recent Customers & Upcoming Tasks -->
        <div
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; margin-bottom: 32px;">
            <!-- Recent Customers List Widget -->
            <div class="recent-activity" style="margin-bottom: 0;">
                <div class="section-header" style="margin-bottom: 16px;">
                    <h2 style="font-size: 16px;"><i class="fas fa-user-clock"
                            style="color: var(--indigo); margin-right: 8px;"></i>Recent Added Customers</h2>
                    <a href="#" class="nav-item-shortcut"
                        onclick="document.querySelector('[data-section=\'customers\']').click(); return false;"
                        style="color: var(--indigo); font-size: 13px; text-decoration: none; font-weight: 600;">View All</a>
                </div>
                <div class="table-container" style="border: none; box-shadow: none;">
                    <table class="data-table" style="font-size: 13px;">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Company</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="dashboardRecentCustomersBody">
                            <!-- Populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Upcoming Urgent Tasks Widget -->
            <div class="recent-activity" style="margin-bottom: 0;">
                <div class="section-header" style="margin-bottom: 16px;">
                    <h2 style="font-size: 16px;"><i class="fas fa-calendar-alt"
                            style="color: var(--teal); margin-right: 8px;"></i>Upcoming Pending Tasks</h2>
                    <a href="#" class="nav-item-shortcut"
                        onclick="document.querySelector('[data-section=\'tasks\']').click(); return false;"
                        style="color: var(--teal); font-size: 13px; text-decoration: none; font-weight: 600;">View All</a>
                </div>
                <div id="dashboardUpcomingTasksList" style="display: flex; flex-direction: column; gap: 10px;">
                    <!-- Populated by JavaScript -->
                </div>
            </div>
        </div>

        <!-- Recent Activity History Timeline -->
        <div class="recent-activity">
            <div class="section-header" style="margin-bottom: 20px;">
                <h2><i class="fas fa-history" style="color: var(--indigo); margin-right: 8px;"></i>Recent Activity History
                    Timeline</h2>
                <div style="display: flex; gap: 12px; align-items: center;">
                    <div class="filter-select-wrapper" style="padding: 0 12px;">
                        <select id="activityTypeFilter" style="padding: 8px 0; font-size: 13px; min-width: 120px;">
                            <option value="All">All Activities</option>
                            <option value="customer">Customers Only</option>
                            <option value="lead">Leads Only</option>
                            <option value="task">Tasks Only</option>
                        </select>
                    </div>
                    <button class="btn btn-secondary btn-sm" id="clearActivityLogBtn" title="Clear Timeline History">
                        <i class="fas fa-eraser"></i> Clear
                    </button>
                </div>
            </div>
            <div id="recentActivityList" class="activity-list timeline-style">
                <!-- Activity items will be added here by JavaScript -->
            </div>
        </div>
    </div>

@endsection