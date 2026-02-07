<?php
/**
 * AI Assistant Settings Template - Modern Design
 */

defined('RESTRICTED') or exit('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val;
}

$settings = $tpl->get('settings') ?? [];
$provider = $settings['provider'] ?? 'ollama';
?>

<style>
/* Leantime Native Design - Settings */
.settings-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    transition: border-color 0.2s, box-shadow 0.2s;
}

.settings-card:hover {
    border-color: #3B82F6;
    box-shadow: 0 4px 6px rgba(59, 130, 246, 0.1);
}

.settings-card-header {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e5e7eb;
}

.settings-card-header .icon {
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

.settings-card-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #374151;
}

.settings-card-header .subtitle {
    font-size: 12px;
    color: #9ca3af;
    font-weight: 400;
    margin-top: 2px;
}

.form-group-settings {
    margin-bottom: 20px;
}

.form-group-settings label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
    font-size: 13px;
}

.form-group-settings .help-text {
    font-size: 12px;
    color: #9ca3af;
    margin-top: 6px;
    font-style: italic;
}

.input-modern {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.2s, box-shadow 0.2s;
    box-sizing: border-box;
    font-family: inherit;
}

.input-modern:focus {
    outline: none;
    border-color: #3B82F6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.input-modern:hover {
    border-color: #9ca3af;
}

textarea.input-modern {
    min-height: 180px;
    resize: vertical;
    font-family: 'Courier New', monospace;
    font-size: 13px;
    line-height: 1.6;
}

.provider-toggle {
    display: inline-flex;
    background: #f3f4f6;
    padding: 4px;
    border-radius: 6px;
    gap: 4px;
}

.provider-option {
    padding: 8px 20px;
    border: none;
    background: transparent;
    cursor: pointer;
    border-radius: 4px;
    font-weight: 500;
    font-size: 14px;
    color: #6b7280;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 6px;
}

.provider-option:hover {
    background: #e5e7eb;
    color: #374151;
}

.provider-option.active {
    background: #3B82F6;
    color: white;
}

