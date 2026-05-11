<?php

use Livewire\Component;
use App\Models\Activity;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use App\Models\User;
use App\Models\Customer;
use App\Models\Lead;


new class extends Component {
    public $isTaskModalOpen = false;
    public $isTaskEditMode = false;
    public $isTaskDeleteConfirmationModal = false;
    public $isShowDeleteConfirmationModal = false;

    //Task Realted To Select State
    public $isTaskRelatedTo = null;

    public $taskId, $taskTitle, $taskDescription, $assignTo, $taskDueDate, $taskPriority, $taskRelatedCustomer, $taskRelatedLead;

    //Delete Confirmation Modal
    public $deleteId, $deleteName, $deleteMethod;

    public function openDeleteConfirmationModal($Id, $name = null, $method)
    {
        $this->deleteId = $Id;
        $this->deleteName = $name;
        $this->deleteMethod = $method;
        $this->isShowDeleteConfirmationModal = true;
    }

    public function closeDeleteConfirmationModal()
    {
        $this->deleteId = $this->deleteName = $this->deleteMethod = null;
        $this->isShowDeleteConfirmationModal = false;
    }

    public function openTaskModal($taskId = null)
    {
        if ($taskId) {
            $task = Task::findorFail($taskId);

            $this->taskId = $task->id;
            $this->taskTitle = $task->title;
            $this->taskDescription = $task->description;
            $this->taskDueDate = $task->due_dateTime->format('Y-m-d\TH:i');
            $this->taskPriority = $task->priority;
            if ($task->customer) {
                $this->isTaskRelatedTo = 'customer';
                $this->taskRelatedCustomer = $task->customer;
            } elseif ($task->lead) {
                $this->isTaskRelatedTo = 'lead';
                $this->taskRelatedLead = $task->lead;
            } else {
                $this->isTaskRelatedTo = null;
            }

            $this->isTaskEditMode = true;

        } else {

            $this->taskId = $this->taskDescription = $this->assignTo = $this->taskTitle = $this->taskDueDate = $this->isTaskRelatedTo = $this->taskPriority = $this->taskRelatedCustomer = $this->taskRelatedLead = null;
            $this->isTaskEditMode = false;
        }

        $this->isTaskModalOpen = true;
    }

    public function closeTaskModal()
    {
        $this->taskId = $this->taskDescription = $this->taskTitle = $this->assignTo = $this->taskDueDate = $this->isTaskRelatedTo = $this->taskPriority = $this->taskRelatedCustomer = $this->taskRelatedLead = null;
        $this->isTaskModalOpen = false;
    }

    public function handleTaskRelatedToSelect($value)
    {
        $this->isTaskRelatedTo = $value;
    }

    public function allCustomers()
    {
        return Customer::orderBy('created_at', 'desc')->get();
    }

    public function allLeads()
    {
        return Lead::where('converted', false)->orderBy('created_at', 'desc')->get();
    }

    public function allAgents()
    {
        return User::where('role', 'agent')->orderBy('created_at', 'desc')->get();
    }

    public function allTasks()
    {
        return Task::where('assign_To', auth()->user()->id)->orWhere('created_by', auth()->user()->id)->orderBy('created_at', 'desc')->get();
    }

    public function createTask()
    {
        $taskData = $this->validate([
            'taskTitle' => 'required|string|max:255',
            'taskDescription' => 'nullable|string|max:300',
            'taskDueDate' => 'required|date_format:Y-m-d\TH:i',
            'taskPriority' => 'required|in:low,medium,high',
            'assignTo' => 'nullable|exists:users,id',
            'taskRelatedCustomer' => 'nullable|exists:customers,id',
            'taskRelatedLead' => 'nullable|exists:leads,id',
        ], [
            'taskTitle.required' => 'Enter a task title.'
        ]);

        $created = Task::create([
            'title' => $taskData['taskTitle'],
            'description' => $taskData['taskDescription'],
            'due_dateTime' => $taskData['taskDueDate'],
            'priority' => $taskData['taskPriority'],
            'assign_To' => $taskData['assignTo'] ?? null,
            'customer' => $taskData['taskRelatedCustomer'] ?? null,
            'lead' => $taskData['taskRelatedLead'] ?? null,
            'created_by' => auth()->user()->id,
            'updated_by' => auth()->user()->id,
        ]);

        if ($created) {
            Activity::create([
                'type' => 'task_created',
                'message' => 'Task created: ' . $created->title,
                'icon' => 'fa-tasks',
                'user_id' => Auth::id(),
            ]);

            $this->dispatch(
                'notify',
                type: 'success',
                message: 'Task created successfully.'
            );
        } else {
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'Failed to create task. Please try again.'
            );
        }

        $this->closeTaskModal();
    }

    public function updateTask()
    {
        $taskData = $this->validate([
            'taskTitle' => 'required|string|max:255',
            'taskDescription' => 'nullable|string|max:300',
            'taskDueDate' => 'required|date_format:Y-m-d\TH:i',
            'taskPriority' => 'required|in:low,medium,high',
            'assign_To' => $taskData['assignTo'] ?? null,
            'assignTo' => 'nullable|exists:users,id',
            'taskRelatedCustomer' => 'nullable|exists:customers,id',
            'taskRelatedLead' => 'nullable|exists:leads,id',
        ], [
            'taskTitle.required' => 'Enter a task title.'
        ]);

        $task = Task::find($this->taskId);

        if (!$task) {
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'Task not found.'
            );

            return;
        }

        $task->title = $taskData['taskTitle'];
        $task->description = $taskData['taskDescription'];
        $task->due_dateTime = $taskData['taskDueDate'];
        $task->priority = $taskData['taskPriority'];
        $task->updated_by = auth()->user()->id;

        if ($this->isTaskRelatedTo === 'customer') {
            $task->customer = $taskData['taskRelatedCustomer'] ?? null;
            $task->lead = null;
        } elseif ($this->isTaskRelatedTo === 'lead') {
            $task->lead = $taskData['taskRelatedLead'] ?? null;
            $task->customer = null;
        } else {
            $task->customer = null;
            $task->lead = null;
        }

        if ($task->save()) {
            Activity::create([
                'type' => 'task_updated',
                'message' => 'Task updated: ' . $task->title,
                'icon' => 'fa-edit',
                'user_id' => Auth::id(),
            ]);

            $this->dispatch(
                'notify',
                type: 'success',
                message: 'Task updated successfully.'
            );
        } else {
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'Failed to update task. Please try again.'
            );
        }

        $this->closeTaskModal();
    }

    public function deleteTask()
    {
        $task = Task::findorFail($this->deleteId);

        if ($task->delete()) {
            Activity::create([
                'type' => 'task_deleted',
                'message' => 'Task deleted: ' . $task->title,
                'icon' => 'fa-trash',
                'user_id' => Auth::id(),
            ]);

            $this->dispatch(
                'notify',
                type: 'success',
                message: 'Task deleted successfully.'
            );
        } else {
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'Failed to delete task. Please try again.'
            );
        }

        $this->closeDeleteConfirmationModal();
    }

    public function taskCompleted($id)
    {
        $task = Task::find($id);

        if (!$task) {
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'Task not found.'
            );

            return;
        }

        $task->completed = !$task->completed;
        $task->updated_by = auth()->user()->id;

        if ($task->save()) {
            Activity::create([
                'type' => 'task_status_changed',
                'message' => $task->completed ? 'Task completed: ' . $task->title : 'Task reopened: ' . $task->title,
                'icon' => $task->completed ? 'fa-check-circle' : 'fa-redo',
                'user_id' => Auth::id(),
            ]);

            $this->dispatch(
                'notify',
                type: 'success',
                message: $task->completed ? 'Task marked as completed.' : 'Task Reopened.'
            );
        } else {
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'Unable to update task status. Please try again.'
            );
        }
    }

};
?>

