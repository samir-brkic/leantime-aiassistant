<?php
/**
 * AI Assistant Quick Capture Template
 */

defined('RESTRICTED') or die('Restricted access');

foreach ($__data as $var => $val) {
    $$var = $val;
}

$projects = $tpl->get('projects') ?? [];
$isConfigured = $tpl->get('isConfigured') ?? false;
?>

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-bolt"></span></div>
    <div class="pagetitle">
        <h1><?php echo $tpl->__('aiassistant.quickcapture.headline'); ?></h1>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">
        <?php echo $tpl->displayInlineNotification(); ?>
        
        <?php if (!$isConfigured): ?>
            <div class="center padding-md">
                <span class="fa fa-exclamation-triangle" style="color: #f0ad4e;"></span>
                <?php echo $tpl->__('aiassistant.messages.error.not_configured'); ?>
                <a href="<?php echo BASE_URL; ?>/aiAssistant/settings" class="btn btn-primary" style="margin-left: 10px;">
                    <?php echo $tpl->__('aiassistant.menu.settings'); ?>
                </a>
            </div>
        <?php endif; ?>

        <!-- Input Section -->
        <div class="headertitle">
            <span class="fa fa-edit"></span> <?php echo $tpl->__('aiassistant.quickcapture.input.label'); ?>
        </div>

        <div class="contentInner">
            <form class="stdform">
                <div class="par">
                    <label><?php echo $tpl->__('aiassistant.quickcapture.project.label'); ?></label>
                    <select id="project-select" class="form-control" <?php echo !$isConfigured ? 'disabled' : ''; ?>>
                        <?php if (count($projects) > 1): ?>
                            <option value="">-- Select Project --</option>
                        <?php endif; ?>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?php echo $project['id']; ?>" 
                                <?php echo (count($projects) === 1) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($project['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="par">
                    <label><?php echo $tpl->__('aiassistant.quickcapture.input.label'); ?></label>
                    <textarea id="note-input" class="form-control" rows="10" 
                              style="width: 100%; max-width: 100%;"
                              placeholder="<?php echo $tpl->__('aiassistant.quickcapture.input.placeholder'); ?>"
                              <?php echo !$isConfigured ? 'disabled' : ''; ?>></textarea>
                </div>

                <div class="par">
                    <button type="button" id="analyze-btn" class="btn btn-primary" 
                            onclick="analyzeText()" <?php echo !$isConfigured ? 'disabled' : ''; ?>>
                        <span class="fa fa-robot"></span> <?php echo $tpl->__('aiassistant.quickcapture.analyze.button'); ?>
                    </button>
                    <div id="analyze-status" style="display: inline-block; margin-left: 10px;"></div>
                </div>
            </form>
        </div>

        <!-- Preview Section -->
        <div class="headertitle" style="margin-top: 30px;">
            <span class="fa fa-eye"></span> <?php echo $tpl->__('aiassistant.quickcapture.preview.headline'); ?>
        </div>

        <div class="contentInner">
            <div id="preview-container">
                <div class="center padding-md" id="preview-empty" style="color: #999;">
                    <span class="fa fa-magic fa-3x" style="opacity: 0.3;"></span>
                    <p><?php echo $tpl->__('aiassistant.quickcapture.preview.empty'); ?></p>
                </div>
                
                <div id="preview-content" style="display: none;"></div>
            </div>

            <div id="create-section" style="display: none; margin-top: 20px;">
                <button type="button" id="create-btn" class="btn btn-success" onclick="createTasks()">
                    <span class="fa fa-check-circle"></span> <?php echo $tpl->__('aiassistant.quickcapture.create.button'); ?>
                </button>
                <div id="create-status" style="display: inline-block; margin-left: 10px;"></div>
            </div>
        </div>
    </div>
</div>

<style>
/* Leantime Native Design - Clean & Professional */
.preview-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    transition: border-color 0.2s, box-shadow 0.2s;
}

.preview-card:hover {
    border-color: #3B82F6;
    box-shadow: 0 4px 6px rgba(59, 130, 246, 0.1);
}

.preview-card-header {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e5e7eb;
}

.preview-card-header .icon {
    width: 36px;
    height: 36px;
    background: #3B82F6;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    color: white;
    font-size: 16px;
}

.preview-card-header h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 600;
    color: #374151;
}