.btn-modern {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    font-size: 14px;
    cursor: pointer;
    transition: background 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-modern.btn-primary {
    background: #3B82F6;
    color: white;
}

.btn-modern.btn-primary:hover {
    background: #2563eb;
}

.btn-modern.btn-secondary {
    background: #f3f4f6;
    color: #374151;
    border: 1px solid #d1d5db;
}

.btn-modern.btn-secondary:hover {
    background: #e5e7eb;
}

.btn-modern.btn-success {
    background: #10b981;
    color: white;
    font-size: 15px;
    padding: 12px 32px;
}

.btn-modern.btn-success:hover {
    background: #059669;
}

.btn-group {
    display: flex;
    gap: 10px;
    align-items: center;
}

.status-message {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
}

.status-message.loading {
    background: #fef3c7;
    color: #92400e;
}

.status-message.success {
    background: #d1fae5;
    color: #065f46;
}

.status-message.error {
    background: #fee2e2;
    color: #991b1b;
}

.grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

@media (max-width: 768px) {
    .grid-2 {
        grid-template-columns: 1fr;
    }
}

.notification-modern {
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
    font-size: 14px;
}

.notification-modern.success {
    background: #d1fae5;
    color: #065f46;
    border-left: 3px solid #10b981;
}

.notification-modern.error {
    background: #fee2e2;
    color: #991b1b;
    border-left: 3px solid #ef4444;
}
</style>

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-robot"></span></div>
    <div class="pagetitle">
        <h1><?php echo $tpl->__('aiassistant.settings.headline'); ?></h1>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">
        
        <?php if ($tpl->displayNotification()): ?>
            <div class="notification-modern success">
                <i class="fa fa-check-circle"></i>
                <?php echo $tpl->displayNotification(); ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="">
            
            <!-- Provider Selection Card -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="icon"><i class="fa fa-exchange"></i></div>
                    <div>
                        <h3><?php echo $tpl->__('aiassistant.settings.provider.label'); ?></h3>
                        <div class="subtitle">Choose your AI provider</div>
                    </div>
                    </div>
                    
                    <div class="provider-toggle">
                        <button type="button" class="provider-option <?php echo $provider === 'ollama' ? 'active' : ''; ?>" 
                                onclick="selectProvider('ollama')">
                            <i class="fa fa-server"></i>
                            <?php echo $tpl->__('aiassistant.settings.provider.ollama'); ?>
                        </button>
                        <button type="button" class="provider-option <?php echo $provider === 'openai' ? 'active' : ''; ?>" 
                                onclick="selectProvider('openai')">
                            <i class="fa fa-cloud"></i>
                            <?php echo $tpl->__('aiassistant.settings.provider.openai'); ?>
                        </button>
                    </div>
                    <input type="hidden" name="provider" id="provider-input" value="<?php echo $provider; ?>">
                </div>
                
                <!-- Ollama Settings Card -->
                <div id="ollama-section" class="settings-card" style="display: <?php echo $provider === 'ollama' ? 'block' : 'none'; ?>;">
                    <div class="settings-card-header">
                        <div class="icon"><i class="fa fa-server"></i></div>
                        <div>
                            <h3><?php echo $tpl->__('aiassistant.settings.ollama.headline'); ?></h3>
                            <div class="subtitle">Local AI server configuration</div>
                        </div>
                    </div>
                    
                    <div class="form-group-settings">
                        <label><?php echo $tpl->__('aiassistant.settings.ollama.url.label'); ?></label>
                        <input type="text" name="ollama_url" id="ollama_url" class="input-modern"
                               value="<?php echo htmlspecialchars($settings['ollama_url'] ?? 'http://192.168.200.40:11434'); ?>"
                               placeholder="<?php echo $tpl->__('aiassistant.settings.ollama.url.placeholder'); ?>" />
                    </div>

                    <div class="btn-group" style="margin-bottom: 20px;">
                        <button type="button" class="btn-modern btn-primary" onclick="loadOllamaModels()">
                            <i class="fa fa-download"></i>
                            <?php echo $tpl->__('aiassistant.settings.ollama.loadmodels.button'); ?>
                        </button>
                        <span id="load-models-status"></span>
                    </div>

                    <div class="form-group-settings">
                        <label><?php echo $tpl->__('aiassistant.settings.ollama.model.label'); ?></label>
                        <select name="ollama_model" id="ollama_model" class="input-modern">
                            <option value=""><?php echo $tpl->__('aiassistant.settings.ollama.model.placeholder'); ?></option>
                            <?php if (!empty($settings['ollama_model'])): ?>
                                <option value="<?php echo htmlspecialchars($settings['ollama_model']); ?>" selected>
                                    <?php echo htmlspecialchars($settings['ollama_model']); ?>
                                </option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="btn-group">
                        <button type="button" class="btn-modern btn-secondary" onclick="testOllamaConnection()">
                            <i class="fa fa-plug"></i>
                            <?php echo $tpl->__('aiassistant.settings.ollama.test.button'); ?>
                        </button>
                        <span id="ollama-test-status"></span>
                    </div>
                </div>

                <!-- OpenAI Settings Card -->
                <div id="openai-section" class="settings-card" style="display: <?php echo $provider === 'openai' ? 'block' : 'none'; ?>;">
                    <div class="settings-card-header">
                        <div class="icon"><i class="fa fa-cloud"></i></div>
                        <div>
                            <h3><?php echo $tpl->__('aiassistant.settings.openai.headline'); ?></h3>
                            <div class="subtitle">Cloud AI configuration</div>
                        </div>
                    </div>
                    
                    <div class="form-group-settings">
                        <label><?php echo $tpl->__('aiassistant.settings.openai.apikey.label'); ?></label>
                        <input type="password" name="openai_api_key" id="openai_api_key" class="input-modern"
                               value="<?php echo htmlspecialchars($settings['openai_api_key'] ?? ''); ?>"
                               placeholder="<?php echo $tpl->__('aiassistant.settings.openai.apikey.placeholder'); ?>" />
                    </div>

                    <div class="form-group-settings">
                        <label><?php echo $tpl->__('aiassistant.settings.openai.baseurl.label'); ?></label>
                        <input type="text" name="openai_base_url" id="openai_base_url" class="input-modern"
                               value="<?php echo htmlspecialchars($settings['openai_base_url'] ?? 'https://api.openai.com/v1'); ?>"
                               placeholder="<?php echo $tpl->__('aiassistant.settings.openai.baseurl.placeholder'); ?>" />
                    </div>

                    <div class="btn-group" style="margin-bottom: 20px;">
                        <button type="button" class="btn-modern btn-primary" onclick="loadOpenAIModels()">
                            <i class="fa fa-download"></i>
                            Load Models
                        </button>
                        <span id="load-openai-models-status"></span>
                    </div>

                    <div class="form-group-settings">
                        <label><?php echo $tpl->__('aiassistant.settings.openai.model.label'); ?></label>
                        <select name="openai_model" id="openai_model" class="input-modern">
                            <option value=""><?php echo $tpl->__('aiassistant.settings.openai.model.placeholder'); ?></option>
                            <?php if (!empty($settings['openai_model'])): ?>
                                <option value="<?php echo htmlspecialchars($settings['openai_model']); ?>" selected>
                                    <?php echo htmlspecialchars($settings['openai_model']); ?>
                                </option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="btn-group">
                        <button type="button" class="btn-modern btn-secondary" onclick="testOpenAIConnection()">
                            <i class="fa fa-plug"></i>
                            <?php echo $tpl->__('aiassistant.settings.openai.test.button'); ?>
                        </button>
                        <span id="openai-test-status"></span>
                    </div>
                </div>

                <!-- Timeout & System Prompt Card -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <div class="icon"><i class="fa fa-cogs"></i></div>
                        <div>
                            <h3>Advanced Settings</h3>
                            <div class="subtitle">Fine-tune behavior</div>
                        </div>
                    </div>
                    
                    <div class="grid-2">
                        <div class="form-group-settings">
                            <label><?php echo $tpl->__('aiassistant.settings.timeout.label'); ?></label>
                            <input type="number" name="timeout" class="input-modern"
                                   value="<?php echo htmlspecialchars($settings['timeout'] ?? '30'); ?>"
                                   min="10" max="300"
                                   placeholder="<?php echo $tpl->__('aiassistant.settings.timeout.placeholder'); ?>" />
                            <div class="help-text">Maximum wait time for AI response</div>
                        </div>
                    </div>
                </div>

                <!-- System Prompt Card -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <div class="icon"><i class="fa fa-code"></i></div>
                        <div>
                            <h3><?php echo $tpl->__('aiassistant.settings.prompt.headline'); ?></h3>
                            <div class="subtitle">Customize AI behavior</div>
                        </div>
                    </div>
                    
                    <div class="form-group-settings">
                        <label><?php echo $tpl->__('aiassistant.settings.prompt.label'); ?></label>
                        <textarea name="system_prompt" id="system_prompt" class="input-modern"><?php echo htmlspecialchars($tpl->get('system_prompt') ?? ''); ?></textarea>
                        <div class="help-text">
                            <?php echo $tpl->__('aiassistant.settings.prompt.help'); ?>
                        </div>
                    </div>

                    <button type="button" class="btn-modern btn-secondary" onclick="resetSystemPrompt()">
                        <i class="fa fa-undo"></i>
                        <?php echo $tpl->__('aiassistant.settings.prompt.reset'); ?>
                    </button>
                </div>

                <!-- Save Button -->
                <div class="settings-card" style="text-align: center;">
                    <button type="submit" class="btn-modern btn-success">
                        <i class="fa fa-save"></i>
                        <?php echo $tpl->__('aiassistant.settings.save.button'); ?>
                    </button>
                </div>
                
            </form>
    </div>
</div>

<script>
function selectProvider(provider) {
    document.querySelectorAll('.provider-option').forEach(el => el.classList.remove('active'));
    event.target.closest('.provider-option').classList.add('active');
    document.getElementById('provider-input').value = provider;
    toggleProviderSections();
}

function toggleProviderSections() {
    const provider = document.getElementById('provider-input').value;
    document.getElementById('ollama-section').style.display = provider === 'ollama' ? 'block' : 'none';
    document.getElementById('openai-section').style.display = provider === 'openai' ? 'block' : 'none';
}

function showStatus(elementId, message, type) {
    const el = document.getElementById(elementId);
    el.innerHTML = '<div class="status-message ' + type + '"><i class="fa fa-' + 
        (type === 'loading' ? 'spinner fa-spin' : type === 'success' ? 'check-circle' : 'exclamation-circle') + 
        '"></i>' + message + '</div>';
}

function resetSystemPrompt() {
    if (!confirm('<?php echo $tpl->__('aiassistant.settings.prompt.confirm_reset'); ?>')) {
        return;
    }
    
    jQuery.ajax({
        url: '<?php echo BASE_URL; ?>/AIAssistant/settings',
        method: 'POST',
        data: { action: 'resetPrompt' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                document.getElementById('system_prompt').value = response.prompt;
                alert('<?php echo $tpl->__('aiassistant.settings.prompt.reset_success'); ?>');
            }
        }
    });
}

