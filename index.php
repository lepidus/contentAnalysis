<?php
/**
 * @defgroup plugins_generic_contentAnalysis
 */
/**
 * @file plugins/generic/contentAnalysis/index.php
 *
 * Copyright (c) 2020-2021 Lepidus Tecnologia
 * Copyright (c) 2020-2021 SciELO
 * Distributed under the GNU GPL v3. For full terms see LICENSE or https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @ingroup plugins_generic_contentAnalysis
 * @brief Wrapper for the Content Analysis plugin.
 *
 */

require_once('ContentAnalysisPlugin.inc.php');
return new ContentAnalysisPlugin();