.preview-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .preview-grid {
        grid-template-columns: 1fr;
    }
}

.form-group-modern {
    margin-bottom: 0;
}

.form-group-modern label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
    font-size: 13px;
}

.form-control-modern {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.2s, box-shadow 0.2s;
    box-sizing: border-box;
}

.form-control-modern:focus {
    outline: none;
    border-color: #3B82F6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-control-modern:hover {
    border-color: #9ca3af;
}

.category-dropdown {
    font-size: 15px;
    font-weight: 500;
    padding: 10px 12px;
}

textarea.form-control-modern {
    min-height: 120px;
    resize: vertical;
    font-family: inherit;
}

.subtask-list-modern {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.subtask-item-modern {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f9fafb;
    padding: 10px 12px;
    border-radius: 6px;
    border-left: 3px solid #3B82F6;
    transition: background 0.2s;
}

.subtask-item-modern:hover {
    background: #f3f4f6;
}

.subtask-item-modern .drag-handle {
    cursor: move;
    color: #9ca3af;
    font-size: 14px;
}

.subtask-item-modern input {
    flex: 1;
    border: none;
    background: transparent;
    padding: 6px;
    font-size: 14px;
    border-radius: 4px;
    transition: background 0.2s;
}

.subtask-item-modern input:focus {
    background: white;
    outline: none;
}

.subtask-item-modern .btn-delete {
    padding: 5px 10px;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.2s;
    font-size: 12px;
}

.subtask-item-modern .btn-delete:hover {
    background: #dc2626;
}

.btn-add-modern {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: #3B82F6;
    color: white;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
    font-size: 14px;
}

.btn-add-modern:hover {
    background: #2563eb;
}

.tags-container-modern {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    min-height: 44px;
    padding: 10px;
    background: #f9fafb;
    border-radius: 6px;
    margin-bottom: 12px;
}

.tag-item-modern {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: #3B82F6;
    color: white;
    border-radius: 16px;
    font-size: 13px;
    font-weight: 500;
    transition: background 0.2s;
}

.tag-item-modern:hover {
    background: #2563eb;
}

.tag-item-modern .tag-remove {
    cursor: pointer;
    background: rgba(255,255,255,0.2);
    width: 16px;
    height: 16px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    transition: background 0.2s;
}

.tag-item-modern .tag-remove:hover {
    background: rgba(255,255,255,0.3);
}

.tag-input-group {
    display: flex;
    gap: 10px;
    align-items: center;
}

.tag-input-group input {
    flex: 1;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.2s;
}

.tag-input-group input:focus {
    outline: none;
    border-color: #3B82F6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.empty-state {
    text-align: center;
    padding: 24px;
    color: #9ca3af;
    font-style: italic;
    font-size: 14px;
}

.empty-state .fa {
    font-size: 32px;
    margin-bottom: 8px;
    opacity: 0.3;
}
</style>

<script>
let currentAIResponse = null;

function analyzeText() {
    const text = document.getElementById('note-input').value.trim();
    const projectId = document.getElementById('project-select').value;
    const statusEl = document.getElementById('analyze-status');
    const analyzeBtn = document.getElementById('analyze-btn');
    
    if (!text) {
        alert('<?php echo $tpl->__('aiassistant.quickcapture.input.placeholder'); ?>');
        return;
    }
    
    if (!projectId) {
        alert('Please select a project first');
        return;
    }
    
    // Show loading
    analyzeBtn.disabled = true;
    statusEl.innerHTML = '<span class="fa fa-spinner fa-spin"></span> AI is analyzing...';
    
    // Hide preview
    document.getElementById('preview-empty').style.display = 'none';
    document.getElementById('preview-content').style.display = 'none';
    document.getElementById('create-section').style.display = 'none';
    
    jQuery.ajax({
        url: '<?php echo BASE_URL; ?>/AIAssistant/quickCapture/analyze',
        method: 'POST',
        data: { text: text },
        dataType: 'json',
        success: function(response) {
            analyzeBtn.disabled = false;
            
            if (response.success) {
                currentAIResponse = response.rawResponse;
                renderPreview(response.preview);
                statusEl.innerHTML = '<span style="color: green;"><span class="fa fa-check"></span> Analysis complete!</span>';
                document.getElementById('create-section').style.display = 'block';
            } else {
                statusEl.innerHTML = '<span style="color: red;"><span class="fa fa-times"></span> ' + response.message + '</span>';
                document.getElementById('preview-empty').style.display = 'block';
            }
        },
        error: function(xhr) {
            analyzeBtn.disabled = false;
            statusEl.innerHTML = '<span style="color: red;"><span class="fa fa-times"></span> Connection failed</span>';
            document.getElementById('preview-empty').style.display = 'block';
        }
    });
}

function renderPreview(preview) {
    const container = document.getElementById('preview-content');
    
    // Store current preview data globally for editing
    window.editablePreviewData = preview;
    
    let html = '';
    
    // Title & Description Card
    html += '<div class="preview-card">';
    html += '<div class="preview-card-header">';
    html += '<div class="icon"><i class="fa fa-pencil"></i></div>';
    html += '<h4>Title & Description</h4>';
    html += '</div>';
    
    html += '<div class="form-group-modern">';
    html += '<label>Task Title</label>';
    html += '<input type="text" id="edit-title" class="form-control-modern" value="' + escapeHtml(preview.title) + '" placeholder="Enter task title...">';
    html += '</div>';
    
    html += '<div class="form-group-modern" style="margin-top:20px;">';
    html += '<label>Description</label>';
    html += '<textarea id="edit-description" class="form-control-modern" placeholder="Add detailed description...">' + escapeHtml(preview.description || '') + '</textarea>';
    html += '</div>';
    html += '</div>';
    
    // Category & Priority Card
    html += '<div class="preview-card">';
    html += '<div class="preview-card-header">';
    html += '<div class="icon"><i class="fa fa-sliders"></i></div>';
    html += '<h4>Category & Priority</h4>';
    html += '</div>';
    
    html += '<div class="preview-grid">';
    html += '<div class="form-group-modern">';
    html += '<label>Category</label>';
    html += '<select id="edit-category" class="form-control-modern category-dropdown"></select>';
    html += '</div>';
    
    html += '<div class="form-group-modern">';
    html += '<label>Priority Level</label>';
    html += '<select id="edit-priority" class="form-control-modern">';
    html += '<option value="1">🔥 Critical</option>';
    html += '<option value="2">⬆️ High</option>';
    html += '<option value="3">➖ Normal</option>';
    html += '<option value="4">⬇️ Low</option>';
    html += '<option value="5">⏬ Lowest</option>';
    html += '</select>';
    html += '</div>';
    html += '</div>';
    html += '</div>';
    
    // Deadline Card
    html += '<div class="preview-card">';
    html += '<div class="preview-card-header">';
    html += '<div class="icon"><i class="fa fa-calendar"></i></div>';
    html += '<h4>Deadline</h4>';
    html += '</div>';
    html += '<div class="form-group-modern">';
    html += '<label>Due Date</label>';
    html += '<input type="date" id="edit-deadline" class="form-control-modern" value="' + (preview.deadline || '') + '">';
    html += '</div>';
    html += '</div>';
    
    // Subtasks Card
    html += '<div class="preview-card">';
    html += '<div class="preview-card-header">';
    html += '<div class="icon"><i class="fa fa-list-ul"></i></div>';
    html += '<h4>Subtasks</h4>';
    html += '</div>';
    html += '<div id="subtasks-container"></div>';
    html += '<button type="button" class="btn-add-modern" onclick="addSubtask()" style="margin-top:15px;">';
    html += '<i class="fa fa-plus"></i> Add Subtask</button>';
    html += '</div>';
    
    // Tags Card
    html += '<div class="preview-card">';
    html += '<div class="preview-card-header">';
    html += '<div class="icon"><i class="fa fa-tags"></i></div>';
    html += '<h4>Tags</h4>';
    html += '</div>';
    html += '<div id="tags-container" class="tags-container-modern"></div>';
    html += '<div class="tag-input-group">';
    html += '<input type="text" id="new-tag-input" placeholder="Enter tag name..." onkeypress="if(event.key===\'Enter\'){addTag();return false;}">';
    html += '<button type="button" class="btn-add-modern" onclick="addTag()">';
    html += '<i class="fa fa-plus"></i> Add</button>';
    html += '</div>';
    html += '</div>';
    
    container.innerHTML = html;
    container.style.display = 'block';
    
    // Initialize interactive elements
    loadCategoriesDropdown(preview.category);
    document.getElementById('edit-priority').value = preview.priority;
    renderSubtasks(preview.subtasks || []);
    renderTags(preview.tags || []);
}

function loadCategoriesDropdown(selectedCategory) {
    const categories = [
        {name: 'bestellung', emoji: '🛒', label: 'Bestellung', color: '#3B82F6'},
        {name: 'anfrage', emoji: '❓', label: 'Anfrage', color: '#10B981'},
        {name: 'reklamation', emoji: '⚠️', label: 'Reklamation', color: '#EF4444'},
        {name: 'angebot', emoji: '💰', label: 'Angebot', color: '#F59E0B'},
        {name: 'rechnung', emoji: '📄', label: 'Rechnung', color: '#8B5CF6'},
        {name: 'lagerpruefung', emoji: '📦', label: 'Lager', color: '#FBBF24'},
        {name: 'followup', emoji: '🔔', label: 'Follow-up', color: '#06B6D4'},
        {name: 'lieferant', emoji: '🏭', label: 'Lieferant', color: '#6B7280'}
    ];
    
    const selectEl = document.getElementById('edit-category');
    selectEl.innerHTML = '';
    
    categories.forEach(cat => {
        const option = document.createElement('option');
        option.value = cat.name;
        option.textContent = cat.emoji + ' ' + cat.label;
        option.setAttribute('data-color', cat.color);
        if (cat.name === selectedCategory) {
            option.selected = true;
        }
        selectEl.appendChild(option);
    });
    
    // Update dropdown background color based on selection
    updateCategoryColor();
    selectEl.addEventListener('change', updateCategoryColor);
}

function updateCategoryColor() {
    const selectEl = document.getElementById('edit-category');
    const selectedOption = selectEl.options[selectEl.selectedIndex];
    const color = selectedOption.getAttribute('data-color');
    
    if (color) {
        selectEl.style.backgroundColor = color;
        selectEl.style.color = 'white';
        selectEl.style.fontWeight = '600';
    }
}

function renderSubtasks(subtasks) {
    window.editableSubtasks = subtasks || [];
    const container = document.getElementById('subtasks-container');
    
    if (window.editableSubtasks.length === 0) {
        container.innerHTML = '<div class="empty-state"><div class="fa fa-inbox"></div><p>No subtasks yet. Click "Add Subtask" to create one.</p></div>';
        return;
    }
    
    let html = '<div class="subtask-list-modern">';
    window.editableSubtasks.forEach((subtask, index) => {
        html += '<div class="subtask-item-modern">';
        html += '<span class="drag-handle"><i class="fa fa-bars"></i></span>';
        html += '<input type="text" class="subtask-input" data-index="' + index + '" value="' + escapeHtml(subtask) + '" placeholder="Subtask description...">';
        html += '<button class="btn-delete" onclick="removeSubtask(' + index + ')"><i class="fa fa-trash"></i></button>';
        html += '</div>';
    });
    html += '</div>';
    
    container.innerHTML = html;
    
    // Add change listeners
    document.querySelectorAll('.subtask-input').forEach(input => {
        input.addEventListener('change', function() {
            const idx = parseInt(this.getAttribute('data-index'));
            window.editableSubtasks[idx] = this.value;
        });
    });
}

function addSubtask() {
    if (!window.editableSubtasks) {
        window.editableSubtasks = [];
    }
    window.editableSubtasks.push('New subtask');
    renderSubtasks(window.editableSubtasks);
    
    // Focus new input
    setTimeout(() => {
        const inputs = document.querySelectorAll('.subtask-input');
        if (inputs.length > 0) {
            inputs[inputs.length - 1].focus();
            inputs[inputs.length - 1].select();
        }
    }, 100);
}

function removeSubtask(index) {
    window.editableSubtasks.splice(index, 1);
    renderSubtasks(window.editableSubtasks);
}

function renderTags(tags) {
    window.editableTags = tags || [];
    const container = document.getElementById('tags-container');
    
    if (window.editableTags.length === 0) {
        container.innerHTML = '<div class="empty-state" style="padding:20px;"><div class="fa fa-tag"></div><p>No tags yet</p></div>';
        return;
    }
    
    let html = '';
    window.editableTags.forEach((tag, index) => {
        html += '<div class="tag-item-modern">';
        html += '<span>' + escapeHtml(tag) + '</span>';
        html += '<span class="tag-remove" onclick="removeTag(' + index + ')"><i class="fa fa-times"></i></span>';
        html += '</div>';
    });
    
    container.innerHTML = html;
}

function addTag() {
    const input = document.getElementById('new-tag-input');
    const tagName = input.value.trim();
    
    if (!tagName) {
        return;
    }
    
    if (!window.editableTags) {
        window.editableTags = [];
    }
    
    // Avoid duplicates
    if (!window.editableTags.includes(tagName)) {
        window.editableTags.push(tagName);
        renderTags(window.editableTags);
    }
    
    input.value = '';
    input.focus();
}

function removeTag(index) {
    window.editableTags.splice(index, 1);
    renderTags(window.editableTags);
}

function getEditedPreviewData() {
    // Collect all edited data from the form
    return {
        title: document.getElementById('edit-title').value,
        category: document.getElementById('edit-category').value,
        priority: parseInt(document.getElementById('edit-priority').value),
        description: document.getElementById('edit-description').value,
        deadline: document.getElementById('edit-deadline').value,
        subtasks: window.editableSubtasks || [],
        tags: window.editableTags || []
    };
}

function createTasks() {
    const projectId = document.getElementById('project-select').value;
    const statusEl = document.getElementById('create-status');
    const createBtn = document.getElementById('create-btn');
    
    if (!currentAIResponse) {
        alert('Please analyze text first');
        return;
    }
    
    if (!projectId) {
        alert('Please select a project');
        return;
    }
    
    // Get edited data
    const editedData = getEditedPreviewData();
    
    // Merge with original AI response
    const finalData = Object.assign({}, currentAIResponse, editedData);
    
    createBtn.disabled = true;
    statusEl.innerHTML = '<span class="fa fa-spinner fa-spin"></span> Creating tasks...';
    
    jQuery.ajax({
        url: '<?php echo BASE_URL; ?>/AIAssistant/quickCapture/createTasks',
        method: 'POST',
        data: {
            aiResponse: JSON.stringify(finalData),
            projectId: projectId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                statusEl.innerHTML = '<span style="color: green;"><span class="fa fa-check-circle"></span> ' + response.message + '</span>';
                
                // Redirect to task after 2 seconds
                setTimeout(function() {
                    window.location.href = '<?php echo BASE_URL; ?>/tickets/showTicket/' + response.mainTaskId;
                }, 2000);
            } else {
                createBtn.disabled = false;
                statusEl.innerHTML = '<span style="color: red;"><span class="fa fa-times"></span> ' + response.message + '</span>';
            }
        },
        error: function() {
            createBtn.disabled = false;
            statusEl.innerHTML = '<span style="color: red;"><span class="fa fa-times"></span> Error creating tasks</span>';
        }
    });
}

// Helper functions
function escapeHtml(text) {
    if (text == null || text === undefined) return '';
    text = String(text); // Convert to string
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

function nl2br(text) {
    if (text == null || text === undefined) return '';
    text = String(text); // Convert to string
    return text.replace(/\n/g, '<br>');
}
</script>