function loadOllamaModels() {
    const url = document.getElementById('ollama_url').value;
    const selectEl = document.getElementById('ollama_model');
    
    if (!url) {
        alert('<?php echo $tpl->__('aiassistant.settings.error.enterurl'); ?>');
        return;
    }
    
    showStatus('load-models-status', '<?php echo $tpl->__('aiassistant.settings.loading'); ?>...', 'loading');
    
    jQuery.ajax({
        url: '<?php echo BASE_URL; ?>/AIAssistant/settings',
        method: 'POST',
        data: { action: 'loadModels', url: url },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                selectEl.innerHTML = '<option value=""><?php echo $tpl->__('aiassistant.settings.ollama.model.placeholder'); ?></option>';
                response.models.forEach(function(model) {
                    const option = document.createElement('option');
                    option.value = model;
                    option.textContent = model;
                    selectEl.appendChild(option);
                });
                showStatus('load-models-status', response.models.length + ' models loaded', 'success');
            } else {
                showStatus('load-models-status', response.message, 'error');
            }
        },
        error: function() {
            showStatus('load-models-status', '<?php echo $tpl->__('aiassistant.settings.error.connectionfailed'); ?>', 'error');
        }
    });
}

function loadOpenAIModels() {
    const apiKey = document.getElementById('openai_api_key').value;
    const baseUrl = document.getElementById('openai_base_url').value;
    const selectEl = document.getElementById('openai_model');
    
    if (!apiKey) {
        alert('<?php echo $tpl->__('aiassistant.settings.error.enterapikey'); ?>');
        return;
    }
    
    showStatus('load-openai-models-status', '<?php echo $tpl->__('aiassistant.settings.loading'); ?>...', 'loading');
    
    jQuery.ajax({
        url: '<?php echo BASE_URL; ?>/AIAssistant/settings',
        method: 'POST',
        data: { action: 'loadOpenAIModels', api_key: apiKey, base_url: baseUrl },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                selectEl.innerHTML = '<option value=""><?php echo $tpl->__('aiassistant.settings.openai.model.placeholder'); ?></option>';
                response.models.forEach(function(model) {
                    const option = document.createElement('option');
                    option.value = model;
                    option.textContent = model;
                    selectEl.appendChild(option);
                });
                showStatus('load-openai-models-status', response.models.length + ' models loaded', 'success');
            } else {
                showStatus('load-openai-models-status', response.message, 'error');
            }
        },
        error: function() {
            showStatus('load-openai-models-status', '<?php echo $tpl->__('aiassistant.settings.error.connectionfailed'); ?>', 'error');
        }
    });
}

