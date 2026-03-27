# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Content Analysis Plugin for OPS (Open Preprint Systems) 3.5. It analyzes submitted PDF documents for required elements (authors' contributions, conflict of interest, ethics committee statements, ORCIDs, English metadata, data availability) and presents a checklist report during the submission workflow.

## Development Commands

### Prerequisites
- `poppler-utils` must be installed (provides `pdftotext` for PDF text extraction)
- Plugin lives at `plugins/generic/contentAnalysis/` within an OPS installation
- Node.js required for frontend build

### Running Unit Tests
From the OPS root directory:
```bash
php lib/pkp/lib/vendor/phpunit/phpunit/phpunit --configuration lib/pkp/tests/phpunit.xml plugins/generic/contentAnalysis/tests/
```
Run a single test file:
```bash
php lib/pkp/lib/vendor/phpunit/phpunit/phpunit --configuration lib/pkp/tests/phpunit.xml plugins/generic/contentAnalysis/tests/ConflictInterestTest.php
```

### Frontend Build
From the plugin directory:
```bash
npm install
npm run build        # Production build
npm run dev          # Watch mode (development)
```

### Cypress E2E Tests
Tests are in `cypress/tests/` and run via the shared CI templates.

## Architecture

### Core Pipeline: PDF -> Words -> Pattern Matching -> Checklist

1. **ContentParser** (`classes/ContentParser.php`) - Extracts text from PDFs via `shell_exec('pdftotext ...')`, converts to a word list, and cleans styled text. Handles Unicode, punctuation stripping, line number removal, and stop word filtering.

2. **DocumentChecker** (`classes/DocumentChecker.php`) - The pattern matching engine. Uses `similar_text()` with configurable similarity thresholds (75%-92%) to fuzzy-match multilingual patterns (PT/EN/ES) against the parsed word list. Also validates ORCID checksums. Each check method returns a status string: `'Success'`, `'Error'`, `'Warning'`, `'Skipped'`, or `'Unable'`.

3. **DocumentChecklist** (`classes/DocumentChecklist.php`) - Orchestrator. Decides which checks to run based on document type (article vs. non-article), author count, and ethics flags. Returns an aggregate status array.

### Plugin Integration

- **ContentAnalysisPlugin.php** - Main entry point. Hooks into OPS submission workflow (details step, review step), validates submissions, registers API routes via `Dispatcher::dispatch` hook, extends the submission schema with custom fields, and registers Vue 3 workflow assets.
- **ContentAnalysisController** (`classes/api/v1/ContentAnalysisController.php`) - Laravel-based REST API controller extending `PKPBaseController`. Provides `PUT saveForm/{submissionId}` for saving form data and `GET checklist/{submissionId}` for retrieving checklist results as JSON.
- **ContentAnalysisForm** (`classes/components/forms/`) - PKP FormComponent with radio buttons for ethics council involvement and document type.

### Frontend (Vue 3)

- **main.js** (`resources/js/main.js`) - Registers Vue component and extends workflow store to add "Document verification" tab.
- **ContentAnalysisChecklist.vue** (`resources/js/components/`) - Vue 3 Composition API component that fetches checklist data via API and renders status indicators.
- Built with Vite, outputs to `public/build/`.
- Locale keys auto-extracted via `i18nExtractKeys.vite.js` to `registry/uiLocaleKeysBackend.json`.

### Pattern Matching Details

Each checker method defines pattern arrays for a specific element and uses a two-threshold system:
- **Similarity threshold** (per-word): minimum `similar_text()` percentage to consider a word matched
- **Pattern threshold** (overall): minimum percentage of pattern words that must match in sequence

Different checks use different thresholds (e.g., keywords/abstract use 92% similarity, contribution/conflict use 75%).

## Localization

Three languages: English (`en`), Spanish (`es`), Brazilian Portuguese (`pt_BR`). Translation files use GNU PO format in `locale/`. Translation keys follow `plugins.generic.contentAnalysis.*`.

## Namespace

All PHP classes use `APP\plugins\generic\contentAnalysis\` namespace with PSR-4 autoloading.
