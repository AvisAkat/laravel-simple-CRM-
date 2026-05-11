<?php

use Livewire\Component;
use App\Models\Customer;
use App\Models\User;
use App\Models\Lead;
use App\Models\Task;
use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    private function isAdmin()
    {
        return auth()->user()->role === 'admin';
    }

    private function isManager()
    {
        return auth()->user()->role === 'manager';
    }

    private function isAgent()
    {
        return auth()->user()->role === 'agent';
    }

    private function applyRoleFilter($query)
    {
        if ($this->isAdmin()) {
            return $query;
        }
        return $query->where('created_by', auth()->id());
    }

    public function getTotalCustomers()
    {
        return $this->applyRoleFilter(Customer::query())->count();
    }

    public function getActiveLeads()
    {
        return $this->applyRoleFilter(Lead::where('converted', false))->count();
    }

    public function getFollowUpsDue()
    {
        return $this->applyRoleFilter(Task::where('completed', false)
            ->where('due_dateTime', '<=', now()->addDays(7)))->count();
    }

    public function getDealsClosed()
    {
        return $this->applyRoleFilter(Customer::where('status', 'closed'))->count();
    }

    public function getRecentCustomers()
    {
        return $this->applyRoleFilter(Customer::orderBy('created_at', 'desc'))
            ->limit(5)
            ->get();
    }

    public function getUpcomingTasks()
    {
        return $this->applyRoleFilter(Task::where('completed', false)
            ->orderBy('due_dateTime', 'asc'))
            ->limit(5)
            ->get();
    }

    public function getRecentActivities()
    {
        $query = Activity::with('user')->where('isClear', false)->orderBy('created_at', 'desc');
        if (!$this->isAdmin()) {
            $query->where('user_id', auth()->id());
        }
        return $query->limit(20)->get();
    }

    public function clearActivities()
    {
        Activity::query()->update([
            'isClear' => true
        ]);
    }

    public function getTotalUsers()
    {
        if ($this->isAdmin()) {
            return User::count();
        }
        if ($this->isManager()) {
            return User::where('role', 'agent')->count();
        }
        return 1;
    }

    public function getTotalTasks()
    {
        return $this->applyRoleFilter(Task::query())->count();
    }

    public function getCompletedTasks()
    {
        return $this->applyRoleFilter(Task::where('completed', true))->count();
    }

    public function getHighPriorityTasks()
    {
        return $this->applyRoleFilter(Task::where('priority', 'high')->where('completed', false))->count();
    }

    public function getOverdueTasks()
    {
        return $this->applyRoleFilter(Task::where('completed', false)
            ->where('due_dateTime', '<', now()))->count();
    }

    public function getConvertedLeads()
    {
        return $this->applyRoleFilter(Lead::where('converted', true))->count();
    }

    public function getTaskCompletionRate()
    {
        $totalTasks = $this->applyRoleFilter(Task::query())->count();
        if ($totalTasks == 0)
            return 0;
        $completedTasks = $this->applyRoleFilter(Task::where('completed', true))->count();
        return round(($completedTasks / $totalTasks) * 100);
    }

    public function getTodayActivities()
    {
        $query = Activity::whereDate('created_at', today());
        if (!$this->isAdmin()) {
            $query->where('user_id', auth()->id());
        }
        return $query->count();
    }

    public function getCustomerStatusDistribution()
    {
        return [
            'lead' => $this->applyRoleFilter(Customer::where('status', 'lead'))->count(),
            'active' => $this->applyRoleFilter(Customer::where('status', 'active'))->count(),
            'closed' => $this->applyRoleFilter(Customer::where('status', 'closed'))->count(),
        ];
    }

    public function getTaskPriorityDistribution()
    {
        return [
            'low' => $this->applyRoleFilter(Task::where('priority', 'low'))->count(),
            'medium' => $this->applyRoleFilter(Task::where('priority', 'medium'))->count(),
            'high' => $this->applyRoleFilter(Task::where('priority', 'high'))->count(),
        ];
    }

    public function getLeadStatusDistribution()
    {
        return [
            'new' => $this->applyRoleFilter(Lead::where('status', 'new'))->count(),
            'contacted' => $this->applyRoleFilter(Lead::where('status', 'contacted'))->count(),
            'qualified' => $this->applyRoleFilter(Lead::where('status', 'qualified'))->count(),
            'proposal' => $this->applyRoleFilter(Lead::where('status', 'proposal'))->count(),
        ];
    }

    public function getTaskCompletionTrend()
    {
        $days = 7;
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $data[$date] = [
                'completed' => $this->applyRoleFilter(Task::where('completed', true)->whereDate('updated_at', $date))->count(),
                'total' => $this->applyRoleFilter(Task::whereDate('created_at', $date))->count(),
            ];
        }
        return $data;
    }

    public function getDailyActivityTrend()
    {
        $days = 7;
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $query = Activity::whereDate('created_at', $date->format('Y-m-d'));
            if (!$this->isAdmin()) {
                $query->where('user_id', auth()->id());
            }
            $data[$date->format('M j')] = $query->count();
        }
        return $data;
    }
};
?>

