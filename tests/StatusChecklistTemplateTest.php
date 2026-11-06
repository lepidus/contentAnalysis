<?php

use PHPUnit\Framework\TestCase;

class StatusChecklistTemplateTest extends TestCase
{
    private const MALICIOUS_TITLE = '<img src=x onerror=alert(1)> {{ 7*7 }}';

    private $compileDir;

    public function setUp(): void
    {
        $this->compileDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'contentAnalysisTemplateTest_' . uniqid();
        mkdir($this->compileDir);
    }

    public function tearDown(): void
    {
        foreach (glob($this->compileDir . DIRECTORY_SEPARATOR . '*') as $compiledFile) {
            unlink($compiledFile);
        }
        rmdir($this->compileDir);
    }

    private function renderChecklist(array $variables): string
    {
        $smarty = new Smarty();
        $smarty->setCompileDir($this->compileDir);
        // Like PKPTemplateManager::smartyTranslate, the parameters are inserted in the message without escaping
        $smarty->registerPlugin('function', 'translate', function ($params) {
            $key = $params['key'];
            unset($params['key']);
            return $key . (empty($params) ? '' : ':' . implode('|', $params));
        });
        $smarty->assign(array_merge([
            'placedOn' => 'workflow',
            'generalStatus' => 'Error',
            'orcidStatus' => 'Success',
        ], $variables));

        return $smarty->fetch('file:' . dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'statusChecklist.tpl');
    }

    public function testEnglishTitleIsEscapedInErrorMessage(): void
    {
        $output = $this->renderChecklist([
            'titleEnglishStatus' => 'Error',
            'titleInEnglish' => self::MALICIOUS_TITLE,
        ]);

        $this->assertStringNotContainsString('<img', $output);
        $this->assertStringContainsString(
            '<span v-pre>plugins.generic.contentAnalysis.status.titleEnglishError:&lt;img src=x onerror=alert(1)&gt; {{ 7*7 }}</span>',
            $output
        );
    }
}
