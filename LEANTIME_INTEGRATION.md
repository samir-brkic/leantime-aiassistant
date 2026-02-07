# Leantime Integration - Wichtige Erkenntnisse

## Prioritäts-System

**Quelle:** `/var/www/html/app/Domain/Tickets/Repositories/Tickets.php`

```php
public array $priority = [
    '1' => 'Critical', 
    '2' => 'High', 
    '3' => 'Medium', 
    '4' => 'Low', 
    '5' => 'Lowest'
];
```

**Verwendung für AI Assistant:**
- AI-Output muss Integer (1-5) zurückgeben
- Standard: 3 (Medium)
- Mapping:
  - "dringend" → 1 (Critical)
  - "hoch" → 2 (High)
  - "normal" → 3 (Medium)
  - "niedrig" → 4 (Low)

## Ticket Types

```php
public array $type = ['task', 'subtask', 'story', 'bug'];
```

**Verwendung:**
- Haupttasks: `task`
- Subtasks: `subtask`

## Ticket Service

**Namespace:** `Leantime\Domain\Tickets\Services\Tickets`

**Wichtige Methoden:**
- `getPriorityLabels()` - Gibt Priority-Array zurück
- CRUD-Methoden für Tickets (via Repository)

**Service Resolution:**
```php
$ticketService = app()->make(\Leantime\Domain\Tickets\Services\Tickets::class);
```

## Best Practices

1. **Dependency Injection:** Immer via Constructor
2. **Service Resolution:** `app()->make()` nutzen
3. **Keine direkte DB-Zugriffe:** Immer über Repository
4. **Type Hints:** Überall verwenden
