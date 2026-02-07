-- AI Assistant Categories Table
-- For custom user-defined categories

CREATE TABLE IF NOT EXISTS `zp_aiassistant_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `icon` VARCHAR(50) NOT NULL DEFAULT '📋',
    `color` VARCHAR(20) NOT NULL DEFAULT '#6B7280',
    `keywords` TEXT,
    `is_default` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default categories
INSERT INTO `zp_aiassistant_categories` (`name`, `icon`, `color`, `keywords`, `is_default`) VALUES
('bestellung', '🛒', '#3B82F6', 'bestellen,bestellung,order,kaufen,einkauf', 1),
('anfrage', '❓', '#10B981', 'anfrage,frage,info,information,auskunft', 1),
('reklamation', '⚠️', '#EF4444', 'reklamation,beschwerde,problem,defekt,kaputt', 1),
('angebot', '💰', '#F59E0B', 'angebot,offerte,quote,preis', 1),
('rechnung', '📄', '#8B5CF6', 'rechnung,invoice,bezahlen,zahlung', 1),
('lagerpruefung', '📦', '#FBBF24', 'lager,bestand,verfügbarkeit,stock,inventory', 1),
('followup', '🔔', '#06B6D4', 'followup,nachfassen,rückruf,erinnern,reminder', 1),
('lieferant', '🏭', '#6B7280', 'lieferant,supplier,hersteller,vendor', 1)
ON DUPLICATE KEY UPDATE 
    `icon` = VALUES(`icon`),
    `color` = VALUES(`color`),
    `keywords` = VALUES(`keywords`),
    `is_default` = VALUES(`is_default`);