<div>
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon indigo">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3 id="totalCustomers">{{ $this->getTotalCustomers() }}</h3>
                <p>Total Customers</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon teal">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="stat-info">
                <h3 id="activeLeads">{{ $this->getActiveLeads() }}</h3>
                <p>Active Leads</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3 id="followUpsDue">{{ $this->getFollowUpsDue() }}</h3>
                <p>Follow-Ups Due</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3 id="dealsClosed">{{ $this->getDealsClosed() }}</h3>
                <p>Deals Closed</p>
            </div>
        </div>
    </div>

    <!-- Additional System Summary Cards -->
    <div class="stats-grid" style="margin-bottom: 32px;">
        @if (auth()->user()->role === 'admin')
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #7033ff, #a855f7);">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="stat-info">
                    <h3>{{ $this->getTotalUsers() }}</h3>
                    <p>Total Users</p>
                </div>
            </div>
        @endif
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #8b5cf6, #63f755);">
                <i class="fas fa-tasks"></i>
            </div>
            <div class="stat-info">
                <h3>{{ $this->getTotalTasks() }}</h3>
                <p>Total Tasks</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #06b6d4);">
                <i class="fas fa-check"></i>
            </div>
            <div class="stat-info">
                <h3>{{ $this->getCompletedTasks() }}</h3>
                <p>Completed Tasks</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f97316, #ec4899);">
                <i class="fas fa-fire"></i>
            </div>
            <div class="stat-info">
                <h3>{{ $this->getHighPriorityTasks() }}</h3>
                <p>High Priority Tasks</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #ef4444, #f97316);">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="stat-info">
                <h3>{{ $this->getOverdueTasks() }}</h3>
                <p>Overdue Tasks</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #8b5cf6, #a855f7);">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <div class="stat-info">
                <h3>{{ $this->getConvertedLeads() }}</h3>
                <p>Converted Leads</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #06b6d4, #0891b2);">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="stat-info">
                <h3>{{ $this->getTaskCompletionRate() }}%</h3>
                <p>Task Completion Rate</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b, #f97316);">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-info">
                <h3>{{ $this->getTodayActivities() }}</h3>
                <p>Today's Activities</p>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    @if (auth()->user()->role === 'admin' || auth()->user()->role === 'manager')
        <div
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; margin-bottom: 32px;">
            <!-- Customer Status Distribution Chart -->
            <div class="recent-activity">
                <div class="section-header" style="margin-bottom: 16px;">
                    <h2 style="font-size: 16px;"><i class="fas fa-pie-chart"
                            style="color: var(--indigo); margin-right: 8px;"></i>Customer Status</h2>
                </div>
                <canvas id="customerStatusChart" style="max-height: 250px;"></canvas>
            </div>

            <!-- Task Priority Distribution Chart -->
            <div class="recent-activity">
                <div class="section-header" style="margin-bottom: 16px;">
                    <h2 style="font-size: 16px;"><i class="fas fa-chart-bar"
                            style="color: var(--teal); margin-right: 8px;"></i>Tasks by Priority</h2>
                </div>
                <canvas id="taskPriorityChart" style="max-height: 250px;"></canvas>
            </div>

            <!-- Lead Status Distribution Chart -->
            <div class="recent-activity">
                <div class="section-header" style="margin-bottom: 16px;">
                    <h2 style="font-size: 16px;"><i class="fas fa-funnel"
                            style="color: var(--orange); margin-right: 8px;"></i>Leads by Status</h2>
                </div>
                <canvas id="leadStatusChart" style="max-height: 250px;"></canvas>
            </div>
        </div>

        <!-- Activity Trends Chart -->
        <div class="recent-activity" style="margin-bottom: 32px;">
            <div class="section-header" style="margin-bottom: 16px;">
                <h2><i class="fas fa-chart-line" style="color: var(--green); margin-right: 8px;"></i>7-Day Activity Trend
                </h2>
            </div>
            <canvas id="activityTrendChart" style="max-height: 300px;"></canvas>
        </div>

        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    // Customer Status Chart
                    const customerStatusData = {!! json_encode($this->getCustomerStatusDistribution()) !!};
                    new Chart(document.getElementById('customerStatusChart'), {
                        type: 'doughnut',
                        data: {
                            labels: ['Lead', 'Active', 'Closed'],
                            datasets: [{
                                data: [customerStatusData.lead, customerStatusData.active, customerStatusData.closed],
                                backgroundColor: ['#818cf8', '#14b8a6', '#10b981'],
                                borderColor: 'rgba(255, 255, 255, 0.1)',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: { padding: 15, font: { size: 12 }, color: 'var(--text-secondary)' }
                                }
                            }
                        }
                    });

                    // Task Priority Chart
                    const taskPriorityData = {!! json_encode($this->getTaskPriorityDistribution()) !!};
                    new Chart(document.getElementById('taskPriorityChart'), {
                        type: 'bar',
                        data: {
                            labels: ['Low', 'Medium', 'High'],
                            datasets: [{
                                label: 'Tasks',
                                data: [taskPriorityData.low, taskPriorityData.medium, taskPriorityData.high],
                                backgroundColor: ['#34d399', '#f59e0b', '#ef4444'],
                                borderRadius: 4,
                                borderSkipped: false
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } }
                            }
                        }
                    });

                    // Lead Status Chart
                    const leadStatusData = {!! json_encode($this->getLeadStatusDistribution()) !!};
                    new Chart(document.getElementById('leadStatusChart'), {
                        type: 'doughnut',
                        data: {
                            labels: ['New', 'Contacted', 'Qualified', 'Proposal'],
                            datasets: [{
                                data: [leadStatusData.new, leadStatusData.contacted, leadStatusData.qualified, leadStatusData.proposal],
                                backgroundColor: ['#6366f1', '#06b6d4', '#a855f7', '#f97316'],
                                borderColor: 'rgba(255, 255, 255, 0.1)',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: { padding: 15, font: { size: 12 }, color: 'var(--text-secondary)' }
                                }
                            }
                        }
                    });

                    // Activity Trend Chart
                    const activityTrendData = {!! json_encode($this->getDailyActivityTrend()) !!};
                    new Chart(document.getElementById('activityTrendChart'), {
                        type: 'line',
                        data: {
                            labels: Object.keys(activityTrendData),
                            datasets: [{
                                label: 'Daily Activities',
                                data: Object.values(activityTrendData),
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: '#10b981',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: { labels: { color: 'var(--text-secondary)', font: { size: 12 } } }
                            },
                            scales: {
                                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                                x: { grid: { color: 'rgba(0,0,0,0.05)' } }
                            }
                        }
                    });
                });
            </script>
        @endpush
    @endif
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; margin-bottom: 32px;">
        <!-- Recent Customers List Widget -->
        <div class="recent-activity" style="margin-bottom: 0;">
            <div class="section-header" style="margin-bottom: 16px;">
                <h2 style="font-size: 16px;"><i class="fas fa-user-clock"
                        style="color: var(--indigo); margin-right: 8px;"></i>Recent Added Customers</h2>
                <a href="{{ route('admin.customers') }}" class="nav-item-shortcut"
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
                        @forelse ($this->getRecentCustomers() as $customer)
                            <tr>
                                <td><strong>{{ $customer->full_name }}</strong></td>
                                <td>{{ $customer->company ?? 'N/A' }}</td>
                                <td><span class="status-badge {{ $customer->status }}"
                                        style="font-size: 11px; padding: 2px 8px;text-transform: capitalize">{{ $customer->status }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align: center; color: var(--text-secondary);">No customers found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Upcoming Urgent Tasks Widget -->
        <div class="recent-activity" style="margin-bottom: 0;">
            <div class="section-header" style="margin-bottom: 16px;">
                <h2 style="font-size: 16px;"><i class="fas fa-calendar-alt"
                        style="color: var(--teal); margin-right: 8px;"></i>Upcoming Pending Tasks</h2>
                <a href="{{ route('admin.tasks') }}" class="nav-item-shortcut"
                    style="color: var(--teal); font-size: 13px; text-decoration: none; font-weight: 600;">View All</a>
            </div>
            <div id="dashboardUpcomingTasksList" style="display: flex; flex-direction: column; gap: 10px;">
                @forelse ($this->getUpcomingTasks() as $task)
                    <div
                        style="background-color: var(--bg-primary); padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; font-size: 13px;">
                        <div>
                            <div style="font-weight: 600; color: var(--text-primary);"><i class="fas fa-circle"
                                    style="color: var(--danger); font-size: 8px; margin-right: 6px; vertical-align: middle;"></i>{{ $task->title }}
                            </div>
                            <small style="color: var(--text-secondary); font-size: 11px;"><i class="fas fa-user"
                                    style="font-size: 9px; margin-right: 4px;"></i>{{ $task->creator->first_name ?? 'Unknown' }}
                                {{ $task->creator->last_name ?? '' }}</small>
                        </div>
                        <div
                            style="font-size: 11px; font-weight: 600; background-color: var(--hover-bg); padding: 3px 8px; border-radius: 4px; color: var(--text-secondary);">
                            <i class="far fa-clock"
                                style="margin-right: 4px;"></i>{{$task->due_dateTime->format('M j, Y') }}
                        </div>
                    </div>
                @empty
                    <div>
                        <div style="text-align: center; color: var(--text-secondary);">
                            No pending task
                        </div>
                    </div>
                @endforelse
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
                <button class="btn btn-secondary btn-sm" id="clearActivityLogBtn" wire:click="clearActivities()"
                    title="Clear Timeline History">
                    <i class="fas fa-eraser"></i> Clear
                </button>
            </div>
        </div>
        <div id="recentActivityList" class="activity-list timeline-style">
            @forelse($this->getRecentActivities() as $activity)
                <div class="activity-item">
                    <div class="activity-icon task">
                        <i class="fas {{ $activity->icon }}"></i>
                    </div>
                    <div class="activity-content">
                        <p>{{ $activity->message }}</p>
                        <span>{{ $activity->created_at->format('M j, h:i A') }}</span>
                    </div>
                </div>
            @empty
                <div>
                    <div style="text-align: center; color: var(--text-secondary);">
                        No Activity
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>