function testOllamaConnection() {
    const url = document.getElementById('ollama_url').value;
    const model = document.getElementById('ollama_model').value;
    
    if (!url || !model) {
        alert('<?php echo $tpl->__('aiassistant.settings.error.urlmodel'); ?>');
        return;
    }
    
    showStatus('ollama-test-status', '<?php echo $tpl->__('aiassistant.settings.testing'); ?>...', 'loading');
    
    jQuery.ajax({
        url: '<?php echo BASE_URL; ?>/AIAssistant/settings',
        method: 'POST',
        data: { action: 'testConnection', provider: 'ollama', url: url, model: model },
        dataType: 'json',
        success: function(response) {
            showStatus('ollama-test-status', response.message, response.success ? 'success' : 'error');
        },
        error: function() {
            showStatus('ollama-test-status', '<?php echo $tpl->__('aiassistant.settings.error.connectionfailed'); ?>', 'error');
        }
    });
}

function testOpenAIConnection() {
    const apiKey = document.getElementById('openai_api_key').value;
    const baseUrl = document.getElementById('openai_base_url').value;
    
    if (!apiKey) {
        alert('<?php echo $tpl->__('aiassistant.settings.error.enterapikey'); ?>');
        return;
    }
    
    showStatus('openai-test-status', '<?php echo $tpl->__('aiassistant.settings.testing'); ?>...', 'loading');
    
    jQuery.ajax({
        url: '<?php echo BASE_URL; ?>/AIAssistant/settings',
        method: 'POST',
        data: { action: 'testConnection', provider: 'openai', api_key: apiKey, base_url: baseUrl },
        dataType: 'json',
        success: function(response) {
            showStatus('openai-test-status', response.message, response.success ? 'success' : 'error');
        },
        error: function() {
            showStatus('openai-test-status', '<?php echo $tpl->__('aiassistant.settings.error.connectionfailed'); ?>', 'error');
        }
    });
}
</script>
