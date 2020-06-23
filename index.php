<?php
/**
 * @defgroup plugins_generic_documentMetadataChecklist
 */
/**
 * @file plugins/generic/documentMetadataChecklist/index.php
 *
 * @ingroup plugins_generic_documentMetadataChecklist
 * @brief Wrapper for the Document Metadata Checklist plugin.
 *
 */
require __DIR__ . '/vendor/autoload.php';
require_once('DocumentMetadataChecklistPlugin.inc.php');
return new DocumentMetadataChecklistPlugin();