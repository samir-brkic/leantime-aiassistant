-- AI Assistant Plugin - Database Migration
-- Creates settings table for storing AI provider configuration

CREATE TABLE IF NOT EXISTS `zp_aiassistant_settings` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(255) NOT NULL,
    `value` TEXT DEFAULT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default values
INSERT INTO `zp_aiassistant_settings` (`setting_key`, `value`) VALUES
('provider', 'ollama'),
('ollama_url', 'http://192.168.200.40:11434'),
('ollama_model', ''),
('openai_api_key', ''),
('openai_base_url', 'https://api.openai.com/v1'),
('openai_model', 'gpt-4'),
('timeout', '30')
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;
