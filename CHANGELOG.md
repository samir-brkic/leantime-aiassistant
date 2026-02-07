# Changelog

All notable changes to the Leantime AI Assistant Plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2026-02-07

### 🐛 Fixed
- **Tags now correctly saved**: Tags were passed to `quickAddTicket()` but ignored by Leantime. Now saved separately via `patchTicket()` after task creation. Works for both main tasks and subtasks.

### ✨ Added
- **Category as tag**: Categories are now saved as the first tag with emoji (e.g., "🛒 Kundenbestellung") instead of a description badge. This makes them filterable and searchable via Leantime's native tag system.
- **Dynamic date replacement**: System prompt now supports `{{CURRENT_DATE}}` placeholder which is replaced with the current date before sending to AI. This helps AI calculate relative deadlines accurately.

### 🔄 Changed
- **New industry-specific system prompt**: Rewritten default prompt specialized for signage/glass/fastening industry with 6 precise categories (kundenbestellung, einkauf, anfrage, reklamation, buchhaltung, organisation).
- **Cleaner task descriptions**: Category badge removed from description field. Descriptions now contain only the actual task content + AI-generated note.
- **Improved category logic**: Better title format guidelines, clearer priority assignments, and explicit deadline calculation instructions for AI.

### 🔧 Technical
- `Services/AIAssistant.php`: `getSystemPrompt()` now replaces `{{CURRENT_DATE}}` placeholder
- `Services/AIAssistant.php`: `getDefaultSystemPrompt()` completely rewritten with industry focus
- `Services/TaskGenerator.php`: `saveTags()` extended with category parameter
- `Services/TaskGenerator.php`: `formatDescription()` simplified (no category badge)
- `Services/TaskGenerator.php`: `createMainTask()` passes category to `saveTags()`

## [1.0.0] - 2026-02-07

### 🎉 Initial Release

- ✨ AI-powered task creation with Ollama/OpenAI support
- 🎯 Smart categorization (8 business categories)
- 🏷️ Auto-tagging and deadline parsing
- 📝 Subtask generation for complex tasks
- ✏️ Editable preview before task creation
- 🎨 Leantime native design
- 🌍 Multilingual support (DE/EN)
- 📦 Database-driven categories system
- ⚙️ Configurable system prompt
- 🧪 Fully tested and production ready

[1.1.0]: https://github.com/samir-brkic/leantime-aiassistant/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/samir-brkic/leantime-aiassistant/releases/tag/v1.0.0
