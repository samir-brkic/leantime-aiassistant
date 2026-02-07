# AIAssistant Plugin - Installation Guide

## Voraussetzungen

- ✅ Leantime 3.x oder höher
- ✅ PHP 8.1 oder höher
- ✅ MySQL/MariaDB Datenbank
- ✅ Ollama-Server ODER OpenAI API Key

---

## Installation

### Schritt 1: Plugin kopieren

Kopiere den kompletten `AIAssistant` Ordner in das Leantime Plugins-Verzeichnis:

```bash
cp -r AIAssistant /pfad/zu/leantime/app/Plugins/
```

Oder bei Docker:
```bash
cp -r AIAssistant /pfad/zum/volume/plugins/
```

### Schritt 2: Berechtigungen setzen

```bash
chown -R www-data:www-data /pfad/zu/leantime/app/Plugins/AIAssistant
chmod -R 755 /pfad/zu/leantime/app/Plugins/AIAssistant
```

### Schritt 3: Plugin aktivieren

1. In Leantime als Administrator einloggen
2. Navigiere zu: **Einstellungen → Plugins**
3. Finde **AIAssistant** in der Liste
4. Klicke auf **Aktivieren**

Die Datenbanktabellen werden automatisch erstellt:
- `zp_aiassistant_settings`
- `zp_aiassistant_categories` (mit 8 Standard-Kategorien)

### Schritt 4: AI Provider konfigurieren

Navigiere zu: **AI Assistant → Settings**

#### Option A: Ollama (lokal, kostenlos)

1. **Provider:** Wähle "Ollama"
2. **Ollama URL:** 
   - Lokal: `http://localhost:11434`
   - Docker: `http://host.docker.internal:11434`
   - Remote: `http://ihr-server:11434`
3. **Modell auswählen:** Dropdown zeigt verfügbare Modelle
4. **Speichern**

#### Option B: OpenAI (kostenpflichtig)

1. **Provider:** Wähle "OpenAI"
2. **OpenAI URL:** `https://api.openai.com/v1` (Standard)
3. **API Key:** Deinen OpenAI API Key eingeben
4. **Modell auswählen:** Dropdown zeigt verfügbare GPT-Modelle
5. **Speichern**

### Schritt 5: Fertig!

Navigiere zu: **AI Assistant → Quick Capture**

Das Plugin ist jetzt einsatzbereit! 🎉

---

## Features

### Quick Capture
- Freetext-Notizen in strukturierte Tasks konvertieren
- AI-gestützte Analyse mit 8 Business-Kategorien
- Automatische Subtask-Erstellung (bei komplexen Aufgaben)
- Deadline-Parsing (z.B. "bis morgen", "in 2 Wochen")
- Tag-Generierung
- Prioritäts-Erkennung
- Editierbare Preview vor Task-Erstellung

### Settings
- Provider-Wahl (Ollama/OpenAI)
- Modell-Auswahl (dynamisch)
- Timeout-Konfiguration
- System-Prompt anpassbar
- Test-Funktion für Verbindung

### Kategorien (editierbar im Backend)
1. 🎨 **Design:** UI/UX, Frontend, Wireframes
2. 🔧 **Development:** Code, API, Backend
3. 🐛 **Bug:** Fehler, Issues, Fixes
4. 📋 **Planning:** Konzepte, Roadmaps, Meetings
5. 📄 **Documentation:** Docs, Wiki, README
6. 🧪 **Testing:** QA, Unit-Tests, E2E-Tests
7. 🚀 **Deployment:** Release, CI/CD, DevOps
8. 💬 **Communication:** Team-Updates, Reviews

---

## Troubleshooting

### Plugin erscheint nicht im Menü

1. Cache löschen:
```bash
rm -rf /pfad/zu/leantime/cache/framework/*
```

2. Browser-Cache löschen (Ctrl+Shift+R)

### "AI provider not configured"

- Gehe zu **Settings** und konfiguriere Ollama URL oder OpenAI API Key
- Teste Verbindung mit Test-Button
- Prüfe ob Ollama-Server erreichbar ist: `curl http://localhost:11434/api/tags`

### Ollama Verbindung fehlschlägt (Docker)

Wenn Leantime in Docker läuft und Ollama auf dem Host:
- URL: `http://host.docker.internal:11434`
- Nicht: `http://localhost:11434`

---

## Lizenz

Siehe LICENSE Datei.

---

**Version:** 1.0.0  
**Kompatibilität:** Leantime 3.x  
**Letzte Aktualisierung:** 2026-02-07