<div>
    <!-- Tasks Section -->
    <div id="tasks">
        <div class="section-header">
            <h1 class="section-title">Tasks & Follow-Ups</h1>
            <button class="btn btn-primary" id="addTaskBtn" wire:click="openTaskModal()">
                <i class="fas fa-plus"></i> Add Task
            </button>
        </div>

        <!-- Tasks List -->
        <div class="tasks-container">
            <div class="tasks-list" id="tasksList">
                @foreach ($this->allTasks() as $task)
                    <div class="task-item {{ $task->completed == true ? 'completed' : '' }}">
                        <div class="task-checkbox {{ $task->completed == true ? 'checked' : '' }}"
                            wire:click="taskCompleted({{ $task->id }})">
                            @if ($task->completed == true)
                                <i class="fas fa-check"></i>
                            @endif
                        </div>
                        <div class="task-info">
                            <div class="task-title">{{ $task->title }}</div>
                            <div class="task-description">{{ $task->description }}</div>
                            <div class="task-meta">
                                <span><i class="fas fa-calendar"></i> {{$task->due_dateTime->format('M j, Y') }} </span>
                                <span><i class="fas fa-user"></i> {{ $task->creator->first_name }}
                                    {{ $task->creator->last_name  }}</span>
                                <span>Assigned To: {{ $task->assigned->first_name ?? 'N/A' }}
                                    {{ $task->assigned->last_name ?? '' }}</span>
                            </div>
                        </div>
                        <span class="task-priority {{ $task->priority }}">{{ $task->priority }}</span>
                        <div class="task-actions">
                            <button class="action-btn edit" wire:click="openTaskModal({{ $task->id }})" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn delete"
                                wire:click="openDeleteConfirmationModal({{ $task->id }}, 'Task', 'deleteTask')"
                                title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Add Task Modal -->
    <div class="modal {{ $isTaskModalOpen ? 'active' : '' }}" id="taskModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="taskModalTitle">Add Task</h2>
                <button class="close-btn" id="closeTaskModal" wire:click="closeTaskModal()">&times;</button>
            </div>
            <form id="taskForm" wire:submit="{{ $isTaskEditMode ? 'updateTask()' : 'createTask()' }}">
                @if ($isTaskEditMode)
                    <input type="hidden" id="taskId" wire:model="taskId">
                @endif
                <div class="form-group">
                    <label for="taskTitle">Task Title *</label>
                    <input type="text" id="taskTitle" wire:model="taskTitle" placeholder="Enter task title">
                    @error('taskTitle')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="taskDescription">Description</label>
                    <textarea id="taskDescription" rows="3" wire:model="taskDescription"
                        placeholder="Add task description..."></textarea>
                    @error('taskDescription')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="taskDueDate">Due Date *</label>
                    <input type="datetime-local" id="taskDueDate" wire:model="taskDueDate">
                    @error('taskDueDate')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="taskPriority">Priority</label>
                    <select id="taskPriority" wire:model="taskPriority">
                        <option value="">Select ...</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                    @error('taskPriority')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                @if (auth()->user()->role != 'agent')
                    <div class="form-group">
                        <label for="assignTo">Assign Task To</label>
                        <select id="assignTo" wire:model="assignTo">
                            <option value="">Select ...</option>
                            @foreach ($this->allAgents() as $agent)
                                <option value="{{ $agent->id }}">{{ $agent->id }} . {{ $agent->first_name . ' ' . $agent->last_name  }}</option>
                            @endforeach
                        </select>
                        @error('assignTo')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                @endif
                <div class="form-group">
                    <label for="taskRelatedTo">Related To <small>(Optional)</small></label>
                    <select id="taskRelatedTo" wire:change="handleTaskRelatedToSelect($event.target.value)">
                        <option value="">Select ....</option>
                        <option value="customer">Customer</option>
                        <option value="lead">Lead</option>
                    </select>
                    @error('taskRelatedTo')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                @if($isTaskRelatedTo == 'customer')
                    <div class="form-group">
                        <label for="taskRelatedCustomer">Select Customer</small></label>
                        <select id="taskRelatedCustomer" wire:model="taskRelatedCustomer">
                            <option value="">Select ....</option>
                            @foreach ($this->allCustomers() as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->id }} . {{ $customer->full_name }}</option>
                            @endforeach
                        </select>
                        @error('taskRelatedCustomer')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                @elseif($isTaskRelatedTo == 'lead')
                    <div class="form-group">
                        <label for="taskRelatedLead">Select Lead</small></label>
                        <select id="taskRelatedLead" wire:model="taskRelatedLead">
                            <option value="">Select ....</option>
                            @foreach ($this->allLeads() as $lead)
                                <option value="{{ $lead->id }}">{{ $lead->id }} . {{ $lead->full_name }}</option>
                            @endforeach
                        </select>
                        @error('taskRelatedLead')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                @endif
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancelTaskBtn"
                        wire:click="closeTaskModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Task</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    @include('components.delete-confirmation-modal', [
        'isShowDeleteConfirmationModal' => $isShowDeleteConfirmationModal,
        'deleteName' => $deleteName,
        'deleteMethod' => $deleteMethod,
        'deleteId' => $deleteId,
    ])
</div>